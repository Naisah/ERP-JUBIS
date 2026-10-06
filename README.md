# Jubis ERP - B2B Electrical Distributor System

Jubis ERP is a robust, custom-built enterprise resource planning platform designed for B2B electrical distributors. Built on **Laravel 11**, **Vue 3**, **Inertia.js**, and **TailwindCSS**, it seamlessly integrates warehouse inventory management, dynamic sales pipelines, and B2B financial workflows.

## ?? Key Features

### ?? Advanced Inventory Management
* **Master/Variant Hierarchy:** Intelligently tracks parent containers (Master Folders) and individual SKU variants.
* **Low Stock Automation:** Automatically generates Draft Purchase Orders when variant stock dips below the reorder threshold.
* **Instant Export:** 1-click CSV export of the entire catalog for warehouse staff.
* **Transaction Safety:** Utilizes SQL row-level locking (lockForUpdate) to prevent race conditions during high-volume B2B checkouts.

### ?? B2B Financials & Checkout
* **Dynamic Gateway Routing:** Carts under 1,000 items route to the **PayMongo** API for instant payment (GCash/Card).
* **Net-30 Credit Terms:** Orders exceeding 1,000 items automatically bypass the payment gateway, offering legally binding Corporate Net-30 credit terms.
* **Webhook Security:** Cryptographically verified HMAC-SHA256 webhooks guarantee that automated 'Invoice Paid' triggers cannot be spoofed.

### ?? RMA (Return Merchandise Authorization)
* **Client Portal:** Clients can easily request returns for defective or excess stock directly from their paid dashboard.
* **Admin Processing:** Dedicated dashboard for the warehouse to review, approve, and mathematically restock items back into the live inventory.

### ?? Role-Based Access Control
* **Secure Middleware:** Strict partitioning between Super Admins, Sales, Purchasing, Finance, and Client accounts.

## ?? Tech Stack
* **Backend:** PHP 8, Laravel 11, MySQL
* **Frontend:** Vue.js 3, Inertia.js, Tailwind CSS
* **Integrations:** PayMongo (Payments)

## ?? Local Setup
1. Clone the repository.
2. Run \composer install\ and \
pm install\.
3. Copy \.env.example\ to \.env\ and configure your database and \PAYMONGO_SECRET_KEY\.
4. Run \php artisan migrate --seed\.
5. Run \php artisan serve\ and \
pm run dev\.
