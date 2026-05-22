<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $fillable = ['user_id', 'name', 'url', 'secret', 'events', 'is_active', 'failure_count', 'last_triggered_at'];
    protected $casts = ['events' => 'array', 'is_active' => 'boolean', 'last_triggered_at' => 'datetime'];
    public function user() { return $this->belongsTo(User::class); }
    public function logs() { return $this->hasMany(WebhookLog::class); }
}
