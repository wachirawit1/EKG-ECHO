<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $mysql = DB::connection('mysql')->table('patient')->first();
    echo "MySQL Patient Sample: \n";
    print_r($mysql);

    $cols = DB::connection('mysql')->select('SHOW COLUMNS FROM patient');
    print_r($cols);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
