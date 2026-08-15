<?php

namespace App\Filament\Exports;

use App\Models\Application;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ApplicationExporter extends Exporter
{
    protected static ?string $model = Application::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('رقم التسجيل'),

            ExportColumn::make('contest.name')
                ->label('المناظرة'),

            ExportColumn::make('position')
                ->label('رمز الخطة'),

            ExportColumn::make('position_name')
                ->label('عنوان الخطة')
                ->state(function (Application $record) {
                    return $record->position_name
                        ?? \App\Models\Position::where('code', (string) $record->position)->first()?->name
                        ?? '-';
                }),

            ExportColumn::make('name')
                ->label('الاسم واللقب'),

            ExportColumn::make('gender')
                ->label('الجنس'),

            ExportColumn::make('birth_date')
                ->label('تاريخ الولادة')
                ->state(fn (Application $record) => $record->birth_date ? Carbon::parse($record->birth_date)->format('d/m/Y') : '-'),

            ExportColumn::make('age_at_reference')
                ->label('العمر في تاريخ المرجع (سنة)')
                ->state(fn (Application $record) => $record->getAgeAtReferenceDate()),

            ExportColumn::make('cin')
                ->label('رقم بطاقة التعريف الوطنية'),

            ExportColumn::make('cin_date')
                ->label('تاريخ إصدار بطاقة التعريف')
                ->state(fn (Application $record) => $record->cin_date ? Carbon::parse($record->cin_date)->format('d/m/Y') : '-'),

            ExportColumn::make('tel')
                ->label('رقم الهاتف'),

            ExportColumn::make('email')
                ->label('البريد الإلكتروني'),

            ExportColumn::make('address')
                ->label('العنوان'),

            ExportColumn::make('governorate')
                ->label('الولاية'),

            ExportColumn::make('city')
                ->label('المعتمدية'),

            ExportColumn::make('postal_code')
                ->label('الترقيم البريدي'),

            // ── Academic & School Level Data (All Profiles) ──
            ExportColumn::make('degree')
                ->label('الشهادة العلمية / مؤهل التقني'),

            ExportColumn::make('specialty')
                ->label('الاختصاص'),

            ExportColumn::make('graduation_year')
                ->label('سنة التخرج'),

            ExportColumn::make('institution')
                ->label('المؤسسة الجامعية / مركز التكوين'),

            ExportColumn::make('equivalence_decision')
                ->label('قرار المعادلة'),

            ExportColumn::make('equivalence_date')
                ->label('تاريخ المعادلة')
                ->state(fn (Application $record) => $record->equivalence_date ? Carbon::parse($record->equivalence_date)->format('d/m/Y') : '-'),

            ExportColumn::make('school_level')
                ->label('المستوى الدراسي المصرح به'),

            ExportColumn::make('school_institution')
                ->label('المؤسسة التعليمية / المعهد'),

            ExportColumn::make('end_school_year')
                ->label('سنة الانقطاع عن الدراسة'),

            ExportColumn::make('driving_license_category')
                ->label('صنف رخصة السياقة'),

            ExportColumn::make('driving_license_date')
                ->label('تاريخ الحصول على رخصة السياقة')
                ->state(fn (Application $record) => $record->driving_license_date ? Carbon::parse($record->driving_license_date)->format('d/m/Y') : '-'),

            // ── Scores & Screening Results (All Profiles) ──
            ExportColumn::make('bac_average')
                ->label('معدل البكالوريا'),

            ExportColumn::make('btp_average')
                ->label('معدل مؤهل التقني (BTP)'),

            ExportColumn::make('grad_average')
                ->label('معدل سنة التخرج'),

            ExportColumn::make('grade_9_average')
                ->label('معدل السنة التاسعة أساسي'),

            ExportColumn::make('grade_6_average')
                ->label('معدل السنة السادسة أساسي'),

            ExportColumn::make('calculated_score')
                ->label('مجموع نقاط الفرز الأولي')
                ->state(fn (Application $record) => $record->calculated_score ?? $record->calculateScore()),

            ExportColumn::make('test_grade')
                ->label('عدد اختبار الكفاءة'),

            ExportColumn::make('final_grade')
                ->label('النتيجة النهائية')
                ->state(function (Application $record) {
                    if ($record->test_grade === null) return null;
                    $score = $record->calculated_score ?? $record->calculateScore();
                    return round(($score + $record->test_grade) / 2, 3);
                }),

            ExportColumn::make('is_admissible')
                ->label('القبول الأولي')
                ->state(fn (Application $record) => $record->is_admissible ? 'مستوفي للشروط' : 'غير مستوفي للشروط'),

            ExportColumn::make('created_at')
                ->label('تاريخ ووقت التسجيل الإلكتروني')
                ->state(fn (Application $record) => $record->created_at ? Carbon::parse($record->created_at)->format('d/m/Y H:i') : '-'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your application export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
