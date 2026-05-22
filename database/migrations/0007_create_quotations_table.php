<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id(); $table->string('quotation_number')->unique(); $table->string('reference_number')->nullable(); $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('created_by')->constrained('users'); $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('client_name'); $table->string('client_email')->nullable(); $table->string('client_phone')->nullable(); $table->string('client_company')->nullable(); $table->string('client_gst')->nullable(); $table->text('billing_address')->nullable(); $table->text('shipping_address')->nullable();
            $table->date('quotation_date'); $table->date('valid_until'); $table->string('subject')->nullable(); $table->text('introduction')->nullable(); $table->text('terms_conditions')->nullable(); $table->text('notes')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0); $table->decimal('discount_amount', 15, 2)->default(0); $table->string('discount_type')->default('fixed'); $table->decimal('tax_amount', 15, 2)->default(0); $table->decimal('cgst_amount', 15, 2)->default(0); $table->decimal('sgst_amount', 15, 2)->default(0); $table->decimal('igst_amount', 15, 2)->default(0); $table->decimal('shipping_charges', 15, 2)->default(0); $table->decimal('additional_charges', 15, 2)->default(0); $table->string('additional_charges_label')->nullable(); $table->decimal('grand_total', 15, 2)->default(0);
            $table->enum('status', ['draft','sent','viewed','accepted','rejected','expired','converted'])->default('draft');
            $table->string('approval_token')->unique()->nullable(); $table->timestamp('sent_at')->nullable(); $table->timestamp('viewed_at')->nullable(); $table->timestamp('accepted_at')->nullable(); $table->timestamp('rejected_at')->nullable(); $table->text('rejection_reason')->nullable(); $table->string('accepted_by_name')->nullable(); $table->text('digital_signature')->nullable(); $table->string('template')->default('standard'); $table->integer('version')->default(1); $table->timestamps(); $table->softDeletes();
            $table->index(['status', 'created_by']); $table->index(['quotation_date']);
        });
        Schema::create('quotation_items', function (Blueprint $table) { $table->id(); $table->foreignId('quotation_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete(); $table->string('item_name'); $table->text('description')->nullable(); $table->decimal('quantity', 10, 2)->default(1); $table->string('unit')->default('piece'); $table->decimal('rate', 15, 2)->default(0); $table->decimal('discount', 15, 2)->default(0); $table->string('discount_type')->default('fixed'); $table->decimal('tax_percentage', 5, 2)->default(0); $table->decimal('tax_amount', 15, 2)->default(0); $table->decimal('line_total', 15, 2)->default(0); $table->integer('sort_order')->default(0); $table->timestamps(); });
        Schema::create('quotation_versions', function (Blueprint $table) { $table->id(); $table->foreignId('quotation_id')->constrained()->cascadeOnDelete(); $table->integer('version_number'); $table->json('snapshot'); $table->foreignId('created_by')->constrained('users'); $table->text('change_notes')->nullable(); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('quotation_versions'); Schema::dropIfExists('quotation_items'); Schema::dropIfExists('quotations'); }
};
