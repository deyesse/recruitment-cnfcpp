<?php

namespace Database\Seeders;

use App\Models\ContestType;
use Illuminate\Database\Seeder;

class ContestTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // ----------------------------------------------------------------
            // CADRE (Codes 01-15) : مهندس أول، محلل، متصرف
            // Formule : BAC×60% + Diplôme×40%  — min_score = 12
            // ----------------------------------------------------------------
            [
                'code'               => 'cadre',
                'name'               => 'إطارات (مهندس / محلل / متصرف)',
                'min_school_level'   => 'الشهادة الوطنية لمهندس / الإجازة الوطنية نظام أمد / الأستاذية',
                'min_score'          => 12.0,
                'base_average_field' => 'bac_average',
                'coeff_bac_btp'      => 0.60,
                'coeff_grad_degree'  => 0.40,
                'bonus_per_extra_year' => 0.0,
                'max_extra_years'    => 0,
                'has_bac'            => true,
                'has_degree'         => true,
                'has_school_level'   => false,
                'has_driving_license'=> false,
                'has_age_bonus'      => false,
                'max_age'            => 40,   // السن الأقصى 40 سنة
                'age_reference_date' => '2026-01-01',
                'school_levels'      => null,
            ],

            // ----------------------------------------------------------------
            // TECHNICIEN (Code 16) : ملحق إدارة
            // Formule : BTP×40% + Diplôme×60%  — min_score = 12
            // ----------------------------------------------------------------
            [
                'code'               => 'technicien',
                'name'               => 'ملحق إدارة (مؤهل التقني السامي)',
                'min_school_level'   => 'مؤهل التقني السامي',
                'min_score'          => 12.0,
                'base_average_field' => 'btp_average',
                'coeff_bac_btp'      => 0.40,
                'coeff_grad_degree'  => 0.60,
                'bonus_per_extra_year' => 0.0,
                'max_extra_years'    => 0,
                'has_bac'            => true,
                'has_degree'         => true,
                'has_school_level'   => false,
                'has_driving_license'=> false,
                'has_age_bonus'      => false,
                'max_age'            => 40,
                'age_reference_date' => '2026-01-01',
                'school_levels'      => null,
            ],

            // ----------------------------------------------------------------
            // COMMIS (Codes 17-18) : مستكتب إدارة
            // Formule : معدل 9ème + 1pt/année supplémentaire (max 4 ans)
            // Niveau min : التاسعة أساسي بنجاح
            // Niveau max : الرابعة ثانوي منهاة (نظام جديد)
            // min_score = 10
            // ----------------------------------------------------------------
            [
                'code'               => 'commis',
                'name'               => 'مستكتب إدارة',
                'min_school_level'   => 'التاسعة من التعليم الأساسي بنجاح',
                'min_score'          => 10.0,
                'base_average_field' => 'grade_9_average',
                'coeff_bac_btp'      => 0.0,
                'coeff_grad_degree'  => 0.0,
                'bonus_per_extra_year' => 1.0,
                'max_extra_years'    => 4,
                'has_bac'            => false,
                'has_degree'         => false,
                'has_school_level'   => true,
                'has_driving_license'=> false,
                'has_age_bonus'      => false,
                'max_age'            => 40,
                'age_reference_date' => '2026-01-01',
                'school_levels'      => [
                    ['label' => 'التاسعة أساسي بنجاح (الحد الأدنى)',               'extra_years' => 0],
                    ['label' => 'الأولى ثانوية منهاة (نظام جديد)',                  'extra_years' => 1],
                    ['label' => 'الثانية ثانوية منهاة (نظام جديد)',                 'extra_years' => 2],
                    ['label' => 'الثالثة ثانوية منهاة (نظام جديد)',                'extra_years' => 3],
                    ['label' => 'الرابعة ثانوية منهاة (نظام جديد) - الحد الأقصى', 'extra_years' => 4],
                ],
            ],

            // ----------------------------------------------------------------
            // CHAUFFEUR (Code 19) : سائق
            // Formule : معدل 9ème + 1pt/année supplémentaire (max 4 ans)
            //         + rخصة سياقة صنف ب (منذ سنتين على الأقل)
            // min_score = 10
            // ----------------------------------------------------------------
            [
                'code'               => 'chauffeur',
                'name'               => 'سائق',
                'min_school_level'   => 'التاسعة من التعليم الأساسي بنجاح',
                'min_score'          => 10.0,
                'base_average_field' => 'grade_9_average',
                'coeff_bac_btp'      => 0.0,
                'coeff_grad_degree'  => 0.0,
                'bonus_per_extra_year' => 1.0,
                'max_extra_years'    => 4,
                'has_bac'            => false,
                'has_degree'         => false,
                'has_school_level'   => true,
                'has_driving_license'=> true,
                'has_age_bonus'      => false,
                'max_age'            => 40,
                'age_reference_date' => '2026-01-01',
                'school_levels'      => [
                    ['label' => 'التاسعة أساسي بنجاح (الحد الأدنى)',                'extra_years' => 0],
                    ['label' => 'الأولى ثانوية منهاة',                               'extra_years' => 1],
                    ['label' => 'الثانية ثانوية منهاة',                              'extra_years' => 2],
                    ['label' => 'الثالثة ثانوية منهاة',                             'extra_years' => 3],
                    ['label' => 'الرابعة ثانوية منهاة ودون نجاح - الحد الأقصى',    'extra_years' => 4],
                ],
            ],

            // ----------------------------------------------------------------
            // NETTOYAGE (Codes 20-21) : عون تنظيف
            // Formule : معدل 6ème + 1pt/année supplémentaire (max 3 ans)
            // Niveau min : السادسة أساسي بنجاح
            // Niveau max : التاسعة أساسي منهاة (ودون نجاح)
            // min_score = 12
            // L'âge = critère d'éligibilité (age <= 40 au 01/01/2026)
            // ----------------------------------------------------------------
            [
                'code'               => 'nettoyage',
                'name'               => 'عون تنظيف',
                'min_school_level'   => 'السادسة من التعليم الأساسي بنجاح',
                'min_score'          => 12.0,
                'base_average_field' => 'grade_6_average',
                'coeff_bac_btp'      => 0.0,
                'coeff_grad_degree'  => 0.0,
                'bonus_per_extra_year' => 1.0,
                'max_extra_years'    => 3,
                'has_bac'            => false,
                'has_degree'         => false,
                'has_school_level'   => true,
                'has_driving_license'=> false,
                'has_age_bonus'      => false,
                'max_age'            => 40,
                'age_reference_date' => '2026-01-01',
                'school_levels'      => [
                    ['label' => 'السادسة أساسي بنجاح (الحد الأدنى)',                   'extra_years' => 0],
                    ['label' => 'السابعة أساسي',                                        'extra_years' => 1],
                    ['label' => 'الثامنة أساسي',                                       'extra_years' => 2],
                    ['label' => 'التاسعة أساسي منهاة ودون نجاح - الحد الأقصى',        'extra_years' => 3],
                ],
            ],
        ];

        foreach ($types as $type) {
            ContestType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }

        $this->command->info('✅ 5 أنواع مناظرات تم إنشاؤها/تحديثها بنجاح.');
    }
}
