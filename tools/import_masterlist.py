#!/usr/bin/env python3
"""
Import the FMO Complete Masterlist (.xlsx) into the app's SQLite database.

What it does:
  * Reads the "Master List" sheet of the masterlist workbook.
  * Normalizes messy source data:
      - DATE OF BIRTH  -> YYYY-MM-DD (handles MM/DD/YYYY strings and Excel dates)
      - SEX            -> 'Male' / 'Female'  (matches the app's queries)
      - ADDRESS        -> barangay name (text before first comma, Title Cased)
      - CONTACT NUMBER -> 11-digit 09xxxxxxxxx where possible
      - CATEGORY       -> the 6 boolean activity flags (fuzzy keyword match)
  * De-duplicates: skips IDs already in the DB and repeated IDs within the file.
  * Copies the referenced PHOTO / SIGNATURE files (case-insensitive match) into
    public/uploads/, overwriting, using the filename stored in the DB so the
    app always resolves them.
  * Inserts new rows only. Re-running is safe (idempotent on id_number).

Usage:
    python3 tools/import_masterlist.py [--dry-run]
"""
import argparse
import datetime
import os
import re
import shutil
import sqlite3
import sys

import openpyxl

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
XLSX = os.path.join(ROOT, "masterlist", "0001. Complete Masterlist.xlsx")
DB = os.path.join(ROOT, "data", "fisherfolk.sqlite")
SCHEMA = os.path.join(ROOT, "sql", "schema.sqlite.sql")
PHOTO_SRC = os.path.join(ROOT, "masterlist", "PHOTO")
SIG_SRC = os.path.join(ROOT, "masterlist", "SIGNATURE")
UPLOADS = os.path.join(ROOT, "public", "uploads")
SHEET = "Master List"

# Column order in the Master List sheet (0-based)
C_ID, C_NAME, C_DOB, C_ADDR, C_SEX, C_IMG, C_SIG, C_RSBSA, C_CAT, C_CONTACT = range(10)


def norm_dob(value):
    """Return YYYY-MM-DD or None."""
    if value is None or value == "":
        return None
    if isinstance(value, (datetime.datetime, datetime.date)):
        return value.strftime("%Y-%m-%d")
    s = str(value).strip()
    # Expected source format: MM/DD/YYYY
    m = re.match(r"^(\d{1,2})[/\-](\d{1,2})[/\-](\d{2,4})$", s)
    if m:
        mm, dd, yy = (int(x) for x in m.groups())
        if yy < 100:
            yy += 1900 if yy > 30 else 2000
        try:
            return datetime.date(yy, mm, dd).strftime("%Y-%m-%d")
        except ValueError:
            return None
    return None


def norm_sex(value):
    s = (str(value).strip().lower() if value is not None else "")
    if s.startswith("m"):
        return "Male"
    if s.startswith("f"):
        return "Female"
    return ""


_ROMAN = {"i", "ii", "iii", "iv", "v", "vi", "vii", "viii", "ix", "x"}


def norm_barangay(value):
    """Take text before first comma, collapse spaces, Title Case.
    Roman-numeral suffixes are kept uppercase (e.g. 'Nag-Iba II')."""
    if not value:
        return ""
    s = str(value).split(",")[0]
    s = re.sub(r"\s+", " ", s).strip().title()
    return " ".join(w.upper() if w.lower() in _ROMAN else w for w in s.split())


def norm_contact(value):
    if value is None or value == "":
        return None
    digits = re.sub(r"\D", "", str(value))
    if not digits:
        return None
    if len(digits) == 10 and digits.startswith("9"):
        digits = "0" + digits
    return digits


def category_flags(value):
    """Map the free-text CATEGORY column to the 6 boolean flags."""
    s = (str(value).lower() if value else "")
    return {
        "boat_owneroperator": int("boat owner" in s),
        "capture_fishing": int("capture fish" in s),
        "gleaning": int("gleaning" in s or "gleaner" in s),
        "vendor": int("vend" in s),                       # vendor / vending
        "fish_processing": int("processing" in s),
        "aquaculture": int("aquaculture" in s),
    }


def build_index(folder):
    """lowercase-filename -> actual filename, for case-insensitive lookup."""
    if not os.path.isdir(folder):
        return {}
    return {f.lower(): f for f in os.listdir(folder)}


