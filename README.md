<div align="center">

# Card Layout Generator — BUKA LUWUR 1448 / 2026

<a href="https://laravel.com" target="_blank"><img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12"></a>
<a href="https://livewire.laravel.com" target="_blank"><img src="https://img.shields.io/badge/Livewire-3-FB70A9?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire 3"></a>
<a href="https://filamentphp.com" target="_blank"><img src="https://img.shields.io/badge/FilamentPHP-3-FDAE4B?style=for-the-badge&logoColor=white" alt="FilamentPHP 3"></a>
<img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">

</div>

Aplikasi berbasis **Laravel 12 + Filament v3** untuk membuat kartu berukuran **90 × 55 mm** (ukuran kartu standar) dengan **QR Code unik per nomor**, lalu menatanya otomatis ke lembar cetak **480 × 325 mm** dengan kapasitas **28 kartu per lembar**. Nomor kartu dapat diimpor massal lewat file Excel, dan hasilnya diekspor sebagai PDF siap cetak.

---

## Fitur

- **Panel admin Filament v3** (`/admin`) dengan login bawaan
- **Dua jenis kartu**: **Kartu K** dan **Kartu B**, masing-masing punya data nomor sendiri
- **Import nomor dari Excel** (`.xlsx` / `.xls`) langsung dari tabel Kartu K / Kartu B
  - Toleran terhadap nama kolom (`nomor`, `no`, `number`)
  - Duplikat (di database maupun di dalam file) dan baris kosong otomatis dilewati
  - Import Kartu K dibaca per 500 baris (chunk + batch insert) sehingga aman untuk file besar
- **QR Code otomatis** per nomor kartu (SVG, vektor → tetap tajam saat dicetak)
  - Generate satu per satu, terpilih (bulk), atau **Generate Semua QR** sekaligus
  - Preview QR langsung di tabel dan lewat modal
