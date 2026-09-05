<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$v = App\Models\Variation::all();
$a = App\Models\Addon::all();
$p = App\Models\Product::whereNotNull('meta_data')->get();

echo "Variations: " . json_encode($v) . "\n";
echo "Addons: " . json_encode($a) . "\n";
echo "Products: " . json_encode($p) . "\n";
