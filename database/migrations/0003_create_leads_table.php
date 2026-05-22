<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id(); $table->string('lead_id')->unique(); $table->string('name'); $table->string('email')->nullable(); $table->string('phone')->nullable(); $table->string('whatsapp_number')->nullable(); $table->string('alternate_number')->nullable(); $table->string('company_name')->nullable(); $table->string('designation')->nullable(); $table->string('website')->nullable(); $table->string('industry')->nullable(); $table->text('address')->nullable(); $table->string('city')->nullable(); $table->string('state')->nullable(); $table->string('country')->default('India'); $table->string('pincode')->nullable(); $table->decimal('budget', 15, 2)->nullable(); $table->text('requirement')->nullable(); $table->text('notes')->nullable(); $table->json('tags')->nullable(); $table->string('source')->default('direct'); $table->string('campaign_source')->nullable();
            $table->enum('status', ['new','contacted','interested','follow_up','proposal_sent','negotiation','won','lost','not_interested','duplicate'])->default('new');
            $table->enum('priority', ['low','medium','high','urgent'])->default('medium');
            $table->integer('score')->default(0); $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->string('pipeline_stage')->default('new_lead'); $table->decimal('deal_value', 15, 2)->nullable(); $table->integer('win_probability')->default(0); $table->date('expected_close_date')->nullable(); $table->timestamp('last_contacted_at')->nullable(); $table->timestamp('converted_at')->nullable(); $table->unsignedBigInteger('converted_client_id')->nullable(); $table->timestamps(); $table->softDeletes();
            $table->index(['status', 'assigned_to']); $table->index(['source']); $table->index(['pipeline_stage']); $table->index(['created_at']);
        });
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id(); $table->foreignId('lead_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('type'); $table->string('title'); $table->text('description')->nullable(); $table->json('metadata')->nullable(); $table->timestamps(); $table->index(['lead_id', 'created_at']);
        });
        Schema::create('lead_documents', function (Blueprint $table) {
            $table->id(); $table->foreignId('lead_id')->constrained()->cascadeOnDelete(); $table->foreignId('uploaded_by')->constrained('users'); $table->string('name'); $table->string('file_path'); $table->string('file_type'); $table->integer('file_size'); $table->timestamps();
        });
        Schema::create('lead_notes', function (Blueprint $table) {
            $table->id(); $table->foreignId('lead_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained(); $table->text('content'); $table->boolean('is_internal')->default(true); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('lead_notes'); Schema::dropIfExists('lead_documents'); Schema::dropIfExists('lead_activities'); Schema::dropIfExists('leads'); }
};
