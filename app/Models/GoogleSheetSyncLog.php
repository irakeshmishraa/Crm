<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleSheetSyncLog extends Model
{
    protected $fillable = ['connection_id', 'direction', 'records_processed', 'records_created', 'records_updated', 'records_failed', 'status', 'error_message', 'errors'];
    protected $casts = ['errors' => 'array'];
    public function connection() { return $this->belongsTo(GoogleSheetConnection::class, 'connection_id'); }
}
