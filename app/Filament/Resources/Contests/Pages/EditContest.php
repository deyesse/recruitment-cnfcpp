<?php

namespace App\Filament\Resources\Contests\Pages;

use App\Filament\Resources\Contests\ContestResource;
use App\Models\Application;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditContest extends EditRecord
{
    protected static string $resource = ContestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('disableTestModeAndPurge')
                ->label('إيقاف وضع الاختبار وتصفير العداد')
                ->icon('heroicon-o-beaker')
                ->color('warning')
                ->visible(fn ($record) => (bool) $record->is_test_mode)
                ->requiresConfirmation()
                ->modalHeading('تأكيد إيقاف وضع الاختبار وتصفير العداد')
                ->modalDescription('هل ترغب في إيقاف وضع الاختبار، وحذف جميع المطالب التجريبية المودعة حالياً، وتصفير العداد حتى يبدأ أول مطلب جديد بالرقم (1)؟')
                ->modalSubmitActionLabel('نعم، إيقاف وضع الاختبار وتصفير العداد')
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

                    $this->fillForm();
                }),

            DeleteAction::make(),
        ];
    }
}
