# Jubis Marketing ERP — Technical & Functional System Inventory
**Prepared for:** System Testing & Quality Assurance Plan  
**System:** Jubis Marketing ERP Web Application  
**Generated Date:** October 2026  
**Confidentiality:** Internal Documentation  

---

## 1. System Architecture & Tech Stack

### 1.1 Frontend Framework & State Management
* **Core Framework:** Vue 3 (v3.4.0) with Composition API (`<script setup>`) and single-file components (`.vue`).
* **Routing & Client-Server Bridge:** Inertia.js Vue 3 Adapter (`@inertiajs/vue3` v2.0.0) with Ziggy (`tightenco/ziggy` v2.0) for Laravel named route resolution in Javascript (`route('name')`).
* **Styling & UI Toolkit:** Tailwind CSS (v3.2.1) with `@tailwindcss/forms` (v0.5.3), `@tailwindcss/vite` (v4.0.0), PostCSS (v8.4.31), and Autoprefixer (v10.4.12).
* **Iconography:** Heroicons Vue 24 Outline & Solid (`@heroicons/vue` v2.2.0).
* **Data Visualization:** Chart.js (v4.5.1) integrated via `vue-chartjs` (v5.3.4).
* **State & Utility Libraries:**
  * Inertia Form Helpers (`useForm`) for client-side forms, progress states, and validation error binding.
  * `lodash` (v4.18.1) for debounce mechanisms on dynamic search/filtering.
  * `dayjs` (v1.11.23) for timestamp formatting.
  * `axios` (v1.11.0) for HTTP requests.
* **Build Engine & Runtime:** Vite (v7.0.7) running on Node.js 22 (`"engines": { "node": "22" }`).

### 1.2 Backend Framework, Runtime & Database Engine
* **Backend Framework:** Laravel 12 (`laravel/framework` ^12.0) on PHP 8.2+ (`^8.2`).
* **Server Runtime:**
  * *Local Development:* PHP Built-in Server / Artisan Serve, Laragon / Apache / Nginx.
  * *Production (Railway):* Linux container running Nginx + PHP-FPM managed via Nixpacks.
* **Database Engine:** MySQL 8.x (with local SQLite fallback capability for isolated in-memory unit/feature tests).
* **Document Processing:** Barryvdh Laravel DomPDF (`barryvdh/laravel-dompdf` ^3.1) for server-side PDF invoice compilation.
* **API & Session Layer:** Laravel Sanctum (`laravel/sanctum` ^4.0) and cookie-backed, encrypted stateful web sessions.

### 1.3 Authentication & Authorization Model
* **Authentication Model:** Stateful session-based authentication provided by Laravel Breeze (CSRF token protection via `VerifyCsrfToken`, password hashing via `bcrypt`/Argon2).
* **Role-Based Access Control (RBAC):** Custom `CheckRole` middleware (`App\Http\Middleware\CheckRole`) verifying `users.role` against route parameters.
* **Recognized User Roles:**
  1. `super_admin`: Global system owner (CEO); has unconditional bypass access to every administrative module, configuration screen, user deletion, and financial record.
  2. `admin`: Full operations administrator; can manage staff, products, suppliers, purchase orders, shipments, quotes, invoices, and reports.
  3. `sales`: Access restricted to customer quotes management, catalog pricing, and client dashboard metrics.
  4. `finance`: Access restricted to invoices, payments, RMA tracking, financial analytics, and profit/revenue reporting.
  5. `purchasing`: Access restricted to vendor management, supplier catalogs, purchase orders (PO), restock receipts, and low-stock monitors.
  6. `warehouse`: Access restricted to product inventory adjustments, shipment creation, logistics booking, and delivery status updates.
  7. `client`: Standard B2B customer; access strictly restricted to the public catalog, quote cart, submitting quote requests, credit term activations, invoice PDF downloads, and personal profile management.

---

## 2. Core ERP Modules & Features

