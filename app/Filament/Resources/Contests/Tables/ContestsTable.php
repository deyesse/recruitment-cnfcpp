<?php

namespace App\Filament\Resources\Contests\Tables;

use App\Models\Application;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المناظرة')
                    ->searchable()
                    ->grow()
                    ->lineClamp(3)
                    ->extraHeaderAttributes(['style' => 'min-width: 320px;'])
                    ->extraCellAttributes(['style' => 'min-width: 320px; font-weight: 600;']),

                IconColumn::make('is_test_mode')
                    ->label('وضع الاختبار')
                    ->boolean(),

                TextColumn::make('positions_count')
                    ->counts('positions')
                    ->label('عدد الوظائف المفتوحة')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('positions.name')
                    ->label('الوظائف والأصناف المدرجة')
                    ->badge()
                    ->separator(', ')
                    ->limitList(3)
                    ->toggleable(),

                TextColumn::make('starts_at')
                    ->label('تاريخ الفتح')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('فوراً')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('تاريخ الغلق')
                    ->dateTime('d/m/Y H:i')
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
                Action::make('disableTestMode')
                    ->label('إيقاف الاختبار وتصفير العداد')
                    ->icon('heroicon-o-beaker')
                    ->color('warning')
                    ->visible(fn ($record) => (bool) $record->is_test_mode && auth()->user()?->isSuperAdmin())
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد إيقاف وضع الاختبار وتصفير العداد')
                    ->modalDescription('هل ترغب في إيقاف وضع الاختبار، وحذف جميع المطالب التجريبية المودعة، وتصفير العداد للبدء من الرقم 1؟')
                    ->modalSubmitActionLabel('نعم، إيقاف وتصفير العداد')
                    ->modalCancelActionLabel('إلغاء')
                    ->action(function ($record) {
                        $record->update([
                            'is_test_mode' => false,
                            'test_code' => null,
                        ]);

                        $deletedCount = Application::purgeTestApplicationsAndResetCounter($record->id);

                        Notification::make()
                            ->title('تم إيقاف وضع الاختبار وتصفير العداد')
                            ->body("تم حذف {$deletedCount} مطلب تجريبي وتصفير العداد ليكون المطلب القادم بالرقم 1.")
                            ->success()
                            ->send();
                    }),
                \Filament\Actions\ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
