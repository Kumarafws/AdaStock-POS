# AdaStock — Product Requirements Document (PRD)

**Version:** 1.0  
**Status:** Draft / Development Baseline  
**Product:** AdaStock  
**Document Type:** Product Requirements Document

---

## 1. Product Overview

**AdaStock** adalah sistem **Inventory Management dan Point of Sale (POS)** untuk bisnis retail skala kecil hingga menengah.

Sistem membantu bisnis mengelola produk, supplier, purchasing, receiving, inventory, multi-store/multi-warehouse, stock transfer, stock opname, stock adjustment, POS, discount, multiple payment method, receipt, return, refund recording, reporting, notification, dan audit trail.

AdaStock menggunakan **Stock Ledger / Stock Movement** sebagai fondasi untuk membuat setiap perubahan persediaan dapat ditelusuri.

---

## 2. Background

Bisnis retail memiliki aktivitas yang saling berhubungan: barang dibeli dari supplier, diterima di store/warehouse, disimpan sebagai persediaan, dipindahkan antar lokasi, lalu dijual melalui POS. Barang juga dapat dikembalikan, rusak, hilang, atau mengalami selisih ketika stock opname.

Pencatatan manual atau spreadsheet dapat menyebabkan:

- stok sistem tidak sesuai stok fisik,
- perubahan stok sulit ditelusuri,
- barang habis tanpa diketahui,
- kesalahan penerimaan barang,
- sulit memantau stok antar lokasi,
- kesalahan pencatatan penjualan,
- sulit menangani return,
- sulit mengetahui performa penjualan,
- sulit melakukan audit.

AdaStock menghubungkan seluruh proses tersebut dalam satu sistem.

---

## 3. Problem Statement

### 3.1 Inventory Accuracy
Bisnis membutuhkan stok yang akurat untuk setiap store dan warehouse.

### 3.2 Stock Traceability
Setiap perubahan stok harus dapat ditelusuri berdasarkan jenis aktivitas, jumlah, lokasi, waktu, user, dan referensi transaksi.

### 3.3 Purchasing Visibility
Bisnis perlu mengetahui barang yang dipesan, jumlah yang diharapkan, jumlah yang diterima, dan jumlah outstanding.

### 3.4 Multi-location Management
Bisnis dengan beberapa store/warehouse membutuhkan visibilitas stok per lokasi dan mekanisme transfer.

### 3.5 Sales & Inventory Synchronization
Penjualan harus otomatis memengaruhi stok tanpa update manual.

### 3.6 Operational Control
Stock adjustment, discount, return, dan perubahan master data membutuhkan permission dan audit trail.

---

## 4. Product Goals

1. Menyediakan sistem inventory terpusat.
2. Menghubungkan purchasing dengan inventory.
3. Menghubungkan POS dengan inventory.
4. Menyediakan stock ledger yang dapat diaudit.
5. Mendukung multi-store dan multi-warehouse.
6. Menyediakan stock transfer.
7. Mendukung return dan refund recording.
8. Menyediakan reporting operasional.
9. Menerapkan role-based access control.
10. Menjadi project pembelajaran Laravel dengan business logic realistis.
11. Mempelajari database transaction, concurrency, Events, Jobs, Queues, Scheduler, Cache, Notifications, dan testing.

---

## 5. Non-Goals

AdaStock tidak mencakup pada fase ini:

- payment gateway eksternal,
- integrasi bank langsung,
- accounting/General Ledger penuh,
- payroll,
- HR management,
- CRM lengkap,
- marketplace integration,
- shipping provider integration.

Sistem tetap mencatat metode pembayaran seperti Cash, Card, Bank Transfer, QRIS, dan E-Wallet tanpa memproses pembayaran eksternal.

---

# 6. Target Users & Roles

AdaStock menggunakan tiga role utama.

## 6.1 Admin
Fokus pada administrasi sistem:
- user management,
- role,
- store,
- warehouse,
- master data,
- konfigurasi sistem,
- akses keseluruhan.

