<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'description', 'head_id', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function head() { return $this->belongsTo(User::class, 'head_id'); }
    public function users() { return $this->hasMany(User::class); }
}
