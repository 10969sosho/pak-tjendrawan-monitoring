# Program Monitoring (V1) — Dokumentasi Sistem

Aplikasi internal berbasis Laravel + Filament untuk mencatat proses bisnis dari penawaran sampai PO, penerimaan barang & QC, produksi, packing, pengiriman, serta rekap laporan dan sebagian master data.

## 1) Akses Aplikasi

- URL Admin Panel: `/admin`
- Halaman root (`/`) otomatis redirect ke `/admin`
- Panel Filament memakai brand `Program Monitoring`
- Custom asset: memuat CSS `dropdown-fix.css` untuk perbaikan tampilan dropdown di panel

## 2) Role & Hak Akses

### Role
- `Admin`

### Perilaku akses (umum)
- “Laporan Pusat” dan widget ringkasan dashboard dibatasi untuk `Admin`.
- Sebagian modul “Master Data/Inventory/Keuangan” tidak ditampilkan di sidebar (tetapi tetap ada URL dan bisa diakses jika tahu rutenya).
- Kebijakan (policy) untuk sebagian besar data: semua user bisa lihat (view), tetapi create/update/delete umumnya hanya `Admin`.

### Akun admin default (seeding)
Seeder membuat user admin default. Segera ganti kredensialnya setelah instalasi.

## 3) Alur Utama (End-to-End)

Alur standar yang direkomendasikan:

1. **Penawaran**
   - Buat penawaran ke customer (opsional ambil estimasi biaya dari Master Biaya).
   - Ubah status penawaran menjadi `deal` jika disepakati.
2. **Buat PO dari Penawaran (opsional)**
   - Jika penawaran `deal`, tersedia aksi “Buat PO” di tabel Penawaran.
   - Data customer, nama produk, qty, harga deal akan mengisi PO otomatis.
3. **PO Customer (Tracking)**
   - PO punya tracking tahap (`stage`) untuk memantau proses.
4. **Barang Masuk**
   - Catat barang datang dari PO.
   - Lakukan QC dengan status: `pending` / `lolos` / `reject`.
5. **Produksi**
   - Buat record produksi terkait PO (dan opsional terkait “Barang Masuk” yang diproses).
6. **Packing**
   - Catat hasil packing dari produksi (total pcs, isi per pack, total pack, sisa).
7. **Pengiriman**
   - Catat pengiriman per PO (qty kirim, penerima, status: dikirim/diterima/done).
8. **Laporan**
   - Gunakan “Laporan Pusat” untuk ringkasan bulanan (PDF).
   - Untuk detail data per modul, gunakan list modul masing-masing (dan fitur export bila tersedia).

## 4) Status Tracking PO (Otomatis)

Field `stage` pada PO disesuaikan otomatis berdasarkan data turunan yang sudah ada (berdasarkan relasi).

Urutan prioritas:
1. Jika ada **Pengiriman** dengan `status = done` → `stage = done`
2. Jika ada **Pengiriman** (apapun statusnya) → `stage = pengiriman`
3. Jika ada **Packing** → `stage = packing`
4. Jika ada **Produksi** → `stage = produksi`
5. Jika ada **Barang Masuk**
   - Jika sudah ada hasil QC (`lolos`/`reject`) → `stage = qc`
   - Jika belum ada hasil QC (masih `pending`) → `stage = barang_datang`
6. Jika terkait **Penawaran** dan statusnya `deal` → `stage = deal`
7. Jika tidak memenuhi semua kondisi di atas → `stage = draft`

Catatan:
- Meskipun ada dropdown `stage` di form PO, setelah penyimpanan sistem akan menghitung ulang `stage` berdasarkan kondisi di atas.

## 5) Modul & Fitur (Per Menu)

### A) Master

#### 1. Customer
Tujuan: menyimpan daftar customer yang terkait penawaran dan PO.

Lokasi:
- Menu: `Master > Customer`
- URL: `/admin/customers`

Field:
- Nama Customer, PIC, No HP, Alamat, Catatan

Relasi/Integrasi:
- Dipakai pada Penawaran (`customer_id`)
- Dipakai pada PO (`customer_id`)

#### 2. Master Biaya
Tujuan: template estimasi biaya per produk (untuk membantu penawaran).

Lokasi:
- Menu: `Master > Master Biaya`
- URL: `/admin/master-biayas`

Struktur:
- Header: nama produk, kode produk, catatan
- Detail biaya (repeater):
  - nama biaya, qty, satuan, harga, subtotal (otomatis)
- Total estimasi biaya:
  - `estimasi_biaya` (otomatis dijumlah dari subtotal detail)

Otomatisasi:
- `subtotal = qty * harga`
- `estimasi_biaya = sum(subtotal)`

Dipakai oleh:
- Penawaran (pilih Master Biaya untuk mengisi nama produk + estimasi biaya)

### B) Transaksi

#### 1. Penawaran
Tujuan: mencatat penawaran ke customer, termasuk estimasi profit.

Lokasi:
- Menu: `Transaksi > Penawaran`
- URL: `/admin/penawarans`

