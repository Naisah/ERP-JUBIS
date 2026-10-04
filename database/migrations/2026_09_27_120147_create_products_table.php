<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('brand');
            $table->text('description')->nullable();
            $table->json('specs')->nullable();
            $table->string('image_path')->nullable();
            $table->integer('stock_quantity')->default(0); // Normalized from boolean
            $table->string('unit_of_measure')->default('pc'); // e.g., pc, roll, meter
            $table->decimal('wholesale_price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
