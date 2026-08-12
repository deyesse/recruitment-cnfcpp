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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('المعلومات الشخصية')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('name')
                                ->label('الاسم الكامل'),

                            TextEntry::make('gender')
                                ->label('الجنس'),

                            TextEntry::make('birth_date')
                                ->label('تاريخ الميلاد')
                                ->date('d/m/Y'),
                            TextEntry::make('age')
                                ->label('العمر')
                                ->state(fn ($record) => $record->birth_date ? $record->birth_date->age . ' سنة' : '-'),
                        ]),

                        Grid::make(3)->schema([
                            TextEntry::make('cin')
                                ->label('رقم بطاقة التعريف'),

                            TextEntry::make('cin_date')
                                ->label('تاريخ الإصدار')
                                ->date('d/m/Y'),

                            TextEntry::make('tel')
                                ->label('رقم الهاتف'),
                        ]),
                    ]),
                Section::make('معلومات المناظرة والتسجيل')
                    ->icon('heroicon-o-trophy')
                    ->schema([
                        TextEntry::make('id')
                            ->label('رقم التسجيل'),
                        TextEntry::make('created_at')
                            ->label('تاريخ ووقت التسجيل')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('position')
                            ->label('رمز الوظيفة / الخطة'),
                    ])
                    ->columns(3),

                Section::make('العنوان')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('address')
                                ->label('العنوان'),

                            TextEntry::make('governorate')
                                ->label('الولاية'),

                            TextEntry::make('city')
                                ->label('المعتمدية'),

                            TextEntry::make('postal_code')
                                ->label('الترقيم البريدي'),
                        ]),
                    ]),

                Section::make('المعلومات الأكاديمية')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('degree')
                                ->label('الشهادة'),

                            TextEntry::make('specialty')
                                ->label('الاختصاص'),

                            TextEntry::make('graduation_year')
                                ->label('سنة التخرج'),
                        ]),

                        Grid::make(3)->schema([
                            TextEntry::make('equivalence_decision')
                                ->label('قرار المعادلة'),

                            TextEntry::make('equivalence_date')
                                ->label('تاريخ المعادلة')
                                ->date('d/m/Y'),
                        ]),
                    ]),

                Section::make('المعدلات')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('bac_average')
                                ->label('معدل البكالوريا'),

                            TextEntry::make('grad_average')
                                ->label('معدل التخرج'),
                            TextEntry::make('score')
                                ->label('مجموع النقاط'),
                            TextEntry::make('test_grade')
                                ->placeholder('لم يجتز الاختبار')
                                ->label('اختبار الكفاءة'),
                            TextEntry::make('final')
                                ->state(fn ($record) => $record->test_grade ? ($record->score + $record->test_grade) / 2 : null)
                                ->placeholder('لم يجتز الاختبار')
                                ->label('النتيجة النهائية'),
                        ]),
                    ]),

                Section::make('معلومات الاتصال')
                    ->icon('heroicon-o-envelope')
                    ->schema([
                        TextEntry::make('email')
                            ->label('البريد الإلكتروني')
                            ->copyable(),
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
