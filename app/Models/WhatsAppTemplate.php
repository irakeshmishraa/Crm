<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppTemplate extends Model
{
    protected $table = 'whatsapp_templates';
    protected $fillable = ['name', 'template_id', 'category', 'language', 'content', 'variables', 'status', 'is_active'];
    protected $casts = ['variables' => 'array', 'is_active' => 'boolean'];
}
