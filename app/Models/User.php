<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'phone', 'avatar',
        'designation', 'department_id', 'reporting_to', 'status',
        'google_id', 'google_token', 'google_refresh_token',
        'two_factor_enabled', 'two_factor_secret', 'two_factor_recovery_codes',
        'last_login_at', 'last_login_ip', 'timezone', 'notification_preferences',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'google_token', 'google_refresh_token'];

    protected $casts = [
        'email_verified_at' => 'datetime', 'last_login_at' => 'datetime',
        'password' => 'hashed', 'two_factor_enabled' => 'boolean', 'notification_preferences' => 'array',
    ];

    public function roles() { return $this->belongsToMany(Role::class, 'user_role'); }
    public function department() { return $this->belongsTo(Department::class); }
    public function reportingTo() { return $this->belongsTo(User::class, 'reporting_to'); }
    public function subordinates() { return $this->hasMany(User::class, 'reporting_to'); }
    public function assignedLeads() { return $this->hasMany(Lead::class, 'assigned_to'); }
    public function createdLeads() { return $this->hasMany(Lead::class, 'created_by'); }
    public function followUps() { return $this->hasMany(FollowUp::class, 'assigned_to'); }
    public function tasks() { return $this->hasMany(Task::class, 'assigned_to'); }
    public function emailAccounts() { return $this->hasMany(EmailAccount::class); }
    public function quotations() { return $this->hasMany(Quotation::class, 'created_by'); }
    public function calendarEvents() { return $this->hasMany(CalendarEvent::class); }
    public function loginLogs() { return $this->hasMany(LoginLog::class); }

    public function hasRole(string $role): bool { return $this->roles()->where('slug', $role)->exists(); }
    public function hasAnyRole(array $roles): bool { return $this->roles()->whereIn('slug', $roles)->exists(); }
    public function hasPermission(string $permission): bool {
        return $this->roles()->whereHas('permissions', fn($q) => $q->where('slug', $permission))->exists();
    }
    public function isSuperAdmin(): bool { return $this->hasRole('super-admin'); }
    public function isAdmin(): bool { return $this->hasAnyRole(['super-admin', 'admin']); }
    public function getAvatarUrlAttribute(): string {
        return $this->avatar ? asset('storage/' . $this->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=4f46e5&color=fff';
    }
}
