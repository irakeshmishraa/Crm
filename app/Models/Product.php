<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id', 'name', 'slug', 'category_id', 'sku', 'hsn_sac_code',
        'description', 'unit', 'cost_price', 'selling_price', 'tax_percentage',
        'tax_type', 'image', 'type', 'is_active', 'stock_quantity',
    ];

    protected $casts = ['cost_price' => 'decimal:2', 'selling_price' => 'decimal:2', 'tax_percentage' => 'decimal:2', 'is_active' => 'boolean'];

    protected static function boot() {
        parent::boot();
        static::creating(function ($p) {
            if (!$p->product_id) $p->product_id = 'PRD-' . str_pad(static::count() + 1, 5, '0', STR_PAD_LEFT);
            if (!$p->slug) $p->slug = \Str::slug($p->name) . '-' . uniqid();
        });
    }

    public function category() { return $this->belongsTo(ProductCategory::class, 'category_id'); }
    public function scopeActive($q) { return $q->where('is_active', true); }
}
