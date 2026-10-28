<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }

@page {
    size: 480mm 325mm landscape;
    margin: 0;
}

html, body {
    width: 480mm;
    height: 325mm;
    font-family: Arial, sans-serif;
}

.halaman {
    position: relative;
    width: 480mm;
    height: 325mm;
    page-break-after: always;
}
.halaman:last-child {
    page-break-after: auto;
}

/* Kartu atas: 90x55mm, posisi absolute */
.kartu-atas {
    position: absolute;
    width: 90mm;
    height: 55mm;
    overflow: hidden;
}
.template-img-atas {
    position: absolute;
    top: 0; left: 0;
    width: 90mm;
    height: 55mm;
}

/* Kartu bawah: slot 55x90mm, isi dirotate */
.kartu-bawah {
    position: absolute;
    width: 55mm;
    height: 90mm;
    overflow: hidden;
}
.inner-bawah {
    position: absolute;
    top: 0; left: 0;
    width: 90mm;
    height: 55mm;
    transform-origin: top left;
    transform: rotate(90deg) translateY(-55mm);
}
.template-img-bawah {
    position: absolute;
    top: 0; left: 0;
    width: 90mm;
    height: 55mm;
}

/* QR Box: 18x18mm, relatif terhadap kartu 90x55mm */
/* .qr-box {
    position: absolute;
    right: 24.5mm;
    top: 20.3mm;
    width: 18mm;
    height: 18mm;
    z-index: 10;
    background: #fff;
    overflow: hidden;
} */

.qr-box {
    position: absolute;
    right: 23mm;
    top: 18.5mm;
    width: 20mm;
    z-index: 10;
    background: #fff;
    padding: 1mm;
    box-sizing: border-box;
}

.qr-box table {
    width: 100%;
    border-collapse: collapse;
}

.qr-box td {
    text-align: center;
    padding: 0;
}

.qr-box img {
    width: 17mm;
    height: 17mm;
    display: block;
    margin: 0 auto;
}

.qr-nomor {
    font-size: 2mm;
    line-height: 1.5;
    color: #333;
    font-weight: 700;
}

.empty { background: #f3f4f6; }
</style>
</head>
<body>

@php
    $qrList    = array_values($qrCodes);
    $nomorList = array_keys($qrCodes);

    $allData = [];
    foreach ($qrList as $i => $qr) {
        $allData[] = [
            'qr'    => $qr,
            'nomor' => $nomorList[$i] ?? ''
        ];
    }

    $pages = array_chunk($allData, 28);

    // ── Konfigurasi posisi grid (dalam mm) ──
    $offsetLeft = 15; // padding-left halaman
    $offsetTop  = 5;  // padding-top halaman

    $cardW = 90; // lebar kartu atas
    $cardH = 55; // tinggi kartu atas

    $slotW = 55; // lebar slot kartu bawah
    $slotH = 90; // tinggi slot kartu bawah
@endphp

@foreach ($pages as $pageItems)
    <div class="halaman">

        @php
            $atasItems  = array_slice($pageItems, 0, 20);
            $bawahItems = array_slice($pageItems, 20, 8);
        @endphp

        {{-- GRID ATAS: 5 kolom x 4 baris (20 kartu) --}}
        @for ($i = 0; $i < 20; $i++)
            @php
                $row       = intdiv($i, 5);
                $col       = $i % 5;
                $leftPos   = $offsetLeft + ($col * $cardW);
                $topPos    = $offsetTop  + ($row * $cardH);
                $item      = $atasItems[$i] ?? null;
                
                // Trik: satukan seluruh string atribut "style" ke dalam variabel PHP 
                // agar VS Code tidak membaca kurung kurawal di dalam tag HTML
                $htmlStyle = 'style="left: ' . $leftPos . 'mm; top: ' . $topPos . 'mm;"';
            @endphp
            <div class="kartu-atas {{ ($item === null && ! $templateBg) ? 'empty' : '' }}" {!! $htmlStyle !!}>
                @if($templateBg)
                    <img class="template-img-atas" src="{{ $templateBg }}" alt="">
                @endif

              @if($item)
                <div class="qr-box">
                    <table>
                        <tr>
                            <td>
                                @if($item['qr'])
                                    <img src="{{ $item['qr'] }}" alt="">
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="qr-nomor">{{ $item['nomor'] ?? 'KOSONG' }}</td>
                        </tr>
                    </table>
                </div>
@endif
            </div>
        @endfor

        {{-- GRID BAWAH: 8 kolom, rotated 90deg --}}
        @php
            $bawahTop = $offsetTop + (4 * $cardH); // setelah 4 baris grid atas
        @endphp
        @for ($i = 0; $i < 8; $i++)
            @php
                $leftPos     = $offsetLeft + ($i * $slotW);
                $item        = $bawahItems[$i] ?? null;
                
                // Lakukan trik yang sama untuk grid bawah
                $htmlStyle   = 'style="left: ' . $leftPos . 'mm; top: ' . $bawahTop . 'mm;"';
            @endphp
            <div class="kartu-bawah {{ ($item === null && ! $templateBg) ? 'empty' : '' }}" {!! $htmlStyle !!}>
                <div class="inner-bawah">
                    @if($templateBg)
                        <img class="template-img-bawah" src="{{ $templateBg }}" alt="">
                    @endif

                  @if($item)
                        <div class="qr-box">
                            <table>
                                <tr>
                                    <td>
                                        @if($item['qr'])
                                            <img src="{{ $item['qr'] }}" alt="">
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="qr-nomor">{{ $item['nomor'] ?? 'KOSONG' }}</td>
                                </tr>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endfor

    </div>
@endforeach

</body>
</html>