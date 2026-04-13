# Master POS - Backend

Laravel API backend for Master POS with shared-database multi-tenancy (`tenant_id` isolation).

## Workspace

- Backend: `master-pos-laravel`
- Frontend: `../master-pos-vuejs`

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create DB and run:

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS master_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

## Demo tenant

- Email: `demo@masterpos.com`
- Password: `demo1234`
- Business: `Demo Store`

## Multi-tenant API notes

- Tenant registration: `POST /api/register-tenant`
- Tenant-aware auth login: `POST /api/auth/login`
- Tenant identification: `X-Tenant-Id` header (or subdomain fallback)
# master-pos
