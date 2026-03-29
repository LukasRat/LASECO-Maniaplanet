#!/bin/bash
set -e

DEDICATED_HOST="${DEDICATED_HOST:-dedicated}"
DEDICATED_PORT="${DEDICATED_PORT:-5000}"
DB_HOST="${DB_HOST:-database}"
DB_PORT="${DB_PORT:-3306}"

echo "[Entrypoint] Waiting for dedicated server at ${DEDICATED_HOST}:${DEDICATED_PORT}..."
until nc -z "$DEDICATED_HOST" "$DEDICATED_PORT" 2>/dev/null; do
    echo "[Entrypoint] Dedicated server not yet reachable, retrying in 2s..."
    sleep 2
done
echo "[Entrypoint] Dedicated server is up!"

# Wait for the database TCP port to open (uses bash built-in, no nc needed)
echo "[Entrypoint] Waiting for database at ${DB_HOST}:${DB_PORT}..."
until bash -c "echo > /dev/tcp/${DB_HOST}/${DB_PORT}" 2>/dev/null; do
    echo "[Entrypoint] Database not yet reachable, retrying in 2s..."
    sleep 2
done
echo "[Entrypoint] Database is up!"

echo "[Entrypoint] Starting WebRequest worker..."
php webrequest.php &

echo "[Entrypoint] Starting UASECO..."
exec php uaseco.php
