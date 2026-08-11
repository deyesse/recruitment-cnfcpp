<?php

namespace App\Filament\Resources\ContestTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContestTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('الرمز')
                    ->badge()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('اسم نوع المناظرة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('min_score')
                    ->label('الحد الأدنى للنقاط')
                    ->numeric(decimalPlaces: 1, locale: 'fr')
                    ->sortable(),

                TextColumn::make('coeff_bac_btp')
                    ->label('ضارب البكالوريا/BTP')
                    ->formatStateUsing(fn ($state) => ($state * 100).'%'),

                TextColumn::make('coeff_grad_degree')
                    ->label('ضارب التخرج')
                    ->formatStateUsing(fn ($state) => ($state * 100).'%'),

                IconColumn::make('has_bac')
                    ->label('بكالوريا/BTP')
                    ->boolean(),

                IconColumn::make('has_degree')
                    ->label('شهادة جامعية')
                    ->boolean(),

                IconColumn::make('has_school_level')
                    ->label('مستوى أساسي/ثانوي')
                    ->boolean(),

                IconColumn::make('has_driving_license')
                    ->label('رخصة سياقة')
                    ->boolean(),

                TextColumn::make('max_age')
                    ->label('الحد الأقصى للسن')
                    ->placeholder('بدون حد')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' سنة' : '—')
                    ->sortable(),

                TextColumn::make('age_reference_date')
                    ->label('تاريخ مرجع السن')
                    ->date('d/m/Y')
                    ->placeholder('—'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
