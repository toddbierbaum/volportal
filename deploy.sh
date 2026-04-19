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
DB_CONNECTION="$(grep '^DB_CONNECTION=' .env | cut -d= -f2 | tr -d '"[:space:]')"
TIMESTAMP="$(date +%Y%m%d-%H%M%S)"

if [ "$DB_CONNECTION" = "sqlite" ]; then
    SQLITE_FILE="database/database.sqlite"
    if [ -f "$SQLITE_FILE" ]; then
        BACKUP_FILE="storage/backups/db-${TIMESTAMP}.sqlite"
        echo "==> Copying SQLite DB to $BACKUP_FILE"
        cp "$SQLITE_FILE" "$BACKUP_FILE"
    else
        echo "==> SQLite file $SQLITE_FILE not found — skipping backup"
    fi
elif [ "$DB_CONNECTION" = "mysql" ]; then
    BACKUP_FILE="storage/backups/db-${TIMESTAMP}.sql.gz"
    echo "==> Dumping MySQL database to $BACKUP_FILE"
    DB_HOST="$(grep '^DB_HOST=' .env | cut -d= -f2 | tr -d '"')"
    DB_USER="$(grep '^DB_USERNAME=' .env | cut -d= -f2 | tr -d '"')"
    DB_PASS="$(grep '^DB_PASSWORD=' .env | cut -d= -f2 | tr -d '"')"
    DB_NAME="$(grep '^DB_DATABASE=' .env | cut -d= -f2 | tr -d '"')"
    if [ -n "$DB_NAME" ] && [ -n "$DB_USER" ]; then
        mysqldump --host="$DB_HOST" --user="$DB_USER" --password="$DB_PASS" "$DB_NAME" 2>/dev/null \
            | gzip > "$BACKUP_FILE" || echo "    warning: mysqldump failed, continuing without backup"
    else
        echo "    warning: DB credentials incomplete, skipping backup"
    fi
else
    echo "==> Unknown DB_CONNECTION '$DB_CONNECTION' — skipping backup"
fi

# Keep the last 20 backups (both extensions)
ls -1t storage/backups/db-*.sql.gz 2>/dev/null | tail -n +21 | xargs -r rm -f
ls -1t storage/backups/db-*.sqlite 2>/dev/null | tail -n +21 | xargs -r rm -f

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
# Clear any cached config from a previous deploy first — otherwise
# migrate reads stale DB settings and can fail before the end-of-deploy
# config:cache step has a chance to rebuild.
echo "==> Clearing stale caches"
php artisan config:clear
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
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