### 2.1 Implemented Modules
1. **Authentication & Identity Management:** Registration, login, password resets, email verification notifications, profile editing, and user management.
2. **Public E-Commerce & Product Catalog:** Searchable and filterable catalog, brand grouping, pagination, product detail views, variant viewing, and Contact Us messaging.
3. **Sales & Quotation Management (B2B CRM):** Client draft quote cart, inventory-locked quote submission, admin quote review, status approval/rejection, and automatic invoice conversion.
4. **Billing & Invoicing (Finance):** Auto-generated invoices, VAT (12%) calculation, Net-30 B2B credit terms activation, manual payment logging, automated PayMongo payment link generation, and DomPDF generation.
5. **Inventory & Warehouse Management:** Stock level tracking, CSV inventory exports, automated reorder level alerts, multi-brand catalog management, and full stock movement audit logging.
6. **Procurement & Vendor Management:** Supplier directory, vendor contact and tax ID records, Purchase Order (PO) creation, and auto-restocking goods receipt workflows.
7. **Logistics & Delivery Management:** Shipment generation, tracking numbers, automated Lalamove courier dispatch simulation, and automated shipment notification emails.
8. **Customer Returns (RMA):** B2B client return merchandise requests tied to paid invoices, itemized return tracking, and admin return review.
9. **Analytics & Financial Reporting:** Executive dashboard KPI cards, 6-month revenue trends, 6-month procurement spend charts, top 10 products by revenue, inventory brand distribution, and recent stock audit trails.

---

### 2.2 Key End-to-End Workflows

#### Workflow A: The B2B Quote-to-Invoice & Fulfillment Flow
1. **Cart Addition:** Client adds items to cart via `QuoteController@add`. Stock availability checked.
2. **Quote Submission:** Client submits cart via `QuoteController@submit`. Pessimistic DB lock (`lockForUpdate`) reserves inventory, decrementing `stock_quantity` and creating a negative `sale` entry in `stock_movements`.
3. **Admin Review:** Sales or Admin staff reviews the quote in `Admin/QuoteController@update`.
   * *If Rejected:* Reserved stock is restored, and an `adjustment` movement is logged.
   * *If Approved:* An Invoice is automatically generated (Status: `unpaid`, Net-30 terms).
4. **Payment Processing:** PayMongo checkout link is auto-generated (`PayMongoService`). Payment callback or webhook updates invoice status to `paid`. Alternatively, orders $\ge 1,000$ items can activate Net-30 terms (`on_terms`).
5. **Fulfillment & Dispatch:** Warehouse staff creates a Shipment (`Admin/ShipmentController@store`). Courier (Lalamove) is booked, tracking is issued, and `ShipmentDispatchedMail` is sent.
6. **Delivery:** Status updated to `delivered`; `ShipmentDeliveredMail` sent to client.

#### Workflow B: The Procurement & Inventory Restock Flow
1. **Supplier Registration:** Vendor added with contact and tax data via `Admin/SupplierController@store`.
2. **PO Generation:** Purchasing staff creates a draft PO specifying items, quantities, and unit costs.
3. **Order Placement:** PO status transitions `draft -> ordered`.
4. **Goods Receipt:** On arrival, staff updates PO status to `received`.
5. **Auto-Restock:** System automatically increments `product.stock_quantity` and logs an immutable `purchase` entry in `stock_movements`.
6. **Reversal Protection:** If PO status is reversed, the incremented stock is automatically deducted and logged as an `adjustment`.

---

### 2.3 Known Business Logic Rules & Edge Cases
* **Pessimistic Inventory Locking:** Submitting quotes initiates a database transaction with `lockForUpdate` on all items to prevent race conditions and overselling.
* **Negative Stock Restrictions:** Products enforce non-negative stock levels (`min:0`).
* **Automated Stock Restoration:** Rejecting quotes restores reserved units to live inventory automatically.
* **Philippine 12% Value Added Tax (VAT):**
  $$\text{VAT Amount} = \text{Subtotal} \times 0.12$$
  $$\text{Total Amount} = \text{Subtotal} + \text{VAT Amount}$$
* **Net-30 B2B Credit Eligibility:** Client invoices can only activate Net-30 credit terms if total order volume is $\ge 1,000$ items. Ineligible requests abort with HTTP 400.
* **Stock Movement Audit Trail:** Every stock change (sale reservation, PO receipt, cancellation rollback) creates an immutable record in `stock_movements`.
* **Self-Deletion Guard:** Administrators are prevented from deleting their own user account (`UserController@destroy`).

---

## 3. API Inventory & Endpoints

