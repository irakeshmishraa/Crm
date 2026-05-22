<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'parent_id', 'is_active', 'sort_order'];
    protected $casts = ['is_active' => 'boolean'];
    public function products() { return $this->hasMany(Product::class, 'category_id'); }
    public function parent() { return $this->belongsTo(ProductCategory::class, 'parent_id'); }
    public function children() { return $this->hasMany(ProductCategory::class, 'parent_id'); }
}
