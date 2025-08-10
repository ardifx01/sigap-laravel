<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CheckIn extends Model implements HasMedia
{
    use HasFactory, LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'sales_id',
        'customer_id',
        'latitude',
        'longitude',
        'foto_selfie',
        'catatan',
        'checked_in_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'checked_in_at' => 'datetime',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['sales_id', 'customer_id', 'latitude', 'longitude', 'checked_in_at'])
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scopes
     */
    public function scopeBySales($query, $salesId)
    {
        return $query->where('sales_id', $salesId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('checked_in_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('checked_in_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Helper methods
     */
    public function getDistanceFromCustomer()
    {
        if (!$this->customer->latitude || !$this->customer->longitude) {
            return null;
        }

        return $this->calculateDistance(
            $this->latitude,
            $this->longitude,
            $this->customer->latitude,
            $this->customer->longitude
        );
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c; // Distance in meters
    }

    public function isWithinRadius($radiusMeters = 100)
    {
        $distance = $this->getDistanceFromCustomer();
        return $distance !== null && $distance <= $radiusMeters;
    }
}