## 6.2 Manager
Fokus pada operasional:
- inventory,
- purchasing,
- supplier,
- stock adjustment,
- stock opname,
- transfer,
- sales monitoring,
- reports,
- return,
- discount control.

## 6.3 Cashier
Fokus pada transaksi:
- POS,
- cart,
- checkout,
- payment,
- receipt,
- sales history,
- customer return sesuai permission.

---

## 7. Permission Overview

| Feature | Admin | Manager | Cashier |
|---|---:|---:|---:|
| Dashboard | Yes | Yes | Yes |
| Product Management | Yes | Yes | View |
| Category Management | Yes | Yes | View |
| Supplier Management | Yes | Yes | No |
| Store Management | Yes | Limited | View |
| Warehouse Management | Yes | Limited | No |
| Purchase Order | Yes | Yes | No |
| Goods Receiving | Yes | Yes | No |
| Inventory | Yes | Yes | View |
| Stock Adjustment | Yes | Yes | No |
| Stock Opname | Yes | Yes | No |
| Stock Transfer | Yes | Yes | No |
| POS | Yes | Yes | Yes |
| Discount Management | Yes | Yes | No |
| Sales History | Yes | Yes | Own / Allowed |
| Return | Yes | Yes | Allowed |
| Reports | Yes | Yes | Limited |
| User Management | Yes | No | No |
| System Configuration | Yes | No | No |
| Audit Log | Yes | Yes | Limited |

Authorization wajib diterapkan di backend.

---

# 8. Core Business Flow

```text
Supplier
    ↓
Purchase Order
    ↓
Goods Receiving
    ↓
Inventory
    ↓
Stock Ledger
    ↓
┌──────────────────────────────┐
│                              │
▼                              ▼
Transfer                     POS Sale
│                              │
▼                              ▼
Other Location              Payment
                               │
                               ▼
                            Sale
                               │
                               ▼
                         Stock Decrease
                               │
                               ▼
                           Stock Ledger
                               │
                               ▼
                            Return
                               │
                               ▼
                         Stock Handling
                               │
                               ▼
                            Reporting
```

---

# 9. Product Management

Product minimal memiliki:

- name,
- SKU,
- barcode,
- category,
- brand,
- unit,
- purchase price,
- selling price,
- minimum stock,
- reorder point,
- product image,
- active status.

### Business Rules

1. SKU harus unik.
2. Barcode harus unik jika diisi.
3. Produk inactive tidak dapat digunakan untuk transaksi baru.
4. Produk yang sudah memiliki transaksi tidak boleh dihapus secara hard delete.
5. Harga tidak boleh negatif.
6. Minimum stock tidak boleh negatif.

---

# 10. Category, Brand & Unit

Master data:

- Category,
- Subcategory,
- Brand,
- Unit.

Master data yang telah digunakan transaksi sebaiknya dinonaktifkan daripada dihapus secara hard delete.

---

# 11. Supplier Management

Supplier memiliki:

- name,
- contact person,
- phone,
- email,
- address,
- tax/identification information jika diperlukan,
- status.

Supplier yang memiliki riwayat purchase tidak boleh dihapus secara hard delete.

---

# 12. Store & Warehouse

AdaStock mendukung multi-location.

```text
Location
├── Store
└── Warehouse
```

Contoh:

```text
Central Warehouse
Jakarta Store
Bandung Store
Yogyakarta Store
```

Setiap location memiliki inventory sendiri.

---

# 13. Inventory

Inventory dapat dilihat berdasarkan:

- product,
- location,
- stock quantity,
- reserved quantity jika digunakan,
- available quantity,
- minimum stock,
- reorder point.

Formula:

```text
Available Stock = Stock Quantity - Reserved Quantity
```

Jika reservation belum digunakan, available stock dapat sama dengan stock quantity.

---

# 14. Stock Ledger / Stock Movement

Stock Movement adalah bagian inti AdaStock.

Movement type minimal:

```text
PURCHASE_RECEIPT
SALE
SALE_RETURN
PURCHASE_RETURN
TRANSFER_IN
TRANSFER_OUT
ADJUSTMENT_IN
ADJUSTMENT_OUT
STOCK_OPNAME
DAMAGE
LOSS
```

