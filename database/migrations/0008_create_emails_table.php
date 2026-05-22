<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_accounts', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('email_address'); $table->string('provider')->default('gmail'); $table->text('access_token')->nullable(); $table->text('refresh_token')->nullable(); $table->timestamp('token_expires_at')->nullable(); $table->boolean('is_active')->default(true); $table->boolean('is_primary')->default(false); $table->json('settings')->nullable(); $table->timestamps(); });
        Schema::create('emails', function (Blueprint $table) { $table->id(); $table->foreignId('email_account_id')->constrained()->cascadeOnDelete(); $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete(); $table->string('message_id')->nullable(); $table->string('thread_id')->nullable(); $table->string('from_email'); $table->string('from_name')->nullable(); $table->json('to_emails'); $table->json('cc_emails')->nullable(); $table->json('bcc_emails')->nullable(); $table->string('subject'); $table->longText('body_html')->nullable(); $table->longText('body_text')->nullable(); $table->enum('direction', ['inbound','outbound'])->default('outbound'); $table->enum('status', ['draft','sent','delivered','opened','clicked','bounced','failed'])->default('draft'); $table->integer('open_count')->default(0); $table->integer('click_count')->default(0); $table->timestamp('opened_at')->nullable(); $table->timestamp('clicked_at')->nullable(); $table->timestamp('sent_at')->nullable(); $table->boolean('has_attachments')->default(false); $table->json('attachments')->nullable(); $table->timestamps(); $table->index(['thread_id']); $table->index(['lead_id']); });
        Schema::create('email_templates', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('name'); $table->string('subject'); $table->longText('body'); $table->string('category')->nullable(); $table->boolean('is_shared')->default(false); $table->integer('usage_count')->default(0); $table->timestamps(); });
        Schema::create('email_signatures', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('name'); $table->longText('content'); $table->boolean('is_default')->default(false); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('email_signatures'); Schema::dropIfExists('email_templates'); Schema::dropIfExists('emails'); Schema::dropIfExists('email_accounts'); }
};
