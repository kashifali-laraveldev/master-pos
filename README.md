# Master POS — Point of Sale System

[![Laravel](https://img.shields.io/badge/Backend-Laravel-red.svg)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Frontend-Vue.js-green.svg)](https://vuejs.org)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

A full-featured, multi-tenant Point of Sale (POS) system built with **Laravel** (API backend) and **Vue.js** (frontend). Designed for retail businesses to manage products, inventory, categories, sales, and reporting — all in one place.

**Live Demo:**
- Frontend: https://demo-master-pos.kitsoftsol.com/
- Backend API: https://demo-backend-master-pos.kitsoftsol.com/
- Demo Credentials: `demo@masterpos.com` / `demo1234`

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Architecture Overview](#architecture-overview)
- [Multi-Tenancy](#multi-tenancy)
- [Getting Started](#getting-started)
- [Environment Configuration](#environment-configuration)
- [API Reference](#api-reference)
  - [Authentication](#authentication)
  - [Dashboard](#dashboard)
  - [Categories](#categories)
  - [Products](#products)
  - [Inventory](#inventory)
  - [Sales](#sales)
  - [Customers](#customers)
  - [Users](#users)
  - [Reports](#reports)
- [Screens & Modules](#screens--modules)
- [Subscription & Plan Limits](#subscription--plan-limits)
- [Error Handling](#error-handling)
- [Rate Limiting](#rate-limiting)

---

## Features

- **Multi-Tenant SaaS** — Each business (tenant) operates in a fully isolated environment
- **POS Billing Board** — Touch-friendly product grid, cart management, multiple payment methods (Cash, Card, Bank Transfer, Credit)
- **Product Management** — Create, edit, deactivate products with images, SKU, unit types, pricing, and stock alerts
- **Category Management** — Color-coded categories with images and active/inactive status
- **Inventory Control** — Real-time stock tracking with purchase, adjustment, and return entries
- **Sales History** — Full invoice ledger with cashier tracking, status management (completed/cancelled), and search by date
- **Dashboard Analytics** — Revenue KPIs, 14-day trend charts, payment mix donut chart, top products, critical low stock, recent sales
- **Reports** — Revenue charts, top products ranking, and payment method breakdown
- **Role-Based Access** — Admin and Staff roles with plan-based feature gating
- **Subscription Plans** — Free and paid tiers with product/user limits enforced at the API level

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel (PHP) |
| Frontend | Vue.js |
| Authentication | Laravel Sanctum (Bearer tokens) |
| API Style | RESTful JSON API |
| Multi-Tenancy | Header-based (`X-Tenant-Id`) |
| Hosting | LiteSpeed / hPanel (Hostinger) |

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────┐
│                    Vue.js Frontend                   │
│   POS Board │ Dashboard │ Products │ Inventory ...   │
└────────────────────────┬────────────────────────────┘
                         │ HTTPS / JSON API
┌────────────────────────▼────────────────────────────┐
│               Laravel Backend API                    │
│  Middleware: tenant.identify → auth:sanctum →        │
│             tenant.user → subscription               │
└────────────────────────┬────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────┐
│                    Database                          │
│         (Tenant-scoped data per X-Tenant-Id)         │
└─────────────────────────────────────────────────────┘
```

Every authenticated request passes through the following middleware chain:

1. `throttle:api` — Rate limiting
2. `tenant.identify` — Resolves tenant from `X-Tenant-Id` header
3. `auth:sanctum` — Validates Bearer token
4. `tenant.user` — Ensures the user belongs to the resolved tenant
5. `subscription` — Enforces plan limits

---

## Multi-Tenancy

The system uses **header-based multi-tenancy**. Every API request (except public routes) must include:

```
X-Tenant-Id: your-tenant-slug
```

Example:
```
X-Tenant-Id: demo-tenant
```

This header is required on all endpoints. The login endpoint also requires it to scope authentication to the correct tenant.

---

## Getting Started

### Prerequisites

- PHP >= 8.1
- Composer
- Node.js >= 18
- MySQL or PostgreSQL

### Backend Setup (Laravel)

```bash
# Clone the repository
git clone https://github.com/your-org/master-pos-backend.git
cd master-pos-backend

# Install PHP dependencies
composer install

# Copy and configure environment
cp .env.example .env
php artisan key:generate

# Run migrations and seeders
php artisan migrate --seed

# Start development server
php artisan serve
```

### Frontend Setup (Vue.js)

```bash
# Clone the frontend repository
git clone https://github.com/your-org/master-pos-frontend.git
cd master-pos-frontend

# Install dependencies
npm install

# Configure environment (see Environment Configuration below)
cp .env.example .env.local

# Start development server
npm run dev

# Build for production
npm run build
```

---

## Environment Configuration

### Backend `.env` (Laravel)

```env
APP_NAME="Master POS"
APP_ENV=production
APP_URL=https://your-backend-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=masterpos
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=your-frontend-domain.com
SESSION_DOMAIN=.your-domain.com
```

### Frontend `.env.local` (Vue.js)

```env
VITE_API_BASE_URL=https://your-backend-domain.com/api
VITE_TENANT_ID=your-tenant-slug
```

---

## API Reference

**Base URL:** `https://demo-backend-master-pos.kitsoftsol.com/api`

All protected endpoints require:
- `Authorization: Bearer {token}` header
- `X-Tenant-Id: {tenant_slug}` header
- `Content-Type: application/json`

---

### Authentication

#### POST `/api/auth/login`

Authenticate a user and receive a Bearer token.

**Headers:**
```
X-Tenant-Id: demo-tenant
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "demo@masterpos.com",
  "password": "demo1234"
}
```

**Response `200`:**
```json
{
  "user": {
    "id": 1,
    "tenant_id": "demo-tenant",
    "name": "Demo Owner",
    "email": "demo@masterpos.com",
    "role": "admin",
    "is_active": true,
    "created_at": "2026-04-08T08:04:37.000000Z",
    "updated_at": "2026-04-08T12:10.000000Z"
  },
  "token": "15|YzTc0djuwtc7OoodeZcLLUEqS3FJp9SwY441XKf15e1b0372"
}
```

**Rate Limit:** `throttle:login` (stricter than standard API)

---

#### POST `/api/auth/logout`

Invalidate the current Bearer token.

**Response `200`:** `{ "message": "Logged out successfully" }`

---

#### GET `/api/auth/me`

Get the currently authenticated user's profile.

**Response `200`:** Returns the user object (same structure as login response `user` field).

---

#### POST `/api/auth/register` / `/api/register-tenant`

Register a new tenant. This is a public endpoint — no authentication required.

---

### Dashboard

#### GET `/api/dashboard/stats`

Returns KPI stats and chart data for the dashboard.

**Response `200`:**
```json
{
  "today": {
    "sales_count": 0,
    "revenue": 0,
    "yesterday_revenue": 0,
    "growth_percent": null
  },
  "month": {
    "sales_count": 18,
    "revenue": 541122.55,
    "last_month_revenue": 3577084.19,
    "growth_percent": -84.87
  },
  "low_stock_count": 0,
  "total_products": 220,
  "avg_ticket": "39542.277276",
  "daily_chart": [
    { "date": "2026-03-31", "count": 4, "revenue": "183375.59" },
    { "date": "2026-04-01", "count": 5, "revenue": "187466.84" }
  ]
}
```

Also accessible at `/api/dashboard`.

---

### Categories

#### GET `/api/categories`

List all categories for the tenant.

**Headers:**
```
X-Tenant-Id: demo-tenant
Authorization: Bearer {token}
```

**Response `200`:**
```json
[
  {
    "id": 1,
    "tenant_id": "demo-tenant",
    "name": "Grocery Essentials",
    "slug": "grocery-essentials",
    "description": "Grocery Essentials items for demo sales and inventory",
    "image": null,
    "color": "#477593",
    "is_active": true,
    "display_order": 1,
    "products_count": 11,
    "image_url": null,
    "created_at": "2026-04-08T08:12:16.000000Z",
    "updated_at": "2026-04-08T08:12:16.000000Z"
  }
]
```

#### POST `/api/categories`

Create a new category. Supports multipart/form-data for image uploads.

**Request Body:**
```json
{
  "name": "New Category",
  "description": "Category description",
  "color": "#FF5733",
  "is_active": true
}
```

#### PUT `/api/categories/{id}`

Update an existing category (name, description, color, active status, image).

#### DELETE `/api/categories/{id}`

Delete a category.

---

### Products

#### GET `/api/products`

List all products for the tenant.

**Headers:**
```
X-Tenant-Id: demo-tenant
Authorization: Bearer {token}
```

**Response `200`:**
```json
[
  {
    "id": 1,
    "tenant_id": "demo-tenant",
    "category_id": 13,
    "name": "Fresh iusto voluptatem 1",
    "sku": "MP-0001",
    "description": "Fast moving item for POS demo",
    "image": null,
    "unit_type": "weight",
    "unit_label": "kg",
    "price_per_unit": "2207.00",
    "cost_price": "3312.00",
    "stock_quantity": "440.000",
    "low_stock_alert": "46.00",
    "stock_unit": "kg",
    "is_active": true,
    "is_featured": false,
    "display_order": 1,
    "created_at": "2026-04-08T08:12:16.000000Z",
    "updated_at": "2026-04-08T08:12:25.000000Z"
  }
]
```

#### POST `/api/products`

Create a new product. Enforces plan limits — free plan allows max 100 products.

**Middleware:** `tenant.plan:products`

**Request Body:**
```json
{
  "category_id": 1,
  "name": "Basmati Chawal 1kg",
  "sku": "KR-CH-1001",
  "unit_type": "piece",
  "unit_label": "pack",
  "stock_unit": "pack",
  "price_per_unit": 420,
  "cost_price": 360,
  "stock_quantity": 50,
  "low_stock_alert": 8
}
```

**Response `422` (Plan Limit Exceeded):**
```json
{ "message": "Free plan allows max 100 products" }
```

#### PUT/PATCH `/api/products/{id}`

Update product details.

#### DELETE `/api/products/{id}`

Delete (or deactivate) a product.

#### GET `/api/products/low-stock`

Returns products where `stock_quantity <= low_stock_alert`.

#### POST `/api/products/{product}/adjust-stock`

Adjust stock for a specific product directly.

---

### Inventory

#### GET `/api/inventory`

List all products with current stock levels.

**Response `200`:** Array of product objects including `stock_quantity`, `stock_unit`, `low_stock_alert`.

```json
[
  {
    "id": 42,
    "tenant_id": "demo-tenant",
    "category_id": 3,
    "name": "Acha accusantium voluptas 42",
    "sku": "MP-0042",
    "unit_type": "piece",
    "unit_label": "piece",
    "price_per_unit": "2752.00",
    "cost_price": "3497.00",
    "stock_quantity": "210.000",
    "low_stock_alert": "27.000",
    "stock_unit": "piece",
    "is_active": true
  }
]
```

#### POST `/api/inventory/adjust`

Submit a stock adjustment (purchase, adjustment, or return).

**Request Body:**
```json
{
  "product_id": 1,
  "type": "purchase",
  "quantity": 100,
  "notes": "Restocked from supplier"
}
```

**Adjustment Types:**
- `purchase` — Adds stock (positive quantity)
- `adjustment` — Manual correction (use negative for deduction)
- `return` — Customer return, adds back to stock

#### GET `/api/inventory/history`

Returns a log of all stock adjustment entries.

---

### Sales

#### GET `/api/sales`

List all sales with invoice number, date, cashier, item count, total, and status.

**Query Parameters:**
- `date` — Filter by specific date (mm/dd/yyyy)
- `status` — Filter by status (`completed`, `cancelled`)

#### POST `/api/sales`

Create a new sale (complete a cart transaction).

**Request Body:**
```json
{
  "items": [
    { "product_id": 1, "quantity": 2, "price": 2207 }
  ],
  "payment_method": "cash",
  "discount": 0,
  "received": 5000
}
```

**Payment Methods:** `cash`, `card`, `bank_transfer`, `credit`

#### GET `/api/sales/{id}`

Get full details of a single sale/invoice.

#### POST `/api/sales/{sale}/cancel`

Cancel a completed sale. Updates status to `cancelled`.

#### GET `/api/sales/daily-report`

Returns aggregated sales data for the current day.

#### GET `/api/sales/monthly-report`

Returns aggregated sales data for the current month.

---

### Customers

#### GET `/api/customers`

List all customers.

#### POST `/api/customers`

Create a new customer.

**Middleware:** `tenant.plan:users`

#### PUT `/api/customers/{id}`

Update customer details.

#### DELETE `/api/customers/{id}`

Delete a customer.

#### GET `/api/customers/{customer}/purchase-history`

Returns all sales associated with a specific customer.

---

### Users

#### GET `/api/users`

List all staff users for the tenant. **Admin only.**

#### POST `/api/users`

Create a new staff user.

**Middleware:** `tenant.plan:users`

#### PUT `/api/users/{id}`

Update a user's details or role.

#### PUT `/api/users/{user}/role`

Update a user's role specifically (`admin` or `staff`).

#### DELETE `/api/users/{id}`

Remove a user from the tenant.

---

### Reports

#### GET `/api/reports`

Returns a comprehensive report including:
- Today's revenue & sales count
- This month's revenue & sales count
- Low stock item count
- Payment method breakdown (cash/card/bank_transfer/credit counts)
- Top 5 products by revenue this month
- Revenue chart data for the last N days

**Query Parameters:**
- `days` — Number of days for the revenue chart (default: `14`, options: `7`, `14`, `30`)

**Response `200`:**
```json
{
  "today": { "revenue": 0, "sales": 0 },
  "month": { "revenue": 541123, "sales": 18 },
  "low_stock_count": 0,
  "payment_mix": {
    "cash": 6,
    "card": 6,
    "bank_transfer": 6,
    "credit": 4
  },
  "top_products": [
    { "name": "Family et quis 45", "quantity": 14, "unit": "piece", "revenue": 47670 }
  ],
  "chart": [
    { "date": "2026-03-31", "revenue": 183375.59 }
  ]
}
```

---

## Screens & Modules

### Login Screen
Purple gradient background with a centered white card. Default credentials shown for demo access.

### Dashboard
- **KPI Cards:** Today's Revenue, Today's Sales, This Month Revenue, Avg Ticket, Low Stock Items
- **Bar Chart:** Last 14 days revenue trend (toggleable: Revenue / Sales Count)
- **Donut Chart:** Payment mix over last 30 days (cash, card, bank_transfer, credit)
- **Bottom Panels:** Top Products (this month), Critical Low Stock alerts, Recent Sales feed

### POS Billing Board
- Scrollable product grid with category filter tabs
- Product cards display: unit type badge, name, price, stock level, Low Stock warning
- Right panel: Cart items, Discount input, Subtotal / Total, Payment method selector (Cash / Card / Bank / Credit), Received amount, Complete Sale button

### Products
- Table view: Product name + SKU, Category badge, Unit, Price, Stock
- Search by name or SKU, filter by unit type
- Edit (pencil icon) and Delete (trash icon) per row
- Add Product modal: Name, Category, SKU, Description, Unit Type, Unit Label, Price, Cost Price, Opening Stock, Low Stock Alert, Featured toggle, Image upload

### Categories
- Grid of category cards with color icon, name, slug, product count, active badge
- Edit Category modal: Name, Description, Color picker, Active checkbox, Image upload

### Inventory
- Left: Product list with current stock and unit
- Right: Stock Adjustment panel — select product, adjustment type (purchase/adjustment/return), quantity, notes

### Sales History
- Filterable table: date picker + status dropdown
- Columns: Invoice (clickable), Date, Cashier, Items, Total, Status, Cancel action
- Status badges: `completed` (green), `cancelled` (red)

### Reports
- Summary cards: Today's Revenue, This Month Revenue, Low Stock Count, Payment Mix breakdown
- Revenue bar chart (last 14 days, configurable)
- Top 5 products panel with rank, name, quantity sold, revenue

---

## Subscription & Plan Limits

The API enforces plan-based limits via the `tenant.plan` middleware:

| Feature | Free Plan | Paid Plan |
|---|---|---|
| Max Products | 100 | Unlimited |
| Max Users | Limited | Unlimited |

When a limit is exceeded, the API returns `HTTP 422` with an explanatory message:

```json
{ "message": "Free plan allows max 100 products" }
```

---

## Error Handling

| HTTP Code | Meaning |
|---|---|
| `200` | Success |
| `401` | Unauthenticated — missing or invalid Bearer token |
| `403` | Forbidden — user does not have permission (e.g., staff accessing admin routes) |
| `404` | Resource not found |
| `422` | Validation error or plan limit exceeded |
| `429` | Too Many Requests — rate limit hit |
| `500` | Server error |

**Validation Error Example (`422`):**
```json
{
  "message": "The name field is required.",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

---

## Rate Limiting

| Route Group | Limit |
|---|---|
| `throttle:login` | Stricter (anti-brute-force) |
| `throttle:api` | Standard API rate limit |

Rate limit headers are returned on each response:
```
x-ratelimit-limit: 10
x-ratelimit-remaining: 9
```

---

## License

This project is proprietary software developed by **KitSoft Solutions**. All rights reserved.

For licensing inquiries, visit [kitsoftsol.com](https://kitsoftsol.com).
