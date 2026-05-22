<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = ['user_id', 'name', 'subject', 'body', 'category', 'is_shared', 'usage_count'];
    protected $casts = ['is_shared' => 'boolean'];
    public function user() { return $this->belongsTo(User::class); }
}
