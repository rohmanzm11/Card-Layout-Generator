# Card Layout Generator (Laravel 12 + Filament v3)

Aplikasi untuk membuat **kartu berukuran 90 × 55 mm** (ukuran kartu standar), lalu menata otomatis ke dalam **lembar A3 cetak 480 × 320 mm** dengan target **28 kartu per lembar**. Setiap kartu memiliki **QR Code** unik, dan nomor kartu dapat diinput **massal lewat file Excel**.

---

## Daftar Isi

1. [Fitur Utama](#fitur-utama)
2. [Teknologi](#teknologi)
3. [Catatan Penting Ukuran Kartu](#catatan-penting-ukuran-kartu)
4. [Instalasi](#instalasi)
5. [Konfigurasi](#konfigurasi)
6. [Struktur Proyek](#struktur-proyek)
7. [Format File Excel](#format-file-excel)
8. [Alur Penggunaan](#alur-penggunaan)
9. [Contoh Implementasi](#contoh-implementasi)
10. [Tips Cetak](#tips-cetak)
11. [Roadmap](#roadmap)

---

## Fitur Utama

- Panel admin berbasis **Filament v3**
- **Import nomor kartu dari Excel** (`.xlsx`, `.xls`, `.csv`)
- **QR Code generator** otomatis per nomor kartu (SVG/PNG, vektor untuk cetak tajam)
- **Layout engine**: menghitung otomatis jumlah kolom × baris pada lembar 480 × 320 mm
- **Export PDF** siap cetak (ukuran halaman tepat 480 × 320 mm) dengan crop mark opsional
- Pembagian otomatis ke beberapa lembar (misal 100 kartu → 4 lembar, 28 kartu per lembar)
- Preview layout di panel sebelum cetak
- Ukuran kartu, ukuran lembar, margin, gap, dan jumlah per lembar dapat diatur lewat `.env`

## Teknologi

| Komponen | Versi |
|---|---|
| PHP | 8.4 |
| Laravel | 12.x |
| Filament | 3.x |
| Import Excel | `maatwebsite/excel` |
| QR Code | `endroid/qr-code` (atau `simplesoftwareio/simple-qrcode`) |
| PDF | `barryvdh/laravel-dompdf` (alternatif: `spatie/laravel-pdf` + Browsershot) |
| Database | MySQL 8 / MariaDB / PostgreSQL / SQLite |

Ekstensi PHP yang dibutuhkan: `gd`, `zip`, `xml`, `mbstring`, `intl`, `bcmath`, `fileinfo`.

---

## Catatan Penting Ukuran Kartu

> Baca bagian ini sebelum mulai, karena memengaruhi hasil layout.

Spesifikasi awal: kartu **90 × 55 mm**, lembar **480 × 320 mm**, target **28 kartu**.

Hasil perhitungan geometri (tanpa margin dan gap):

| Orientasi kartu | Kolom × Baris | Total muat |
|---|---|---|
| Landscape (90 × 55) | 5 × 5 | **25** |
| Portrait (55 × 90) | 8 × 3 | **24** |
| Campuran (landscape + portrait) | 4×5 + 2×3 | **26** |

Artinya **28 kartu 90 × 55 mm tidak muat** di 480 × 320 mm tanpa mengecilkan kartu. Pilihan yang tersedia:

1. **Ubah ukuran kartu** agar muat grid 7 × 4 = 28 (sel maksimal ±68,5 × 80 mm), misalnya kartu **55 × 80 mm** (portrait).
2. **Ubah ukuran lembar** (misalnya A3+/SRA3 lebih besar) atau **kurangi jumlah** menjadi 25 kartu per lembar.
3. Jika yang dimaksud adalah kartu **9 × 55 mm** (bukan 90 × 55), grid 28 kartu sangat longgar dan tinggal diatur `CARD_WIDTH_MM=9`.

Semua nilai ini diatur di `.env`, dan aplikasi akan **menampilkan peringatan** jika kapasitas lembar lebih kecil dari jumlah kartu yang diminta.

---

## Instalasi

### 1. Buat proyek

```bash
composer create-project laravel/laravel card-generator "^12.0"
cd card-generator
```

Pastikan versi PHP:

```bash
php -v   # PHP 8.4.x
```

### 2. Install dependensi

```bash
composer require filament/filament:"^3.3" -W
composer require maatwebsite/excel
composer require endroid/qr-code
composer require barryvdh/laravel-dompdf
```

### 3. Setup Filament

```bash
php artisan filament:install --panels
# Pilih panel id: admin
php artisan make:filament-user
```

### 4. Konfigurasi database

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=card_generator
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Migrasi & storage

```bash
php artisan migrate
php artisan storage:link
```

### 6. Jalankan

```bash
php artisan serve
```

Buka `http://localhost:8000/admin`.

---

## Konfigurasi

Tambahkan di `.env`:

```env
# Ukuran kartu (mm)
CARD_WIDTH_MM=90
CARD_HEIGHT_MM=55

# Ukuran lembar cetak (mm)
SHEET_WIDTH_MM=480
SHEET_HEIGHT_MM=320

# Jumlah kartu target per lembar
CARDS_PER_SHEET=28

# Tata letak
SHEET_MARGIN_MM=5
CARD_GAP_MM=0
ALLOW_ROTATE=true
CROP_MARKS=true

# QR Code
QR_SIZE_MM=25
QR_PREFIX=
QR_ERROR_CORRECTION=medium
```

Buat file `config/card.php`:

```php
<?php

return [
    'card' => [
        'width'  => (float) env('CARD_WIDTH_MM', 90),
        'height' => (float) env('CARD_HEIGHT_MM', 55),
    ],
    'sheet' => [
        'width'  => (float) env('SHEET_WIDTH_MM', 480),
        'height' => (float) env('SHEET_HEIGHT_MM', 320),
        'margin' => (float) env('SHEET_MARGIN_MM', 5),
        'gap'    => (float) env('CARD_GAP_MM', 0),
        'per_sheet' => (int) env('CARDS_PER_SHEET', 28),
        'allow_rotate' => (bool) env('ALLOW_ROTATE', true),
        'crop_marks'   => (bool) env('CROP_MARKS', true),
    ],
    'qr' => [
        'size'   => (float) env('QR_SIZE_MM', 25),
        'prefix' => env('QR_PREFIX', ''),
        'ecc'    => env('QR_ERROR_CORRECTION', 'medium'),
    ],
];
```

---

## Struktur Proyek

```
app/
├── Filament/
│   └── Resources/
│       ├── CardResource.php            # CRUD kartu + tombol Import Excel
│       └── PrintBatchResource.php      # Batch cetak + preview + export PDF
├── Imports/
│   └── CardsImport.php                 # Import nomor dari Excel
├── Models/
│   ├── Card.php
│   └── PrintBatch.php
├── Services/
│   ├── LayoutCalculator.php            # Hitung grid kartu pada lembar
│   ├── QrCodeService.php               # Generate QR (SVG/PNG)
│   └── SheetPdfService.php             # Render PDF per lembar
config/
└── card.php
resources/views/pdf/
└── sheet.blade.php                     # Template lembar cetak
```

---

## Format File Excel

Baris pertama adalah header. Kolom minimal: `nomor`.

| nomor | nama | keterangan |
|---|---|---|
| 0001 | Budi Santoso | Peserta A |
| 0002 | Siti Aminah | Peserta B |
| 0003 | Andi Wijaya | Peserta C |

Aturan:

- Kolom `nomor` **wajib** dan **unik**.
- Format kolom `nomor` sebaiknya **Text** agar angka nol di depan (`0001`) tidak hilang.
- Kolom `nama` dan `keterangan` opsional.
- Baris duplikat atau kosong akan dilewati dan dilaporkan setelah import.

---

## Alur Penggunaan

1. Login ke `/admin`.
2. Buka menu **Kartu** → klik **Import Excel** → unggah file.
3. Buka menu **Batch Cetak** → **Buat Batch** → pilih kartu (atau seluruh kartu baru).
4. Klik **Preview Layout** untuk melihat susunan di lembar 480 × 320 mm.
5. Klik **Export PDF** → hasil berupa 1 PDF multi-halaman (1 halaman = 1 lembar).
6. Cetak di ukuran **100% / Actual Size** (jangan *fit to page*).

---

## Contoh Implementasi

### Migrasi

```php
Schema::create('cards', function (Blueprint $table) {
    $table->id();
    $table->string('number')->unique();
    $table->string('name')->nullable();
    $table->string('note')->nullable();
    $table->timestamp('printed_at')->nullable();
    $table->timestamps();
});

Schema::create('print_batches', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->unsignedInteger('total_cards')->default(0);
    $table->unsignedInteger('total_sheets')->default(0);
    $table->timestamps();
});
```

### Import Excel (`app/Imports/CardsImport.php`)

```php
<?php

namespace App\Imports;

use App\Models\Card;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class CardsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public function model(array $row): Card
    {
        return Card::updateOrCreate(
            ['number' => (string) $row['nomor']],
            ['name' => $row['nama'] ?? null, 'note' => $row['keterangan'] ?? null],
        );
    }

    public function rules(): array
    {
        return ['nomor' => ['required']];
    }
}
```

### Tombol Import di Filament (`CardResource` → `ListCards`)

```php
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CardsImport;
use Filament\Notifications\Notification;

protected function getHeaderActions(): array
{
    return [
        Actions\CreateAction::make(),
        Actions\Action::make('import')
            ->label('Import Excel')
            ->icon('heroicon-o-arrow-up-tray')
            ->form([
                FileUpload::make('file')
                    ->label('File Excel')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                    ])
                    ->disk('local')
                    ->directory('imports')
                    ->required(),
            ])
            ->action(function (array $data) {
                Excel::import(new CardsImport, storage_path('app/private/' . $data['file']));
                Notification::make()->title('Import berhasil')->success()->send();
            }),
    ];
}
```

> Sesuaikan path file dengan disk `local` pada Laravel 12 (`storage/app/private`).

### Layout Calculator (`app/Services/LayoutCalculator.php`)

```php
<?php

namespace App\Services;

class LayoutCalculator
{
    public function calculate(): array
    {
        $cfg = config('card');
        $sw = $cfg['sheet']['width'];  $sh = $cfg['sheet']['height'];
        $m  = $cfg['sheet']['margin']; $g  = $cfg['sheet']['gap'];
        $cw = $cfg['card']['width'];   $ch = $cfg['card']['height'];

        $usableW = $sw - 2 * $m;
        $usableH = $sh - 2 * $m;

        $landscape = $this->grid($usableW, $usableH, $cw, $ch, $g);
        $portrait  = $this->grid($usableW, $usableH, $ch, $cw, $g);

        $best = ($cfg['sheet']['allow_rotate'] && $portrait['total'] > $landscape['total'])
            ? $portrait + ['rotated' => true]
            : $landscape + ['rotated' => false];

        $best['requested'] = $cfg['sheet']['per_sheet'];
        $best['fits']      = $best['total'] >= $best['requested'];

        return $best;
    }

    private function grid(float $w, float $h, float $cw, float $ch, float $gap): array
    {
        $cols = (int) floor(($w + $gap) / ($cw + $gap));
        $rows = (int) floor(($h + $gap) / ($ch + $gap));

        return ['cols' => $cols, 'rows' => $rows, 'total' => $cols * $rows,
                'cell_w' => $cw, 'cell_h' => $ch];
    }
}
```

### QR Code Service (`app/Services/QrCodeService.php`)

```php
<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\ErrorCorrectionLevel;

class QrCodeService
{
    public function svgDataUri(string $content): string
    {
        $result = Builder::create()
            ->writer(new SvgWriter())
            ->data(config('card.qr.prefix') . $content)
            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->margin(0)
            ->build();

        return $result->getDataUri();
    }
}
```

### Template Lembar (`resources/views/pdf/sheet.blade.php`)

```blade
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { size: {{ config('card.sheet.width') }}mm {{ config('card.sheet.height') }}mm; margin: 0; }
    body { margin: 0; font-family: sans-serif; }
    .sheet { position: relative; width: {{ config('card.sheet.width') }}mm;
             height: {{ config('card.sheet.height') }}mm; page-break-after: always; }
    .card  { position: absolute; width: {{ $layout['cell_w'] }}mm; height: {{ $layout['cell_h'] }}mm;
             border: .1mm solid #999; box-sizing: border-box; padding: 3mm; }
    .qr    { width: {{ config('card.qr.size') }}mm; height: {{ config('card.qr.size') }}mm; }
    .num   { font-size: 14pt; font-weight: bold; }
</style>
</head>
<body>
@foreach ($sheets as $sheet)
    <div class="sheet">
        @foreach ($sheet as $i => $card)
            @php
                $col = $i % $layout['cols'];
                $row = intdiv($i, $layout['cols']);
                $x = config('card.sheet.margin') + $col * ($layout['cell_w'] + config('card.sheet.gap'));
                $y = config('card.sheet.margin') + $row * ($layout['cell_h'] + config('card.sheet.gap'));
            @endphp
            <div class="card" style="left: {{ $x }}mm; top: {{ $y }}mm;">
                <img class="qr" src="{{ $qr->svgDataUri($card->number) }}">
                <div class="num">{{ $card->number }}</div>
                <div>{{ $card->name }}</div>
            </div>
        @endforeach
    </div>
@endforeach
</body>
</html>
```

### Generate PDF

```php
use Barryvdh\DomPDF\Facade\Pdf;

$layout = app(LayoutCalculator::class)->calculate();
$perSheet = min($layout['total'], config('card.sheet.per_sheet'));
$sheets = $cards->chunk($perSheet);

return Pdf::loadView('pdf.sheet', [
        'sheets' => $sheets,
        'layout' => $layout,
        'qr'     => app(QrCodeService::class),
    ])
    ->setPaper([0, 0, 480 / 25.4 * 72, 320 / 25.4 * 72]) // mm → pt
    ->stream('kartu.pdf');
```

> Untuk presisi layout yang lebih baik (flex/grid, font custom), pertimbangkan `spatie/laravel-pdf` dengan Browsershot.

---

## Tips Cetak

- Cetak dengan skala **100%** (Actual Size), matikan *Fit to page*.
- Pastikan driver printer mendukung ukuran kustom **480 × 320 mm** (atau gunakan kertas SRA3/A3+ lalu potong).
- Cetak **1 lembar uji** dan ukur kartu dengan penggaris/kaliper sebelum cetak massal.
- QR sebaiknya minimal **15 × 15 mm** dengan kontras tinggi agar mudah discan.
- Gunakan crop mark untuk memudahkan pemotongan (`CROP_MARKS=true`).

## Roadmap

- [ ] Template desain kartu (logo, background, font)
- [ ] Foto per kartu
- [ ] Cetak sisi depan–belakang (duplex)
- [ ] Scan & verifikasi QR via endpoint publik
- [ ] Queue untuk batch berukuran besar (ribuan kartu)

## Lisensi

MIT