Setiap movement menyimpan:

- product,
- location,
- quantity,
- direction,
- movement type,
- reference type,
- reference ID,
- actor,
- timestamp.

Contoh:

```text
+100 PURCHASE_RECEIPT
-5  SALE
-2  DAMAGE
+10 ADJUSTMENT_IN
-20 TRANSFER_OUT
```

Stock movement harus immutable untuk user biasa. Koreksi dilakukan dengan correction movement, bukan mengubah histori lama.

---

# 15. Purchase Order

Purchase Order memiliki:

- PO number,
- supplier,
- destination location,
- order date,
- expected delivery date,
- status,
- notes,
- items,
- total.

Status:

```text
DRAFT
SUBMITTED
APPROVED
ORDERED
PARTIALLY_RECEIVED
RECEIVED
CANCELLED
```

---

# 16. Goods Receiving

Barang yang datang tidak otomatis diasumsikan sesuai PO.

Contoh:

```text
Ordered: 100
Received: 80
Outstanding: 20
```

Flow:

```text
Purchase Order
      ↓
Goods Receipt
      ↓
Validate Quantity
      ↓
Increase Inventory
      ↓
Create Stock Movement
```

Partial receiving wajib didukung.

---

# 17. Purchase Return

```text
Purchase
   ↓
Purchase Return
   ↓
Stock Decrease
   ↓
Stock Movement
```

Return menyimpan supplier, product, quantity, reason, reference purchase/receipt, actor, dan timestamp.

---

# 18. Stock Transfer

Transfer digunakan untuk memindahkan barang antar location.

```text
Source Location
      ↓
Transfer Request
      ↓
Approval
      ↓
Shipment
      ↓
IN_TRANSIT
      ↓
Destination Receiving
      ↓
RECEIVED
```

Status:

```text
DRAFT
REQUESTED
APPROVED
IN_TRANSIT
PARTIALLY_RECEIVED
RECEIVED
CANCELLED
```

Source stock berkurang saat shipment dan destination stock bertambah saat receiving.

Barang in-transit tidak dihitung sebagai available stock di destination sebelum receiving.

---

# 19. Stock Adjustment

Digunakan untuk:

- barang rusak,
- barang hilang,
- koreksi kesalahan,
- stock correction.

Wajib memiliki product, location, quantity, direction, reason, actor, timestamp.

Manager/Admin dapat melakukan adjustment. Cashier tidak dapat.

---

# 20. Stock Opname

```text
Create Stock Opname
       ↓
System Stock
       ↓
Physical Count
       ↓
Calculate Difference
       ↓
Review
       ↓
Approve
       ↓
Create Adjustment Movement
```

Contoh:

```text
System Stock: 100
Physical:      97
Difference:    -3
```

Stock opname memiliki audit trail.

---

# 21. Low Stock & Reorder

Jika:

```text
Current Stock <= Reorder Point
```

produk ditandai LOW_STOCK.

Jika:

```text
Current Stock = 0
```

produk ditandai OUT_OF_STOCK.

Scheduler dapat digunakan untuk pengecekan berkala dan notification kepada Manager/Admin.

---

# 22. POS

Flow:

```text
Open POS
    ↓
Select / Scan Product
    ↓
Add to Cart
    ↓
Apply Discount
    ↓
Calculate Total
    ↓
Select Payment Method
    ↓
Checkout
    ↓
Validate Stock
    ↓
Create Sale
    ↓
Decrease Stock
    ↓
Create Stock Movement
    ↓
Generate Receipt
```

---

# 23. Cart & Discount

Cart memiliki:

- product,
- quantity,
- unit price,
- discount,
- subtotal.

Formula:

```text
Subtotal = Quantity × Unit Price
```

Discount mendukung:

- fixed amount,
- percentage,
- product discount,
- cart/order discount.

Discount memiliki:

- name,
- type,
- value,
- start date,
- end date,
- active status,
- optional minimum purchase,
- optional maximum discount.

