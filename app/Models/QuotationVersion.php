<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationVersion extends Model
{
    protected $fillable = ['quotation_id', 'version_number', 'snapshot', 'created_by', 'change_notes'];
    protected $casts = ['snapshot' => 'array'];
    public function quotation() { return $this->belongsTo(Quotation::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