### 3.1 Public & Authentication Endpoints
| HTTP Method | Route URI | Expected Payload / Parameters | Expected Status | Validation Rules / Logic |
|---|---|---|---|---|
| `GET` | `/` | None | `200 OK` | Renders `Welcome.vue` |
| `GET` | `/products` | Query: `search`, `categories`, `brands`, `sort` | `200 OK` | Paginated 12/page; filters active non-variant items |
| `GET` | `/products/{id}` | Route parameter: `id` | `200 OK`, `404 Not Found` | Renders product details, variants, related items |
| `GET` | `/about` | None | `200 OK` | Renders `About.vue` |
| `GET` | `/contact` | None | `200 OK` | Renders `Contact.vue` |
| `POST` | `/contact` | `first_name`, `last_name`, `email`, `company`, `phone`, `message` | `302 Found` | Validates required fields; emails admin |
| `GET` | `/login` | None | `200 OK` | Renders `Auth/Login.vue` |
| `POST` | `/login` | `email`, `password`, `remember` | `302 Found`, `422 Unprocessable` | Standard Breeze session authentication |
| `POST` | `/logout` | CSRF token | `302 Found` | Invalidates session |
| `GET` | `/register` | None | `200 OK` | Renders `Auth/Register.vue` |
| `POST` | `/register` | `name`, `email`, `password`, `password_confirmation`, `company_name`, `phone`, `billing_address` | `302 Found`, `422 Unprocessable` | Unique email, password min 8; assigns `role = client` |
| `GET` | `/forgot-password` | None | `200 OK` | Renders `Auth/ForgotPassword.vue` |
| `POST` | `/forgot-password` | `email` | `302 Found`, `422 Unprocessable` | Dispatches reset token email |
| `POST` | `/reset-password` | `token`, `email`, `password`, `password_confirmation` | `302 Found`, `422 Unprocessable` | Overwrites password hash |

### 3.2 Client Portal & B2B Cart Endpoints (`auth` middleware)
| HTTP Method | Route URI | Expected Payload / Parameters | Expected Status | Validation Rules / Logic |
|---|---|---|---|---|
| `GET` | `/dashboard` | None | `200 OK` | Lists client's active orders and quotes |
| `GET` | `/cart` | None | `200 OK` | Renders user's draft quote with items |
| `POST` | `/cart/add` | `product_id`, `quantity` | `302 Found`, `422 Unprocessable` | `product_id: exists`, `quantity: min:1`; rejects if `quantity > stock` |
| `DELETE` | `/cart/{item}` | Route parameter: `item` | `302 Found`, `404 Not Found` | Deletes item; recalculates totals |
| `POST` | `/cart/submit` | `client_notes`, `shipping_address` | `302 Found`, `404 Not Found` | Locks stock in DB; status `draft -> pending` |
| `POST` | `/invoices/{id}/rma` | Route parameter: `id` | `302 Found`, `404 Not Found` | Only for `paid` or `on_terms` invoices; prevents duplicate |
| `GET` | `/invoices/{invoice}/pdf`| Route parameter: `invoice` | `200 OK`, `403 Forbidden` | Generates DomPDF stream; verifies authorization |
| `POST` | `/api/invoices/{id}/accept-terms` | Route parameter: `id` | `302 Found`, `400 Bad Request`, `403 Forbidden` | Enforces aggregate order `qty >= 1000`; sets Net-30 status |
| `GET` | `/profile` | None | `200 OK` | Renders `Profile/Edit.vue` |
| `PATCH` | `/profile` | `name`, `email`, company details | `302 Found`, `422 Unprocessable` | Updates profile; verifies unique email |
| `DELETE` | `/profile` | `password` | `302 Found`, `422 Unprocessable` | Account deletion |

