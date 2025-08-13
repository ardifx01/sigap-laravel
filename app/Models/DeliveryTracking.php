<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'driver_id',
        'latitude',
        'longitude',
        'status',
        'notes',
        'tracked_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'tracked_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Scopes
     */
    public function scopeRecent($query, $minutes = 30)
    {
        return $query->where('tracked_at', '>=', now()->subMinutes($minutes));
    }

    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    /**
     * Helper methods
     */
    public function isRecent($minutes = 5)
    {
        return $this->tracked_at >= now()->subMinutes($minutes);
    }

    public function getDistanceFromPrevious()
    {
        $previous = static::where('delivery_id', $this->delivery_id)
                          ->where('tracked_at', '<', $this->tracked_at)
                          ->orderBy('tracked_at', 'desc')
                          ->first();

        if (!$previous) {
            return 0;
        }

        return $this->calculateDistance(
            $previous->latitude,
            $previous->longitude,
            $this->latitude,
            $this->longitude
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
}