Cashier hanya dapat menggunakan discount aktif dan diizinkan.

---

# 24. Multiple Payment Method

Metode pembayaran minimal:

```text
CASH
DEBIT_CARD
CREDIT_CARD
BANK_TRANSFER
QRIS
E_WALLET
```

Split payment didukung.

Contoh:

```text
Total: Rp500.000
Cash:  Rp200.000
QRIS:  Rp300.000
```

Business rule:

```text
Sum(Payments) = Grand Total
```

Checkout tidak boleh selesai jika pembayaran kurang dari total.

Untuk cash:

```text
Change = Cash Received - Total
```

---

# 25. Sales

Sale memiliki:

- sale number,
- store,
- cashier,
- customer optional,
- subtotal,
- discount,
- tax jika diaktifkan,
- grand total,
- status,
- created at.

Status:

```text
COMPLETED
VOIDED
PARTIALLY_RETURNED
RETURNED
```

Sale completed tidak boleh diedit langsung. Correction dilakukan melalui void/return.

---

# 26. Receipt

Receipt dapat:

- ditampilkan,
- di-download,
- di-print.

Format:

- HTML printable receipt,
- PDF.

PDF dapat dibuat menggunakan DomPDF.

Receipt berisi store, sale number, date, cashier, items, subtotal, discount, total, payment methods, dan change.

---

# 27. Sales Return

Flow:

```text
Sale
 ↓
Return
 ↓
Select Items
 ↓
Quantity
 ↓
Reason
 ↓
Validate
 ↓
Process Return
 ↓
Create Stock Movement
 ↓
Record Refund
```

Kondisi barang:

```text
GOOD
DAMAGED
```

GOOD dapat kembali ke sellable inventory. DAMAGED tidak boleh otomatis masuk sellable inventory.

Return quantity tidak boleh melebihi quantity eligible.

---

# 28. Refund Recording

Tidak ada payment gateway.

Refund hanya dicatat di sistem.

Contoh:

```text
Return Amount: Rp150.000
Refund Method: CASH
```

Refund memiliki reference return.

---

# 29. Reporting

Admin dan Manager dapat melihat:

### Sales Report
- total sales,
- transaction count,
- sales by date,
- sales by store,
- sales by cashier,
- sales by category,
- sales by product,
- payment method.

### Inventory Report
- stock by location,
- stock movement,
- low stock,
- out of stock,
- inventory value.

### Purchase Report
- purchase volume,
- purchase value,
- supplier,
- receiving status.

### Profit Report

```text
Gross Profit = Net Sales - Cost of Goods Sold
```

Metode COGS harus ditentukan konsisten pada implementasi.

### Return Report
- return count,
- return value,
- return reason,
- returned products.

---

# 30. Audit Log

Aktivitas penting dicatat, misalnya:

```text
Admin created product.
Manager created purchase order.
Manager approved stock adjustment.
Cashier completed sale.
Manager processed return.
Admin changed user role.
```

Audit log menyimpan:

- actor,
- action,
- entity type,
- entity ID,
- old values jika relevan,
- new values jika relevan,
- timestamp.

Audit log tidak dapat diedit user biasa.

---

# 31. Notification

Gunakan Laravel Database Notifications untuk MVP.

Contoh:

- Low stock.
- Out of stock.
- Purchase order approved.
- Goods receipt completed.
- Transfer received.
- Stock opname requires review.
- Return processed.

Email notification dapat menjadi pengembangan berikutnya.

---

# 32. Advanced Laravel Requirements

AdaStock sengaja menggunakan fitur Laravel advanced.

## Database Transactions

Operasi berikut wajib transactional:

### Sale
Create Sale → Create Sale Items → Create Payments → Decrease Stock → Create Stock Movements → Create Activity Log.

### Goods Receipt
Create Receipt → Update PO → Increase Stock → Create Stock Movement → Create Activity Log.

### Transfer Receiving
Create Receiving → Increase Destination Stock → Update Transfer → Create Stock Movement.

### Return
Create Return → Create Return Items → Update Sale Return State → Update Stock → Create Stock Movement → Create Refund Record.

