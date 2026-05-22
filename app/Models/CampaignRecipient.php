<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignRecipient extends Model
{
    protected $fillable = ['campaign_id', 'lead_id', 'current_step', 'status', 'last_sent_at', 'next_send_at'];
    protected $casts = ['last_sent_at' => 'datetime', 'next_send_at' => 'datetime'];
    public function campaign() { return $this->belongsTo(Campaign::class); }
    public function lead() { return $this->belongsTo(Lead::class); }
}
