# FMO Fisherfolk Reporting Tool — Handover

> Handover snapshot as of **2026-06-24**, for the AIEF framework / any new operator.
> A mirror of this lives in the local Claude memory store
> (`~/.claude/projects/<this-project>/memory/aief-handover.md`).
> Repo: `~/UbuntuDevFiles/FMO-CalapanCity/fmo-fisherfolk-reporting-tool`.

## 1. What it is
Read-only dashboard for the Calapan City Fisheries Management Office (FMO): registered
fisherfolk with stats (per-barangay, gender, age, activity category) and a
searchable/filterable list with photos & signatures. Built by Powerbyte IT Solutions.
**2,976 fisherfolk** across **51 barangay labels**.

## 2. Stack (current)
Vanilla **PHP 8.3** (no framework, no composer) + **SQLite** (PDO) + plain HTML/JS
frontend (Bootstrap/Tailwind CDN, Chart.js, Font Awesome). Served by **Apache (mod_php)**
in Docker, or `php -S` locally. No package manager.

## 3. Work completed (2026-06-24)
1. **MySQL → SQLite migration.** DB is a single file `data/fisherfolk.sqlite` (gitignored, PII).
   Schema `sql/schema.sqlite.sql`; `config/database-auto.php` auto-creates it. Ported MySQL-only
   SQL in `age-group-stats.php` (TIMESTAMPDIFF/CURDATE→strftime) and `summary-stats.php`
   (DATE_SUB/NOW→datetime). Old `sql/schema.sql` / `sample_data.sql` are stale/unused.
2. **Masterlist import.** `tools/import_masterlist.py` parses the FMO Excel into SQLite with
   normalization (DOB→YYYY-MM-DD, sex→Male/Female, address→barangay Title Case,
   contact→09xxxxxxxxx, free-text CATEGORY→6 boolean activity flags) and dedup (in-file +
   in-DB; idempotent). Copies photo/signature files into `public/uploads/` matched by
   fisherfolk ID. Supports `--dry-run`.
3. **Asset reconciliation.** 2,954/2,976 have photos, 2,965/2,976 signatures. Recovered 14
   mis-linked assets by ID. 22 photos + 11 sigs genuinely missing (no source file) →
   `data/missing_assets_report.csv`. ~184 orphan photos/sigs belong to people NOT in the
   masterlist (unlinkable). Source xlsx + PHOTO/SIGNATURE backed up to
   `~/UbuntuDevFiles/FMO-CalapanCity/masterlist-backup-2026-06-24.zip`; `masterlist/` emptied.
4. **Broken-image fix.** Created the missing `public/uploads/faceplaceholder.png` (fallback for
   records without a photo/signature); tracked via gitignore/dockerignore exceptions.
5. **Dockerized.** `Dockerfile` (php:8.3-apache, docroot public/, www-data remapped to host
   UID/GID), `docker/000-default.conf`, `docker-compose.yml` (bind-mounts data/, public/uploads,
   config/auth.php). Published to Docker Hub **`bonitobonita24/fmo-fisherfolk-reporting-tool:latest`**
   (PII-free image). Default host port **61862** (override via `APP_PORT`).
6. **Excel/PDF export.** `public/assets/js/export.js` + buttons in the Fisherfolk List export the
   current filtered set. PDF = A4, header "Fisheries Management Office", filter-derived subtitle,
   summary-first (total/gender/age/per-barangay) then records. Excel = Summary + Fisherfolk sheets.
   List API now returns `date_of_birth`.
7. **Login.** Session-based single-user auth. `public/login.php`, `public/logout.php`, guard logic
   in `config/auth-functions.php`. `index.html`→`index.php` (page-gated); all 9 data APIs call
   `require_api_auth()` (401 if unauthenticated). Logout button + username in the nav.

## 4. How to run
- **Docker (primary):** `PUID=$(id -u) PGID=$(id -g) docker compose up -d --build` →
  http://localhost:61862 . Container `fmo-fisherfolk`. App code is baked into the image (only
  data/, public/uploads, config/auth.php are bind-mounted) → **code changes require `--build`**.
  After editing the bind-mounted `config/auth.php`, run `docker compose up -d --force-recreate`
  (single-file bind mounts don't track inode replacement on edit).
- **Local PHP:** `APP_ENV=development php -S localhost:8080 -t public dev-router.php`.
- **Re-import a new masterlist:** drop the xlsx + PHOTO/ + SIGNATURE/ into `masterlist/`, run
  `python3 tools/import_masterlist.py` (idempotent).

## 5. Credentials & secrets
- **Dashboard login:** username **`kaye`**. Password is **NOT stored here** — it lives as a
  bcrypt hash in `config/auth.php` (gitignored + dockerignored; bind-mounted into the container)
  and the literal is in the local Claude memory handover. Change it with
  `php -r 'echo password_hash("NEW", PASSWORD_BCRYPT);'` then edit `config/auth.php`, or set
  `AUTH_USERNAME` / `AUTH_PASSWORD_HASH` env vars.
- **Docker Hub PAT** was shared in plaintext earlier → **rotate it** at hub.docker.com and store
  the new one in the Server-Setups SOPS vault, not in the repo.
- DB holds PII (names, contacts, photos, signatures): keep `data/` and `public/uploads/` out of git.

## 6. Git state ⚠️
- **Remote `origin/main` is a DIFFERENT project** — a Laravel rewrite with unrelated history.
  Do NOT force-push over it.
- Local branch `main` holds this vanilla-PHP work. Commit `6daa901` (SQLite + importer + Docker)
  was pushed to branch **`legacy-php-sqlite-docker`** on origin.
- **Uncommitted at handover:** login, Excel/PDF export, placeholder fix, port change, API auth
  gating, `index.html`→`index.php` rename. The Docker Hub image predates login/export — re-push
  after rebuild if it should include them.

## 7. Open TODOs / known issues
- Commit + push the uncommitted login/export/placeholder work; re-push the Docker Hub image.
- `deploy-production.sh` still targets MySQL — out of sync with the SQLite switch; update before
  any Hostinger deploy.
- 22 records have no photo, 11 no signature (source gap) — see `data/missing_assets_report.csv`.
- Barangay field has source typos (`Communal`/`Comunal`, `Nag-Iba 1` vs `Nag-Iba I`, one-offs like
  `Svs`); ~184 orphan images for people not in the list. Not cleaned.
- One DOB is a 3-digit-year typo (`990-11-19`, id `2026-175205000-08472`) → "Unknown" age bucket.
- CDN `<script>` tags lack Subresource Integrity (SRI) — pre-existing across the app.
- `public/index-bootstrap.html` is an unused alternate dashboard (static, not auth-gated; APIs are
  gated so it shows no data) — consider removing.
- `public/` still has exposed debug/test scripts (debug-images.php, test-*.php, diagnostic.php) —
  not auth-gated; security concern.

## 8. Key files
`config/database-auto.php` (SQLite conn) · `config/auth.php` (creds, gitignored) ·
`config/auth-functions.php` (auth logic) · `sql/schema.sqlite.sql` · `tools/import_masterlist.py` ·
`public/index.php` (gated dashboard) · `public/login.php` · `public/logout.php` ·
`public/api/*.php` (gated JSON APIs) · `public/assets/js/charts.js` (dashboard JS) ·
`public/assets/js/export.js` (PDF/Excel) · `Dockerfile` · `docker-compose.yml` ·
`docker/000-default.conf`.
