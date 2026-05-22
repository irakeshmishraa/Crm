<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id(); $table->string('client_id')->unique(); $table->string('company_name'); $table->string('contact_person'); $table->string('email')->unique(); $table->string('phone')->nullable(); $table->string('whatsapp')->nullable(); $table->string('website')->nullable(); $table->string('industry')->nullable(); $table->string('gst_number')->nullable(); $table->string('pan_number')->nullable(); $table->text('billing_address')->nullable(); $table->string('billing_city')->nullable(); $table->string('billing_state')->nullable(); $table->string('billing_country')->default('India'); $table->string('billing_pincode')->nullable(); $table->text('shipping_address')->nullable(); $table->string('shipping_city')->nullable(); $table->string('shipping_state')->nullable(); $table->string('shipping_country')->default('India'); $table->string('shipping_pincode')->nullable();
            $table->enum('status', ['active','inactive','blocked'])->default('active');
            $table->json('tags')->nullable(); $table->text('notes')->nullable(); $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); $table->foreignId('converted_from_lead')->nullable()->constrained('leads')->nullOnDelete(); $table->decimal('total_revenue', 15, 2)->default(0); $table->string('portal_password')->nullable(); $table->boolean('portal_access')->default(false); $table->timestamps(); $table->softDeletes();
        });
        Schema::create('client_contacts', function (Blueprint $table) { $table->id(); $table->foreignId('client_id')->constrained()->cascadeOnDelete(); $table->string('name'); $table->string('designation')->nullable(); $table->string('email')->nullable(); $table->string('phone')->nullable(); $table->boolean('is_primary')->default(false); $table->timestamps(); });
        Schema::create('client_documents', function (Blueprint $table) { $table->id(); $table->foreignId('client_id')->constrained()->cascadeOnDelete(); $table->foreignId('uploaded_by')->constrained('users'); $table->string('name'); $table->string('file_path'); $table->string('file_type'); $table->integer('file_size'); $table->string('category')->nullable(); $table->timestamps(); });
        Schema::create('client_payments', function (Blueprint $table) { $table->id(); $table->foreignId('client_id')->constrained()->cascadeOnDelete(); $table->string('payment_id')->unique(); $table->decimal('amount', 15, 2); $table->string('method')->nullable(); $table->string('reference_number')->nullable(); $table->date('payment_date'); $table->text('notes')->nullable(); $table->enum('status', ['pending','completed','failed','refunded'])->default('completed'); $table->foreignId('recorded_by')->constrained('users'); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('client_payments'); Schema::dropIfExists('client_documents'); Schema::dropIfExists('client_contacts'); Schema::dropIfExists('clients'); }
};
