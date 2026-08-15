<?php

namespace App\Filament\Resources\Applications;

use App\Filament\Resources\Applications\Pages\ListApplications;
use App\Filament\Resources\Applications\Pages\ViewApplication;
use App\Filament\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use App\Models\Contest;
use App\Models\Position;
use BackedEnum;
use Carbon\Carbon;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $modelLabel = 'مطلب';

    protected static ?string $pluralModelLabel = 'مطالب';

    protected static ?string $navigationLabel = 'مطالب الترشح';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table)
            ->filters([
                SelectFilter::make('position')
                    ->label('الخطة الوظيفية')
                    ->options(function () {
                        $contest = Contest::where('ends_at', '>', now())->first();
                        $positions = $contest
                            ? $contest->positions()->orderBy('code')->get()
                            : Position::orderBy('code')->get();

                        return $positions->mapWithKeys(
                            fn ($p) => [$p->code => $p->code . ' – ' . $p->name]
                        );
                    })
                    ->multiple()
                    ->searchable()
                    ->placeholder('الكل'),
            ])
            ->modifyQueryUsing(function ($query) {
                return $query->join('contests', 'contests.id', '=', 'applications.contest_id')
                    ->select('applications.*');
            });
    }

    protected static function getProfileType(?Application $record): string
    {
        if (! $record) return 'cadre';
        $pos = Position::where('code', (string) $record->position)->first();
        if ($pos && $pos->type) return $pos->type;
        $posNum = intval($record->position);
        if ($posNum >= 1 && $posNum <= 15) return 'cadre';
        if ($record->position == '16') return 'technicien';
        if (in_array((string) $record->position, ['17', '18'])) return 'commis';
        if ((string) $record->position == '19') return 'chauffeur';
        if (in_array((string) $record->position, ['20', '21'])) return 'nettoyage';
        return 'cadre';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── 1. بطاقة ملخص المترشح والمناظرة ──
                Section::make('معلومات المناظرة والتسجيل')
                    ->icon('heroicon-o-trophy')
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('id')
                                ->label('رقم التسجيل')
                                ->badge()
                                ->color('primary'),

                            TextEntry::make('position_display')
                                ->label('الخطة ورمز المناظرة')
                                ->state(function (Application $record) {
                                    $pos = Position::where('code', (string) $record->position)->first();
                                    $name = $pos?->name ?? ($record->position_name ?? 'غير محدد');
                                    return "[ رمز {$record->position} ] {$name}";
                                })
                                ->weight('bold'),

                            TextEntry::make('profile_label')
                                ->label('الصنف المهني')
                                ->state(function (Application $record) {
                                    $type = static::getProfileType($record);
                                    return match ($type) {
                                        'cadre'      => 'إطار (مهندسين / متصرفين / محللين)',
                                        'technicien' => 'تقني (مؤهل التقني السامي BTP)',
                                        'commis'     => 'مستكتب إدارة',
                                        'chauffeur'  => 'سائق',
                                        'nettoyage'  => 'عون تنظيف',
                                        default      => 'صنف إداري',
                                    };
                                })
                                ->badge()
                                ->color('info'),

                            TextEntry::make('created_at')
                                ->label('تاريخ ووقت التسجيل')
                                ->dateTime('d/m/Y H:i'),
                        ]),
                    ]),

                // ── 2-Column Grid Layout ──
                Grid::make(2)->schema([

                    // ── العمود الأول: المعلومات الشخصية + المستوى الدراسي ──
                    Grid::make(1)->columnSpan(1)->schema([

                        // ── 2. المعلومات الشخصية ومعلومات الاتصال ──
                        Section::make('المعلومات الشخصية والاتصال')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextEntry::make('name')
                                        ->label('الاسم الكامل')
                                        ->weight('bold'),

                                    TextEntry::make('gender')
                                        ->label('الجنس'),

                                    TextEntry::make('birth_date')
                                        ->label('تاريخ الولادة (العمر)')
                                        ->state(fn ($record) => ($record->birth_date ? $record->birth_date->format('d/m/Y') : '-') . ' (' . ($record->birth_date ? $record->birth_date->age . ' سنة' : '-') . ')')
                                        ->fontFamily('mono')
                                        ->weight('bold'),
                                ]),

                                Grid::make(3)->schema([
                                    TextEntry::make('cin')
                                        ->label('رقم بطاقة التعريف')
                                        ->fontFamily('mono')
                                        ->weight('bold'),

                                    TextEntry::make('cin_date')
                                        ->label('تاريخ الإصدار')
                                        ->date('d/m/Y'),

                                    TextEntry::make('tel')
                                        ->label('رقم الهاتف')
                                        ->copyable(),
                                ]),

                                TextEntry::make('email')
                                    ->label('البريد الإلكتروني')
                                    ->copyable(),
                            ]),

                        // ── 3. المستوى التعليمي والتكويني (مخصص حسب نوع الصنف) ──
                        Section::make('المستوى التعليمي / الدراسي والتكوين')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                // --- PROFILE: CADRES ---
                                Grid::make(2)
                                    ->visible(fn ($record) => static::getProfileType($record) === 'cadre')
                                    ->schema([
                                        TextEntry::make('degree')
                                            ->label('الشهادة العلمية')
                                            ->weight('bold'),

                                        TextEntry::make('specialty')
                                            ->label('الاختصاص')
                                            ->weight('bold'),

                                        TextEntry::make('graduation_year')
                                            ->label('سنة التخرج')
                                            ->fontFamily('mono'),

                                        TextEntry::make('institution')
                                            ->label('المؤسسة الجامعية')
                                            ->placeholder('مؤسسة تعليم عال عمومية'),

                                        TextEntry::make('equivalence_decision')
                                            ->label('قرار المعادلة')
                                            ->placeholder('غير منطبق (شهادة عمومية)'),

                                        TextEntry::make('equivalence_date')
                                            ->label('تاريخ المعادلة')
                                            ->date('d/m/Y')
                                            ->placeholder('—'),
                                    ]),

                                // --- PROFILE: TECHNICIEN ---
                                Grid::make(2)
                                    ->visible(fn ($record) => static::getProfileType($record) === 'technicien')
                                    ->schema([
                                        TextEntry::make('tech_degree')
                                            ->label('الشهادة المطلوبة')
                                            ->state(fn ($record) => $record->degree ?: 'مؤهل التقني السامي (BTP)')
                                            ->weight('bold'),

                                        TextEntry::make('specialty')
                                            ->label('الاختصاص')
                                            ->placeholder('مساعد مديرية')
                                            ->weight('bold'),

                                        TextEntry::make('graduation_year')
                                            ->label('سنة التخرج')
                                            ->fontFamily('mono'),

                                        TextEntry::make('institution')
                                            ->label('مركز التكوين المهني')
                                            ->placeholder('مركز تكوين مهني عمومي'),
                                    ]),

                                // --- PROFILE: COMMIS ---
                                Grid::make(2)
                                    ->visible(fn ($record) => static::getProfileType($record) === 'commis')
                                    ->schema([
                                        TextEntry::make('school_level')
                                            ->label('المستوى الدراسي المصرح به')
                                            ->helperText('الشروط: أدناه التاسعة أساسي بنجاح وأقصاه الرابعة ثانوي منهاة')
                                            ->weight('bold')
                                            ->columnSpanFull(),

                                        TextEntry::make('school_institution')
                                            ->label('المؤسسة التعليمية / المعهد')
                                            ->placeholder('غير محدد'),

                                        TextEntry::make('end_school_year')
                                            ->label('سنة الانقطاع عن الدراسة')
                                            ->placeholder('—')
                                            ->fontFamily('mono'),
                                    ]),

                                // --- PROFILE: CHAUFFEUR ---
                                Grid::make(2)
                                    ->visible(fn ($record) => static::getProfileType($record) === 'chauffeur')
                                    ->schema([
                                        TextEntry::make('school_level')
                                            ->label('المستوى الدراسي المصرح به')
                                            ->helperText('الشروط: أدناه التاسعة أساسي بنجاح وأقصاه الرابعة ثانوي منهاة ودون نجاح')
                                            ->weight('bold')
                                            ->columnSpanFull(),

                                        TextEntry::make('driving_license_category')
                                            ->label('صنف رخصة السياقة')
                                            ->state(fn ($record) => $record->driving_license_category ?: 'صنف ب (B)')
                                            ->badge()
                                            ->color('primary'),

                                        TextEntry::make('driving_license_date')
                                            ->label('تاريخ الحصول على الرخصة')
                                            ->date('d/m/Y'),
                                    ]),

                                // --- PROFILE: NETTOYAGE ---
                                Grid::make(2)
                                    ->visible(fn ($record) => static::getProfileType($record) === 'nettoyage')
                                    ->schema([
                                        TextEntry::make('school_level')
                                            ->label('المستوى الدراسي المصرح به')
                                            ->helperText('الشروط: أدناه السادسة أساسي بنجاح وأقصاه التاسعة أساسي منهاة ودون نجاح')
                                            ->weight('bold')
                                            ->columnSpanFull(),

                                        TextEntry::make('school_institution')
                                            ->label('المؤسسة التعليمية')
                                            ->placeholder('غير مصرح بها'),

                                        TextEntry::make('end_school_year')
                                            ->label('سنة الانقطاع عن الدراسة')
                                            ->placeholder('—')
                                            ->fontFamily('mono'),
                                    ]),
                            ]),
                    ]),

                    // ── العمود الثاني: العنوان + النتائج وصيغة الفرز + اختبار الكفاءة ──
                    Grid::make(1)->columnSpan(1)->schema([

                        // ── 4. العنوان والإقامة ──
                        Section::make('العنوان ومقر الإقامة')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('address')
                                        ->label('العنوان الحالي')
                                        ->weight('bold'),

                                    TextEntry::make('postal_code')
                                        ->label('الترقيم البريدي')
                                        ->fontFamily('mono')
                                        ->weight('bold'),

                                    TextEntry::make('governorate')
                                        ->label('الولاية'),

                                    TextEntry::make('city')
                                        ->label('المعتمدية'),
                                ]),
                            ]),

                        // ── 5. النتائج ومقياس الفرز الأولي ──
                        Section::make('النتائج وصيغة الفرز الأولي')
                            ->icon('heroicon-o-calculator')
                            ->schema([
                                // --- SCORES: CADRES ---
                                Grid::make(3)
                                    ->visible(fn ($record) => static::getProfileType($record) === 'cadre')
                                    ->schema([
                                        TextEntry::make('bac_average')
                                            ->label('معدل البكالوريا (60%)')
                                            ->fontFamily('mono')
                                            ->weight('bold'),

                                        TextEntry::make('grad_average')
                                            ->label('معدل سنة التخرج (40%)')
                                            ->fontFamily('mono')
                                            ->weight('bold'),

                                        TextEntry::make('score')
                                            ->label('مجموع النقاط المحتسب')
                                            ->badge()
                                            ->color('success')
                                            ->size('lg')
                                            ->weight('bold'),

                                        TextEntry::make('cadre_formula')
                                            ->label('صيغة الفرز الأولي')
                                            ->state('(معدل البكالوريا × 60%) + (معدل سنة التخرج × 40%)')
                                            ->color('gray')
                                            ->columnSpanFull(),
                                    ]),

                                // --- SCORES: TECHNICIEN ---
                                Grid::make(3)
                                    ->visible(fn ($record) => static::getProfileType($record) === 'technicien')
                                    ->schema([
                                        TextEntry::make('btp_or_bac_avg')
                                            ->label('معدل BTP أو البكالوريا (40%)')
                                            ->state(fn ($record) => $record->btp_average ?? $record->bac_average ?? '-')
                                            ->fontFamily('mono')
                                            ->weight('bold'),

                                        TextEntry::make('grad_average')
                                            ->label('معدل سنة التخرج (60%)')
                                            ->fontFamily('mono')
                                            ->weight('bold'),

                                        TextEntry::make('score')
                                            ->label('مجموع النقاط المحتسب')
                                            ->badge()
                                            ->color('success')
                                            ->size('lg')
                                            ->weight('bold'),

                                        TextEntry::make('tech_formula')
                                            ->label('صيغة الفرز الأولي')
                                            ->state('(معدل البكالوريا أو مؤهل التقني المهني × 40%) + (معدل سنة التخرج × 60%)')
                                            ->color('gray')
                                            ->columnSpanFull(),
                                    ]),

                                // --- SCORES: COMMIS ---
                                Grid::make(2)
                                    ->visible(fn ($record) => static::getProfileType($record) === 'commis')
                                    ->schema([
                                        TextEntry::make('grade_9_average')
                                            ->label('معدل السنة التاسعة أساسي')
                                            ->state(fn ($record) => $record->grade_9_average ? $record->grade_9_average . ' / 20' : '-')
                                            ->fontFamily('mono')
                                            ->weight('bold'),

                                        TextEntry::make('score')
                                            ->label('مجموع نقاط الفرز الأولي')
                                            ->badge()
                                            ->color('success')
                                            ->size('lg')
                                            ->weight('bold'),

                                        TextEntry::make('commis_formula')
                                            ->label('صيغة الفرز الأولي')
                                            ->state('معدل التاسعة أساسي + 1 نقطة عن كل سنة دراسية تفوق المستوى الأدنى (في حدود 4 سنوات كحد أقصى)')
                                            ->color('gray')
                                            ->columnSpanFull(),
                                    ]),

                                // --- SCORES: CHAUFFEUR ---
                                Grid::make(2)
                                    ->visible(fn ($record) => static::getProfileType($record) === 'chauffeur')
                                    ->schema([
                                        TextEntry::make('grade_9_average')
                                            ->label('معدل السنة التاسعة أساسي')
                                            ->state(fn ($record) => $record->grade_9_average ? $record->grade_9_average . ' / 20' : '-')
                                            ->fontFamily('mono')
                                            ->weight('bold'),

                                        TextEntry::make('score')
                                            ->label('مجموع نقاط الفرز الأولي')
                                            ->badge()
                                            ->color('success')
                                            ->size('lg')
                                            ->weight('bold'),

                                        TextEntry::make('chauf_formula')
                                            ->label('صيغة الفرز الأولي')
                                            ->state('معدل التاسعة أساسي + 1 نقطة عن كل سنة دراسية تفوق المستوى الأدنى المطلوب')
                                            ->color('gray')
                                            ->columnSpanFull(),
                                    ]),

                                // --- SCORES: NETTOYAGE ---
                                Grid::make(2)
                                    ->visible(fn ($record) => static::getProfileType($record) === 'nettoyage')
                                    ->schema([
                                        TextEntry::make('grade_6_average')
                                            ->label('معدل السنة السادسة أساسي')
                                            ->state(fn ($record) => $record->grade_6_average ? $record->grade_6_average . ' / 20' : '-')
                                            ->fontFamily('mono')
                                            ->weight('bold'),

                                        TextEntry::make('score')
                                            ->label('مجموع نقاط الفرز الأولي')
                                            ->badge()
                                            ->color('success')
                                            ->size('lg')
                                            ->weight('bold'),

                                        TextEntry::make('nettoyage_formula')
                                            ->label('صيغة الفرز الأولي')
                                            ->state('معدل السادسة أساسي + 1 نقطة عن كل سنة دراسية تفوق المستوى الأدنى المطلوب (في حدود 3 سنوات كحد أقصى)')
                                            ->color('gray')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // ── 6. اختبار الكفاءة والنتيجة النهائية ──
                        Section::make('اختبار الكفاءة والنتيجة النهائية')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextEntry::make('score')
                                        ->label('نقاط الفرز الأولي')
                                        ->badge()
                                        ->color('primary'),

                                    TextEntry::make('test_grade')
                                        ->placeholder('لم يجر الاختبار بعد')
                                        ->label('اختبار الكفاءة')
                                        ->fontFamily('mono'),

                                    TextEntry::make('final')
                                        ->state(fn ($record) => $record->test_grade ? number_format(($record->score + $record->test_grade) / 2, 2) : null)
                                        ->placeholder('في انتظار الاختبار')
                                        ->label('النتيجة النهائية')
                                        ->badge()
                                        ->color(fn ($state) => $state ? 'success' : 'gray'),
                                ]),
                            ]),
                    ]),

                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),
            'view' => ViewApplication::route('/{record}'),
        ];
    }
}