## Concurrency

Checkout harus aman terhadap concurrent transactions.

Gunakan transaction dan pessimistic locking pada stock record ketika diperlukan.

## Events & Listeners

Gunakan events untuk side effects.

Contoh:

```text
SaleCompleted
    ↓
Listeners
    ├── CreateStockMovement
    ├── SendNotification
    └── GenerateReceipt
```

Side effect yang sesuai dapat dipindahkan ke queue.

## Jobs & Queues

Gunakan Redis sebagai queue backend.

Contoh job:

- Generate receipt.
- Send email.
- Send notification.
- Generate report.
- Process scheduled low-stock check.

## Scheduler

Gunakan Laravel Scheduler untuk:

- check low stock,
- daily sales summary,
- weekly report,
- monthly sales summary.

## Cache

Redis digunakan untuk data relatif stabil seperti active categories, payment methods, store configuration, dan dashboard summary jika diperlukan.

Cache harus memiliki invalidation strategy.

---

# 33. Authentication & Authorization

Gunakan Laravel Sanctum.

Gunakan:

- Middleware,
- Policies,
- Gates.

Backend adalah source of truth.

Contoh:

```text
Cashier → Can create sale.
Cashier → Cannot adjust stock.
Manager → Can adjust stock.
Admin   → Can manage users.
```

Frontend hanya memberikan UX; authorization wajib divalidasi backend.

---

# 34. REST API

Gunakan API version:

```text
/api/v1
```

Domain endpoint:

```text
/api/v1/auth
/api/v1/products
/api/v1/categories
/api/v1/suppliers
/api/v1/locations
/api/v1/inventory
/api/v1/stock-movements
/api/v1/purchases
/api/v1/goods-receipts
/api/v1/transfers
/api/v1/sales
/api/v1/payments
/api/v1/returns
/api/v1/discounts
/api/v1/reports
/api/v1/users
/api/v1/notifications
```

Gunakan Laravel API Resources untuk response konsisten.

---

# 35. Frontend Architecture

Stack:

- React
- TypeScript
- Vite
- React Router
- Material UI
- TanStack Query
- Zustand
- Axios
- React Hook Form
- Zod
- Recharts

Recommended structure:

```text
src/
├── components/
├── pages/
│   ├── admin/
│   ├── manager/
│   └── cashier/
├── layouts/
├── hooks/
├── services/
├── stores/
├── types/
├── utils/
└── routes/
```

---

# 36. POS UX

POS memprioritaskan kecepatan transaksi.

```text
┌─────────────────────────────────────────┐
│ Search / Barcode                        │
├─────────────────────┬───────────────────┤
│ Products            │ Cart              │
│ Product A           │ Product A × 2     │
│ Product B           │ Product B × 1     │
│ Product C           │                   │
│                     │ Subtotal          │
│                     │ Discount          │
│                     │ Total             │
│                     │ [Checkout]        │
└─────────────────────┴───────────────────┘
```

Barcode scanner berbasis kamera dapat menjadi fitur lanjutan.

---

# 37. Technology Stack

| Layer | Technology |
|---|---|
| Frontend | React |
| Language | TypeScript |
| Build Tool | Vite |
| UI | Material UI |
| Server State | TanStack Query |
| Client State | Zustand |
| HTTP Client | Axios |
| Form | React Hook Form |
| Validation | Zod |
| Charts | Recharts |
| Backend | Laravel 13 |
| Backend Language | PHP 8.3+ |
| API | REST API |
| Authentication | Laravel Sanctum |
| Authorization | Policies / Gates / Middleware |
| ORM | Eloquent |
| Database | PostgreSQL |
| Cache | Redis |
| Queue | Redis |
| Events | Laravel Events |
| Background Jobs | Laravel Jobs |
| Scheduler | Laravel Scheduler |
| Notifications | Laravel Notifications |
| Storage | Laravel Filesystem |
| PDF | DomPDF |
| Backend Testing | Pest |
| Frontend Testing | Vitest + React Testing Library |
| API Documentation | OpenAPI / Swagger |
| Development | Docker / Laravel Sail |
| API Testing | Postman / Bruno |
| Version Control | Git + GitHub |

