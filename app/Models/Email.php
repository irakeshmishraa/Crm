<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    protected $fillable = [
        'email_account_id', 'lead_id', 'client_id', 'message_id', 'thread_id',
        'from_email', 'from_name', 'to_emails', 'cc_emails', 'bcc_emails',
        'subject', 'body_html', 'body_text', 'direction', 'status',
        'open_count', 'click_count', 'opened_at', 'clicked_at', 'sent_at', 'has_attachments', 'attachments',
    ];
    protected $casts = ['to_emails' => 'array', 'cc_emails' => 'array', 'bcc_emails' => 'array', 'attachments' => 'array', 'has_attachments' => 'boolean', 'opened_at' => 'datetime', 'sent_at' => 'datetime'];
    public function emailAccount() { return $this->belongsTo(EmailAccount::class); }
    public function lead() { return $this->belongsTo(Lead::class); }
    public function client() { return $this->belongsTo(Client::class); }
}
