<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = ['user_id', 'title', 'description', 'start_at', 'end_at', 'all_day', 'color', 'type', 'related_id', 'related_type', 'google_event_id', 'location', 'attendees', 'reminder_sent'];
    protected $casts = ['start_at' => 'datetime', 'end_at' => 'datetime', 'all_day' => 'boolean', 'attendees' => 'array', 'reminder_sent' => 'boolean'];
    public function user() { return $this->belongsTo(User::class); }
}