- **Template kartu**: unggah gambar desain kartu (maks. 5 MB), dipakai sebagai latar tiap kartu
- **Layout engine**: kartu ditata otomatis ke lembar 480 × 325 mm (lihat bagian [Tata Letak](#tata-letak-lembar-cetak))
- **Pembagian otomatis ke banyak lembar**: jumlah kartu dipecah per 28 kartu → 1 halaman PDF per lembar
- **Export PDF** (DomPDF) dengan ukuran halaman tepat 480 × 325 mm, tampil langsung di browser
- **Preview HTML** dengan tombol cetak langsung dari browser

---

## Tech Stack

| Komponen | Keterangan |
|---|---|
| Framework | Laravel 12 (PHP 8.2+) |
| Admin panel | Filament v3 |
| Reaktivitas UI | Livewire 3 (dibawa otomatis oleh Filament v3) |
| Import Excel | `maatwebsite/excel` |
| QR Code | `simplesoftwareio/simple-qrcode` (output SVG) |
| PDF | `barryvdh/laravel-dompdf` |
| Database | SQLite (default), bisa diganti MySQL/PostgreSQL lewat `.env` |

---

## Instalasi

> Jika repository belum menyertakan `composer.json`, jalankan `composer require` seperti pada langkah 2. Jika sudah ada, cukup `composer install`.

```bash
# 1. Clone repository, lalu masuk ke foldernya
git clone https://github.com/rohmanzm11/Card-Layout-Generator.git
cd Card-Layout-Generator

# 2. Install dependency
composer install

# Jika composer.json belum ada / paket belum terpasang:
composer require filament/filament:"^3.0" \
    maatwebsite/excel \
    simplesoftwareio/simple-qrcode \
    barryvdh/laravel-dompdf

# 3. Siapkan environment
cp .env.example .env
php artisan key:generate

# 4. Siapkan database (default: SQLite)
touch database/database.sqlite
php artisan migrate

# 5. Link storage agar gambar template bisa diakses
php artisan storage:link

# 6. Buat akun admin
php artisan make:filament-user

# 7. Jalankan
php artisan serve
```

Buka `http://localhost:8000` — otomatis diarahkan ke `/admin/login`.

### Ekstensi PHP yang dibutuhkan

`gd`, `zip`, `xml`, `mbstring`, `fileinfo`, `sqlite3` (atau driver DB yang dipakai).

---

## Cara Penggunaan

### 1. Siapkan Template
Menu **Master → Template**: isi `Nama Batch` dan unggah gambar desain kartu (rasio 90 × 55 mm). Gambar akan diregangkan penuh menutupi tiap kartu.

### 2. Import Nomor Kartu
Menu **Kartu → Kartu K** atau **Kartu B** → tombol **Import Excel**.

Format file Excel — baris pertama wajib berisi header `nomor`:

| nomor |
|-------|
| 1001  |
| 1002  |
| 1003  |

Hasil import ditampilkan sebagai notifikasi: jumlah berhasil dan jumlah dilewati (duplikat/kosong).

> **Catatan:** Import hanya mengisi kolom `nomor`. QR Code dibuat di langkah berikutnya.

### 3. Generate QR Code
Pada tabel Kartu K / Kartu B:
- **Generate Semua QR** — isi QR untuk semua kartu yang belum punya
- **Generate QR** per baris, atau **Generate QR Terpilih** (bulk)
- **Preview QR** untuk melihat hasilnya

Isi QR Code = nomor kartu (`qr_code` diisi sama dengan `nomor`).

### 4. Export / Cetak
Ada dua cara:

**a. Halaman Export Kartu** (menu **Printing → Export Kartu**)
Pilih **Nomor Awal**, **Jumlah**, dan **Template** untuk Kartu K (kiri) atau Kartu B (kanan), lalu klik export. PDF langsung terbuka di browser.

**b. Print Kartu** (menu **Printing → Print Kartu**)
Buat record cetak (jenis, kartu awal, jumlah, template), lalu klik **Lihat** untuk membuka PDF.

Batas jumlah per proses: **Kartu K maks. 5.040** (180 lembar), **Kartu B maks. 5.000**.

Contoh: 100 kartu → 4 lembar (28 + 28 + 28 + 16).

---

## Tata Letak Lembar Cetak

Ukuran halaman: **480 × 325 mm (landscape)**, margin 0, offset grid **15 mm kiri** dan **5 mm atas**.

```
┌──────────────────────────────────────────────────────────┐
│ 5 kolom × 4 baris  = 20 kartu (90 × 55 mm, tegak lurus)   │
│ ┌────┬────┬────┬────┬────┐                                │
│ │ 1  │ 2  │ 3  │ 4  │ 5  │                                │
│ ├────┼────┼────┼────┼────┤                                │
│ │ …  │    │    │    │ 20 │                                │
│ └────┴────┴────┴────┴────┘                                │
│ 8 kolom × 1 baris = 8 kartu (diputar 90°, slot 55 × 90 mm)│
│ ┌──┬──┬──┬──┬──┬──┬──┬──┐                                 │
│ │21│22│23│24│25│26│27│28│                                 │
│ └──┴──┴──┴──┴──┴──┴──┴──┘                                 │
└──────────────────────────────────────────────────────────┘
        Total: 20 + 8 = 28 kartu per lembar
```

QR Code berukuran sekitar 17–20 mm, ditempatkan di area kanan-tengah kartu, dengan nomor kartu tercetak di bawahnya. Posisi dapat disesuaikan di CSS `.qr-box` pada `resources/views/print-kartu/pdf.blade.php` (dan `preview.blade.php`) sesuai desain template Anda.

---

## Struktur Proyek

```
app/
├── Filament/
│   ├── Pages/ExportKartu.php          # Halaman export (form K & B)
│   └── Resources/
│       ├── KartuKResource.php         # CRUD + import + generate QR Kartu K
│       ├── KartuBResource.php         # CRUD + import + generate QR Kartu B
│       ├── TemplateResource.php       # Master template gambar
│       └── PrintKartuResource.php     # Riwayat/pengaturan cetak
├── Http/Controllers/Printkartucontroller.php   # Susun data QR + render PDF/preview
├── Imports/
│   ├── KartuKImport.php               # Import Excel Kartu K (chunk 500)
│   └── KartuBImport.php               # Import Excel Kartu B
├── Models/                            # KartuK, KartuB, Template, PrintKartu, User
└── Providers/Filament/AdminPanelProvider.php

resources/views/
├── print-kartu/pdf.blade.php          # Layout PDF 480×325 mm (28 kartu/halaman)
├── print-kartu/preview.blade.php      # Preview HTML + tombol print
└── filament/…                         # View halaman Filament & modal QR

routes/web.php                         # Route preview & download PDF
```

### Skema Database

| Tabel | Kolom utama |
|---|---|
| `kartu_k_s` | `id`, `nomor`, `qr_code` |
| `kartu_b_s` | `id`, `nomor`, `qr_code` |
| `templates` | `id`, `nama_batch`, `gambar` |
| `print_kartus` | `id`, `jenis_kartu` (K/B), `kartu_k_s_id`, `kartu_b_s_id`, `jumlah`, `template_id` |

### Routes

| Method | URL | Fungsi |
|---|---|---|
| GET | `/` | Redirect ke `/admin/login` |
| GET | `/print-kartu/{record}/preview` | Preview HTML siap print |
| GET | `/print-kartu/{record}/download` | PDF (tampil inline di browser) |

---

## Catatan & Tips

- **Keamanan:** route `preview` dan `download` saat ini **tidak** memakai middleware `auth` (baris `->middleware(['auth'])` sedang dikomentari). Aktifkan kembali bila aplikasi dipublikasikan.
- **Template tidak muncul di PDF?** Pastikan sudah menjalankan `php artisan storage:link` dan file gambar ada di `storage/app/public/templates/`.
- **Import gagal untuk `.csv`?** Form import saat ini hanya menerima `.xlsx` dan `.xls`. Simpan ulang file CSV sebagai Excel, atau tambahkan MIME `text/csv` pada `acceptedFileTypes` di resource terkait.
- **Nomor harus unik** per jenis kartu; nomor yang sudah ada akan dilewati saat import.
- **Jumlah besar:** untuk ribuan kartu, naikkan `memory_limit` dan `max_execution_time` PHP karena DomPDF merender seluruh halaman dalam satu proses.
- Untuk hasil cetak presisi, atur printer ke **skala 100% / Actual size** tanpa margin tambahan.

---

## Lisensi

Proyek internal — sesuaikan lisensi sesuai kebutuhan Anda.
