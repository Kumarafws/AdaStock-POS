# AdaStock — Product Requirements Document (PRD)

**Version:** 2.0 (Final Architecture & Specification Baseline)  
**Status:** Approved for Implementation  
**Product:** AdaStock (Inventory Management & Point of Sale System)  
**Document Type:** Product Requirements Document  
**Architecture:** Fullstack Laravel 13 Monolith (Blade Engine + Alpine.js) + MVC & Clean OOP  

---

## 1. Product Overview

**AdaStock** adalah sistem **Inventory Management dan Point of Sale (POS)** terintegrasi yang dirancang untuk bisnis retail skala kecil hingga menengah (multi-store dan multi-warehouse).

Sistem ini menghubungkan seluruh siklus operasional retail dalam satu alur data tunggal:
1. **Purchasing & Goods Receiving** (Pengadaan barang dari supplier dengan kalkulasi HPP otomatis).
2. **Inventory Ledger** (Pencatatan mutasi stok berbasis *Stock Movement* yang *immutable* dan dapat diaudit).
3. **Multi-Location & Transfer** (Manajemen stok antar Toko, Gudang Pusat, dan Karantina Barang Rusak).
4. **Shift Kasir & POS** (Operasional kasir cepat, buka/tutup laci kas, split payment, multi-satuan, otorisasi diskon/void berjenjang).
5. **Stock Opname & Karantina** (Pemeriksaan fisik stok, penanganan barang rusak untuk dimusnahkan atau diretur ke supplier).
6. **Pelaporan Keuangan & Laba Kotor** (Laporan laba kotor berbasis *Moving Average Costing*, rekonsiliasi kasir, dan audit trail).

---

## 2. Prinsip Utama Sistem (Core System Principles)

1. **Every Inventory Change Must Be Explainable**:  
   Tidak ada perubahan stok langsung via `UPDATE stock`. Setiap penambahan atau pengurangan wajib memiliki baris mutasi (*Stock Movement*) dengan data: *What, How much, Where, Why, Who, When, dan Reference Transaction*.
2. **Single Source of Truth dalam Base Unit**:  
   Meskipun barang dijual atau dibeli dalam satuan karton, dus, atau pak, seluruh perhitungan saldo inventori dan mutasi di database selalu dikonversi dan disimpan dalam **Base Unit** (satuan dasar terkecil, misal: Pcs).
3. **Moving Average Costing (HPP Bergerak)**:  
   Harga Pokok Penjualan (HPP) dihitung ulang secara otomatis saat barang masuk dan dikunci secara permanen di setiap baris transaksi penjualan untuk menjaga integritas laporan historis laba rugi.
4. **Cashier Location & Shift Strictness**:  
   Kasir terikat pada satu toko fisik tertentu saat bertugas. Transaksi POS hanya boleh memotong stok toko bersangkutan. Kasir wajib membuka register shift kas sebelum bertransaksi dan melakukan rekonsiliasi fisik kas saat tutup shift.
5. **Strict Authorization & Anti-Fraud**:  
   Tindakan rawan manipulasi (Void transaksi setelah struk terbit, diskon manual di atas batas wajar, stock adjustment) wajib meminta otorisasi Supervisor/Manager menggunakan PIN/Password otorisasi.
6. **MVC & Clean OOP Architecture**:  
   Controller tetap tipis (*Thin Controller*). Seluruh logika bisnis berada pada *Service Classes / Action Classes*, validasi berada pada *Form Requests*, dan status data menggunakan *PHP 8.3 Backed Enums*.

---

## 3. Scope & Non-Goals

### 3.1 In Scope
- Manajemen Pengguna, Hak Akses Berbasis Role (Admin, Manager, Cashier), dan PIN Supervisor.
- Master Data: Produk, Kategori, Brand, Supplier, Multi-Satuan (UOM Conversion), Barcode multi-unit.
- Multi-Lokasi: Toko (*Store*), Gudang Pusat (*Warehouse*), Gudang Karantina Barang Rusak (*Quarantine*).
- Manajemen Stok: Saldo stok, riwayat mutasi (*Stock Movement*), penyesuaian stok (*Stock Adjustment*).
- Pengadaan: Purchase Order (PO), Penerimaan Bertahap (*Partial Goods Receiving*), Retur Pembelian (*Return to Vendor*).
- Transfer Stok antar Lokasi dengan status transit (*In-Transit*).
- Stock Opname berkala dengan mekanisme persetujuan (*Approval*) dan penyesuaian otomatis.
- Modul POS (Point of Sale) responsif keyboard/touch, pencarian/scan barcode, multi-satuan, diskon bertingkat, split payment, dan cetak struk/PDF.
- Manajemen Shift Kasir: Buka Kasir (*Open Register*), Kas Masuk/Keluar (*Cash Drawer Movement*), Tutup Kasir (*Close Register* & Rekonsiliasi).
- Penanganan Barang Rusak: Karantina barang rusak, opsi pemusnahan (*Write-off/Disposal*), atau retur ke supplier.
- Laporan Lengkap: Penjualan, Laba Kotor (Net Sales - COGS), Perputaran Stok, Pengadaan, Rekonsiliasi Kasir, dan Log Audit.

