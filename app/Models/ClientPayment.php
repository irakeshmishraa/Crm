<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPayment extends Model
{
    protected $fillable = ['client_id', 'payment_id', 'amount', 'method', 'reference_number', 'payment_date', 'notes', 'status', 'recorded_by'];
    protected $casts = ['amount' => 'decimal:2', 'payment_date' => 'date'];
    public function client() { return $this->belongsTo(Client::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }
}
