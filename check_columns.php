<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    $columns = Schema::connection('sqlsrv')->getColumnListing('PATIENT');
    echo "PATIENT columns: " . implode(', ', $columns) . "\n";

    $sample = DB::connection('sqlsrv')->table('PATIENT')->limit(1)->first();
    print_r($sample);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
