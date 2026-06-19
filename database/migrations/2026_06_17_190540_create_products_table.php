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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['mobile', 'tablet', 'laptop', 'accessory'])->default('mobile');
            $table->enum('condition', ['new', 'used', 'refurbished'])->default('new');
            $table->string('imei_serial')->nullable()->unique();
            $table->string('color')->nullable();
            $table->string('storage')->nullable();
            $table->string('image')->nullable();
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->enum('status', ['in_stock', 'sold', 'in_repair'])->default('in_stock');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