Field utama:
- nomor penawaran (otomatis `QTN-YYYYmmddHHMMSS`)
- tanggal
- customer
- master biaya (opsional)
- nama produk
- qty
- harga penawaran
- estimasi biaya (otomatis)
- estimasi profit (otomatis)
- status: `draft` / `deal` / `reject`

Otomatisasi:
- Saat memilih Master Biaya:
  - `nama_produk` mengikuti Master Biaya
  - `estimasi_biaya` mengikuti Master Biaya
- Saat mengisi `harga_penawaran`:
  - `estimasi_profit = harga_penawaran - estimasi_biaya`

Aksi penting:
- **Buat PO** (muncul hanya jika `status = deal` dan PO belum pernah dibuat dari penawaran tersebut)
  - Membuat PO baru dengan stage awal `deal`

#### 2. PO Customer
Tujuan: pusat tracking proses order customer sampai pengiriman selesai.

Lokasi:
- Menu: `Transaksi > PO Customer`
- URL: `/admin/purchase-orders`

Field utama:
- number (otomatis `PO-YYYYmmddHHMMSS`, read-only)
- tanggal PO
- customer
- penawaran (opsional)
- nama produk, deskripsi order
- qty order
- harga deal
- deadline
- status tracking (`stage`) dan snapshot harga (`total_amount`)
- catatan

Otomatisasi:
- Jika memilih penawaran:
  - customer, nama produk, qty, harga deal, total_amount terisi otomatis
- Saat menyimpan:
  - total_amount mengikuti harga_deal
  - stage akan dihitung ulang otomatis (lihat bagian “Status Tracking PO”)

#### 3. Barang Masuk + QC
Tujuan: mencatat barang datang terkait PO, sekaligus status QC.

Lokasi:
- Menu: `Transaksi > Barang Masuk`
- URL: `/admin/barang-masuks`

Field:
- PO
- tanggal masuk
- nama barang, jenis barang
- berat masuk + satuan (`kg`/`ton`)
- hasil konversi ke kg (otomatis)
- status QC: `pending` / `lolos` / `reject`
- catatan QC, catatan umum

Otomatisasi:
- Konversi:
  - jika satuan `ton`, `hasil_konversi_kg = berat_masuk * 1000`
  - jika satuan `kg`, `hasil_konversi_kg = berat_masuk`
- Setelah simpan:
  - stage PO akan disesuaikan otomatis (barang datang / qc)

#### 4. Produksi
Tujuan: mencatat proses produksi yang dilakukan untuk PO (opsional berdasarkan barang masuk tertentu).

Lokasi:
- Menu: `Transaksi > Produksi`
- URL: `/admin/productions`

Field:
- number (otomatis `PRD-YYYYmmddHHMMSS`, read-only)
- tanggal proses
- PO (wajib)
- barang masuk (opsional)
- berat awal (kg)
- hasil produksi (qty) + satuan hasil (default pcs)
- status: `Proses(draft)` / `Selesai(completed)` / `Batal(cancelled)`
- catatan

Integrasi:
- Setelah simpan, stage PO akan disesuaikan otomatis (produksi)

#### 5. Packing
Tujuan: mencatat hasil packing dari produksi.

Lokasi:
- Menu: `Transaksi > Packing`
- URL: `/admin/packings`

Field:
- produksi (wajib)
- tanggal packing
- total pcs
- isi per pack
- total pack (otomatis)
- sisa pcs (otomatis)
- catatan

Otomatisasi:
- Jika `isi_per_pack <= 0`:
  - total_pack = 0
  - sisa_pcs = total_pcs
- Jika `isi_per_pack > 0`:
  - total_pack = floor(total_pcs / isi_per_pack)
  - sisa_pcs = total_pcs - (total_pack * isi_per_pack)

Integrasi:
- Setelah simpan, stage PO akan disesuaikan otomatis (packing)

#### 6. Pengiriman
Tujuan: mencatat pengiriman barang per PO sampai selesai (create manual, tidak otomatis dari Packing).

Lokasi:
- Menu: `Transaksi > Pengiriman`
- URL: `/admin/pengirimans`

Field:
- PO (wajib)
- Item / Produk (wajib, memilih item PO yang sudah di packing dan siap dikirim)
- Qty Kirim (tidak boleh melebihi qty siap kirim)
- tanggal kirim
- penerima
- tujuan pengiriman
- detail pengiriman
- status: `siap_kirim` (Belum Kirim) / `dikirim` / `diterima` / `done`
- catatan

Alur:
1. Pilih PO terlebih dahulu
2. Pilih Item / Produk (hanya item yang sudah di packing dan memiliki qty siap kirim > 0 yang muncul)
3. Input Qty Kirim (default sesuai qty siap kirim)
4. Isi field lainnya dan simpan

Catatan:
- Setiap pengiriman hanya untuk 1 item. Untuk multiple item, buat pengiriman satu per satu.
- Qty siap kirim dihitung dari total packing complete dikurangi total yang sudah dikirim.
- Setelah simpan, stage PO dan PO Item akan disesuaikan otomatis (pengiriman/done).

