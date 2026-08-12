<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),

                TextColumn::make('role')
                    ->label('الدور / الصلاحية')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'super_admin' => 'مدير فائق (Super Admin)',
                        'admin' => 'مدير (Admin)',
                        'gestionnaire' => 'متصرف (Gestionnaire)',
                        default => $state ?? 'غير محدد',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'gestionnaire' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable(),

                TextColumn::make('email_verified_at')
                    ->label('تم التحقق في')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }
}
