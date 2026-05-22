<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = ['webhook_id', 'event', 'payload', 'response_code', 'response_body', 'status'];
    protected $casts = ['payload' => 'array'];
    public function webhook() { return $this->belongsTo(Webhook::class); }
}
