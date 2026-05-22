<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'company_name', 'contact_person', 'email', 'phone', 'whatsapp',
        'website', 'industry', 'gst_number', 'pan_number', 'billing_address',
        'billing_city', 'billing_state', 'billing_country', 'billing_pincode',
        'shipping_address', 'shipping_city', 'shipping_state', 'shipping_country',
        'shipping_pincode', 'status', 'tags', 'notes', 'assigned_to',
        'converted_from_lead', 'total_revenue', 'portal_password', 'portal_access',
    ];

    protected $casts = ['tags' => 'array', 'total_revenue' => 'decimal:2', 'portal_access' => 'boolean'];
    protected $hidden = ['portal_password'];

    protected static function boot() {
        parent::boot();
        static::creating(function ($c) {
            if (!$c->client_id) $c->client_id = 'CL-' . date('Ym') . '-' . str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT);
        });
    }

    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function convertedFromLead() { return $this->belongsTo(Lead::class, 'converted_from_lead'); }
    public function contacts() { return $this->hasMany(ClientContact::class); }
    public function documents() { return $this->hasMany(ClientDocument::class); }
    public function payments() { return $this->hasMany(ClientPayment::class); }
    public function quotations() { return $this->hasMany(Quotation::class); }
    public function emails() { return $this->hasMany(Email::class); }
    public function whatsappMessages() { return $this->hasMany(WhatsAppMessage::class); }
    public function tasks() { return $this->hasMany(Task::class); }
    public function scopeActive($q) { return $q->where('status', 'active'); }
}
