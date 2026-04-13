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
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->string('name', 200);
            $table->string('sku', 100)->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->json('images')->nullable();

            $table->enum('unit_type', ['weight', 'length', 'piece', 'dozen'])->default('piece');
            $table->string('unit_label', 30)->default('piece');

            $table->decimal('price_per_unit', 12, 2);
            $table->decimal('cost_price', 12, 2)->nullable();

            $table->decimal('stock_quantity', 12, 3)->default(0);
            $table->decimal('low_stock_alert', 12, 3)->default(10);
            $table->string('stock_unit', 30)->default('piece');

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

