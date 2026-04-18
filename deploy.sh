#!/usr/bin/env bash
#
# Run on the DreamHost server (inside the site directory) to update the
# app after pushing code to GitHub:
#
#   cd ~/volunteer.floridachautauqua.com
#   ./deploy.sh
#
# The script aborts on the first failure. The database backup at the top
# makes rollback practical: untar the backup and `php artisan migrate`
# will re-apply schema.
#
# Env expected in .env: DB_* credentials, APP_KEY, MAIL_* config.

set -euo pipefail

SITE_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SITE_DIR"

echo "==> Deploy starting at $(date)"

# --- 1. Back up the database before anything mutating ---
mkdir -p storage/backups
BACKUP_FILE="storage/backups/db-$(date +%Y%m%d-%H%M%S).sql.gz"
echo "==> Dumping database to $BACKUP_FILE"
DB_NAME=$(php -r "echo parse_url('mysql://'.getenv('DB_USERNAME').':'.getenv('DB_PASSWORD').'@'.getenv('DB_HOST').'/'.getenv('DB_DATABASE'), PHP_URL_PATH);" | tr -d /)
if [ -z "${DB_NAME:-}" ]; then
    DB_NAME=$(grep '^DB_DATABASE=' .env | cut -d= -f2 | tr -d '"')
fi
mysqldump \
    --host="$(grep '^DB_HOST=' .env | cut -d= -f2 | tr -d '"')" \
    --user="$(grep '^DB_USERNAME=' .env | cut -d= -f2 | tr -d '"')" \
    --password="$(grep '^DB_PASSWORD=' .env | cut -d= -f2 | tr -d '"')" \
    "$DB_NAME" 2>/dev/null | gzip > "$BACKUP_FILE"

# Keep the last 20 backups
ls -1t storage/backups/db-*.sql.gz 2>/dev/null | tail -n +21 | xargs -r rm -f

# --- 2. Pull latest code ---
echo "==> Pulling latest code"
git fetch --all --prune
git reset --hard origin/main

# --- 3. Install PHP deps (prod mode, optimized autoload) ---
echo "==> Installing composer dependencies"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# --- 4. Install and build JS/CSS ---
# On DreamHost shared hosting npm is usually unavailable, so built
# assets are committed to the repo (public/build/) and ship with git.
# If npm is present (VPS / dedicated), rebuild for freshness.
if command -v npm >/dev/null 2>&1; then
    echo "==> npm detected — rebuilding frontend assets"
    if [ -f package-lock.json ]; then
        npm ci
    else
        npm install
    fi
    npm run build
else
    echo "==> npm not available — using committed public/build/ assets"
fi

# --- 5. Apply migrations ---
echo "==> Running migrations"
php artisan migrate --force

# --- 6. Compute and stamp the version ---
# VERSION file holds MAJOR.MINOR (e.g. "0.1"). PATCH is the git commit
# count on main so it auto-bumps with every push, no file-writeback.
# Major/minor bumps: edit VERSION in git, commit, deploy.
if [ ! -f VERSION ]; then
    echo "0.1" > VERSION
fi
MAJOR_MINOR="$(cat VERSION | tr -d '[:space:]')"
PATCH="$(git rev-list --count HEAD)"
VERSION="${MAJOR_MINOR}.${PATCH}"
if grep -q '^APP_VERSION=' .env; then
    php -r "\$e=file_get_contents('.env'); file_put_contents('.env', preg_replace('/^APP_VERSION=.*/m', 'APP_VERSION=$VERSION', \$e));"
else
    echo "APP_VERSION=$VERSION" >> .env
fi
echo "    Version stamped: $VERSION"

# --- 7. Rebuild Laravel caches ---
echo "==> Refreshing caches"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache 2>/dev/null || true

# --- 8. Restart queue workers if any are running ---
php artisan queue:restart 2>/dev/null || true

echo "==> Deploy finished at $(date)"
echo "    Backup: $BACKUP_FILE"
echo "    Version: $VERSION"
