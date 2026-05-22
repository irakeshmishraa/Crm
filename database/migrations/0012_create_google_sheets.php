<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('google_sheet_connections', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('spreadsheet_id'); $table->string('spreadsheet_name'); $table->string('worksheet_name')->nullable(); $table->integer('worksheet_index')->default(0); $table->json('column_mapping')->nullable(); $table->enum('sync_direction', ['import','export','both'])->default('both'); $table->enum('sync_type', ['leads','followups','quotations','clients'])->default('leads'); $table->boolean('auto_sync')->default(false); $table->integer('sync_interval_minutes')->default(30); $table->timestamp('last_synced_at')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps(); });
        Schema::create('google_sheet_sync_logs', function (Blueprint $table) { $table->id(); $table->foreignId('connection_id')->constrained('google_sheet_connections')->cascadeOnDelete(); $table->enum('direction', ['import','export']); $table->integer('records_processed')->default(0); $table->integer('records_created')->default(0); $table->integer('records_updated')->default(0); $table->integer('records_failed')->default(0); $table->enum('status', ['success','partial','failed'])->default('success'); $table->text('error_message')->nullable(); $table->json('errors')->nullable(); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('google_sheet_sync_logs'); Schema::dropIfExists('google_sheet_connections'); }
};
