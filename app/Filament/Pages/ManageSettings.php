<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    protected static ?string $navigationLabel = 'إعدادات المنظومة';
    protected static ?string $title = 'إعدادات المنظومة (Super Admin)';
    protected static ?int $navigationSort = 99;
    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    public function mount(): void
    {
        $this->form->fill([
            'footer_text' => Setting::get('footer_text', '© Powered by <span class="font-semibold text-sky-700">E..E.E. Bouzekri</span> - <span class="font-semibold text-sky-700">DSI-CNFCPP</span> August 2026'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('حقوق التطوير والتذييل (Footer / Powered by)')
                    ->description('تخصيص النص الذي يظهر في تذييل جميع صفحات الموقع، الاستمارات المطبوعة، ولوحة التحكم.')
                    ->schema([
                        Textarea::make('footer_text')
                            ->label('نص حقوق التطوير (Powered by Text)')
                            ->rows(3)
                            ->placeholder('© Powered by E..E.E. Bouzekri - DSI-CNFCPP August 2026')
                            ->helperText('يمكنك إدخال نص بسيط أو كود HTML مخصص. هذا الإعداد متاح حصرياً للـ Super Admin.')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function save(): void
    {
        if (! auth()->user()?->isSuperAdmin()) {
            abort(403, 'غير مصرح لك بتعديل هذه الإعدادات.');
        }

        $state = $this->form->getState();

        Setting::set('footer_text', $state['footer_text'] ?? '');

        Notification::make()
            ->title('تم حفظ الإعدادات بنجاح')
            ->body('تم تحديث نص التذييل (Powered by) على كامل المنظومة والمطبوعات.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resetDefault')
                ->label('استرجاع النص الافتراضي')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('استرجاع النص الافتراضي')
                ->modalDescription('هل ترغب في إعادة تعيين نص التذييل إلى القيمة الافتراضية؟')
                ->action(function () {
                    $default = '© Powered by <span class="font-semibold text-sky-700">E..E.E. Bouzekri</span> - <span class="font-semibold text-sky-700">DSI-CNFCPP</span> August 2026';
                    Setting::set('footer_text', $default);
                    $this->form->fill(['footer_text' => $default]);

                    Notification::make()
                        ->title('تم استرجاع النص الافتراضي')
                        ->success()
                        ->send();
                }),
        ];
    }
}
