<?php

namespace App\Filament\Resources\KartuBResource\Pages;

use App\Filament\Resources\KartuBResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKartuB extends EditRecord
{
    protected static string $resource = KartuBResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