### 3.3 Admin ERP Endpoints (`auth` + `role` middlewares)
| HTTP Method | Route URI | Allowed Roles | Expected Payload | Status Codes | Notes / Actions |
|---|---|---|---|---|---|
| `GET` | `/admin/dashboard` | All Staff Roles | None | `200`, `403` | Renders overview metrics |
| `GET` | `/admin/inventory` | `warehouse`, `purchasing`, `admin`, `super_admin` | Query: `search`, `stock` | `200`, `403` | Paginated product list with total inventory value |
| `GET` | `/admin/inventory-export` | `warehouse`, `purchasing`, `admin`, `super_admin` | None | `200 OK` (CSV) | Direct CSV download of full inventory |
| `POST` | `/admin/inventory` | `warehouse`, `purchasing`, `admin`, `super_admin` | Product attributes & optional `image` | `302`, `422`, `403` | Unique SKU, non-negative price/stock |
| `PUT/PATCH` | `/admin/inventory/{product}` | `warehouse`, `purchasing`, `admin`, `super_admin` | Product attributes & optional `image` | `302`, `422`, `403` | Updates product and image |
| `DELETE` | `/admin/inventory/{product}` | `warehouse`, `purchasing`, `admin`, `super_admin` | None | `302`, `403` | Soft-deletes product |
| `GET` | `/admin/shipments` | `warehouse`, `purchasing`, `admin`, `super_admin` | Query: `status` | `200`, `403` | Paginated shipments list |
| `POST` | `/admin/shipments` | `warehouse`, `purchasing`, `admin`, `super_admin` | `invoice_id`, `carrier`, `tracking_number`, `shipping_address` | `302`, `422`, `403` | Unique invoice link; triggers Lalamove dispatch |
| `POST` | `/admin/shipments/{shipment}/status` | `warehouse`, `purchasing`, `admin`, `super_admin` | `status` (`processing,picking_packing,dispatched,in_transit,delivered`) | `302`, `422`, `403` | Dispatches emails on `dispatched`/`delivered` |
| `GET` | `/admin/suppliers` | `purchasing`, `admin`, `super_admin` | Query: `search` | `200`, `403` | Supplier directory |
| `POST` | `/admin/suppliers` | `purchasing`, `admin`, `super_admin` | `name`, `brands_carried`, contact fields | `302`, `422`, `403` | Unique supplier name |
| `PUT/PATCH` | `/admin/suppliers/{supplier}` | `purchasing`, `admin`, `super_admin` | Supplier fields | `302`, `422`, `403` | Updates supplier |
| `DELETE` | `/admin/suppliers/{supplier}` | `purchasing`, `admin`, `super_admin` | None | `302`, `403` | Soft-deletes supplier |
| `GET` | `/admin/purchase-orders` | `purchasing`, `admin`, `super_admin` | Query: `status` | `200`, `403` | PO listings |
| `POST` | `/admin/purchase-orders` | `purchasing`, `admin`, `super_admin` | `supplier_id`, items array (`product_id`, `quantity`, `unit_cost`) | `302`, `422`, `403` | Creates PO; starts as `draft` |
| `POST` | `/admin/purchase-orders/{po}/status` | `purchasing`, `admin`, `super_admin` | `status` (`draft,ordered,partially_received,received`) | `302`, `422`, `403` | `received` auto-increments stock and logs movements |
| `GET` | `/admin/quotes` | `sales`, `finance`, `admin`, `super_admin` | Query: `status` | `200`, `403` | Customer quote requests |
| `PUT/PATCH` | `/admin/quotes/{quote}` | `sales`, `finance`, `admin`, `super_admin` | `status` (`pending,reviewed,approved,rejected`) | `302`, `422`, `403` | `rejected` restores stock; `approved` generates invoice |
| `GET` | `/admin/invoices` | `finance`, `admin`, `super_admin` | None | `200`, `403` | Invoices list |
| `POST` | `/admin/invoices/{invoice}/payment` | `finance`, `admin`, `super_admin` | `status`, `amount_paid` | `302`, `422`, `403` | Manual payment updates |
| `GET` | `/admin/rmas` | `finance`, `admin`, `super_admin` | None | `200`, `403` | Return authorization requests |
| `GET` | `/admin/reports` | `finance`, `admin`, `super_admin` | None | `200`, `403` | Executive financial and inventory analytics |
| `GET` | `/admin/settings` | `admin`, `super_admin` | None | `200`, `403` | Global system settings |
| `POST` | `/admin/settings` | `admin`, `super_admin` | Settings payload | `302`, `403` | Persists in cache forever |
| `GET` | `/admin/users` | `admin`, `super_admin` | Query: `search`, `type`, `role` | `200`, `403` | Staff and client directory |
| `POST` | `/admin/users` | `admin`, `super_admin` | `name`, `email`, `password`, `role`, `company_name` | `302`, `422`, `403` | RBAC user creation |
| `PUT/PATCH` | `/admin/users/{user}` | `admin`, `super_admin` | `name`, `email`, `role`, `credit_status`, optional `password` | `302`, `422`, `403` | Updates user permissions |
| `DELETE` | `/admin/users/{user}` | `super_admin` | None | `302`, `403` | Prevents deleting self; soft-deletes user |