---

# 38. Testing Strategy

## Backend

Prioritaskan business tests:

### Inventory
- Purchase increases stock.
- Sale decreases stock.
- Return increases appropriate stock.
- Transfer decreases source stock.
- Transfer receiving increases destination stock.
- Adjustment changes stock.
- Stock cannot become negative.

### POS
- Checkout succeeds with sufficient stock.
- Checkout fails with insufficient stock.
- Multiple payment equals total.
- Discount calculation is correct.
- Concurrent checkout is safe.

### Purchase
- PO can be created.
- Partial receiving works.
- Remaining quantity is calculated correctly.
- Receiving updates inventory.

### Return
- Return cannot exceed eligible quantity.
- Good return increases sellable stock.
- Damaged return does not enter sellable stock.

### Authorization
- Cashier cannot adjust stock.
- Cashier cannot manage users.
- Manager cannot manage users.
- Admin can access administrative features.

## Frontend

Gunakan Vitest + React Testing Library untuk:

- POS cart,
- discount calculation,
- payment validation,
- product search,
- checkout states,
- form validation,
- role-based navigation,
- loading/error states.

---

# 39. Implementation Phases

## Phase 1 — Foundation
- Project setup
- Authentication
- Roles
- User
- Store
- Warehouse
- Base layout
- API structure

## Phase 2 — Master Data
- Product
- Category
- Brand
- Unit
- Supplier
- Location

## Phase 3 — Inventory Core
- Stock balance
- Stock movement
- Stock adjustment
- Low stock
- Inventory dashboard

## Phase 4 — Purchasing
- Purchase Order
- PO items
- Goods Receipt
- Partial receiving
- Purchase return

## Phase 5 — Multi-location
- Transfer request
- Transfer approval
- Shipment
- In-transit
- Receiving
- Transfer history

## Phase 6 — POS
- Product search
- Barcode input
- Cart
- Discount
- Multiple payment
- Checkout
- Sale
- Receipt

## Phase 7 — Return
- Sales return
- Return items
- Refund recording
- Stock handling

## Phase 8 — Stock Opname
- Opname session
- Physical count
- Difference
- Approval
- Adjustment

## Phase 9 — Reporting
- Sales report
- Inventory report
- Purchase report
- Return report
- Profit report
- Dashboard analytics

## Phase 10 — Advanced Backend
- Events
- Listeners
- Jobs
- Queue
- Redis
- Scheduler
- Notifications
- Cache
- Audit log

## Phase 11 — Quality
- Backend tests
- Frontend tests
- API documentation
- Security review
- Performance review
- Responsive refinement

---

# 40. Success Metrics

### Inventory
- Stock dapat dilihat per location.
- Setiap perubahan stock memiliki movement.
- Stock tidak berubah tanpa reference atau audit trail.

### Purchasing
- Purchase Order dapat dibuat.
- Partial receiving dapat dilakukan.
- Receiving memperbarui inventory.

### POS
- Cashier dapat menyelesaikan transaksi.
- Sale otomatis mengurangi stock.
- Multiple payment dapat dicatat.
- Receipt dapat dihasilkan.

### Transfer
- Stock dapat dipindahkan antar location.
- Source stock berkurang saat shipment.
- Destination stock bertambah saat receiving.

### Return
- Return tervalidasi terhadap sale.
- Stock dikembalikan sesuai kondisi barang.
- Refund tercatat.

### Security
- Role restriction bekerja.
- Unauthorized request ditolak backend.

### Reliability
- Transaction mencegah partial update.
- Concurrent stock operation tidak menghasilkan negative stock.

---

# 41. Definition of Done

