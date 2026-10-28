<?php

namespace App\Filament\Resources\PrintKartuResource\Pages;

use App\Filament\Resources\PrintKartuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrintKartus extends ListRecords
{
    protected static string $resource = PrintKartuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
