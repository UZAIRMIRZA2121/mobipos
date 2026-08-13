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
        Schema::create('product_stock_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('imeis')->nullable()->comment('Comma separated IMEIs for this specific unit (e.g. dual sim)');
            $table->enum('status', ['available', 'sold', 'returned'])->default('available');
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stock_units');
    }
};
