<?php

namespace App\Filament\Resources\ContestTypes\Pages;

use App\Filament\Resources\ContestTypes\ContestTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContestType extends EditRecord
{
    protected static string $resource = ContestTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
