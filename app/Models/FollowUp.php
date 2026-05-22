<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUp extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id', 'assigned_to', 'created_by', 'type', 'title', 'description',
        'scheduled_at', 'completed_at', 'status', 'outcome', 'notes',
        'is_recurring', 'recurrence_pattern', 'recurrence_interval', 'recurrence_end_date',
        'reminder_sent', 'reminder_minutes_before', 'reminder_channels',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime', 'completed_at' => 'datetime', 'recurrence_end_date' => 'date',
        'is_recurring' => 'boolean', 'reminder_sent' => 'boolean', 'reminder_channels' => 'array',
    ];

    public function lead() { return $this->belongsTo(Lead::class); }
    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    public function scopePending($q) { return $q->where('status', 'pending'); }
    public function scopeOverdue($q) { return $q->where('status', 'pending')->where('scheduled_at', '<', now()); }
    public function scopeToday($q) { return $q->whereDate('scheduled_at', today()); }
    public function scopeUpcoming($q) { return $q->where('status', 'pending')->where('scheduled_at', '>=', now())->orderBy('scheduled_at'); }
    public function scopeDueToday($q) { return $q->where('status', 'pending')->whereDate('scheduled_at', today()); }
    public function scopeThisMonth($q) { return $q->whereMonth('scheduled_at', now()->month); }

    public function isOverdue(): bool { return $this->status === 'pending' && $this->scheduled_at->isPast(); }

    public function getStatusBadgeAttribute(): string {
        $b = ['pending'=>'warning','completed'=>'success','missed'=>'danger','cancelled'=>'secondary','rescheduled'=>'info'];
        return $b[$this->status] ?? 'secondary';
    }
    public function getTypeBadgeAttribute(): string {
        $b = ['call'=>'primary','email'=>'info','whatsapp'=>'success','meeting'=>'warning','site_visit'=>'dark','demo'=>'secondary','video_call'=>'primary'];
        return $b[$this->type] ?? 'secondary';
    }
}
