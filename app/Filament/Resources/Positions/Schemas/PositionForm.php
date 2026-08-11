<?php

namespace App\Filament\Resources\Positions\Schemas;

use App\Models\ContestType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PositionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تعريف الوظيفة')
                    ->schema([
                        TextInput::make('code')
                            ->label('الرمز (Code)')
                            ->required()
                            ->numeric()
                            ->placeholder('مثال: 01، 07، 16...')
                            ->helperText('الرمز الرقمي للوظيفة كما في إعلان المناظرة'),

                        TextInput::make('name')
                            ->label('عنوان الوظيفة (خطة)')
                            ->required()
                            ->placeholder('مثال: مهندس أول، محلل، متصرف، سائق...')
                            ->helperText('اسم الخطة الوظيفية'),
                    ])->columns(2),

                Section::make('الصنف والمؤهل المطلوب')
                    ->schema([
                        // Lien vers le profil (ContestType) — ex: cadre, technicien, commis...
                        Select::make('contest_type_id')
                            ->label('الصنف / نوع المناظرة (Profil)')
                            ->relationship('contestType', 'name')
                            ->getOptionLabelFromRecordUsing(
                                fn (ContestType $record) => "[{$record->code}] {$record->name}"
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('مثال: [cadre] إطارات، [commis] مستكتب إدارة، [nettoyage] عون نظافة...')
                            ->columnSpanFull(),

                        TextInput::make('degree')
                            ->label('الشهادة المطلوبة')
                            ->placeholder('مثال: الشهادة الوطنية لمهندس / الإجازة الوطنية نظام أمد...')
                            ->helperText('الشهادة العلمية المشترطة للترشح لهذه الوظيفة'),

                        TextInput::make('specialty')
                            ->label('الاختصاص المطلوب')
                            ->placeholder('مثال: هندسة البرمجيات، إعلامية التصرف، مالية...')
                            ->helperText('الاختصاص أو التوجه الأكاديمي المطلوب'),
                    ])->columns(2),
            ]);
    }
}
