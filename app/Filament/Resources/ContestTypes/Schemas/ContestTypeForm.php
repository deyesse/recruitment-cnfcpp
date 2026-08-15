<?php

namespace App\Filament\Resources\ContestTypes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContestTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معطيات نوع المناظرة')
                    ->schema([
                        TextInput::make('code')
                            ->label('الرمز (Code / Slug)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('cadre, technicien, commis, chauffeur, nettoyage...'),

                        TextInput::make('name')
                            ->label('اسم نوع المناظرة')
                            ->required()
                            ->placeholder('الإطارات، التقني السامي، سائق...'),

                        TextInput::make('min_school_level')
                            ->label('المستوى التعليمي الأدنى المطلوب')
                            ->placeholder('التاسعة أساسي بنجاح / السادسة أساسي / البكالوريا...'),

                        TextInput::make('min_score')
                            ->label('الحد الأدنى للنقاط لقبول الترشح')
                            ->numeric()
                            ->default(12.0)
                            ->required(),
                    ])->columns(2),

                Section::make('ضوارب الحساب وإسناد النقاط (Coefficients & Bonuses)')
                    ->schema([
                        Select::make('base_average_field')
                            ->label('حقل المعدل الأساسي المستعمل في الحساب')
                            ->options([
                                'bac_average'    => 'معدل البكالوريا (bac_average)',
                                'btp_average'    => 'معدل مؤهل التقني المهني / BTP',
                                'grade_9_average' => 'معدل السنة التاسعة أساسي',
                                'grade_6_average' => 'معدل السنة السادسة أساسي',
                            ])
                            ->default('bac_average')
                            ->required()
                            ->helperText('يحدد هذا الحقل أي معدل يستعمل كأساس في حساب النقطة'),

                        TextInput::make('coeff_bac_btp')
                            ->label('ضارب البكالوريا / BTP (من 0 إلى 1)')
                            ->numeric()
                            ->step(0.05)
                            ->default(0.60)
                            ->helperText('مثال: 0.60 تعني 60% من المجموع'),

                        TextInput::make('coeff_grad_degree')
                            ->label('ضارب سنة التخرج (من 0 إلى 1)')
                            ->numeric()
                            ->step(0.05)
                            ->default(0.40)
                            ->helperText('مثال: 0.40 تعني 40% من المجموع'),

                        TextInput::make('bonus_per_extra_year')
                            ->label('نقاط إضافية لكل سنة فوق المستوى الأدنى')
                            ->numeric()
                            ->step(0.5)
                            ->default(1.0)
                            ->helperText('نقطة عن كل سنة دراسية إضافية فوق المستوى المطلوب'),

                        TextInput::make('max_extra_years')
                            ->label('الحد الأقصى للسنوات الإضافية المحتسبة')
                            ->numeric()
                            ->default(4)
                            ->helperText('مثال: 4 للوصول إلى الرابعة ثانوي، 3 للتاسعة أساسي'),
                    ])->columns(2),

                Section::make('قائمة المستويات الدراسية وعدد السنوات الإضافية')
                    ->description('هذه القائمة تُحدد الخيارات المتاحة في استمارة الترشح وكيفية احتساب النقاط الإضافية لكل مستوى.')
                    ->schema([
                        Repeater::make('school_levels')
                            ->label('المستويات الدراسية')
                            ->schema([
                                TextInput::make('label')
                                    ->label('اسم المستوى')
                                    ->required()
                                    ->placeholder('مثال: التاسعة أساسي بنجاح، الأولى ثانوية...'),

                                TextInput::make('extra_years')
                                    ->label('عدد السنوات الإضافية (نقاط bonus)')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required()
                                    ->helperText('0 = مستوى أدنى (بدون bonus)، 1 = سنة واحدة فوق الأدنى...'),
                            ])
                            ->columns(2)
                            ->addActionLabel('إضافة مستوى دراسي')
                            ->reorderable()
                            ->collapsible()
                            ->defaultItems(0)
                            ->helperText(
                                'مثال للمستكتب إدارة (17-18): التاسعة=0 نقطة، الأولى ثانوية=1، الثانية=2، الثالثة=3، الرابعة=4' . PHP_EOL .
                                'مثال لعون التنظيف (20-21): السادسة=0، السابعة=1، الثامنة=2، التاسعة (غير ناجح)=3'
                            ),
                    ]),

                Section::make('معايير القبول والأهلية')
                    ->schema([
                        TextInput::make('min_age')
                            ->label('السن الأدنى المقبول (Âge minimum)')
                            ->numeric()
                            ->nullable()
                            ->placeholder('مثال: 18')
                            ->helperText('إذا كان سن المترشح أقل من هذا الحد بتاريخ المرجع، يُرفض الملف تلقائياً. اتركه فارغاً إذا لا يوجد حد أدنى.'),

                        TextInput::make('max_age')
                            ->label('السن الأقصى المقبول (Âge maximum)')
                            ->numeric()
                            ->nullable()
                            ->placeholder('مثال: 40')
                            ->helperText('إذا كان سن المترشح يتجاوز هذا الحد بتاريخ المرجع، يُرفض الملف تلقائياً. اتركه فارغاً إذا لا يوجد حد أقصى.'),

                        DatePicker::make('age_reference_date')
                            ->label('تاريخ مرجع حساب السن (الأدنى والأقصى)')
                            ->nullable()
                            ->displayFormat('d/m/Y')
                            ->placeholder('مثال: 01/01/2026')
                            ->helperText('السن الأدنى والأقصى للمترشح يُحسبان بناءً على هذا التاريخ المرجعي (مثال: غرة جانفي 2026 = 01-01-2026).'),

                        Toggle::make('has_bac')
                            ->label('تفعيل معدل البكالوريا / BTP')
                            ->default(true),

                        Toggle::make('has_degree')
                            ->label('تفعيل الشهادة العلمية ومعدل التخرج')
                            ->default(true),

                        Toggle::make('has_school_level')
                            ->label('تفعيل المستوى الدراسي (التعليم الأساسي/الثانوي)')
                            ->default(false),

                        Toggle::make('has_driving_license')
                            ->label('تفعيل معطيات رخصة السياقة')
                            ->default(false)
                            ->live(),

                        TextInput::make('driving_license_min_years')
                            ->label('الأقدمية الدنيا لرخصة السياقة (بالسنوات)')
                            ->numeric()
                            ->default(2)
                            ->minValue(0)
                            ->placeholder('مثال: 2')
                            ->visible(fn ($get) => (bool) $get('has_driving_license'))
                            ->helperText('الحد الأدنى لسنوات الحصول على رخصة السياقة بتاريخ المرجع. مثال: 2 تعني سنتين على الأقل.'),
                    ])->columns(2),
            ]);
    }
}