### 3.2 Non-Goals
- Payment Gateway otomatis pihak ketiga (transaksi non-tunai dicatat sebagai konfirmasi bukti bayar fisik/manual EDC/QRIS).
- Modul Akuntansi Penuh / General Ledger / Pajak Faktur Pajak PPN e-Faktur.
- Modul Penggajian (Payroll) & Human Resources (HR).
- Integrasi Marketplace (Shopee, Tokopedia, TikTok Shop) & Ekspedisi kurir instan.

---

## 4. User Roles & Permission Matrix

AdaStock mengoperasikan 3 tingkatan peran utama dengan otorisasi ketat di level backend (Policies, Form Requests, dan Middleware):

| Modul / Fitur | Admin | Manager | Cashier | Catatan Otorisasi |
|---|:---:|:---:|:---:|---|
| **User & Role Management** | Full | No | No | Hanya Admin yang dapat menambah/mengedit akun & role |
| **System Configuration** | Full | No | No | Konfigurasi toko, nama perusahaan, footer struk |
| **Master Data (Produk, Kategori, Satuan)** | Full | Full | View | Kasir hanya dapat melihat katalog untuk referensi |
| **Supplier Management** | Full | Full | No | Akses data supplier dan kontak vendor |
| **Store & Warehouse Management** | Full | Limited | View | Manager hanya dapat mengelola lokasi tugasnya |
| **Purchase Order & Goods Receiving** | Full | Full | No | Dibuat dan diverifikasi oleh Manager/Admin |
| **Stock Transfer Request & Approval** | Full | Full | No | Pengiriman & penerimaan antar lokasi |
| **Stock Adjustment & Opname** | Full | Full | No | Penyesuaian stok fisik dan selisih |
| **POS Checkout** | Full | Full | Full | Kasir menjalankan transaksi penjualan toko aktifnya |
| **Shift Kasir (Open/Close Drawer)** | Full | Full | Full | Kasir mengelola sesi shift laci kas masing-masing |
| **Void Transaksi (Setelah Struk Terbit)**| Full | Auth | PIN Required | Kasir wajib meminta input PIN Supervisor/Manager |
| **Diskon Manual Khusus (> 5% / Rp20k)** | Full | Auth | PIN Required | Kasir terbatas diskon kecil; selebihnya butuh PIN |
| **Sales Return & Refund Recording** | Full | Full | Limited | Kasir hanya bisa proses retur dengan validasi struk lama |
| **Laporan Finansial & Laba Kotor** | Full | Full | No | Kasir hanya bisa melihat ringkasan shift miliknya sendiri |
| **Audit Trail & System Logs** | Full | View | No | Rekaman riwayat aktivitas dan aksi sensitif |

---

## 5. Alur Bisnis Inti (Core Business Flow)

```text
                                  SUPPLIER
                                     │
                                     ▼
                            PURCHASE ORDER (PO)
                                     │
                                     ▼
                         GOODS RECEIVING (Partial/Full)
                                     │
                    ┌────────────────┴────────────────┐
                    ▼                                 ▼
           Update Stok Fisik                  Hitung Ulang HPP
           (Base Unit Pcs)                 (Moving Average Cost)
                    │                                 │
                    └────────────────┬────────────────┘
                                     ▼
                            STOCK LEDGER ENGINE
                                     │
     ┌───────────────────────────────┼───────────────────────────────┐
     ▼                               ▼                               ▼
STOCK TRANSFER                  KASIR BUKA SHIFT                STOCK OPNAME
(Store / Gudang)                (Modal Awal Kas)             (Hitung Fisik Stok)
     │                               │                               │
     ▼                               ▼                               ▼
Status IN-TRANSIT               TRANSAKSI POS                   Selisih Stok
     │                        (Scan Barcode / Qty)                   │
     ▼                               │                               ▼
Penerimaan Tujuan                    ▼                         Approval Manager
     │                         Hitung Diskon                         │
     ▼                     (Otorisasi jika > limit)                  ▼
Update Stok Tujuan                   │                        Update Penyesuaian
                                     ▼
                               BAYAR PESANAN
                         (Tunai / Split Payment)
                                     │
                                     ▼
                          CETAK STRUK TRANSAKSI
                                     │
                    ┌────────────────┴────────────────┐
                    ▼                                 ▼
           Potong Stok Toko                   Kunci Nilai COGS
           (Base Unit Pcs)                   & Catat Laba Kotor
                    │                                 │
                    └────────────────┬────────────────┘
                                     │
                    ┌────────────────┴────────────────┐
                    ▼                                 ▼
           TUTUP SHIFT KASIR                 RETUR PENJUALAN
         (Rekonsiliasi Kas Laci)             (Dari Pelanggan)
                                                      │
                                             ┌────────┴────────┐
                                             ▼                 ▼
                                        Kondisi BAIK     Kondisi RUSAK
                                             │                 │
                                             ▼                 ▼
                                        Kembali ke      GUDANG KARANTINA
                                         Stok Jual             │
                                                        ┌──────┴──────┐
                                                        ▼             ▼
                                                   Pemusnahan    Retur Vendor
                                                  (Write-off)        (RTV)
```

