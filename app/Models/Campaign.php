<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'created_by', 'name', 'description', 'type', 'status', 'email_account_id',
        'recipient_filters', 'total_recipients', 'sent_count', 'open_count',
        'click_count', 'reply_count', 'bounce_count', 'unsubscribe_count',
        'stop_on_reply', 'scheduled_at', 'started_at', 'completed_at',
    ];

    protected $casts = ['recipient_filters' => 'array', 'stop_on_reply' => 'boolean', 'scheduled_at' => 'datetime', 'started_at' => 'datetime', 'completed_at' => 'datetime'];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function emailAccount() { return $this->belongsTo(EmailAccount::class); }
    public function sequences() { return $this->hasMany(CampaignSequence::class)->orderBy('step_number'); }
    public function recipients() { return $this->hasMany(CampaignRecipient::class); }
    public function getOpenRateAttribute(): float { return $this->sent_count > 0 ? round(($this->open_count / $this->sent_count) * 100, 1) : 0; }
    public function getClickRateAttribute(): float { return $this->sent_count > 0 ? round(($this->click_count / $this->sent_count) * 100, 1) : 0; }
}
