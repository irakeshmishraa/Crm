<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quotation_number', 'reference_number', 'lead_id', 'client_id', 'created_by', 'assigned_to',
        'client_name', 'client_email', 'client_phone', 'client_company', 'client_gst',
        'billing_address', 'shipping_address', 'quotation_date', 'valid_until',
        'subject', 'introduction', 'terms_conditions', 'notes',
        'subtotal', 'discount_amount', 'discount_type', 'tax_amount',
        'cgst_amount', 'sgst_amount', 'igst_amount', 'shipping_charges',
        'additional_charges', 'additional_charges_label', 'grand_total',
        'status', 'approval_token', 'sent_at', 'viewed_at', 'accepted_at', 'rejected_at',
        'rejection_reason', 'accepted_by_name', 'digital_signature', 'template', 'version',
    ];

    protected $casts = [
        'quotation_date' => 'date', 'valid_until' => 'date', 'sent_at' => 'datetime',
        'viewed_at' => 'datetime', 'accepted_at' => 'datetime', 'rejected_at' => 'datetime',
        'subtotal' => 'decimal:2', 'grand_total' => 'decimal:2', 'tax_amount' => 'decimal:2',
    ];

    protected static function boot() {
        parent::boot();
        static::creating(function ($q) {
            if (!$q->quotation_number) {
                $prefix = config('crm.quotation_prefix', 'QT');
                $count = static::whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->count() + 1;
                $q->quotation_number = "{$prefix}-" . date('Y-m') . "-" . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
            if (!$q->approval_token) $q->approval_token = bin2hex(random_bytes(32));
        });
    }

    public function lead() { return $this->belongsTo(Lead::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function items() { return $this->hasMany(QuotationItem::class)->orderBy('sort_order'); }
    public function versions() { return $this->hasMany(QuotationVersion::class)->orderByDesc('version_number'); }

    public function scopeStatus($q, $s) { return $q->where('status', $s); }
    public function scopeThisMonth($q) { return $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year); }
    public function isExpired(): bool { return $this->valid_until && $this->valid_until->isPast() && !in_array($this->status, ['accepted', 'converted']); }

    public function getStatusBadgeAttribute(): string {
        $b = ['draft'=>'secondary','sent'=>'primary','viewed'=>'info','accepted'=>'success','rejected'=>'danger','expired'=>'warning','converted'=>'dark'];
        return $b[$this->status] ?? 'secondary';
    }
}