---

## 6. Spesifikasi Fitur & Aturan Bisnis Detail

### 6.1 Master Data Produk & Multi-Satuan (UOM Conversion)
Setiap produk dicatat dengan spesifikasi:
- **Atribut Produk**: Nama, SKU unik, Brand, Kategori, Sub-kategori, Gambar, Min Stock, Reorder Point, Deskripsi, Status Aktif.
- **Base Unit (Satuan Terkecil)**: Satuan mutlak untuk penyimpanan stok di sistem (misal: `Pcs`, `Botol`, `Gram`).
- **Tabel Konversi Satuan (`product_units`)**:
  - `unit_name`: Nama satuan (misal: `Dus`, `Pak`, `Lusin`).
  - `conversion_factor`: Jumlah Base Unit per satuan ini (misal: 1 Dus = 24 Pcs, maka faktor = 24).
  - `barcode`: Barcode opsional khusus untuk kemasan tersebut (barcode dus berbeda dengan barcode pcs).
  - `selling_price`: Harga jual khusus untuk satuan tersebut (misal: Eceran Rp10.000/pcs; Beli 1 Dus Rp220.000).
- **Aturan Bisnis Produk**:
  1. SKU tidak boleh duplikat. Barcode per satuan tidak boleh bentrok dengan barcode manapun.
  2. Produk yang sudah pernah memiliki riwayat transaksi/mutasi stok **tidak boleh di-hard delete** (hanya boleh dinonaktifkan/Soft Delete).
  3. Seluruh saldo inventori (`inventories.quantity`) dan catatan mutasi (`stock_movements.quantity`) **wajib dalam Base Unit**. Jika kasir menjual "2 Dus" (isi 24), maka sistem menyimpan item penjualan "2 Dus", namun mengurangi inventori sebanyak **48 Pcs**.

---

### 6.2 Metode Penilaian Persediaan & HPP (Moving Average Costing)
Sistem menggunakan metode **Moving Average Cost** (Rata-rata Bergerak) yang dihitung otomatis secara sistemik:

1. **Kalkulasi Saat Penerimaan Barang (`GoodsReceipt`)**:
   $$\text{HPP Baru} = \frac{(\text{Stok Tersedia Saat Ini} \times \text{HPP Lama}) + (\text{Qty Diterima} \times \text{Harga Beli Baru})}{\text{Stok Tersedia Saat Ini} + \text{Qty Diterima}}$$
2. **Pencatatan Saat Checkout Penjualan**:
   - Saat kasir menyelesaikan pesanan, nilai HPP produk pada detik itu disalin ke `sale_items.cogs_per_unit`.
   - $\text{Total COGS Item} = \text{sale_items.quantity\_in\_base\_unit} \times \text{sale_items.cogs\_per\_unit}$.
   - $\text{Laba Kotor Item} = \text{sale_items.subtotal} - \text{Total COGS Item}$.
3. **Keuntungan**: Laporan laba kotor masa lalu tidak akan pernah terdistorsi meskipun di masa depan harga beli supplier naik drastis.

---

### 6.3 Lokasi Inventori & Karantina Barang Rusak (Damaged Stock)
Sistem membagi lokasi menjadi 3 tipe:
1. `STORE`: Toko retail tempat kasir melayani transaksi langsung.
2. `WAREHOUSE`: Gudang penyimpanan atau gudang induk/distribusi.
3. `QUARANTINE`: Lokasi penampungan barang rusak/cacat/kadaluarsa.

**Aturan Penanganan Barang Rusak**:
1. Barang rusak hasil retur penjualan, barang rusak di rak toko, atau temuan selisih minus saat opname dipindahkan ke lokasi `QUARANTINE` dengan tipe mutasi `ADJUSTMENT_OUT` (lokasi asal) dan `TRANSFER_IN` (karantina).
2. Stok di `QUARANTINE` berstatus non-sellable (tidak akan pernah muncul di katalog kasir POS).
3. **Tindak Lanjut Barang Karantina**:
   - **Pemusnahan (Disposal / Write-off)**: Dibuat Berita Acara Pemusnahan oleh Manager. Stok karantina dipotong dengan mutasi `LOSS_DISPOSAL` dan dicatat sebagai biaya beban kerugian barang rusak.
   - **Retur ke Supplier (Return to Vendor - RTV)**: Dibuat dokumen Retur Pembelian ke Supplier. Stok karantina dipotong dengan mutasi `PURCHASE_RETURN` untuk ditukar barang baru atau nota kredit pengurangan hutang.