### 3.4 Webhooks & Integration Endpoints
| HTTP Method | Route URI | Payload | Status Codes | Description |
|---|---|---|---|---|
| `POST` | `/webhooks/paymongo` | PayMongo Event JSON | `200 OK`, `401 Unauthorized` | HMAC-SHA256 signature verified; auto-marks invoice paid |
| `GET` | `/api/mock/payment/invoice/{id}` | Route parameter: `id` | `200 OK`, `302 Found` | Mock checkout simulator page |
| `POST` | `/api/mock/payment/process/invoice/{id}` | `payment_method` | `302 Found` | Simulates payment success; marks invoice paid |
| `GET` | `/api/mock/tracking/{tracking}` | Route parameter: `tracking` | `200 OK` | Courier mock tracking status view |

---

## 4. Integrations & Background Jobs

### 4.1 Third-Party Integrations
1. **PayMongo API (Payment Gateway):**
   * Class: `App\Services\PayMongoService`
   * Channels: Credit/Debit Card, GCash, Maya, and QR Ph.
   * Auto-fallback: Generates simulated links if API key is not in `.env`.
   * Webhook: Validates HMAC-SHA256 signatures via `Paymongo-Signature` header.
2. **Lalamove Logistics:**
   * Class: `App\Services\LalamoveService`
   * Auto-fallback: Generates simulated tracking numbers (`LALA-XXXXXXXX`) and status tracker views.
3. **Gmail SMTP (Email Notifications):**
   * Mailables: `ContactMessageMail`, `LowStockAlertMail`, `ShipmentDispatchedMail`, `ShipmentDeliveredMail`.
4. **Barryvdh DomPDF:**
   * Generates formatted PDF invoices with VAT breakdowns via `pdf.invoice` Blade view.

### 4.2 Background Jobs & Schedules
* **Low Stock Alarm:** `php artisan inventory:check-stock`
  * Runs daily at **06:00 AM (Asia/Manila timezone)**.
  * Dispatches `LowStockAlertMail` to purchasing if stock is $\le 10$ units.
* **Queue Workers:**
  * Uses database queue driver (`QUEUE_CONNECTION=database` or `sync`).

---

## 5. Test Environment & Mock Data

### 5.1 Pre-Configured Test Accounts (Password: `password123`)
| Role | User Name | Email Address | Access Level |
|---|---|---|---|
| `super_admin` | Jubis CEO | `andreapanganiban05@gmail.com` | Unrestricted global access |
| `admin` | Jubis Admin | `admin@jubismarketing.com` | Full operations administration |
| `sales` | Sales Department | `georgeilagan62@gmail.com` | Quotes review & catalog management |
| `finance` | Finance Department | `andreabermudez0511@gmail.com` | Invoices, payments, and financial analytics |
| `purchasing` | Purchasing Department | `karlammagalong6@gmail.com` | Supplier directory & Purchase Orders |
| `warehouse` | Warehouse & Logistics | `dwyanetjhung@gmail.com` | Stock inventory & shipments dispatch |
| `client` | Demo Client | `client@engineeringcorp.com` | Customer purchasing, cart, and terms |
| `client` | Demo Client (Alt) | `naisahaspiras24@gmail.com` | Primary client testing account |

### 5.2 Seeders Available
* `DatabaseSeeder.php`: Foundational categories, standard products, and 100 random industrial catalog items.
* `StaffRecoverySeeder.php`: Restores staff accounts from `database/seeders/staff_backup.json`.
* `MasterRecoverySeeder.php`: Bulk seeds 914 production catalog products from `master_products.json`.
* `ImageLinkerSeeder.php`: Heuristic matching tool linking images from `public/images/products` to products.

### 5.3 Known Limitations & QA Focus Items
1. **RMA Action Buttons:** Clients can submit RMAs and Finance can view them, but no backend endpoints exist to approve/reject or refund returns.
2. **Global Settings Cache:** Settings are stored in Laravel Cache (`Cache::forever('global_settings')`). Cache clearing resets settings to default empty state.
3. **Product Images:** Products without images use the fallback placeholder `public/images/product-placeholder.svg`.
4. **Third-Party Mocking:** QA test plans should verify both simulated and live API execution paths.
