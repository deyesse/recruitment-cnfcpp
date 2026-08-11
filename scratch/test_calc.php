<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$contest = \App\Models\Contest::first();

// Case 1: 9th grade 11.5 + 3rd secondary (+3)
$application1 = \App\Models\Application::create([
    'contest_id' => $contest->id,
    'name' => 'محمد بن علي',
    'cin' => '01234567',
    'cin_date' => '2018-01-01',
    'gender' => 'ذكر',
    'birth_date' => '1998-05-15',
    'phone' => '98123456',
    'tel' => '98123456',
    'email' => 'test1@example.com',
    'address' => 'تونس',
    'governorate' => 'تونس',
    'city' => 'تونس المدينة',
    'postal_code' => '1000',
    'position' => '17',
    'grade_9_average' => 11.5,
    'school_level' => 'الثالثة ثانوية',
]);

// Case 2: 9th grade 11.5 + 4th secondary (+4 max cap)
$application2 = \App\Models\Application::create([
    'contest_id' => $contest->id,
    'name' => 'أحمد بن صالح',
    'cin' => '07654321',
    'cin_date' => '2017-01-01',
    'gender' => 'ذكر',
    'birth_date' => '1997-03-20',
    'phone' => '98654321',
    'tel' => '98654321',
    'email' => 'test2@example.com',
    'address' => 'صفاقس',
    'governorate' => 'صفاقس',
    'city' => 'صفاقس المدينة',
    'postal_code' => '3000',
    'position' => '17',
    'grade_9_average' => 11.5,
    'school_level' => 'الرابعة ثانوية',
]);

echo "App 1 (3rd sec) -> Calculated Score: " . $application1->calculated_score . " | Is Admissible: " . ($application1->is_admissible ? 'YES' : 'NO') . PHP_EOL;
echo "App 2 (4th sec) -> Calculated Score: " . $application2->calculated_score . " | Is Admissible: " . ($application2->is_admissible ? 'YES' : 'NO') . PHP_EOL;
