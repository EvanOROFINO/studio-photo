#!/bin/bash
# =============================================================================
# docker-entrypoint.sh
# -----------------------------------------------------------------------------
# Runs migrations + warms the Symfony cache before Apache starts.
# Skips DB tasks gracefully if DATABASE_URL is missing or not yet reachable
# (so the container still boots and serves a maintenance page).
# =============================================================================

set -e

cd /var/www/html

# Wait for the database to be reachable (max ~60s)
if [ -n "$DATABASE_URL" ]; then
    echo "[entrypoint] Waiting for database…"
    for i in $(seq 1 30); do
        if php bin/console doctrine:query:sql "SELECT 1" --env=prod >/dev/null 2>&1; then
            echo "[entrypoint] Database is ready."
            break
        fi
        sleep 2
    done

    echo "[entrypoint] Running migrations…"
    php bin/console doctrine:migrations:migrate --no-interaction --env=prod --allow-no-migration || true

    echo "[entrypoint] Loading fixtures (one-time bootstrap)…"
    php bin/console doctrine:query:sql "SELECT COUNT(*) FROM \`user\`" --env=prod 2>/dev/null | grep -q "0" && \
        php bin/console doctrine:fixtures:load --no-interaction --env=prod || true
else
    echo "[entrypoint] DATABASE_URL not set — skipping migrations."
fi

# Warm the cache
echo "[entrypoint] Warming cache…"
php bin/console cache:clear --env=prod --no-debug || true
php bin/console cache:warmup --env=prod --no-debug || true

# Hand off to Apache
echo "[entrypoint] Starting Apache on port ${PORT:-8080}…"
exec "$@"
