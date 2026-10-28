<?php

namespace App\Filament\Resources\KartuBResource\Pages;

use App\Filament\Resources\KartuBResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKartuBS extends ListRecords
{
    protected static string $resource = KartuBResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