---

### 6.4 Pengadaan (Purchasing) & Penerimaan Barang (Goods Receiving)
1. **Alur PO**:
   `DRAFT` $\rightarrow$ `SUBMITTED` $\rightarrow$ `APPROVED` $\rightarrow$ `ORDERED` $\rightarrow$ `PARTIALLY_RECEIVED` $\rightarrow$ `RECEIVED` $\rightarrow$ `CANCELLED`.
2. **Penerimaan Barang Parsial (*Partial Receiving*)**:
   - Supplier seringkali mengirimkan barang bertahap.
   - Setiap kali kiriman tiba, Manager membuat lembar `GoodsReceipt` baru yang mereferensikan PO tersebut.
   - Input fisik barang yang datang dicatat (`received_qty`).
   - Sisa barang dihitung: $\text{Outstanding Qty} = \text{Ordered Qty} - \text{Total Received Qty}$.
   - Status PO otomatis beralih ke `PARTIALLY_RECEIVED` jika masih ada sisa, atau `RECEIVED` jika seluruh pesanan telah terpenuhi.
3. **Mutasi Stok**: Stok bertambah HANYA saat `GoodsReceipt` di-approve/disubmit, bukan saat PO dibuat.

---

### 6.5 Transfer Stok Antar Lokasi
1. **Alur Transfer**:
   `DRAFT` $\rightarrow$ `REQUESTED` $\rightarrow$ `APPROVED` $\rightarrow$ `IN_TRANSIT` $\rightarrow$ `RECEIVED` $\rightarrow$ `CANCELLED`.
2. **Prinsip In-Transit**:
   - Ketika transfer disetujui dan barang dikirim dari lokasi asal (*Shipment*), stok lokasi asal **langsung berkurang** dengan mutasi `TRANSFER_OUT`.
   - Barang berstatus `IN_TRANSIT`. Barang ini belum masuk dan belum bisa dijual di lokasi tujuan.
   - Saat lokasi tujuan menerima barang (*Receiving*), staf tujuan mengonfirmasi jumlah fisik yang diterima. Stok tujuan bertambah dengan mutasi `TRANSFER_IN`.
   - Jika terdapat selisih saat penerimaan (misal barang hilang di jalan), selisih dicatat sebagai `DAMAGE` atau `LOSS`.

---

### 6.6 Manajemen Shift Kasir (Cash Register / Cash Drawer)
Modul penting untuk mencegah manipulasi uang fisik di meja kasir:

1. **Buka Register (Open Shift)**:
   - Kasir login dan memilih shift.
   - Kasir wajib menginput **Modal Awal Kas (Opening Cash Float)** di laci (misal: Rp200.000 uang pecahan untuk kembalian).
   - Kasir tidak dapat membuka layar transaksi POS jika belum membuka shift.
2. **Pergerakan Kas Kasir (Cash In / Cash Out)**:
   - Pengeluaran kecil tak terduga (misal: beli kantong plastik darurat, bayar air galon toko) wajib dicatat sebagai *Cash Out* dengan alasan dan nominal.
   - Tambahan modal kembalian dari manager dicatat sebagai *Cash In*.
3. **Tutup Register (Close Shift & Rekonsiliasi)**:
   - Di akhir giliran kerja, kasir menekan tombol "Tutup Kasir".
   - Kasir menghitung uang fisik di laci dan menginput nominal total uang tunai yang dihitung (*Actual Cash Count*).
   - Sistem secara otomatis menghitung:
     $$\text{Expected Cash} = \text{Modal Awal} + \text{Total Penjualan Tunai} + \text{Total Cash In} - \text{Total Cash Out}$$
     $$\text{Selisih (Discrepancy)} = \text{Actual Cash Count} - \text{Expected Cash}$$
   - Jika ada selisih (Lebih/Kurang Kas), sistem mencatat nilai selisih dan mewajibkan catatan keterangan.
   - Jika selisih melebihi batas toleransi (misal > Rp10.000), sistem mewajibkan verifikasi dan tanda tangan elektronik/PIN Supervisor.
   - Sistem mencetak **Lembar Laporan Tutup Shift (X/Z Report)**.

---

### 6.7 Operasional POS (Point of Sale) & Desain Interaktif

1. **Antarmuka Kasir Ergonomis**:
   - Dibangun dengan Blade Components + **Alpine.js** untuk reaktivitas state keranjang belanja di memori browser.
   - **Shortcut Keyboard**:
     - `F1`: Fokus ke kolom Pencarian / Scan Barcode.
     - `F2`: Buka modal diskon manual / voucher.
     - `F4`: Hold / Simpan sementara transaksi keranjang (*Pending Cart*).
     - `F8`: Bayar Cepat Uang Pas Tunai.
     - `F9`: Buka modal Pembayaran Lengkap (Split Payment).
     - `ESC`: Tutup modal popup.
   - Input barcode mendukung USB/Bluetooth Barcode Scanner standar (auto-enter menambah item ke cart).
