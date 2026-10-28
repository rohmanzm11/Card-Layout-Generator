<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print Kartu {{ $record->id }}</title>
    <style>
        /* ══════════════════════════════════════════════════════
           RESET & SCREEN
           ══════════════════════════════════════════════════════ */
        * { margin:0; padding:0; box-sizing:border-box; }

        body { background:#6b7280; font-family: Arial, sans-serif; }

        /* ── Toolbar ─────────────────────────────────────────── */
        .toolbar {
            position: fixed; top:0; left:0; right:0; z-index:200;
            background:#1e293b; color:#fff;
            padding:8px 16px;
            display:flex; align-items:center; gap:10px;
        }
        .toolbar h1 { font-size:13px; font-weight:600; flex:1; }
        .toolbar .meta { font-size:11px; color:#94a3b8; }
        .btn {
            border:none; padding:7px 16px; border-radius:5px;
            font-size:12px; font-weight:600; cursor:pointer;
            text-decoration:none; display:inline-flex; align-items:center; gap:5px;
        }
        .btn-print { background:#16a34a; color:#fff; }
        .btn-back  { background:#475569; color:#fff; }

        /* ── No-template warning ────────────────────────────── */
        .no-template-warn {
            background: #fef3c7; border: 2px solid #f59e0b;
            color: #92400e; padding: 12px 20px; border-radius:6px;
            margin-bottom: 12px; font-size: 13px; font-weight: 600;
        }

        /* ── Page wrapper ──────────────────────────────────────── */
        .page-wrapper {
            margin-top: 56px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ══════════════════════════════════════════════════════
           HALAMAN CETAK
           Ukuran fisik: 480mm × 325mm
           Preview scale 40%: 1mm = 3.7795px × 0.4 = 1.5118px/mm
           ══════════════════════════════════════════════════════ */
        :root { --s: 1.5118px; }

        .halaman {
            width:  calc(480 * var(--s));
            height: calc(325 * var(--s));
            background: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,.4);
            
padding-left: calc(15 * var(--s)); 
    padding-top:  calc(5 * var(--s));
        }

        /* ── Grid atas: 5 col × 4 row (kartu 90×55mm) ──────── */
        .grid-atas {
            display: grid;
            grid-template-columns: repeat(5, calc(90 * var(--s)));
            grid-template-rows: repeat(4, calc(55 * var(--s)));
            width: calc(450 * var(--s));
        }

        /* ── Grid bawah: 8 col × 1 row (slot 60×90mm) ──────── */
        .grid-bawah {
            display: grid;
            grid-template-columns: repeat(8, calc(55 * var(--s)));
            grid-template-rows: calc(90 * var(--s));
            width: calc(480 * var(--s));
            /* border-top: 0.5px solid #ccc; */
        }

        /* ── Kartu individu ─────────────────────────────────── */
        .kartu {
            /* border: 0.5px solid #d1d5db; */
            overflow: hidden;
            position: relative;
        }

        /* Kartu atas: 90×55mm */
        .kartu-atas {
            width:  calc(90 * var(--s));
            height: calc(55 * var(--s));
        }

        /* Gambar template mengisi penuh kartu (pakai <img> bukan bg) */
        .kartu-atas .template-img {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            object-fit: fill;   /* fill = stretch penuh, sesuai PDF */
            display: block;
        }

        /* Kartu bawah: slot 60×90mm, inner dirotate */
        .kartu-bawah {
            width:  calc(60 * var(--s));
            height: calc(90 * var(--s));
            overflow: hidden;
        }

        /* Inner: ukuran asli kartu 90×55mm, dirotate 90° */
        .kartu-bawah .inner {
            width:  calc(90 * var(--s));
            height: calc(55 * var(--s));
            transform-origin: top left;
            transform: rotate(90deg) translateY(calc(-55 * var(--s)));
            position: absolute;
            top: 0; left: 0;
        }

        /* Gambar template dalam inner kartu bawah */
        .kartu-bawah .template-img {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            object-fit: fill;
            display: block;
        }

        /* ── QR Box: 18mm × 18mm ─────────────────────────────
           Posisi sesuai PDF: kanan atas area isi kartu
           right: 2mm, top: 10mm dari atas kartu (55mm tinggi)
           ──────────────────────────────────────────────────── */
        .qr-box {
            position: absolute;
            right: calc(24.8 * var(--s));
            top:   calc(20.3 * var(--s));
            width:  calc(18 * var(--s));   /* 18mm */
            height: calc(18 * var(--s));   /* 18mm */
            z-index: 10;
            background: #fff;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .qr-nomor {
            font-size: calc(2 * var(--s));
            color: #333;
            font-weight: 700;
            text-align: center;
        }

        /* Slot kosong (tanpa QR dan tanpa template) */
        .kartu.empty { background: #f3f4f6; }

        /* ══════════════════════════════════════════════════════
           PRINT
           ══════════════════════════════════════════════════════ */
        @media print {
            @page { size: 480mm 325mm landscape; margin: 0; }

            body { background: none; }
            .toolbar, .no-template-warn { display: none !important; }
            .page-wrapper { margin: 0; padding: 0; }

            :root { --s: 3.7795px; }

            .halaman { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>

{{-- Toolbar --}}
<div class="toolbar">
    <a href="{{ url()->previous() }}" class="btn btn-back">← Kembali</a>
    <h1>Print Kartu
        @if($record->jenis_kartu === 'K')
            &mdash; Kartu K
            @if($record->range_start && $record->range_end)
                (No. {{ $record->range_start }}–{{ $record->range_end }})
            @endif
        @else
            &mdash; Kartu B
        @endif
    </h1>
    <span class="meta">480×325mm &nbsp;|&nbsp; {{ count($qrCodes) }} QR &nbsp;|&nbsp; 28 slot</span>
    <button class="btn btn-print" onclick="window.print()">🖨️ Print</button>
</div>

<div class="page-wrapper">

    {{-- Peringatan jika template tidak ada --}}
    @if(! $templateBg)
        <div class="no-template-warn">
            ⚠️ Template gambar tidak ditemukan.
            Pastikan kolom <code>gambar</code> pada tabel template sudah diisi
            dan file sudah diupload ke <code>storage/app/public/</code>.
            <br>
            Debug: template_id = {{ $record->template_id ?? 'NULL' }}
            &nbsp;|&nbsp; template->gambar = {{ $template->gambar ?? 'NULL' }}
        </div>
    @endif

<div class="halaman">

@php
    $qrList    = array_values($qrCodes);
    $nomorList = array_keys($qrCodes);
    $idx       = 0;
@endphp

{{-- ═══ GRID ATAS: 5 kolom × 4 baris (20 kartu) ═══ --}}
<div class="grid-atas">
@for ($i = 0; $i < 20; $i++)
    @php
        $qr    = $qrList[$idx]    ?? null;
        $nomor = $nomorList[$idx] ?? null;
        $idx++;
    @endphp

    <div class="kartu kartu-atas {{ ($qr === null && ! $templateBg) ? 'empty' : '' }}">

        {{-- Gambar template sebagai <img> agar pasti tercetak --}}
        @if($templateBg)
            <img class="template-img" src="{{ $templateBg }}" alt="">
        @endif

        {{-- QR Code, z-index di atas gambar --}}
        <div class="qr-box">
    @if($qr)
        <img src="{{ $qr }}" alt="QR {{ $nomor }}">
    @else
        <span class="qr-nomor">{{ $nomor ?? '' }}</span>
    @endif
</div>
    </div>
@endfor
</div>

{{-- ═══ GRID BAWAH: 8 kolom × 1 baris (rotated 90°) ═══ --}}
<div class="grid-bawah">
@for ($i = 0; $i < 8; $i++)
    @php
        $qr    = $qrList[$idx]    ?? null;
        $nomor = $nomorList[$idx] ?? null;
        $idx++;
    @endphp

    <div class="kartu kartu-bawah {{ ($qr === null && ! $templateBg) ? 'empty' : '' }}">
        <div class="inner">

            {{-- Gambar template sebagai <img> dalam inner --}}
            @if($templateBg)
                <img class="template-img" src="{{ $templateBg }}" alt="">
            @endif

            {{-- QR Code --}}
            <div class="qr-box">
    @if($qr)
        <img src="{{ $qr }}" alt="QR {{ $nomor }}">
    @else
        <span class="qr-nomor">{{ $nomor ?? '' }}</span>
    @endif
</div>
        </div>
    </div>
@endfor
</div>

</div>{{-- .halaman --}}
</div>{{-- .page-wrapper --}}

</body>
</html>