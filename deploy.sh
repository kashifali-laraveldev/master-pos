#!/usr/bin/env bash
set -euo pipefail

composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan l5-swagger:generate
php artisan storage:link

echo "MasterPOS backend deploy steps completed."
