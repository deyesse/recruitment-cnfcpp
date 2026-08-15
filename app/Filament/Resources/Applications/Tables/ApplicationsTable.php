<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Filament\Exports\ApplicationExporter;
use App\Models\Contest;
use App\Models\Position;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('export_with_presets')
                    ->label('تصدير مطالب (حسب الصنف)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->modalHeading('تصدير مطالب الترشح (Excel / CSV)')
                    ->modalDescription('اختر الصنف المهني لتحديد الأعمدة المناسبة له تلقائياً، مع إمكانية تعديل الاختيارات يدوياً قبل التنزيل.')
                    ->modalSubmitActionLabel('تحميل ملف Excel')
                    ->form([
                        \Filament\Forms\Components\Select::make('preset')
                            ->label('اختيار قالب مسبق حسب الصنف المهني')
                            ->options([
                                'all'        => 'جميع الأعمدة (الكل)',
                                'cadre'      => 'إطارات (مهندسين / متصرفين / محللين)',
                                'technicien' => 'مساعد مديرية - تقني (مؤهل تقني سامي BTP)',
                                'commis'     => 'مستكتب إدارة',
                                'chauffeur'  => 'سائق',
                                'nettoyage'  => 'عون تنظيف (Femme / Agent de ménage)',
                            ])
                            ->default('all')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('columns', static::getPresetColumns($state));
                            }),

                        \Filament\Forms\Components\Select::make('position_filter')
                            ->label('تصفية حسب الخطة (اختياري)')
                            ->options(function () {
                                $positions = Position::orderBy('code')->get();
                                $opts = ['all' => 'جميع الخطط'];
                                foreach ($positions as $p) {
                                    $opts[$p->code] = "[{$p->code}] {$p->name}";
                                }
                                return $opts;
                            })
                            ->default('all')
                            ->searchable(),

                        \Filament\Forms\Components\Select::make('sort_by')
                            ->label('ترتيب النتائج في ملف Excel')
                            ->options([
                                'score_desc' => 'حسب مجموع النقاط (تنازلي: من الأعلى إلى الأدنى)',
                                'score_asc'  => 'حسب مجموع النقاط (تصاعدي: من الأدنى إلى الأعلى)',
                                'id_asc'     => 'حسب رقم التسجيل (تصاعدي)',
                                'name_asc'   => 'أبجدياً حسب الاسم (أ - ي)',
                            ])
                            ->default('score_desc'),

                        \Filament\Forms\Components\CheckboxList::make('columns')
                            ->label('الأعمدة المراد تصديرها')
                            ->options(static::getAllExportColumns())
                            ->default(array_keys(static::getAllExportColumns()))
                            ->bulkToggleable()
                            ->searchable()
                            ->columns(2)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $query = \App\Models\Application::query()->with('contest');
                        if (! empty($data['position_filter']) && $data['position_filter'] !== 'all') {
                            $query->where('position', (string) $data['position_filter']);
                        }

                        $sortBy = $data['sort_by'] ?? 'score_desc';
                        if ($sortBy === 'score_asc') {
                            $query->orderBy('position')->orderBy('calculated_score', 'asc');
                        } elseif ($sortBy === 'id_asc') {
                            $query->orderBy('id', 'asc');
                        } elseif ($sortBy === 'name_asc') {
                            $query->orderBy('name', 'asc');
                        } else {
                            $query->orderBy('position')->orderBy('calculated_score', 'desc');
                        }

                        $applications = $query->get();
                        $selectedCols = $data['columns'] ?? array_keys(static::getAllExportColumns());
                        $allCols = static::getAllExportColumns();

                        return response()->streamDownload(function () use ($applications, $selectedCols, $allCols) {
                            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
                            echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
                            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
                            echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
                            echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
                            echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
                            echo ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
                            echo ' <Styles>' . "\n";
                            echo '  <Style ss:ID="Default" ss:Name="Normal">' . "\n";
                            echo '   <Alignment ss:Vertical="Center"/>' . "\n";
                            echo '   <Borders/>' . "\n";
                            echo '   <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#000000"/>' . "\n";
                            echo '  </Style>' . "\n";
                            echo '  <Style ss:ID="Header">' . "\n";
                            echo '   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>' . "\n";
                            echo '   <Borders>' . "\n";
                            echo '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#004D4D"/>' . "\n";
                            echo '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#004D4D"/>' . "\n";
                            echo '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#004D4D"/>' . "\n";
                            echo '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#004D4D"/>' . "\n";
                            echo '   </Borders>' . "\n";
                            echo '   <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>' . "\n";
                            echo '   <Interior ss:Color="#008080" ss:Pattern="Solid"/>' . "\n";
                            echo '  </Style>' . "\n";
                            echo '  <Style ss:ID="Cell">' . "\n";
                            echo '   <Alignment ss:Vertical="Center" ss:Horizontal="Right"/>' . "\n";
                            echo '   <Borders>' . "\n";
                            echo '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>' . "\n";
                            echo '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>' . "\n";
                            echo '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>' . "\n";
                            echo '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>' . "\n";
                            echo '   </Borders>' . "\n";
                            echo '  </Style>' . "\n";
                            echo '  <Style ss:ID="CellCenter">' . "\n";
                            echo '   <Alignment ss:Vertical="Center" ss:Horizontal="Center"/>' . "\n";
                            echo '   <Borders>' . "\n";
                            echo '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>' . "\n";
                            echo '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>' . "\n";
                            echo '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>' . "\n";
                            echo '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>' . "\n";
                            echo '   </Borders>' . "\n";
                            echo '  </Style>' . "\n";
                            echo ' </Styles>' . "\n";
                            echo ' <Worksheet ss:Name="مطالب الترشح" ss:RightToLeft="1">' . "\n";
                            echo '  <Table ss:DefaultRowHeight="20">' . "\n";

                            // Header row
                            echo '   <Row ss:Height="28">' . "\n";
                            foreach ($selectedCols as $colKey) {
                                $label = htmlspecialchars($allCols[$colKey] ?? $colKey, ENT_XML1, 'UTF-8');
                                echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">' . $label . '</Data></Cell>' . "\n";
                            }
                            echo '   </Row>' . "\n";

                            // Data rows
                            foreach ($applications as $app) {
                                echo '   <Row ss:Height="22">' . "\n";
                                foreach ($selectedCols as $colKey) {
                                    $val = static::getExportCellValue($app, $colKey);
                                    $cleanVal = htmlspecialchars((string) $val, ENT_XML1, 'UTF-8');
                                    $isCentered = in_array($colKey, [
                                        'id', 'position', 'gender', 'birth_date', 'age_at_reference',
                                        'cin', 'cin_date', 'tel', 'postal_code', 'graduation_year',
                                        'end_school_year', 'equivalence_date', 'driving_license_date',
                                        'bac_average', 'btp_average', 'grad_average', 'grade_9_average',
                                        'grade_6_average', 'calculated_score', 'test_grade', 'final_grade',
                                        'is_admissible', 'created_at',
                                    ]);
                                    $style = $isCentered ? 'CellCenter' : 'Cell';
                                    echo '    <Cell ss:StyleID="' . $style . '"><Data ss:Type="String">' . $cleanVal . '</Data></Cell>' . "\n";
                                }
                                echo '   </Row>' . "\n";
                            }

                            echo '  </Table>' . "\n";
                            echo '  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">' . "\n";
                            echo '   <DisplayRightToLeft/>' . "\n";
                            echo '  </WorksheetOptions>' . "\n";
                            echo ' </Worksheet>' . "\n";
                            echo '</Workbook>' . "\n";
                        }, 'candidatures_cnfcpp_' . date('Ymd_His') . '.xls', [
                            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                            'Content-Disposition' => 'attachment; filename="candidatures_cnfcpp_' . date('Ymd_His') . '.xls"',
                        ]);
                    }),
            ])
            ->columns([
                TextColumn::make('id')
                    ->label('رقم التسجيل')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(
                        query: fn ($query, string $search): \Illuminate\Database\Eloquent\Builder => $query->where('applications.name', 'like', "%{$search}%")
                    ),

                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable(),

                TextColumn::make('tel')
                    ->label('رقم الهاتف')
                    ->searchable(),

                TextColumn::make('age')
                    ->label("العمر (المرجع)\n(سنة)")
                    ->state(fn ($record) => $record->getAgeAtReferenceDate() ?? ($record->birth_date ? $record->birth_date->age : '-'))
                    ->alignCenter()
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('birth_date', $direction === 'asc' ? 'desc' : 'asc');
                    }),

                TextColumn::make('created_at')
                    ->label('تاريخ ووقت التسجيل')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('position')
                    ->label('رمز الوظيفة / الخطة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('calculated_score')
                    ->label('مجموع النقاط')
                    ->sortable()
                    ->numeric(decimalPlaces: 3, locale: 'en_US'),

                TextColumn::make('test_grade')
                    ->placeholder('لم يجتز الاختبار')
                    ->label('نتيجة الاختبار')
                    ->sortable(),

                TextColumn::make('final')
                    ->state(fn ($record) => $record->test_grade !== null ? (($record->calculated_score ?? $record->calculateScore()) + $record->test_grade) / 2 : null)
                    ->placeholder('لم يجتز الاختبار')
                    ->label('النتيجة النهائية')
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderByRaw('(COALESCE(calculated_score, 0) + COALESCE(test_grade, 0)) / 2 ' . $direction);
                    }),

            ])
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
                    ->searchable()
                    ->placeholder('الكل'),
            ])
            ->groups([
                Group::make('position')
                    ->label('الوظيفة')
                    ->orderQueryUsing(fn ($query, string $direction) => $query->orderBy('position', $direction)->orderBy('calculated_score', 'desc')),
            ])
            ->defaultSort('calculated_score', 'desc')
            ->recordActions([
                Action::make('passer')
                    ->label('قبول اولي')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->status = 'مقبول';

                        $record->save();
                    })
                    ->visible(fn ($record) => $record->status === 'جديد' && ! auth()->user()?->isGestionnaire()),
                Action::make('revert')
                    ->label('إلغاء القبول')
                    ->color('danger')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->action(function ($record) {
                        $record->test_grade = null;
                        $record->status = 'جديد';
                        $record->save();
                    })
                    ->visible(fn ($record) => $record->status === 'مقبول' && ! auth()->user()?->isGestionnaire()),

                Action::make('note_test')
                    ->label('تقييم الاختبار')
                    ->schema([
                        TextInput::make('test_grade')
                            ->label('درجة الاختبار')
                            ->numeric()
                            ->maxValue(20)
                            ->minValue(0)
                            ->required(),
                    ])
                    ->action(function ($record, $data) {
                        $record->test_grade = $data['test_grade'];
                        $record->save();
                    })
                    ->visible(fn ($record) => $record->status === 'مقبول' && ! auth()->user()?->isGestionnaire()),

                ViewAction::make()
                    ->label('عرض'),
            ])
            ->toolbarActions([
            ]);
    }

    public static function getAllExportColumns(): array
    {
        return [
            'id'                       => 'رقم التسجيل',
            'contest'                  => 'المناظرة',
            'position'                 => 'رمز الخطة',
            'position_name'            => 'عنوان الخطة',
            'name'                     => 'الاسم واللقب',
            'gender'                   => 'الجنس',
            'birth_date'               => 'تاريخ الولادة',
            'age_at_reference'         => 'العمر في تاريخ المرجع (سنة)',
            'cin'                      => 'رقم بطاقة التعريف الوطنية',
            'cin_date'                 => 'تاريخ إصدار بطاقة التعريف',
            'tel'                      => 'رقم الهاتف',
            'email'                    => 'البريد الإلكتروني',
            'address'                  => 'العنوان',
            'governorate'              => 'الولاية',
            'city'                     => 'المعتمدية',
            'postal_code'              => 'الترقيم البريدي',
            'degree'                   => 'الشهادة العلمية / مؤهل التقني (BTP)',
            'specialty'                => 'الاختصاص',
            'graduation_year'          => 'سنة التخرج',
            'institution'              => 'المؤسسة الجامعية / مركز التكوين',
            'equivalence_decision'     => 'قرار المعادلة',
            'equivalence_date'         => 'تاريخ المعادلة',
            'school_level'             => 'المستوى الدراسي المصرح به',
            'school_institution'       => 'المؤسسة التعليمية / المعهد',
            'end_school_year'          => 'سنة الانقطاع عن الدراسة',
            'driving_license_category' => 'صنف رخصة السياقة',
            'driving_license_date'     => 'تاريخ الحصول على رخصة السياقة',
            'bac_average'              => 'معدل البكالوريا',
            'btp_average'              => 'معدل مؤهل التقني (BTP)',
            'grad_average'             => 'معدل سنة التخرج',
            'grade_9_average'          => 'معدل السنة التاسعة أساسي',
            'grade_6_average'          => 'معدل السنة السادسة أساسي',
            'calculated_score'         => 'مجموع نقاط الفرز الأولي',
            'test_grade'               => 'عدد اختبار الكفاءة',
            'final_grade'              => 'النتيجة النهائية',
            'is_admissible'            => 'القبول الأولي',
            'created_at'               => 'تاريخ ووقت التسجيل الإلكتروني',
        ];
    }

    public static function getPresetColumns(?string $preset): array
    {
        $common = [
            'id', 'contest', 'position', 'position_name',
            'name', 'gender', 'birth_date', 'age_at_reference',
            'cin', 'cin_date', 'tel', 'email', 'address', 'governorate', 'city', 'postal_code',
        ];

        return match ($preset) {
            'cadre' => array_merge($common, [
                'degree', 'specialty', 'graduation_year', 'institution',
                'equivalence_decision', 'equivalence_date',
                'bac_average', 'grad_average',
                'calculated_score', 'test_grade', 'final_grade', 'is_admissible', 'created_at',
            ]),

            'technicien' => array_merge($common, [
                'degree', 'specialty', 'graduation_year', 'institution',
                'equivalence_decision', 'equivalence_date',
                'btp_average', 'grad_average',
                'calculated_score', 'test_grade', 'final_grade', 'is_admissible', 'created_at',
            ]),

            'commis' => array_merge($common, [
                'school_level', 'school_institution', 'end_school_year',
                'grade_9_average',
                'calculated_score', 'test_grade', 'final_grade', 'is_admissible', 'created_at',
            ]),

            'chauffeur' => array_merge($common, [
                'school_level', 'school_institution', 'end_school_year',
                'driving_license_category', 'driving_license_date',
                'grade_9_average',
                'calculated_score', 'test_grade', 'final_grade', 'is_admissible', 'created_at',
            ]),

            'nettoyage' => array_merge($common, [
                'school_level', 'school_institution', 'end_school_year',
                'grade_6_average',
                'calculated_score', 'test_grade', 'final_grade', 'is_admissible', 'created_at',
            ]),

            default => array_keys(static::getAllExportColumns()),
        };
    }

    public static function getExportCellValue($app, string $colKey): string
    {
        return match ($colKey) {
            'id'                       => (string) $app->id,
            'contest'                  => (string) ($app->contest?->name ?? 'المناظرة الخارجية لانتداب إطارات وأعوان'),
            'position'                 => (string) $app->position,
            'position_name'            => (string) ($app->position_name ?? Position::where('code', (string) $app->position)->first()?->name ?? '-'),
            'profile_type'             => match ($app->getProfileType()) {
                'cadre'      => 'إطار',
                'technicien' => 'تقني (BTP)',
                'commis'     => 'مستكتب إدارة',
                'chauffeur'  => 'سائق',
                'nettoyage'  => 'عون تنظيف',
                default      => 'إداري',
            },
            'name'                     => (string) $app->name,
            'gender'                   => (string) $app->gender,
            'birth_date'               => $app->birth_date ? Carbon::parse($app->birth_date)->format('d/m/Y') : '-',
            'age_at_reference'         => (string) ($app->getAgeAtReferenceDate() ?? ($app->birth_date ? $app->birth_date->age : '-')),
            'cin'                      => (string) $app->cin,
            'cin_date'                 => $app->cin_date ? Carbon::parse($app->cin_date)->format('d/m/Y') : '-',
            'tel'                      => (string) $app->tel,
            'email'                    => (string) $app->email,
            'address'                  => (string) $app->address,
            'governorate'              => (string) $app->governorate,
            'city'                     => (string) $app->city,
            'postal_code'              => (string) $app->postal_code,
            'degree'                   => (string) ($app->degree ?? '-'),
            'specialty'                => (string) ($app->specialty ?? '-'),
            'graduation_year'          => (string) ($app->graduation_year ?? '-'),
            'institution'              => (string) ($app->institution ?? '-'),
            'equivalence_decision'     => (string) ($app->equivalence_decision ?? '-'),
            'equivalence_date'         => $app->equivalence_date ? Carbon::parse($app->equivalence_date)->format('d/m/Y') : '-',
            'school_level'             => (string) ($app->school_level ?? '-'),
            'school_institution'       => (string) ($app->school_institution ?? '-'),
            'end_school_year'          => (string) ($app->end_school_year ?? '-'),
            'driving_license_category' => (string) ($app->driving_license_category ?? '-'),
            'driving_license_date'     => $app->driving_license_date ? Carbon::parse($app->driving_license_date)->format('d/m/Y') : '-',
            'bac_average'              => $app->bac_average ? number_format($app->bac_average, 2) : '-',
            'btp_average'              => $app->btp_average ? number_format($app->btp_average, 2) : '-',
            'grad_average'             => $app->grad_average ? number_format($app->grad_average, 2) : '-',
            'grade_9_average'          => $app->grade_9_average ? number_format($app->grade_9_average, 2) : '-',
            'grade_6_average'          => $app->grade_6_average ? number_format($app->grade_6_average, 2) : '-',
            'calculated_score'         => number_format($app->calculated_score ?? $app->calculateScore(), 3),
            'test_grade'               => $app->test_grade !== null ? number_format($app->test_grade, 2) : '-',
            'final_grade'              => $app->test_grade !== null ? number_format((($app->calculated_score ?? $app->calculateScore()) + $app->test_grade) / 2, 3) : '-',
            'is_admissible'            => $app->is_admissible ? 'مستوفي للشروط' : 'غير مستوفي للشروط',
            'created_at'               => $app->created_at ? Carbon::parse($app->created_at)->format('d/m/Y H:i') : '-',
            default                    => '',
        };
    }
}
