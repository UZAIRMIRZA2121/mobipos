<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds nullable cat_id (category_id) to variations and addons tables
     * so they can be filtered per product category (e.g. Pizza sizes, Burger sizes).
     */
    public function up(): void
    {
        Schema::table('variations', function (Blueprint $table) {
            $table->foreignId('cat_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('categories')
                  ->onDelete('set null');
        });

        Schema::table('addons', function (Blueprint $table) {
            $table->foreignId('cat_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('categories')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('variations', function (Blueprint $table) {
            $table->dropForeign(['cat_id']);
            $table->dropColumn('cat_id');
        });

        Schema::table('addons', function (Blueprint $table) {
            $table->dropForeign(['cat_id']);
            $table->dropColumn('cat_id');
        });
    }
};