2. **Multi-Payment & Split Payment**:
   - Metode pembayaran yang didukung: `CASH`, `DEBIT_CARD`, `CREDIT_CARD`, `QRIS`, `BANK_TRANSFER`, `E_WALLET`.
   - Kasir dapat membagi tagihan (Split Payment), contoh: Total belanja Rp350.000 dibayar Tunai Rp100.000 dan QRIS Rp250.000.
   - Aturan validasi:
     $$\sum \text{Payment Methods} \ge \text{Grand Total}$$
   - Untuk pembayaran tunai berlebih, sistem menghitung uang kembalian (*Change*). Pembayaran non-tunai tidak boleh melebihi nilai sisa tagihan.

---

### 6.8 Aturan Otorisasi Void & Diskon Manual (Pencegahan Fraud)

1. **Pembatalan Item di Keranjang (Pre-Checkout Void)**:
   - Kasir bebas menghapus atau mengubah kuantitas item di keranjang belanja sebelum tombol bayar diproses, tanpa membutuhkan izin supervisor.
2. **Void Transaksi Setelah Struk Terbit (Post-Payment Void)**:
   - Terjadi jika pelanggan membatalkan pesanan sesaat setelah struk tercetak atau ada kesalahan fatal input metode bayar.
   - **Syarat Otorisasi**:
     - Wajib memasukkan **PIN Supervisor / Manager**.
     - Hanya boleh dilakukan pada **hari yang sama dan shift kasir yang sedang aktif**.
     - Kasir wajib memilih alasan void (*Salah input barang*, *Pelanggan batal*, *Transaksi ganda*).
   - **Dampak Sistemik**:
     - Status transaksi penjualan beralih menjadi `VOIDED`.
     - Stok barang otomatis dikembalikan ke inventori toko kasir dengan mutasi `SALE_VOID`.
     - Nominal pembayaran tunai dikeluarkan kembali dari pencatatan laci kas aktif.
     - Masuk ke tabel **Audit Log Void** yang dapat diinspeksi oleh Pemilik Toko.
3. **Diskon Manual di Layar Kasir**:
   - Diskon kupon/promo resmi sistem: Kasir langsung memilih dari dropdown diskon aktif.
   - Diskon manual dadakan (*Custom Discount*):
     - Kasir diberikan toleransi diskon maksimal **5% atau Rp20.000** per struk.
     - Jika diskon manual melebihi batas tersebut, layar otomatis memunculkan modal: **"Otorisasi Supervisor Dibutuhkan"** dan wajib memasukkan PIN Supervisor.

---

### 6.9 Retur Penjualan dari Pelanggan (Sales Return)
1. Pelanggan membawa struk belanja lama.
2. Kasir mencari nomor transaksi penjualan terkait (`sale_number`).
3. Sistem memvalidasi item dan sisa kuantitas yang berhak diretur (*Eligible Return Qty*).
4. Kasir memilih kondisi barang:
   - **Kondisi BAIK (GOOD)**: Barang otomatis masuk kembali ke stok jual toko kasir dengan mutasi `SALE_RETURN`.
   - **Kondisi RUSAK (DAMAGED)**: Barang **dilarang masuk ke stok jual**, melainkan otomatis dialihkan ke lokasi `QUARANTINE` (Gudang Karantina Barang Rusak).
5. Kasir mencatat metode pengembalian dana (*Refund Method*), misal pengembalian tunai dari laci kasir atau voucher belanja.

---

### 6.10 Stock Opname Berkala
1. Manager membuat sesi opname untuk lokasi tertentu (Toko / Gudang).
2. Sistem mengambil *snapshot* data saldo sistem saat sesi dibuka (*System Stock*).
3. Staf toko melakukan penghitungan fisik (*Blind Count* atau *Targeted Count*) dan menginput angka riil (*Physical Count*).
4. Sistem menghitung selisih: $\text{Difference} = \text{Physical Count} - \text{System Stock}$.
5. Manager/Admin memeriksa laporan selisih nilai rupiah (*Variance Value*).
6. Saat Manager menekan tombol **Approve & Adjust**:
   - Sistem secara otomatis menerbitkan mutasi penyesuaian `ADJUSTMENT_IN` (jika fisik lebih banyak) atau `ADJUSTMENT_OUT` (jika fisik kurang).
   - Saldo inventori sistem menjadi sama persis dengan fisik.
   - Sesi opname dikunci secara permanen sebagai dokumen audit.

---

## 7. Arsitektur Teknis (Laravel 13 Monolith)

### 7.1 Tech Stack Resmi

