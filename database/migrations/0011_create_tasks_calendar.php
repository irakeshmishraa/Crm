<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) { $table->id(); $table->string('title'); $table->text('description')->nullable(); $table->foreignId('assigned_to')->constrained('users'); $table->foreignId('created_by')->constrained('users'); $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete(); $table->enum('priority', ['low','medium','high','urgent'])->default('medium'); $table->enum('status', ['pending','in_progress','completed','cancelled'])->default('pending'); $table->date('due_date')->nullable(); $table->time('due_time')->nullable(); $table->timestamp('completed_at')->nullable(); $table->json('attachments')->nullable(); $table->timestamps(); $table->softDeletes(); $table->index(['assigned_to', 'status']); $table->index(['due_date', 'status']); });
        Schema::create('task_comments', function (Blueprint $table) { $table->id(); $table->foreignId('task_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained(); $table->text('comment'); $table->timestamps(); });
        Schema::create('calendar_events', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('title'); $table->text('description')->nullable(); $table->datetime('start_at'); $table->datetime('end_at')->nullable(); $table->boolean('all_day')->default(false); $table->string('color')->default('#3788d8'); $table->string('type')->default('event'); $table->unsignedBigInteger('related_id')->nullable(); $table->string('related_type')->nullable(); $table->string('google_event_id')->nullable(); $table->string('location')->nullable(); $table->json('attendees')->nullable(); $table->boolean('reminder_sent')->default(false); $table->timestamps(); $table->index(['user_id', 'start_at']); });
    }
    public function down(): void { Schema::dropIfExists('calendar_events'); Schema::dropIfExists('task_comments'); Schema::dropIfExists('tasks'); }
};
