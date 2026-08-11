<?php

namespace App\Filament\Resources\ContestTypes\Pages;

use App\Filament\Resources\ContestTypes\ContestTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContestTypes extends ListRecords
{
    protected static string $resource = ContestTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