### C) Laporan

#### 1. Laporan Pusat (Admin)
Tujuan: satu pintu laporan dan ringkasan bulanan.

Lokasi:
- Menu: `Laporan > Laporan Pusat`
- URL: `/admin/reports`

Konten:
- Ringkasan statistik (widget)
- Kartu shortcut untuk menuju laporan per modul

Header Action:
- Download Ringkasan Bulanan (PDF)
  - berisi: total PO aktif, total PO done, barang pending QC, total produksi bulan ini, total harga deal (estimasi), total estimasi biaya, total estimasi profit

#### 2. Dashboard (Ringkasan)
Tujuan: ringkasan cepat aktivitas dan kondisi operasional.

Lokasi:
- URL: `/admin`

Konten (widget):
- Ringkasan status PO/produksi/QC
- Tabel barang stok menipis (berdasarkan `current_stock <= min_stock`)
- Tabel aktivitas terbaru (berdasarkan data pergerakan stok)

### D) Sistem

#### 1. Manajemen User
Tujuan: kelola user dan role.

Lokasi:
- Menu: `Sistem > Manajemen User`
- URL: `/admin/users`

Field:
- name, email, roles, email_verified_at, password

Catatan:
- Role menggunakan Spatie Laravel Permission.

### E) Inventory (Sebagian menu disembunyikan dari sidebar)

#### 1. Kategori Barang
Tujuan: mengelompokkan produk.

Lokasi:
- URL: `/admin/categories` (menu sidebar tidak ditampilkan)

Field:
- name (slug dibuat otomatis dari name dan dijaga agar unik)

#### 2. Master Barang
Tujuan: master produk dan BOM (bahan baku).

Lokasi:
- URL: `/admin/products` (menu sidebar tidak ditampilkan)

Field:
- kategori, nama, SKU, unit, harga, min stock, current stock
- BOM (repeater):
  - material (produk bahan baku), qty needed

Catatan stok:
- `current_stock` pada form produk bersifat read-only.

#### 3. Kartu Stok / Pergerakan
Tujuan: melihat histori pergerakan stok masuk/keluar.

Lokasi:
- URL: `/admin/stock-movements` (menu sidebar tidak ditampilkan)

Fitur:
- Filter berdasarkan produk dan tipe (in/out)
- Menampilkan tanggal, produk, tipe, qty, referensi

Catatan:
- Pencatatan “Stock Movement” saat ini bersifat histori; tidak ada proses otomatis di UI yang mengubah `current_stock` ketika membuat Stock Movement.

### F) Keuangan (Sebagian menu disembunyikan dari sidebar)

#### 1. Pengeluaran Keuangan
Tujuan: catat pengeluaran operasional dan bisa dikaitkan ke PO/Produksi.

Lokasi:
- URL: `/admin/expenses` (menu sidebar tidak ditampilkan)

Field:
- kategori biaya
- tanggal
- jumlah
- catatan
- referensi (opsional):
  - tipe referensi: Purchase Order / Produksi
  - pilih nomor referensi

#### 2. Kategori Biaya
Tujuan: master kategori untuk pengeluaran.

Lokasi:
- URL: `/admin/expense-categories` (menu sidebar tidak ditampilkan)

## 6) Export & Output File

- Beberapa list menyediakan export (bulk action) untuk mengunduh data (misalnya modul Produk dan Pengeluaran).
- “Ringkasan Bulanan” tersedia dalam format PDF dari “Laporan Pusat”.

## 7) Struktur Data (Ringkas)

Relasi inti:
- Customer → memiliki banyak Penawaran, PO
- Penawaran → milik Customer, opsional milik Master Biaya, bisa membuat PO
- PO → milik Customer, opsional milik Penawaran
- PO → memiliki banyak Barang Masuk, Produksi, Pengiriman
- Produksi → milik PO, opsional terkait Barang Masuk
- Produksi → memiliki banyak Packing

Master biaya:
- Master Biaya → memiliki banyak Master Biaya Detail

Inventory:
- Kategori → memiliki banyak Produk
- Produk → memiliki banyak BOM (bill_of_materials) sebagai finished product → material
- Produk → memiliki banyak Stock Movement

Keuangan:
- Kategori Biaya → memiliki banyak Pengeluaran
- Pengeluaran → opsional memiliki referensi polymorphic (PO atau Produksi)

## 8) Setup & Menjalankan (Developer)

### Requirement
- PHP 8.2+
- Composer
- Node.js + npm
- Database: SQLite (default file: `database/database.sqlite`) atau driver lain sesuai `.env`

### Setup cepat (disarankan)

```bash
composer run setup
```

### Setup manual (alternatif)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

### Menjalankan mode development

```bash
composer run dev
```

Akses:
- `http://127.0.0.1:8000/admin`

### Testing

```bash
composer run test
```

## 9) Catatan Operasional

- Tracking PO bersifat otomatis berdasarkan ada/tidaknya data turunan (barang masuk, produksi, packing, pengiriman).
- Gunakan “Laporan Pusat” untuk ringkasan, dan list modul masing-masing untuk detail.
