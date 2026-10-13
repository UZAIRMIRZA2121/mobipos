<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'is_refunded')) {
                $table->boolean('is_refunded')->default(false)->after('sell_price');
            }
            if (!Schema::hasColumn('order_items', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('is_refunded');
            }
        });

        try {
            DB::statement("ALTER TABLE installments MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Active'");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Could not alter installments status column: " . $e->getMessage());
        }
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'is_refunded')) {
                $table->dropColumn('is_refunded');
            }
            if (Schema::hasColumn('order_items', 'refunded_at')) {
                $table->dropColumn('refunded_at');
            }
        });
    }
};
