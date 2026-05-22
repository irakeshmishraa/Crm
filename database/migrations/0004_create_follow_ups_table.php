<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id(); $table->foreignId('lead_id')->constrained()->cascadeOnDelete(); $table->foreignId('assigned_to')->constrained('users'); $table->foreignId('created_by')->constrained('users');
            $table->enum('type', ['call','email','whatsapp','meeting','site_visit','demo','video_call','custom'])->default('call');
            $table->string('title'); $table->text('description')->nullable(); $table->datetime('scheduled_at'); $table->datetime('completed_at')->nullable();
            $table->enum('status', ['pending','completed','missed','cancelled','rescheduled'])->default('pending');
            $table->text('outcome')->nullable(); $table->text('notes')->nullable(); $table->boolean('is_recurring')->default(false); $table->string('recurrence_pattern')->nullable(); $table->integer('recurrence_interval')->nullable(); $table->date('recurrence_end_date')->nullable(); $table->boolean('reminder_sent')->default(false); $table->integer('reminder_minutes_before')->default(30); $table->json('reminder_channels')->nullable(); $table->timestamps(); $table->softDeletes();
            $table->index(['scheduled_at', 'status']); $table->index(['assigned_to', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('follow_ups'); }
};