| Layer / Komponen | Teknologi Terpilih | Keterangan |
|---|---|---|
| **Framework Backend** | **Laravel 13** | Monolitik modern dengan PHP 8.3 / 8.4 |
| **Templating Engine** | **Laravel Blade** | Blade View Components modular dan berdaya guna tinggi |
| **Frontend Interactivity**| **Alpine.js + Vanilla JS** | Reaktivitas cepat untuk POS Cart, Modal PIN, dan Hotkey |
| **Styling & UI Design** | **Tailwind CSS + Custom Theme** | Bersih, elegan, modern, kontras tinggi, anti-AI Slop |
| **Database Utama** | **PostgreSQL / MySQL 8.0+** | Integritas foreign keys, ACID Transactions, Pessimistic Locking |
| **In-Memory Cache & Queue**| **Redis** | Antrean background jobs, cache master data, session |
| **PDF & Struk Generator** | **Barryvdh / DomPDF** | Generate cetak struk kasir 58mm/80mm thermal & laporan A4 |
| **Authentication** | **Laravel Session Auth** | Native web guard, proteksi CSRF, session per device |
| **Authorization** | **Gates, Policies & Middleware** | Penjagaan hak akses role dan verifikasi PIN Supervisor |
| **Testing Framework** | **Pest PHP** | Feature testing untuk transaksi stok dan checkout |

---

### 7.2 Struktur Arsitektur Bersih (Clean OOP & Layered MVC)

Aplikasi dibangun dengan memisahkan tanggung jawab kode secara tegas (*Separation of Concerns*):

```text
app/
├── Actions/                  # Single-action classes untuk alur simpel
│   └── CalculateProductPriceAction.php
├── Enums/                    # PHP 8.3 Backed Enums
│   ├── LocationType.php      # STORE, WAREHOUSE, QUARANTINE
│   ├── MovementType.php      # PURCHASE_RECEIPT, SALE, SALE_VOID, TRANSFER, etc.
│   ├── OrderStatus.php       # DRAFT, SUBMITTED, APPROVED, CANCELLED
│   ├── PaymentMethod.php     # CASH, DEBIT_CARD, CREDIT_CARD, QRIS, etc.
│   ├── ReturnCondition.php   # GOOD, DAMAGED
│   ├── ShiftStatus.php       # OPEN, CLOSED
│   └── UserRole.php          # ADMIN, MANAGER, CASHIER
├── Events/                   # Domain events untuk decoupled side-effects
│   ├── GoodsReceivedEvent.php
│   ├── LowStockDetectedEvent.php
│   ├── SaleCompletedEvent.php
│   └── SaleVoidedEvent.php
├── Http/
│   ├── Controllers/          # Thin Controllers (hanya delegasi & return view/response)
│   │   ├── InventoryController.php
│   │   ├── PosController.php
│   │   ├── PurchaseOrderController.php
│   │   ├── ShiftController.php
│   │   └── StockOpnameController.php
│   ├── Middleware/           # EnsureCashierHasOpenShift, CheckRole, etc.
│   └── Requests/             # Form Requests untuk validasi ketat
│       ├── CheckoutRequest.php
│       ├── OpenShiftRequest.php
│       └── SupervisorAuthRequest.php
├── Listeners/                # Event listeners (bisa di-queue)
│   ├── DecrementStockOnSaleListener.php
│   ├── SendLowStockNotificationListener.php
│   └── WriteAuditLogListener.php
├── Models/                   # Eloquent Models (Relasi, Mutator, Scope)
│   ├── Inventory.php
│   ├── Product.php
│   ├── ProductUnit.php
│   ├── Sale.php
│   ├── SaleItem.php
│   ├── Shift.php
│   └── StockMovement.php
├── Policies/                 # Laravel Policies untuk otorisasi per entitas
├── Services/                 # Pure OOP Domain Logic Layer
│   ├── CostingService.php    # Perhitungan Moving Average HPP
│   ├── InventoryService.php  # Pemotongan & penambahan stok + movement record
│   ├── PosCheckoutService.php# DB Transaction, lockForUpdate, sale creation
│   ├── ShiftService.php      # Buka/tutup shift & rekonsiliasi uang kas
│   └── StockTransferService.php
└── View/
    └── Components/           # Blade View Components reusable
        ├── Badge.php
        ├── Modal.php
        ├── StatCard.php
        └── Table.php
```

---

### 7.3 Concurrency & Database Transaction Handling
Operasi vital retail (terutama saat 2 kasir menjual barang terakhir secara bersamaan) dilindungi dengan:

```php
// Contoh implementasi di dalam PosCheckoutService:
DB::transaction(function () use ($dto, $cashier) {
    // 1. Pessimistic Locking pada stok toko kasir
    $inventory = Inventory::where('product_id', $dto->productId)
        ->where('location_id', $cashier->current_store_id)
        ->lockForUpdate()
        ->firstOrFail();

    if ($inventory->quantity < $dto->requestedBaseUnitQty) {
        throw new InsufficientStockException("Stok tidak mencukupi untuk item: {$dto->productName}");
    }

    // 2. Buat Transaksi Penjualan & Baris Item (kunci HPP saat ini)
    $sale = Sale::create([...]);
    
    // 3. Potong stok fisik
    $inventory->decrement('quantity', $dto->requestedBaseUnitQty);

    // 4. Catat Mutasi Buku Besar (Stock Movement)
    StockMovement::create([
        'product_id' => $dto->productId,
        'location_id' => $cashier->current_store_id,
        'quantity' => -$dto->requestedBaseUnitQty,
        'movement_type' => MovementType::SALE,
        'reference_type' => Sale::class,
        'reference_id' => $sale->id,
        'cogs_unit' => $inventory->product->average_cogs,
        'created_by' => $cashier->id,
    ]);
});
```

