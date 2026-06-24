# FMO Fisherfolk Reporting Tool — Handover

> Handover snapshot as of **2026-06-24**, for the AIEF framework / any new operator.
> A mirror of this lives in the local Claude memory store
> (`~/.claude/projects/<this-project>/memory/aief-handover.md`).
> Repo: `~/UbuntuDevFiles/FMO-CalapanCity/fmo-fisherfolk-reporting-tool`.

## 1. What it is
Read-only dashboard for the Calapan City Fisheries Management Office (FMO): registered
fisherfolk with stats (per-barangay, gender, age, activity category) and a
searchable/filterable list with photos & signatures. Built by Powerbyte IT Solutions.
**3,003 fisherfolk** (2,976 initial + 27 from the EditingPC batch) across ~51 barangay labels.

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
8. **Incremental import.** `tools/import_incremental.py` adds only NEW IDs from a partial
   masterlist and links assets by ID across masterlist/, public/uploads/, and the backup zip.
   Applied to `0001. Complete Masterlist - EditingPC.xlsx`: 27 new inserted (4 already existed),
   26 photos + 27 sigs linked. **DB now 3,003** (photos 2,980, sigs 2,992). DB backed up first to
   `data/fisherfolk.sqlite.bak-before-editingpc`.

## 3b. Production deployment (live 2026-06-24)
- **Live URL:** https://fmo.powerbyte.app (login user `kaye`; password not stored here).
- **Host:** Powerbyte VPS `72.62.74.203`, fronted by **Traefik** (Let's Encrypt, `proxy` network),
  orchestrated by **Komodo**. SSH key `~/.ssh/powerbyte_hostinger`.
- **DNS:** Cloudflare A `fmo.powerbyte.app → 72.62.74.203` (proxied).
- **VPS stack:** `/etc/komodo/stacks/fmo-fisherfolk/` — `docker-compose.yml` (single `app` service +
  Traefik labels), `.env`, bind-mounted `data/` (SQLite) + `uploads/` (photos) + `auth.php` (bcrypt
  credential mounted as a file). Data lives on the host, not in the image.
- **⚠️ Gotcha:** never put the bcrypt hash in `.env` — docker compose treats `$` as variable refs and
  blanks it. Mount `auth.php` as a file instead (or escape `$`→`$$`).
- **CI/CD (auto):** push to **`main`** → `.github/workflows/docker-build.yml` builds + pushes the
  image to Docker Hub (`DOCKERHUB_USERNAME`/`DOCKERHUB_TOKEN`), then **calls the Komodo API to
  redeploy the stack instantly** (`KOMODO_API_KEY`/`KOMODO_API_SECRET` repo secrets). So a normal
  `git push` to main is the whole deploy. Healthcheck probes `login.php`.
  - Note: Komodo's git-listener webhook does NOT fire for files-on-host stacks (tested), and its
    registry poll is **hourly** (`KOMODO_RESOURCE_POLL_INTERVAL=1-hr`), so CI triggers the deploy
    via the API rather than a webhook.
- **Manual redeploy fallback:** `ssh … root@72.62.74.203 'cd /etc/komodo/stacks/fmo-fisherfolk &&
  docker compose pull && docker compose up -d'`.

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
- **GitHub Actions repo secrets:** `DOCKERHUB_USERNAME`, `DOCKERHUB_TOKEN`, `KOMODO_API_KEY`,
  `KOMODO_API_SECRET` (all sourced from `Server-Setups/Powerbyte-Hostinger/secrets/`). The Komodo
  API key is admin-scoped — the build job runs only on push to `main` (not fork PRs), so secrets
  aren't exposed, but rotate if repo access changes.
- DB holds PII (names, contacts, photos, signatures): keep `data/` and `public/uploads/` out of git.

## 6. Git state
- **`main` is the canonical branch** for the FMO app (2026-06-24). It previously held an unrelated
  Laravel project, which was **force-replaced** (its history is off `main`; recoverable from the old
  SHA `a25d821` short-term if needed). Work on `main`; push to `main` builds + deploys (see §3b).
- `legacy-php-sqlite-docker` was deleted 2026-06-24 (it was fully merged into `main`). `main` is the only branch.
- Docker Hub **`bonitobonita24/fmo-fisherfolk-reporting-tool:latest`** includes login + export.
  Data is NOT baked into the image (bind-mounted) → DB record additions need no re-push.

## 7. Open TODOs / known issues
- ⚠️ **UNRESOLVED ID conflict `MR-CL-000534-2015`** (needs FMO decision): two **different people**
  share this ID — the person currently in the DB (from the original masterlist) and a different
  person in the EditingPC batch (different name, barangay, sex, DOB, RSBSA). The EditingPC row was
  NOT applied (incremental importer skips existing IDs), so the DB keeps the original. FMO must
  decide who rightfully owns the ID (and whether the other needs a new one), then update the DB
  manually. Personal details intentionally omitted here (public repo) — see the private local
  Claude handover memory and `~/UbuntuDevFiles/FMO-CalapanCity/masterlist-EditingPC-backup-2026-06-24.zip`.
- Typo ID imported as-is: `MR-CL-0034-55-2017` (MORENO, ARMANDO CUASAY) — likely `MR-CL-003455-2017`.
  OSORIO, SALVACION SAMONTE (`MR-CL-003635-2017`) has a signature but no photo.
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
`config/auth-functions.php` (auth logic) · `sql/schema.sqlite.sql` ·
`tools/import_masterlist.py` (full import) · `tools/import_incremental.py` (incremental batch) ·
`public/index.php` (gated dashboard) · `public/login.php` · `public/logout.php` ·
`public/api/*.php` (gated JSON APIs) · `public/assets/js/charts.js` (dashboard JS) ·
`public/assets/js/export.js` (PDF/Excel) · `Dockerfile` · `docker-compose.yml` ·
`docker/000-default.conf`.
