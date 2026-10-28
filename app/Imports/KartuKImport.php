<?php

namespace App\Imports;

use App\Models\KartuK;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class KartuKImport implements
    ToModel,
    WithHeadingRow,
    SkipsEmptyRows,
    SkipsOnError,
    WithChunkReading,
    WithBatchInserts
{
    use SkipsErrors;

    private int $imported = 0;
    private int $skipped  = 0;

    /** @var array<string,bool> Cache nomor yang sudah ada di DB (preload sekali) */
    private array $existing = [];

    /** @var array<string,bool> Cache nomor yang baru ditambahkan dalam proses import ini (cegah duplikat dalam file) */
    private array $seen = [];

    public function __construct()
    {
        // Preload semua nomor yang sudah ada -> hindari query exists() 5000x
        $this->existing = KartuK::query()
            ->pluck('nomor')
            ->map(fn ($v) => (string) $v)
            ->flip()
            ->map(fn () => true)
            ->toArray();
    }

    public function model(array $row)
    {
        // Ambil nilai nomor, toleran terhadap variasi nama kolom
        $nomor = trim((string) ($row['nomor'] ?? $row['no'] ?? $row['number'] ?? ''));

        // Skip jika kosong
        if ($nomor === '') {
            $this->skipped++;
            return null;
        }

        // Skip jika sudah ada di DB
        if (isset($this->existing[$nomor])) {
            $this->skipped++;
            return null;
        }

        // Skip jika duplikat di dalam file yang sama
        if (isset($this->seen[$nomor])) {
            $this->skipped++;
            return null;
        }

        $this->seen[$nomor] = true;
        $this->imported++;

        return new KartuK([
            'nomor' => $nomor,
        ]);
    }

    /**
     * Baca file per 500 baris -> hemat memory untuk file besar (5000+ baris).
     */
    public function chunkSize(): int
    {
        return 500;
    }

    /**
     * Insert ke DB per 500 baris sekaligus (bulk insert), bukan 1 per 1.
     */
    public function batchSize(): int
    {
        return 500;
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }
}