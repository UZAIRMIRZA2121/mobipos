<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

Schema::disableForeignKeyConstraints();
Schema::dropIfExists('addon_prices');
Schema::dropIfExists('product_addon_prices');
Schema::dropIfExists('product_addons');
Schema::dropIfExists('addons');
Schema::dropIfExists('product_variations');
Schema::dropIfExists('variations');
Schema::enableForeignKeyConstraints();

DB::table('migrations')->where('migration', 'like', '%variations%')->delete();
DB::table('migrations')->where('migration', 'like', '%addons%')->delete();
DB::table('migrations')->where('migration', 'like', '%addon_prices%')->delete();

echo "Tables and migration records dropped\n";