def resolve_and_copy(declared, src_index, src_dir, dest_dir, dry):
    """
    declared: filename as written in the xlsx (e.g. '2025-...-08252.JPG')
    Copies the matching source file (case-insensitive) to dest_dir using the
    declared name. Returns the declared name if copied, else None.
    """
    if not declared:
        return None, "blank"
    key = str(declared).strip().lower()
    actual = src_index.get(key)
    if not actual:
        return None, "missing"
    if not dry:
        shutil.copyfile(os.path.join(src_dir, actual),
                        os.path.join(dest_dir, str(declared).strip()))
    return str(declared).strip(), "copied"


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--dry-run", action="store_true",
                    help="Parse and report, but write nothing.")
    args = ap.parse_args()
    dry = args.dry_run

    if not dry:
        os.makedirs(os.path.dirname(DB), exist_ok=True)
        os.makedirs(UPLOADS, exist_ok=True)

    # --- DB + schema ---
    conn = sqlite3.connect(":memory:" if dry else DB)
    with open(SCHEMA) as fh:
        conn.executescript(fh.read())
    existing = {r[0] for r in conn.execute("SELECT id_number FROM fisherfolk")}
    print(f"DB already has {len(existing)} rows")

    photo_idx = build_index(PHOTO_SRC)
    sig_idx = build_index(SIG_SRC)

    wb = openpyxl.load_workbook(XLSX, read_only=True, data_only=True)
    ws = wb[SHEET]

    seen = set()
    stats = dict(inserted=0, dup_in_file=0, dup_in_db=0, no_id=0,
                 img_copied=0, img_missing=0, sig_copied=0, sig_missing=0,
                 no_dob=0)
    barangays = set()
    cat_totals = dict(boat_owneroperator=0, capture_fishing=0, gleaning=0,
                      vendor=0, fish_processing=0, aquaculture=0)

    insert_sql = """INSERT INTO fisherfolk
        (id_number, full_name, date_of_birth, address, sex, image, signature,
         rsbsa, contact_number, boat_owneroperator, capture_fishing, gleaning,
         vendor, fish_processing, aquaculture)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"""

    for row in ws.iter_rows(min_row=2, values_only=True):
        idv = row[C_ID]
        if idv in (None, ""):
            stats["no_id"] += 1
            continue
        idn = str(idv).strip()
        if idn in seen:
            stats["dup_in_file"] += 1
            continue
        seen.add(idn)
        if idn in existing:
            stats["dup_in_db"] += 1
            continue

        dob = norm_dob(row[C_DOB])
        if dob is None:
            stats["no_dob"] += 1
        sex = norm_sex(row[C_SEX])
        brgy = norm_barangay(row[C_ADDR])
        if brgy:
            barangays.add(brgy)
        contact = norm_contact(row[C_CONTACT])
        flags = category_flags(row[C_CAT])
        for k, v in flags.items():
            cat_totals[k] += v

        img, ist = resolve_and_copy(row[C_IMG], photo_idx, PHOTO_SRC, UPLOADS, dry)
        sig, sst = resolve_and_copy(row[C_SIG], sig_idx, SIG_SRC, UPLOADS, dry)
        stats["img_copied"] += (ist == "copied")
        stats["img_missing"] += (ist == "missing")
        stats["sig_copied"] += (sst == "copied")
        stats["sig_missing"] += (sst == "missing")

        rsbsa = str(row[C_RSBSA]).strip() if row[C_RSBSA] else None
        name = str(row[C_NAME]).strip() if row[C_NAME] else ""

        if not dry:
            conn.execute(insert_sql, (
                idn, name, dob, brgy, sex, img, sig, rsbsa, contact,
                flags["boat_owneroperator"], flags["capture_fishing"],
                flags["gleaning"], flags["vendor"], flags["fish_processing"],
                flags["aquaculture"],
            ))
        stats["inserted"] += 1

    if not dry:
        conn.commit()
    total = conn.execute("SELECT COUNT(*) FROM fisherfolk").fetchone()[0]
    conn.close()

    print("\n=== IMPORT SUMMARY" + (" (DRY RUN)" if dry else "") + " ===")
    for k, v in stats.items():
        print(f"  {k:14s}: {v}")
    print(f"  distinct barangays: {len(barangays)}")
    print("  category totals:")
    for k, v in cat_totals.items():
        print(f"      {k:18s}: {v}")
    print(f"  TOTAL rows in DB now: {total}")


if __name__ == "__main__":
    main()
