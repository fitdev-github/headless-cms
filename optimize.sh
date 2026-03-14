#!/bin/bash
# HeadlessCMS optimization script for production deployment
# Usage: bash optimize.sh

set -e

PHP=${PHP_BIN:-php}

echo "==> Clearing caches..."
$PHP artisan cache:clear
$PHP artisan config:clear
$PHP artisan route:clear
$PHP artisan view:clear

echo "==> Caching config, routes and views..."
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache

echo "==> Linking storage..."
$PHP artisan storage:link 2>/dev/null || echo "    Storage already linked."

echo "==> Setting permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "✓ Optimization complete."
