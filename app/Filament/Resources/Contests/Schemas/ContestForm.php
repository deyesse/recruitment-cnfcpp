<?php

namespace App\Filament\Resources\Contests\Schemas;

use App\Models\Position;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات ومدة المناظرة')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم المناظرة')
                            ->placeholder('مثال: استمارة ترشح للمشاركة في المناظرة الخارجية لانتداب إطارات وأعوان بعنوان سنتي 2025 و2026')
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('header_text')
                            ->label('نص الترويسة العليا للاستمارة المطبوعة (En-tête - اليمين)')
                            ->rows(3)
                            ->placeholder("الجمهورية التونسية\nوزارة التشغيل والتكوين المهني\nالمركز الوطني لتكوين المكونين وهندسة التكوين")
                            ->helperText('النص الرسمي الذي يظهر في أعلى يمين الاستمارة المطبوعة (كل سطر في سطر مستقل)')
                            ->hidden(fn () => ! auth()->user()?->isSuperAdmin())
                            ->columnSpan(1),

                        FileUpload::make('logo_path')
                            ->label('شعار المؤسسة (Logo - اليسار)')
                            ->image()
                            ->directory('contest-logos')
                            ->visibility('public')
                            ->imageResizeMode('contain')
                            ->maxSize(2048)
                            ->helperText('الشعار الذي سيظهر في أعلى يسار الاستمارة المطبوعة')
                            ->hidden(fn () => ! auth()->user()?->isSuperAdmin())
                            ->columnSpan(1),

                        DateTimePicker::make('starts_at')
                            ->format('Y-m-d H:i')
                            ->label('تاريخ ووقت فتح المناظرة')
                            ->nullable()
                            ->helperText('اتركه فارغاً إذا كانت المناظرة مفتوحة فوراً'),

                        DateTimePicker::make('ends_at')
                            ->format('Y-m-d H:i')
                            ->label('تاريخ ووقت غلق المناظرة')
                            ->required()
                            ->after('starts_at'),

                        Toggle::make('show_score')
                            ->label('إظهار مجموع النقاط للمترشح')
                            ->helperText('عند التفعيل، يتم عرض مجموع النقاط المحسوب للمترشح في الاستمارة والطباعة')
                            ->default(true)
                            ->columnSpanFull(),

                        Select::make('uniqueness_mode')
                            ->label('نمط التحقق من الترشحات المكررة')
                            ->helperText('يحدد هذا الخيار ما إذا كان بإمكان المترشح الواحد إيداع أكثر من مطلب ترشح في نفس المناظرة.')
                            ->options([
                                'per_contest'  => '🔒 مطلب ترشح واحد فقط لكل المناظرة (رقم بطاقة التعريف فريد في كامل المناظرة)',
                                'per_type'     => '📂 مطلب ترشح واحد لكل صنف (إطار، تقني، مستكتب...) في نفس المناظرة',
                                'per_position' => '📋 مطلب ترشح واحد لكل خطة وظيفية (يمكن التسجيل في عدة خطط مختلفة)',
                            ])
                            ->default('per_contest')
                            ->hidden(fn () => ! auth()->user()?->isSuperAdmin())
                            ->required(fn () => (bool) auth()->user()?->isSuperAdmin())
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('وضع الاختبار التجريبي (Mode Test)')
                    ->description('تفعيل أو تعطيل حظر تقديم الترشحات على العموم واشتراط رمز سر للتجربة.')
                    ->hidden(fn () => ! auth()->user()?->isSuperAdmin())
                    ->schema([
                        Toggle::make('is_test_mode')
                            ->label('تفعيل وضع الاختبار (Mode Test)')
                            ->helperText('عند تفعيل هذا الخيار، تكون المناظرة محمية برمز سر ولا يمكن للعموم تقديم ترشحات إلا بعد إدخال الرمز.')
                            ->live()
                            ->columnSpanFull(),

                        TextInput::make('test_code')
                            ->label('رمز الدخول لوضع الاختبار (Code de Test)')
                            ->placeholder('مثال: TEST2026')
                            ->helperText('الرمز الذي يجب على المترشح التجريبي إدخاله للوصول إلى استمارة الترشح')
                            ->required(fn ($get) => (bool) $get('is_test_mode'))
                            ->visible(fn ($get) => (bool) $get('is_test_mode'))
                            ->columnSpanFull(),
                    ]),

                Section::make('الوظائف والأصناف المعنية بهذه المناظرة (Profils & Postes)')
                    ->description('اختر الوظائف المفتوحة في هذه المناظرة. يمكن اختيار وظائف تنتمي لعدة أصناف (إطارات، تقني، مستكتب، سائق، عون تنظيف...) في نفس المناظرة.')
                    ->hidden(fn () => ! auth()->user()?->isSuperAdmin())
                    ->schema([
                        CheckboxList::make('positions')
                            ->label('قائمة الوظائف المتاحة')
                            ->relationship('positions', 'name')
                            ->getOptionLabelFromRecordUsing(function (Position $record) {
                                $profileName = $record->contestType?->name ?? 'غير محدد';
                                $spec = $record->specialty ? " | {$record->specialty}" : '';
                                return "[رمز {$record->code}] {$record->name}{$spec} — (الصنف: {$profileName})";
                            })
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(2)
                            ->required(fn () => (bool) auth()->user()?->isSuperAdmin())
                            ->helperText('كل وظيفة تتبع تلقائياً ضوابط التقييم وشروط السن والمعدل الخاصة بصنفها.'),
                    ]),
            ]);
    }
}