- [ ] Authentication berjalan.
- [ ] Tiga role berjalan.
- [ ] Product management berjalan.
- [ ] Supplier management berjalan.
- [ ] Multi-store/multi-warehouse berjalan.
- [ ] Inventory berjalan.
- [ ] Stock ledger berjalan.
- [ ] Purchase Order berjalan.
- [ ] Partial goods receiving berjalan.
- [ ] Purchase return berjalan.
- [ ] Stock adjustment berjalan.
- [ ] Stock opname berjalan.
- [ ] Stock transfer berjalan.
- [ ] POS berjalan.
- [ ] Discount berjalan.
- [ ] Multiple payment berjalan.
- [ ] Receipt berjalan.
- [ ] Sales return berjalan.
- [ ] Refund recording berjalan.
- [ ] Low-stock alert berjalan.
- [ ] Dashboard berjalan.
- [ ] Reporting berjalan.
- [ ] Audit log berjalan.
- [ ] Notification berjalan.
- [ ] Queue berjalan.
- [ ] Scheduler berjalan.
- [ ] Events/listeners berjalan.
- [ ] Redis cache digunakan secara tepat.
- [ ] Database transactions diterapkan.
- [ ] Concurrency handling diterapkan.
- [ ] Backend authorization diterapkan.
- [ ] Backend tests tersedia.
- [ ] Frontend tests tersedia.
- [ ] API terdokumentasi.
- [ ] Responsive UI tersedia.
- [ ] Security review dilakukan.

---

# 42. Portfolio Learning Objectives

Setelah menyelesaikan AdaStock, developer diharapkan memahami:

### Laravel Fundamentals
- Routing
- Controllers
- Models
- Migrations
- Seeders
- Factories
- Form Requests
- API Resources
- Validation

### Database
- Relationships
- Transactions
- Indexing
- Query optimization
- Locking
- Data integrity

### Laravel Advanced
- Sanctum
- Policies
- Gates
- Events
- Listeners
- Jobs
- Queues
- Scheduler
- Notifications
- Cache
- Storage

### Software Engineering
- REST API design
- Business/domain logic
- Error handling
- Concurrency
- Testing
- API documentation
- Security
- Maintainable architecture

---

# 43. Final Product Principle

> **Every inventory change must be explainable.**

Setiap perubahan stok harus dapat menjawab:

```text
What changed?
How much?
Where?
Why?
Who?
When?
Based on which transaction?
```

Contoh:

```text
Product:
Laptop ASUS

Location:
Central Warehouse

Change:
-5

Reason:
TRANSFER_OUT

Reference:
TRF-2026-00012

Actor:
Manager

Time:
2026-08-31 10:30
```

Prinsip ini menjadi fondasi utama desain inventory AdaStock.

---

# 44. Final Product Flow

```text
                         SUPPLIER
                            │
                            ▼
                    PURCHASE ORDER
                            │
                            ▼
                     GOODS RECEIPT
                            │
                            ▼
                       INVENTORY
                            │
                            ▼
                     STOCK LEDGER
                            │
              ┌─────────────┴─────────────┐
              │                           │
              ▼                           ▼
        STOCK TRANSFER                  POS
              │                           │
              ▼                           ▼
        OTHER LOCATION                 SALE
                                          │
                                          ▼
                                      PAYMENT
                                          │
                                          ▼
                                   STOCK DECREASE
                                          │
                                          ▼
                                     RECEIPT
                                          │
                                          ▼
                                       RETURN
                                          │
                              ┌───────────┴───────────┐
                              │                       │
                              ▼                       ▼
                           GOOD                    DAMAGED
                              │                       │
                              ▼                       ▼
                       SELLABLE STOCK          DAMAGED STOCK
                              │
                              ▼
                         STOCK LEDGER
                              │
                              ▼
                          REPORTING
```

---

# 45. Product Vision

**AdaStock** adalah sistem retail operations yang menghubungkan **purchasing, inventory, multi-location, POS, return, dan reporting** dalam satu alur data yang konsisten.

Nilai utama:

> **Accurate Stock. Traceable Movement. Integrated Sales. Controlled Operations.**

---

## Document Status

This PRD is the baseline for product design and implementation.

Business rules dapat disempurnakan selama database design, API design, UX design, dan implementation, tetapi perubahan harus tetap konsisten dengan product goals dan core inventory principles dalam dokumen ini.
