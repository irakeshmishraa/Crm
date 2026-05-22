<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) { $table->uuid('id')->primary(); $table->string('type'); $table->morphs('notifiable'); $table->text('data'); $table->timestamp('read_at')->nullable(); $table->timestamps(); });
        Schema::create('settings', function (Blueprint $table) { $table->id(); $table->string('group')->default('general'); $table->string('key')->unique(); $table->text('value')->nullable(); $table->string('type')->default('string'); $table->text('description')->nullable(); $table->timestamps(); $table->index(['group']); });
        Schema::create('activity_logs', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('action'); $table->string('module'); $table->morphs('subject'); $table->text('description')->nullable(); $table->json('properties')->nullable(); $table->string('ip_address')->nullable(); $table->string('user_agent')->nullable(); $table->timestamps(); $table->index(['user_id', 'created_at']); });
        Schema::create('login_logs', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('ip_address'); $table->string('user_agent')->nullable(); $table->string('device')->nullable(); $table->string('browser')->nullable(); $table->string('platform')->nullable(); $table->string('location')->nullable(); $table->enum('status', ['success','failed'])->default('success'); $table->timestamps(); });
        Schema::create('webhooks', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('name'); $table->string('url'); $table->string('secret')->nullable(); $table->json('events'); $table->boolean('is_active')->default(true); $table->integer('failure_count')->default(0); $table->timestamp('last_triggered_at')->nullable(); $table->timestamps(); });
        Schema::create('webhook_logs', function (Blueprint $table) { $table->id(); $table->foreignId('webhook_id')->constrained()->cascadeOnDelete(); $table->string('event'); $table->json('payload')->nullable(); $table->integer('response_code')->nullable(); $table->text('response_body')->nullable(); $table->enum('status', ['success','failed'])->default('success'); $table->timestamps(); });
        Schema::create('jobs', function (Blueprint $table) { $table->bigIncrements('id'); $table->string('queue')->index(); $table->longText('payload'); $table->unsignedTinyInteger('attempts'); $table->unsignedInteger('reserved_at')->nullable(); $table->unsignedInteger('available_at'); $table->unsignedInteger('created_at'); });
        Schema::create('failed_jobs', function (Blueprint $table) { $table->id(); $table->string('uuid')->unique(); $table->text('connection'); $table->text('queue'); $table->longText('payload'); $table->longText('exception'); $table->timestamp('failed_at')->useCurrent(); });
        Schema::create('cache', function (Blueprint $table) { $table->string('key')->primary(); $table->mediumText('value'); $table->integer('expiration'); });
        Schema::create('cache_locks', function (Blueprint $table) { $table->string('key')->primary(); $table->string('owner'); $table->integer('expiration'); });
    }
    public function down(): void { Schema::dropIfExists('cache_locks'); Schema::dropIfExists('cache'); Schema::dropIfExists('failed_jobs'); Schema::dropIfExists('jobs'); Schema::dropIfExists('webhook_logs'); Schema::dropIfExists('webhooks'); Schema::dropIfExists('login_logs'); Schema::dropIfExists('activity_logs'); Schema::dropIfExists('settings'); Schema::dropIfExists('notifications'); }
};
