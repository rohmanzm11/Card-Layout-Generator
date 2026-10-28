<?php

namespace App\Filament\Resources\PrintKartuResource\Pages;

use App\Filament\Resources\PrintKartuResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePrintKartu extends CreateRecord
{
    protected static string $resource = PrintKartuResource::class;

    // app/Filament/Resources/PrintKartuResource/Pages/CreatePrintKartu.php

protected function mutateFormDataBeforeCreate(array $data): array
{
    // Pastikan range valid sebelum disimpan
    $data['range_start'] = max(1, min(100, (int) ($data['range_start'] ?? 1)));
    $data['range_end']   = max(1, min(100, (int) ($data['range_end']   ?? 1)));

    // Pastikan start <= end
    if ($data['range_start'] > $data['range_end']) {
        [$data['range_start'], $data['range_end']] = [$data['range_end'], $data['range_start']];
    }

    return $data;
}
}
