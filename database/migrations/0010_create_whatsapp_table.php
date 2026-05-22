<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) { $table->id(); $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete(); $table->string('whatsapp_message_id')->nullable(); $table->string('from_number'); $table->string('to_number'); $table->enum('direction', ['inbound','outbound'])->default('outbound'); $table->enum('type', ['text','template','image','document','video','audio'])->default('text'); $table->text('content')->nullable(); $table->string('template_name')->nullable(); $table->json('template_params')->nullable(); $table->string('media_url')->nullable(); $table->enum('status', ['pending','sent','delivered','read','failed'])->default('pending'); $table->timestamp('delivered_at')->nullable(); $table->timestamp('read_at')->nullable(); $table->text('error_message')->nullable(); $table->timestamps(); $table->index(['lead_id', 'created_at']); $table->index(['to_number', 'created_at']); });
        Schema::create('whatsapp_templates', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('template_id')->nullable(); $table->string('category')->default('general'); $table->string('language')->default('en'); $table->text('content'); $table->json('variables')->nullable(); $table->enum('status', ['pending','approved','rejected'])->default('pending'); $table->boolean('is_active')->default(true); $table->timestamps(); });
        Schema::create('whatsapp_auto_responses', function (Blueprint $table) { $table->id(); $table->string('trigger_keyword'); $table->text('response_message'); $table->boolean('is_active')->default(true); $table->integer('priority')->default(0); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('whatsapp_auto_responses'); Schema::dropIfExists('whatsapp_templates'); Schema::dropIfExists('whatsapp_messages'); }
};
