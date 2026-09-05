<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('unit')->default('pcs')->after('stock');
            $table->decimal('stock', 10, 3)->default(1)->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('qty', 10, 3)->default(1)->change();
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('qty', 10, 3)->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
