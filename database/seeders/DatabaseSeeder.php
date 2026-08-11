<?php

namespace Database\Seeders;

use App\Models\ContestType;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Exécuter le ContestTypeSeeder pour avoir les 5 profils paramétrés
        $this->call(ContestTypeSeeder::class);

        // 2. Charger les types de concours enregistrés
        $typeModels = ContestType::all()->keyBy('code');

        // 3. Définir les 21 وظائف avec leur profil (ContestType) correspondant
        $positions = [
            ['code' => '01', 'name' => 'مهندس أول', 'type' => 'cadre', 'degree' => 'الشهادة الوطنية لمهندس', 'specialty' => 'هندسة البرمجيات أو هندسة البرمجيات ونظم المعلومات'],
            ['code' => '02', 'name' => 'مهندس أول', 'type' => 'cadre', 'degree' => 'الشهادة الوطنية لمهندس', 'specialty' => 'اختصاص هندسة الإعلامية'],
            ['code' => '03', 'name' => 'مهندس أول', 'type' => 'cadre', 'degree' => 'الشهادة الوطنية لمهندس', 'specialty' => 'الهندسة الصناعية'],
            ['code' => '04', 'name' => 'مهندس أول', 'type' => 'cadre', 'degree' => 'الشهادة الوطنية لمهندس', 'specialty' => 'الهندسة الصناعية'],
            ['code' => '05', 'name' => 'مهندس أول', 'type' => 'cadre', 'degree' => 'الشهادة الوطنية لمهندس', 'specialty' => 'الهندسة الصناعية'],
            ['code' => '06', 'name' => 'مهندس أول', 'type' => 'cadre', 'degree' => 'الشهادة الوطنية لمهندس', 'specialty' => 'إحصاء وتحليل المعلومات'],
            ['code' => '07', 'name' => 'محلل', 'type' => 'cadre', 'degree' => 'شهادة الإجازة الوطنية نظام أمد أو الأستاذية', 'specialty' => 'إعلامية التصرف أو ذكاء الأعمال'],
            ['code' => '08', 'name' => 'محلل', 'type' => 'cadre', 'degree' => 'شهادة الإجازة الوطنية نظام أمد أو الأستاذية', 'specialty' => 'إعلامية التصرف أو ذكاء الأعمال'],
            ['code' => '09', 'name' => 'متصرف', 'type' => 'cadre', 'degree' => 'شهادة الإجازة الوطنية نظام أمد أو الأستاذية', 'specialty' => 'التصرف في الموارد البشرية'],
            ['code' => '10', 'name' => 'متصرف', 'type' => 'cadre', 'degree' => 'شهادة الإجازة الوطنية نظام أمد أو الأستاذية', 'specialty' => 'مالية'],
            ['code' => '11', 'name' => 'متصرف', 'type' => 'cadre', 'degree' => 'شهادة الإجازة الوطنية نظام أمد أو الأستاذية', 'specialty' => 'بنوك'],
            ['code' => '12', 'name' => 'متصرف', 'type' => 'cadre', 'degree' => 'شهادة الإجازة الوطنية نظام أمد أو الأستاذية', 'specialty' => 'إدارة الأعمال'],
            ['code' => '13', 'name' => 'متصرف', 'type' => 'cadre', 'degree' => 'شهادة الإجازة الوطنية نظام أمد أو الأستاذية', 'specialty' => 'إدارة الأعمال'],
            ['code' => '14', 'name' => 'متصرف', 'type' => 'cadre', 'degree' => 'شهادة الإجازة الوطنية نظام أمد أو الأستاذية', 'specialty' => 'الصناعات الغذائية'],
            ['code' => '15', 'name' => 'متصرف', 'type' => 'cadre', 'degree' => 'شهادة الإجازة الوطنية نظام أمد أو الأستاذية', 'specialty' => 'المالية'],
            ['code' => '16', 'name' => 'ملحق إدارة', 'type' => 'technicien', 'degree' => 'مؤهل التقني السامي', 'specialty' => 'مساعد مديرية'],
            ['code' => '17', 'name' => 'مستكتب إدارة', 'type' => 'commis', 'degree' => 'التاسعة من التعليم الأساسي بنجاح / 4 ثانوي منهاة', 'specialty' => 'إدارة'],
            ['code' => '18', 'name' => 'مستكتب إدارة', 'type' => 'commis', 'degree' => 'التاسعة من التعليم الأساسي بنجاح / 4 ثانوي منهاة', 'specialty' => 'إدارة'],
            ['code' => '19', 'name' => 'سائق', 'type' => 'chauffeur', 'degree' => 'التاسعة من التعليم الأساسي + رخصة سياقة صنف ب', 'specialty' => 'سياقة'],
            ['code' => '20', 'name' => 'عون تنظيف', 'type' => 'nettoyage', 'degree' => 'السادسة من التعليم الأساسي بنجاح / 9 أساسي منهاة', 'specialty' => 'تنظيف'],
            ['code' => '21', 'name' => 'عون تنظيف', 'type' => 'nettoyage', 'degree' => 'السادسة من التعليم الأساسي بنجاح / 9 أساسي منهاة', 'specialty' => 'تنظيف'],
        ];

        foreach ($positions as $p) {
            $p['contest_type_id'] = $typeModels[$p['type']]->id ?? null;
            Position::updateOrCreate(['code' => $p['code']], $p);
        }

        // 4. Utilisateur Admin
        User::firstOrCreate(
            ['email' => 'samira.elmejdi@cnfcpp.tn'],
            [
                'name' => 'Admin',
                'password' => 'Ghorassen75868@',
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
