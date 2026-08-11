<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

foreach (App\Models\ContestType::all() as $t) {
    echo $t->code . " => " . json_encode($t->school_levels, JSON_UNESCAPED_UNICODE) . "\n";
}
