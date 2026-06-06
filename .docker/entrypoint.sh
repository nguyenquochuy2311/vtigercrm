#!/bin/bash
# vtiger container entrypoint
# 1) Remap www-data to a configurable UID/GID so file ownership lines up with
#    the host bind-mount (set PUID/PGID in .env to match the host owner).
# 2) Ensure vtiger's writable paths are owned by Apache's user on every start,
#    so rebuilds or newly-created files never reintroduce the install 500 error.
set -e

PUID="${PUID:-33}"
PGID="${PGID:-33}"

# --- (2) Remap www-data UID/GID to match the host ---------------------------
if [ "$(id -g www-data)" != "$PGID" ]; then
    groupmod -o -g "$PGID" www-data
fi
if [ "$(id -u www-data)" != "$PUID" ]; then
    usermod -o -u "$PUID" www-data
fi

# --- (1) Fix ownership of vtiger writable paths -----------------------------
# Installer + runtime need to write these (Smarty compile, config, cache, ...).
WRITABLE="cache storage test user_privileges modules logs cron Smarty"
for d in $WRITABLE; do
    if [ -e "/var/www/html/$d" ]; then
        chown -R www-data:www-data "/var/www/html/$d" 2>/dev/null || true
    fi
done

# Installer writes config.inc.php at the web root; create it if missing.
if [ ! -f /var/www/html/config.inc.php ]; then
    touch /var/www/html/config.inc.php
fi
chown www-data:www-data /var/www/html/config.inc.php 2>/dev/null || true

exec "$@"
