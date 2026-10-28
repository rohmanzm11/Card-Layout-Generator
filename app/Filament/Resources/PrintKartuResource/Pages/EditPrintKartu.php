<?php

namespace App\Filament\Resources\PrintKartuResource\Pages;

use App\Filament\Resources\PrintKartuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrintKartu extends EditRecord
{
    protected static string $resource = PrintKartuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
