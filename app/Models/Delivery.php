<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Delivery extends Model implements HasMedia
{
    use HasFactory, LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'order_id',
        'driver_id',
        'assigned_by',
        'rute_kota',
        'assigned_at',
        'k3_checked_at',
        'started_at',
        'delivered_at',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_notes',
        'status',
        'estimated_arrival',
        'actual_distance',
        'delivery_proof_photo',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'delivered_at' => 'datetime',
        'estimated_arrival' => 'datetime',
        'delivery_latitude' => 'decimal:8',
        'delivery_longitude' => 'decimal:8',
        'actual_distance' => 'decimal:2',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['order_id', 'driver_id', 'status', 'assigned_at', 'started_at', 'delivered_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function supir()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function trackingLogs()
    {
        return $this->hasMany(DeliveryTracking::class);
    }

    public function k3Checklist()
    {
        return $this->hasOne(K3Checklist::class);
    }

    /**
     * Scopes
     */
    public function scopeBySupir($query, $supirId)
    {
        return $query->where('driver_id', $supirId);
    }

    public function scopeByDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Helper methods
     */
    public function canBeStarted()
    {
        return $this->status === 'assigned' && $this->k3Checklist && $this->k3Checklist->isAllItemsPassed();
    }

    public function canBeCompleted()
    {
        return $this->status === 'in_progress';
    }

    public function getDistanceFromCustomer()
    {
        if (!$this->delivery_latitude || !$this->delivery_longitude) {
            return null;
        }

        $customer = $this->order->customer;
        if (!$customer->latitude || !$customer->longitude) {
            return null;
        }

        return $this->calculateDistance(
            $this->delivery_latitude,
            $this->delivery_longitude,
            $customer->latitude,
            $customer->longitude
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

    public function isWithinDeliveryRadius($radiusMeters = 100)
    {
        $distance = $this->getDistanceFromCustomer();
        return $distance !== null && $distance <= $radiusMeters;
    }

    public function getDurationInMinutes()
    {
        if (!$this->started_at || !$this->delivered_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->delivered_at);
    }
}
