<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleSheetConnection extends Model
{
    protected $fillable = ['user_id', 'spreadsheet_id', 'spreadsheet_name', 'worksheet_name', 'worksheet_index', 'column_mapping', 'sync_direction', 'sync_type', 'auto_sync', 'sync_interval_minutes', 'last_synced_at', 'is_active'];
    protected $casts = ['column_mapping' => 'array', 'auto_sync' => 'boolean', 'is_active' => 'boolean', 'last_synced_at' => 'datetime'];
    public function user() { return $this->belongsTo(User::class); }
    public function syncLogs() { return $this->hasMany(GoogleSheetSyncLog::class, 'connection_id'); }
}
