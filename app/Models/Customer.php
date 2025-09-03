<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Customer extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'sales_id',
        'nama_toko',
        'phone',
        'alamat',
        'foto_ktp',
        'limit_hari_piutang',
        'limit_amount_piutang',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'limit_amount_piutang' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama_toko', 'phone', 'alamat', 'limit_hari_piutang', 'limit_amount_piutang', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */
    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function checkIns()
    {
        return $this->hasMany(CheckIn::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySales($query, $salesId)
    {
        return $query->where('sales_id', $salesId);
    }

    /**
     * Get KTP photo URL
     */
    public function getKtpPhotoUrlAttribute()
    {
        return $this->foto_ktp ? asset('storage/ktp_photos/' . $this->foto_ktp) : null;
    }

    /**
     * Delete KTP photo file when customer is deleted
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($customer) {
            if ($customer->foto_ktp) {
                \Storage::disk('public')->delete('ktp_photos/' . $customer->foto_ktp);
            }
        });
    }
}
