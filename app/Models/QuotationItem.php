<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    protected $fillable = ['quotation_id', 'product_id', 'item_name', 'description', 'quantity', 'unit', 'rate', 'discount', 'discount_type', 'tax_percentage', 'tax_amount', 'line_total', 'sort_order'];
    protected $casts = ['quantity' => 'decimal:2', 'rate' => 'decimal:2', 'tax_amount' => 'decimal:2', 'line_total' => 'decimal:2'];
    public function quotation() { return $this->belongsTo(Quotation::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function calculateLineTotal(): void {
        $amount = $this->quantity * $this->rate;
        $discountAmt = $this->discount_type === 'percentage' ? ($amount * $this->discount / 100) : ($this->discount ?? 0);
        $taxable = $amount - $discountAmt;
        $this->tax_amount = $taxable * ($this->tax_percentage / 100);
        $this->line_total = $taxable + $this->tax_amount;
    }
}
