<?php

namespace App\Filament\Resources\ContestTypes;

use App\Filament\Resources\ContestTypes\Pages\CreateContestType;
use App\Filament\Resources\ContestTypes\Pages\EditContestType;
use App\Filament\Resources\ContestTypes\Pages\ListContestTypes;
use App\Filament\Resources\ContestTypes\Schemas\ContestTypeForm;
use App\Filament\Resources\ContestTypes\Tables\ContestTypesTable;
use App\Models\ContestType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContestTypeResource extends Resource
{
    protected static ?string $model = ContestTypeResourceModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'أنواع المناظرات والمعايير';

    protected static ?string $modelLabel = 'نوع مناظرة';

    protected static ?string $pluralModelLabel = 'أنواع المناظرات';

    protected static ?int $navigationSort = 4;

    public static function getModel(): string
    {
        return ContestType::class;
    }

    public static function form(Schema $schema): Schema
    {
        return ContestTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContestTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContestTypes::route('/'),
            'create' => CreateContestType::route('/create'),
            'edit' => EditContestType::route('/{record}/edit'),
        ];
    }
}
