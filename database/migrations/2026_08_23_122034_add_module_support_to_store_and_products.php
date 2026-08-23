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
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('business_type')->default('mobile')->after('user_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->json('meta_data')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn('business_type');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('meta_data');
        });
    }
};
