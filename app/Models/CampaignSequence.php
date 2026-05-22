<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignSequence extends Model
{
    protected $fillable = ['campaign_id', 'step_number', 'delay_days', 'delay_hours', 'subject', 'body', 'type', 'is_active', 'sent_count', 'open_count'];
    protected $casts = ['is_active' => 'boolean'];
    public function campaign() { return $this->belongsTo(Campaign::class); }
}
