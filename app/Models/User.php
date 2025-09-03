<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'photo',
        'role',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'role', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */
    public function customers()
    {
        return $this->hasMany(\App\Models\Customer::class, 'sales_id');
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class, 'sales_id');
    }

    public function checkIns()
    {
        return $this->hasMany(\App\Models\CheckIn::class, 'sales_id');
    }

    public function deliveries()
    {
        return $this->hasMany(\App\Models\Delivery::class, 'driver_id');
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class, 'sales_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Helper methods
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isSales()
    {
        return $this->role === 'sales';
    }

    public function isGudang()
    {
        return $this->role === 'gudang';
    }

    public function isSupir()
    {
        return $this->role === 'supir';
    }

    /**
     * Get user photo URL
     */
    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/photos/' . $this->photo) : null;
    }

    /**
     * Delete photo file when user is deleted
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($user) {
            if ($user->photo) {
                \Storage::disk('public')->delete('photos/' . $user->photo);
            }
        });
    }
}
