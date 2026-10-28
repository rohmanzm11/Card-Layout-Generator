<?php

namespace App\Http\Controllers;

use App\Models\KartuB;
use App\Models\KartuK;
use App\Models\PrintKartu;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PrintKartuController extends Controller
{
    /**
     * Ambil data QR codes & template
     */
    private function buildData(PrintKartu $record): array
    {
        $record->loadMissing(['template', 'kartuK', 'kartuB']);

        $qrCodes = [];

        if ($record->jenis_kartu === 'K') {
            $jumlah = $record->jumlah ?? 1;
            $startNomor = $record->kartuK->nomor ?? null;

            $query = KartuK::orderBy('nomor');
            if ($startNomor !== null) {
                $query->where('nomor', '>=', $startNomor);
            }

            $kartuList = $query->limit($jumlah)->get();

            foreach ($kartuList as $kartu) {
                if (!empty($kartu->qr_code)) {
                    $svg = QrCode::format('svg')->size(200)->generate($kartu->qr_code);
                    $qrCodes[$kartu->nomor] = 'data:image/svg+xml;base64,' . base64_encode($svg);
                }
            }
        }

        if ($record->jenis_kartu === 'B') {
            $jumlah = $record->jumlah ?? 1;
            $startNomor = $record->kartuB->nomor ?? null;

            $query = KartuB::orderBy('nomor');
            if ($startNomor !== null) {
                $query->where('nomor', '>=', $startNomor);
            }

            $kartuList = $query->limit($jumlah)->get();

            foreach ($kartuList as $kartu) {
                if (!empty($kartu->qr_code)) {
                    $svg = QrCode::format('svg')->size(200)->generate($kartu->qr_code);
                    $qrCodes[$kartu->nomor] = 'data:image/svg+xml;base64,' . base64_encode($svg);
                }
            }
        }

        if (empty($qrCodes)) {
            session()->flash('warning', 'Tidak ada QR Code yang ditemukan.');
        }

        $template   = $record->template;
        $templateBg = null;

        if ($template && $template->gambar) {
            $gambar = $template->gambar;

            if (str_starts_with($gambar, 'http')) {
                $templateBg = $gambar;
            } else {
                $templateBg = asset('storage/' . ltrim($gambar, '/'));
            }
        }

        return compact('record', 'qrCodes', 'template', 'templateBg');
    }

    /**
     * Preview di browser (HTML asli, dengan tombol window.print())
     */
    public function preview(PrintKartu $record)
    {
        $data = $this->buildData($record);

        return view('print-kartu.preview', $data);
    }

    /**
     * Loadview PDF — tampil langsung di browser (inline), TIDAK memaksa download.
     */
    public function download(PrintKartu $record)
    {
        $data = $this->buildData($record);

        if ($data['templateBg'] && !str_starts_with($data['templateBg'], 'http')) {
            $template = $data['template'];
            $path = storage_path('app/public/' . ltrim($template->gambar, '/'));

            if (file_exists($path)) {
                $mime = mime_content_type($path);
                $bin  = base64_encode(file_get_contents($path));
                $data['templateBg'] = "data:{$mime};base64,{$bin}";
            } else {
                $data['templateBg'] = null;
            }
        }

        $pdf = app('dompdf.wrapper')
            ->loadView('print-kartu.pdf', $data)
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'dpi'                  => 96,
            ]);

        $filename = 'kartu-' . $record->jenis_kartu . '-' . $record->id . '.pdf';

        return $pdf->stream($filename);   // ← inilah kuncinya, bukan ->download()
    }
}