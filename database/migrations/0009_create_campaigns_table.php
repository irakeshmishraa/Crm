<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) { $table->id(); $table->foreignId('created_by')->constrained('users'); $table->string('name'); $table->text('description')->nullable(); $table->enum('type', ['email','whatsapp','sms'])->default('email'); $table->enum('status', ['draft','active','paused','completed','cancelled'])->default('draft'); $table->foreignId('email_account_id')->nullable()->constrained()->nullOnDelete(); $table->json('recipient_filters')->nullable(); $table->integer('total_recipients')->default(0); $table->integer('sent_count')->default(0); $table->integer('open_count')->default(0); $table->integer('click_count')->default(0); $table->integer('reply_count')->default(0); $table->integer('bounce_count')->default(0); $table->integer('unsubscribe_count')->default(0); $table->boolean('stop_on_reply')->default(true); $table->timestamp('scheduled_at')->nullable(); $table->timestamp('started_at')->nullable(); $table->timestamp('completed_at')->nullable(); $table->timestamps(); $table->softDeletes(); });
        Schema::create('campaign_sequences', function (Blueprint $table) { $table->id(); $table->foreignId('campaign_id')->constrained()->cascadeOnDelete(); $table->integer('step_number'); $table->integer('delay_days')->default(0); $table->integer('delay_hours')->default(0); $table->string('subject'); $table->longText('body'); $table->enum('type', ['email','whatsapp'])->default('email'); $table->boolean('is_active')->default(true); $table->integer('sent_count')->default(0); $table->integer('open_count')->default(0); $table->timestamps(); });
        Schema::create('campaign_recipients', function (Blueprint $table) { $table->id(); $table->foreignId('campaign_id')->constrained()->cascadeOnDelete(); $table->foreignId('lead_id')->constrained()->cascadeOnDelete(); $table->integer('current_step')->default(0); $table->enum('status', ['active','completed','replied','unsubscribed','bounced'])->default('active'); $table->timestamp('last_sent_at')->nullable(); $table->timestamp('next_send_at')->nullable(); $table->timestamps(); $table->unique(['campaign_id', 'lead_id']); });
    }
    public function down(): void { Schema::dropIfExists('campaign_recipients'); Schema::dropIfExists('campaign_sequences'); Schema::dropIfExists('campaigns'); }
};
