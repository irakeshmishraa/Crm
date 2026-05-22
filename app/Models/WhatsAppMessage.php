<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppMessage extends Model
{
    protected $table = 'whatsapp_messages';
    protected $fillable = [
        'lead_id', 'client_id', 'sent_by', 'whatsapp_message_id', 'from_number',
        'to_number', 'direction', 'type', 'content', 'template_name',
        'template_params', 'media_url', 'status', 'delivered_at', 'read_at', 'error_message',
    ];
    protected $casts = ['template_params' => 'array', 'delivered_at' => 'datetime', 'read_at' => 'datetime'];
    public function lead() { return $this->belongsTo(Lead::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function sentBy() { return $this->belongsTo(User::class, 'sent_by'); }
}
