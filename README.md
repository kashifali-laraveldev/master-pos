# Master POS — Point of Sale System

> **A complete, modern POS solution with a Vue.js frontend and a powerful Laravel REST API backend.**

[![Frontend Demo](https://img.shields.io/badge/🖥️_Frontend_Demo-demo--master--pos.kitsoftsol.com-7c3aed?style=for-the-badge)](http://demo-master-pos.kitsoftsol.com)
[![Backend API](https://img.shields.io/badge/⚙️_Backend_API-demo--backend--master--pos.kitsoftsol.com-059669?style=for-the-badge)](http://demo-backend-master-pos.kitsoftsol.com)
[![Portfolio](https://img.shields.io/badge/Portfolio-kashifali--laraveldev.kitsoftsol.com-0077B5?style=for-the-badge)](http://kashifali-laraveldev.kitsoftsol.com)
[![LinkedIn](https://img.shields.io/badge/LinkedIn-Kashif_Ali-0077B5?style=for-the-badge&logo=linkedin)](https://www.linkedin.com/in/kashif-ali-39659518a)

---

## 📌 Overview

**Master POS** is a full-stack Point of Sale system designed for retail businesses, grocery stores, and multi-product shops. It features a clean, responsive **Vue.js** frontend for cashiers and managers, and a robust **Laravel REST API** backend with multi-tenant support, Bearer token authentication, and full Swagger documentation.

Whether you're running a single store or managing multiple branches, Master POS gives your team the tools to sell faster, track inventory in real time, and analyze business performance with live dashboards.

---

## 🏗️ Tech Stack

| Layer | Technology |
|---|---|
| **Frontend** | Vue.js (SPA) |
| **Backend** | Laravel (PHP) |
| **API Style** | RESTful JSON API |
| **Authentication** | Bearer Token (Sanctum) |
| **Multi-Tenancy** | `X-Tenant-Id` header |
| **API Docs** | Swagger / OpenAPI 3.0 |
| **Database** | MySQL |

---

## 🖥️ Screenshots

### 🔐 Login Page
![Login](screenshots/master_pos_login.PNG)
*Clean, minimal login screen with demo credentials pre-filled for quick access.*

---

### 📊 Dashboard — Business Analytics
![Dashboard](screenshots/pos_master_dashboard.PNG)
*Live business insights: today's revenue, this month's revenue, average ticket, low stock alerts, last 14 days revenue trend, payment mix (cash/card/credit/bank), top products, and recent sales.*

---

### 🛒 POS Billing Board
![POS Board](screenshots/pos_board.PNG)
*The main POS screen — cashier-friendly product grid with category filters, live cart, discount, payment method selection (Cash / Card / Bank / Credit), and one-click "Complete Sale".*

---

### 📦 Products Listing
![Products](screenshots/products_listing.PNG)
*Full product catalog with name, SKU, category badge, unit type, price, and live stock levels. Color-coded stock alerts for low inventory.*

---

### ➕ Add Product Form
![Add Product](screenshots/add_product_form.PNG)
*Modal form to add new products: name, category, SKU, description, unit type, price per unit, cost price, opening stock, low stock alert threshold, and featured flag.*

---

### 🏷️ Categories Management
![Categories](screenshots/categories_listing.PNG)
*Visual category grid with color-coded badges, product counts, active status, and slug. Supports Grocery, Beverages, Snacks, Electronics, Personal Care, and many more.*

---

### ✏️ Edit Category
![Edit Category](screenshots/edit_category_form.PNG)
*Modal editor for updating category name, description, color, active status, and category image.*

---

### 📋 Inventory Management
![Inventory](screenshots/inventory_manage_screen.PNG)
*Full inventory list with current stock levels per product. Inline stock adjustment panel: select product, choose type (purchase/return/adjustment), enter quantity with notes.*

---

### 📈 Sales History
![Sales History](screenshots/sales_listing.PNG)
*Complete sales log filterable by date and status. Shows invoice number, date, cashier, item count, total, and status (completed/cancelled).*

---

### 📉 Reports
![Reports](screenshots/reports_screen.PNG)
*Detailed reporting screen: today's revenue, monthly revenue, low stock count, payment mix breakdown, 14-day revenue bar chart, and top 5 products by revenue.*

---

## ⚙️ REST API — Backend

> 🔗 **API Base URL:** `https://demo-backend-master-pos.kitsoftsol.com/api`
> 📖 **Swagger Docs:** `https://demo-backend-master-pos.kitsoftsol.com/docs`

### Swagger API Documentation
![Swagger](screenshots/swagger-api-documentation.PNG)
*Full OpenAPI 3.0 documentation with interactive testing for all endpoints: Auth, Products, Categories, Sales, Customers, Inventory, Dashboard, and Users.*

---

### 🔑 Authentication — Login API
![Login API](screenshots/login_api.PNG)
*`POST /api/auth/login` — Returns user object + Bearer token on successful authentication.*

![Login API Response](screenshots/login-api-response.PNG)
*200 OK response from Swagger with full user object, tenant ID, role, and Bearer token.*

---

### 🛍️ Products API
![Products Listing API](screenshots/products-listing-api-response.PNG)
*`GET /api/products` — Returns paginated product list with full details including category, unit type, pricing, stock quantity, and timestamps.*

![Product Detail API](screenshots/product-detail-api.PNG)
*`GET /api/products/{id}` — Returns full product object with nested category data.*

![Product Create Validation](screenshots/product-create-validation-response.PNG)
*`POST /api/products` — Returns `422` with clear error message when plan limits are exceeded (e.g. free plan max 100 products).*

---

### 📦 Inventory API
![Inventory API](screenshots/inventory_get_api.PNG)
*`GET /api/inventory` — Returns live inventory list with stock quantities, unit types, and product details.*

---

### 👥 Customers API
![Customers API](screenshots/customers-listing-api.PNG)
*`POST /api/customers` — Create customer with name, email, phone, and address. Returns `201 Created` with full customer object.*

---

### 📊 Dashboard Stats API
![Dashboard Stats API](screenshots/dashboard-stats-api.PNG)
*`GET /api/dashboard/stats` — Returns today's stats, monthly stats, growth %, total products, total sales count, avg ticket, and daily chart data.*

![Dashboard Stats](screenshots/dashboard-stats.PNG)
*Swagger live response with real revenue and sales data for the current month.*

---

## ✨ Key Features

| Feature | Description |
|---|---|
| **POS Billing Board** | Fast product search, category filter, cart management, multiple payment methods |
| **Multi-Payment Support** | Cash, Card, Bank Transfer, Credit |
| **Live Dashboard** | Revenue trends, payment mix, top products, low stock alerts |
| **Product Management** | Full CRUD with SKU, units, cost price, featured flags |
| **Category Management** | Color-coded categories with images and slugs |
| **Inventory Tracking** | Real-time stock with purchase/adjustment/return entries |
| **Sales History** | Filterable invoice log per cashier with cancel support |
| **Reports** | Revenue charts, top products, payment analytics |
| **Multi-Tenancy** | Tenant-isolated data via `X-Tenant-Id` header |
| **Bearer Auth** | Secure token-based API authentication |
| **Swagger Docs** | Full interactive OpenAPI 3.0 documentation |
| **Plan Limits** | Enforced limits on products per pricing tier |
| **Role-Based Access** | Admin and staff roles with scoped permissions |

---

## 🚀 Live Demo

### Frontend (Vue.js)
> 🔗 **[demo-master-pos.kitsoftsol.com](http://demo-master-pos.kitsoftsol.com)**

### Backend API (Laravel)
> 🔗 **[demo-backend-master-pos.kitsoftsol.com](http://demo-backend-master-pos.kitsoftsol.com)**
> 📖 **Swagger:** [demo-backend-master-pos.kitsoftsol.com/docs](http://demo-backend-master-pos.kitsoftsol.com/docs)

**Demo Credentials:**

| Field | Value |
|---|---|
| Email | demo@masterpos.com |
| Password | demo1234 |
| Tenant ID | demo-tenant |

---

## 📬 Contact & Hire Me

I'm available for freelance and contract projects. If you need a custom POS system, e-commerce backend, REST API, or any Laravel/Vue.js application — let's talk.

| | |
|---|---|
| 📧 **Email** | [alikashi54321@gmail.com](mailto:alikashi54321@gmail.com) |
| 💼 **LinkedIn** | [linkedin.com/in/kashif-ali-39659518a](https://www.linkedin.com/in/kashif-ali-39659518a) |
| 🌐 **Portfolio** | [kashifali-laraveldev.kitsoftsol.com](http://kashifali.kitsoftsol.com) |
| 📱 **WhatsApp / Phone** | [+92 305 750 2419](https://wa.me/923057502419) |

---

> *Built with ❤️ by Kashif Ali — Laravel & Vue.js Developer*
