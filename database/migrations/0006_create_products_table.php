<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('slug')->unique(); $table->text('description')->nullable(); $table->unsignedBigInteger('parent_id')->nullable(); $table->boolean('is_active')->default(true); $table->integer('sort_order')->default(0); $table->timestamps(); });
        Schema::create('products', function (Blueprint $table) {
            $table->id(); $table->string('product_id')->unique(); $table->string('name'); $table->string('slug')->unique(); $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete(); $table->string('sku')->nullable(); $table->string('hsn_sac_code')->nullable(); $table->text('description')->nullable(); $table->string('unit')->default('piece'); $table->decimal('cost_price', 15, 2)->default(0); $table->decimal('selling_price', 15, 2)->default(0); $table->decimal('tax_percentage', 5, 2)->default(18.00); $table->string('tax_type')->default('gst'); $table->string('image')->nullable(); $table->enum('type', ['product','service'])->default('product'); $table->boolean('is_active')->default(true); $table->integer('stock_quantity')->nullable(); $table->timestamps(); $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('products'); Schema::dropIfExists('product_categories'); }
};
