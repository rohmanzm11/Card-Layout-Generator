<?php

namespace App\Imports;

use App\Models\KartuB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class KartuBImport implements ToModel, WithHeadingRow, SkipsEmptyRows, SkipsOnError
{
    use SkipsErrors;

    private int $imported = 0;
    private int $skipped  = 0;

    public function model(array $row)
    {
        // Lebih toleran terhadap berbagai nama kolom
        $nomor = trim($row['nomor'] 
            ?? $row['no'] 
            ?? $row['number'] 
            ?? $row['Nomor'] 
            ?? $row['No'] 
            ?? $row['NOMOR'] 
            ?? '');

        // Skip jika kosong
        if (empty($nomor)) {
            $this->skipped++;
            return null;
        }

        // Cek duplikat
        if (KartuB::where('nomor', $nomor)->exists()) {
            $this->skipped++;
            return null;
        }

        $this->imported++;

        return new KartuB([
            'nomor' => $nomor,
        ]);
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