<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id', 'name', 'email', 'phone', 'whatsapp_number', 'alternate_number',
        'company_name', 'designation', 'website', 'industry', 'address', 'city',
        'state', 'country', 'pincode', 'budget', 'requirement', 'notes', 'tags',
        'source', 'campaign_source', 'status', 'priority', 'score', 'assigned_to',
        'created_by', 'pipeline_stage', 'deal_value', 'win_probability',
        'expected_close_date', 'last_contacted_at', 'converted_at', 'converted_client_id',
    ];

    protected $casts = [
        'tags' => 'array', 'budget' => 'decimal:2', 'deal_value' => 'decimal:2',
        'expected_close_date' => 'date', 'last_contacted_at' => 'datetime', 'converted_at' => 'datetime',
    ];

    protected static function boot() {
        parent::boot();
        static::creating(function ($lead) {
            if (!$lead->lead_id) {
                $lead->lead_id = 'LD-' . date('Ym') . '-' . str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function followUps() { return $this->hasMany(FollowUp::class); }
    public function activities() { return $this->hasMany(LeadActivity::class)->orderByDesc('created_at'); }
    public function documents() { return $this->hasMany(LeadDocument::class); }
    public function notes() { return $this->hasMany(LeadNote::class); }
    public function quotations() { return $this->hasMany(Quotation::class); }
    public function emails() { return $this->hasMany(Email::class); }
    public function whatsappMessages() { return $this->hasMany(WhatsAppMessage::class); }
    public function tasks() { return $this->hasMany(Task::class); }
    public function client() { return $this->belongsTo(Client::class, 'converted_client_id'); }

    public function scopeStatus($q, $s) { return $q->where('status', $s); }
    public function scopeAssignedTo($q, $id) { return $q->where('assigned_to', $id); }
    public function scopeNew($q) { return $q->where('status', 'new'); }
    public function scopeWon($q) { return $q->where('status', 'won'); }
    public function scopeLost($q) { return $q->where('status', 'lost'); }
    public function scopeToday($q) { return $q->whereDate('created_at', today()); }
    public function scopeThisWeek($q) { return $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]); }
    public function scopeThisMonth($q) { return $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year); }

    public function getStatusBadgeAttribute(): string {
        $b = ['new'=>'primary','contacted'=>'info','interested'=>'success','follow_up'=>'warning','proposal_sent'=>'secondary','negotiation'=>'dark','won'=>'success','lost'=>'danger','not_interested'=>'secondary','duplicate'=>'light'];
        return $b[$this->status] ?? 'secondary';
    }
    public function getPriorityBadgeAttribute(): string {
        $b = ['low'=>'secondary','medium'=>'info','high'=>'warning','urgent'=>'danger'];
        return $b[$this->priority] ?? 'secondary';
    }
}
