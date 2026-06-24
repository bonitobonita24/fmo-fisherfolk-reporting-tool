#!/usr/bin/env python3
"""
Incrementally import NEW fisherfolk from a partial masterlist xlsx (e.g. an
"EditingPC" batch) into the SQLite DB. Existing IDs are skipped (never updated).

Photos/signatures are resolved by fisherfolk ID from, in priority order:
  1. masterlist/PHOTO and masterlist/SIGNATURE (if present)
  2. public/uploads (already on disk, incl. previous "orphan" files)
  3. the masterlist backup zip
The matched file is copied into public/uploads as "<id>.<ext>" and stored on
the record. Reuses the normalization rules from import_masterlist.py.

Usage:
    python3 tools/import_incremental.py "masterlist/0001. Complete Masterlist - EditingPC.xlsx" [--backup-zip PATH] [--dry-run]
"""
import argparse, os, re, sqlite3, sys, zipfile
import openpyxl

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from import_masterlist import (  # reuse normalization + constants
    norm_dob, norm_sex, norm_barangay, norm_contact, category_flags,
    C_ID, C_NAME, C_DOB, C_ADDR, C_SEX, C_IMG, C_SIG, C_RSBSA, C_CAT, C_CONTACT,
    DB, UPLOADS, PHOTO_SRC, SIG_SRC, ROOT,
)

SHEET = "Master List"
norm = lambda s: re.sub(r'[^a-z0-9]', '', str(s).lower())
stem = lambda p: os.path.splitext(os.path.basename(p))[0]


def build_indexes(backup_zip):
    """normalized-id -> ('fs', path) or ('zip', member). Photos = jpg/jpeg, sigs = png."""
    photo, sig = {}, {}

    def add(d, key, val):
        d.setdefault(key, val)

    # 1. masterlist/PHOTO + SIGNATURE (highest priority)
    if os.path.isdir(PHOTO_SRC):
        for f in os.listdir(PHOTO_SRC):
            add(photo, norm(stem(f)), ('fs', os.path.join(PHOTO_SRC, f)))
    if os.path.isdir(SIG_SRC):
        for f in os.listdir(SIG_SRC):
            add(sig, norm(stem(f)), ('fs', os.path.join(SIG_SRC, f)))
    # 2. public/uploads
    for f in os.listdir(UPLOADS):
        ext = os.path.splitext(f)[1].lower()
        if ext in ('.jpg', '.jpeg'):
            add(photo, norm(stem(f)), ('fs', os.path.join(UPLOADS, f)))
        elif ext == '.png':
            add(sig, norm(stem(f)), ('fs', os.path.join(UPLOADS, f)))
    # 3. backup zip
    if backup_zip and os.path.exists(backup_zip):
        zf = zipfile.ZipFile(backup_zip)
        for m in zf.namelist():
            if m.endswith('/'):
                continue
            if m.startswith("masterlist/PHOTO/"):
                add(photo, norm(stem(m)), ('zip', (backup_zip, m)))
            elif m.startswith("masterlist/SIGNATURE/"):
                add(sig, norm(stem(m)), ('zip', (backup_zip, m)))
        zf.close()
    return photo, sig


def fetch_bytes(ref):
    kind, loc = ref
    if kind == 'fs':
        with open(loc, 'rb') as fh:
            return fh.read()
    zip_path, member = loc
    zf = zipfile.ZipFile(zip_path)
    try:
        return zf.read(member)
    finally:
        zf.close()


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("xlsx")
    ap.add_argument("--backup-zip",
                    default=os.path.join(os.path.dirname(ROOT), "masterlist-backup-2026-06-24.zip"))
    ap.add_argument("--dry-run", action="store_true")
    args = ap.parse_args()

    con = sqlite3.connect(DB)
    existing = {r[0] for r in con.execute("SELECT id_number FROM fisherfolk")}

    photo_idx, sig_idx = build_indexes(args.backup_zip)

    ws = openpyxl.load_workbook(args.xlsx, read_only=True, data_only=True)[SHEET]
    insert_sql = """INSERT INTO fisherfolk
        (id_number, full_name, date_of_birth, address, sex, image, signature,
         rsbsa, contact_number, boat_owneroperator, capture_fishing, gleaning,
         vendor, fish_processing, aquaculture)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"""

    inserted = skipped_existing = img_linked = sig_linked = 0
    seen = set()
    rows_out = []
    for row in ws.iter_rows(min_row=2, values_only=True):
        if row[C_ID] in (None, ""):
            continue
        idn = str(row[C_ID]).strip()
        if idn in existing:
            skipped_existing += 1
            continue
        if idn in seen:
            continue
        seen.add(idn)

        k = norm(idn)
        img = sig = None
        if k in photo_idx:
            ext = os.path.splitext(photo_idx[k][1] if photo_idx[k][0] == 'fs' else photo_idx[k][1][1])[1] or '.jpg'
            img = f"{idn}{ext}"
            if not args.dry_run:
                open(os.path.join(UPLOADS, img), 'wb').write(fetch_bytes(photo_idx[k]))
            img_linked += 1
        if k in sig_idx:
            ext = os.path.splitext(sig_idx[k][1] if sig_idx[k][0] == 'fs' else sig_idx[k][1][1])[1] or '.png'
            sig = f"{idn}{ext}"
            if not args.dry_run:
                open(os.path.join(UPLOADS, sig), 'wb').write(fetch_bytes(sig_idx[k]))
            sig_linked += 1

        flags = category_flags(row[C_CAT])
        params = (
            idn, str(row[C_NAME]).strip() if row[C_NAME] else "",
            norm_dob(row[C_DOB]), norm_barangay(row[C_ADDR]), norm_sex(row[C_SEX]),
            img, sig, str(row[C_RSBSA]).strip() if row[C_RSBSA] else None,
            norm_contact(row[C_CONTACT]),
            flags["boat_owneroperator"], flags["capture_fishing"], flags["gleaning"],
            flags["vendor"], flags["fish_processing"], flags["aquaculture"],
        )
        if not args.dry_run:
            con.execute(insert_sql, params)
        inserted += 1
        rows_out.append((idn, params[1], params[3], 'P' if img else '-', 'S' if sig else '-'))

    if not args.dry_run:
        con.commit()
    total = con.execute("SELECT COUNT(*) FROM fisherfolk").fetchone()[0]
    con.close()

    print("=== INCREMENTAL IMPORT" + (" (DRY RUN)" if args.dry_run else "") + " ===")
    print(f"file: {args.xlsx}")
    for idn, name, brgy, p, s in rows_out:
        print(f"  +[{p}{s}] {idn:22s} {name:30s} {brgy}")
    print(f"\ninserted={inserted}  skipped_existing={skipped_existing}  "
          f"photos_linked={img_linked}  sigs_linked={sig_linked}")
    print(f"TOTAL rows in DB now: {total}")


if __name__ == "__main__":
    main()
