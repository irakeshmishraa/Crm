<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailAccount extends Model
{
    protected $fillable = ['user_id', 'email_address', 'provider', 'access_token', 'refresh_token', 'token_expires_at', 'is_active', 'is_primary', 'settings'];
    protected $casts = ['token_expires_at' => 'datetime', 'is_active' => 'boolean', 'is_primary' => 'boolean', 'settings' => 'array'];
    protected $hidden = ['access_token', 'refresh_token'];
    public function user() { return $this->belongsTo(User::class); }
    public function emails() { return $this->hasMany(Email::class); }
    public function isTokenExpired(): bool { return $this->token_expires_at && $this->token_expires_at->isPast(); }
}
