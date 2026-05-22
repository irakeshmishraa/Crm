<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'assigned_to', 'created_by', 'lead_id', 'client_id',
        'priority', 'status', 'due_date', 'due_time', 'completed_at', 'attachments',
    ];

    protected $casts = ['due_date' => 'date', 'completed_at' => 'datetime', 'attachments' => 'array'];

    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function lead() { return $this->belongsTo(Lead::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function comments() { return $this->hasMany(TaskComment::class); }
    public function scopePending($q) { return $q->where('status', 'pending'); }
    public function scopeOverdue($q) { return $q->where('status', '!=', 'completed')->where('due_date', '<', today()); }
}
