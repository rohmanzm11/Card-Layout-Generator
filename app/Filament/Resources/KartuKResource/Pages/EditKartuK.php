<?php

namespace App\Filament\Resources\KartuKResource\Pages;

use App\Filament\Resources\KartuKResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKartuK extends EditRecord
{
    protected static string $resource = KartuKResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