---

## 8. Panduan Desain UI/UX (Anti-AI Slop, Modern & Ergonomis)

Untuk memastikan tampilan sistem terlihat berkelas, tidak kaku, tidak seperti template AI generik, dan nyaman digunakan berjam-jam oleh kasir dan manager:

### 8.1 Prinsip Desain Visual
1. **Tipografi Modern Humanis**: Menggunakan Google Font **Plus Jakarta Sans** atau **Inter**. Font memiliki keterbacaan tinggi dengan dukungan angka desimal/rupiah tabular (`tabular-nums`) agar angka keuangan tersusun rata kanan dengan rapi.
2. **Palet Warna Terkurasi (Tailored HSL Palette)**:
   - **Background & Card**: Netral modern (*Slate/Zinc 50, 100, 800, 900*). Bukan putih silau murni dan bukan hitam pekat. Permukaan kartu memiliki kontras lembut dengan border tipis elegan (`border-slate-200 / border-slate-800`).
   - **Aksen Fungsional**:
     - *Emerald (Hijau Zamrud)*: Status Berhasil, Lunas, Kas Masuk, Stok Aman.
     - *Amber (Kuning Keemasan)*: Peringatan Stok Menipis, Menunggu Persetujuan Supervisor.
     - *Rose / Crimson (Merah Elegan)*: Tindakan Berbahaya, Void Transaksi, Barang Rusak, Selisih Kurang Kas.
     - *Indigo / Electric Blue*: Aksen navigasi utama, tombol Checkout, dan tab aktif.
3. **Ergonomi Layar POS Kasir**:
   - Layout 2 Kolom:
     - **Kolom Kiri (65%)**: Search bar autofocus besar, filter kategori berbasis chip/pill yang cepat diklik, dan grid kartu produk dengan foto tajam, nama jelas, indikator stok toko, dan tombol multi-satuan (Pcs/Dus).
     - **Kolom Kanan (35%)**: Ringkasan struk keranjang (*Sticky Panel*). Setiap item memiliki pengatur qty cepat (+ / -), subtotal per baris, potongan diskon, dan total bayar dengan ukuran teks besar (misal text-3xl font-bold).
     - **Tombol Bayar Raksasa**: Tombol "Bayar / Checkout (F9)" dengan tinggi minimum 56px, warna mencolok dan kontras, ramah layar sentuh maupun keyboard enter.

---

## 9. Skema Relasi Database Inti (Data Model Blueprint)

```text
users
├── id
├── name
├── username / email
├── password
├── role (Enum: ADMIN, MANAGER, CASHIER)
├── supervisor_pin (Hashed, untuk Manager/Admin)
├── assigned_store_id (Foreign Key ke locations, khusus kasir)
└── status (ACTIVE, INACTIVE)

locations
├── id
├── name (misal: "Toko Cabang Malioboro", "Gudang Pusat", "Gudang Karantina")
├── type (Enum: STORE, WAREHOUSE, QUARANTINE)
├── address
├── phone
└── is_active

products
├── id
├── sku (Unik)
├── name
├── category_id
├── brand_id
├── base_unit_name (misal: "Pcs")
├── average_cogs (Moving Average HPP saat ini)
├── default_selling_price
├── min_stock
├── reorder_point
├── image_path
└── is_active

product_units (Multi-Satuan)
├── id
├── product_id
├── unit_name (misal: "Dus", "Pak")
├── conversion_factor (misal: 24)
├── barcode (Opsional unik per satuan)
└── selling_price (Harga khusus satuan tersebut)

inventories (Saldo Stok per Lokasi dalam Base Unit)
├── id
├── product_id
├── location_id
├── quantity (Total saldo fisik dalam Base Unit)
└── UNIQUE(product_id, location_id)

stock_movements (Buku Besar Mutasi - IMMUTABLE)
├── id
├── product_id
├── location_id
├── quantity (+ atau - dalam Base Unit)
├── movement_type (Enum: PURCHASE_RECEIPT, SALE, SALE_VOID, TRANSFER_IN, etc.)
├── reference_type (Nama Model, misal: App\Models\Sale)
├── reference_id (ID Transaksi terkait)
├── cogs_per_unit (HPP per Base Unit saat mutasi)
├── notes
├── created_by (User ID)
└── created_at

shifts (Sesi Kasir)
├── id
├── cashier_id (User ID)
├── store_id (Location ID)
├── opened_at
├── closed_at (Nullable)
├── opening_cash_float (Modal awal laci)
├── expected_cash (Dihitung sistem)
├── actual_cash_count (Dihitung fisik kasir)
├── discrepancy (Selisih)
├── status (OPEN, CLOSED)
├── supervisor_approved_by (Nullable)
└── notes

sales
├── id
├── sale_number (Format: SLS/YYYYMMDD/XXXX)
├── store_id (Location ID)
├── cashier_id (User ID)
├── shift_id (Shift ID aktif)
├── customer_name (Nullable)
├── subtotal
├── discount_amount
├── grand_total
├── total_cogs (Akumulasi HPP transaksi ini)
├── total_profit (Grand Total - Total COGS)
├── status (COMPLETED, VOIDED)
├── void_reason (Nullable)
├── voided_by (Supervisor User ID, Nullable)
├── voided_at (Nullable)
└── created_at

sale_items
├── id
├── sale_id
├── product_id
├── product_unit_id (Nullable jika pakai base unit)
├── unit_name
├── unit_conversion_factor
├── quantity_in_unit (Jumlah dalam satuan beli, misal: 2 Dus)
├── quantity_in_base_unit (Jumlah dalam base unit, misal: 48 Pcs)
├── unit_selling_price
├── subtotal
├── cogs_per_unit (HPP Base Unit saat transaksi)
└── item_profit

sale_payments
├── id
├── sale_id
├── payment_method (Enum: CASH, QRIS, DEBIT, TRANSFER, etc.)
├── amount_paid
├── change_given
└── reference_number (Nomor Approval EDC / Ref QRIS)
```

---

## 10. Rencana Pengujian & Quality Assurance

Pengujian dilakukan secara komprehensif menggunakan **Pest PHP**:

1. **Inventory & Costing Tests**:
   - Tes penerimaan barang: Stok bertambah, mutasi tercatat, HPP Moving Average terhitung akurat.
   - Tes batas stok: Penjualan ditolak jika stok kurang dari permintaan (*No Negative Stock*).
   - Tes *pessimistic locking*: Menjamin tidak terjadi *race condition* saat dua request checkout terjadi di detik yang sama.
2. **POS & Shift Tests**:
   - Kasir tidak dapat bertransaksi tanpa membuka shift.
   - Transaksi POS sukses memotong stok toko bersangkutan secara tepat dalam base unit.
   - Perhitungan kembalian dan split payment valid $\sum \text{Payment} = \text{Grand Total}$.
   - Tes rekonsiliasi kas: Menghitung selisih uang fisik vs sistem secara presisi.
3. **Security & Authorization Tests**:
   - Kasir tidak bisa mengakses menu Pengguna, Konfigurasi, atau Laporan Keuangan Toko lain.
   - Void transaksi setelah struk terbit gagal tanpa PIN Supervisor yang valid.
   - Diskon manual melebihi 5% gagal tanpa PIN Supervisor.

---

## 11. Roadmap Implementasi Bertahap

- **Tahap 1: Setup Fondasi & Otentikasi**: Laravel 13, Migrasi Database, Role & Permission, Manajemen Pengguna, PIN Supervisor, Layout Blade responsif.
- **Tahap 2: Master Data & Multi-Satuan**: CRUD Produk, Kategori, Brand, Supplier, Lokasi (Toko, Gudang, Karantina), Konversi Satuan (`product_units`).
- **Tahap 3: Engine Buku Besar Stok & HPP**: Service Mutasi Stok, Perhitungan Moving Average HPP, Stock Adjustment, dan Karantina Barang Rusak.
- **Tahap 4: Pengadaan & Transfer**: Purchase Order, Penerimaan Bertahap (*Partial Goods Receipt*), Transfer Stok *In-Transit*, dan Retur Supplier (*RTV*).
- **Tahap 5: Manajemen Shift & Cash Drawer**: Buka Kasir (*Opening Float*), Kas Masuk/Keluar, Tutup Kasir, dan Rekonsiliasi Selisih Fisik.
- **Tahap 6: POS Interaktif & Checkout**: Blade + Alpine.js POS Screen, Multi-satuan, Hotkey, Scanner Autocomplete, Split Payment, Cetak Struk PDF/Thermal.
- **Tahap 7: Otorisasi Khusus & Retur Penjualan**: Modal Input PIN Supervisor (Void & Diskon Khusus), Retur Penjualan dengan pemisahan barang Baik vs Rusak.
- **Tahap 8: Stock Opname & Penyesuaian**: Sesi opname, input fisik, kalkulasi selisih, approval, dan mutasi koreksi otomatis.
- **Tahap 9: Laporan & Dashboard Analitik**: Laporan Laba Kotor, Perputaran Stok, Rekonsiliasi Kasir, dan Audit Log.
- **Tahap 10: Optimasi, Testing Pest PHP & Polishing**: End-to-end testing, optimasi query, caching Redis, dan penyempurnaan UX.
