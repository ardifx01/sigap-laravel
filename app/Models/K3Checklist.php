<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class K3Checklist extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'driver_id',
        'delivery_id',
        'cek_ban',
        'cek_oli',
        'cek_air_radiator',
        'cek_rem',
        'cek_bbm',
        'cek_terpal',
        'catatan',
        'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'cek_ban' => 'boolean',
        'cek_oli' => 'boolean',
        'cek_air_radiator' => 'boolean',
        'cek_rem' => 'boolean',
        'cek_bbm' => 'boolean',
        'cek_terpal' => 'boolean',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['driver_id', 'delivery_id', 'checked_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */
    public function supir()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
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

    public function scopeToday($query)
    {
        return $query->whereDate('checked_at', today());
    }

    /**
     * Helper methods
     */
    public function getChecklistItems()
    {
        return [
            'cek_ban' => 'Kondisi Ban',
            'cek_oli' => 'Kondisi Oli',
            'cek_air_radiator' => 'Air Radiator',
            'cek_rem' => 'Kondisi Rem',
            'cek_bbm' => 'Level BBM',
            'cek_terpal' => 'Kondisi Terpal',
        ];
    }

    public function getPassedItemsCount()
    {
        $items = $this->getChecklistItems();
        $passed = 0;

        foreach (array_keys($items) as $item) {
            if ($this->$item) {
                $passed++;
            }
        }

        return $passed;
    }

    public function getTotalItemsCount()
    {
        return count($this->getChecklistItems());
    }

    public function getCompletionPercentage()
    {
        return round(($this->getPassedItemsCount() / $this->getTotalItemsCount()) * 100);
    }

    public function isAllItemsPassed()
    {
        return $this->getPassedItemsCount() === $this->getTotalItemsCount();
    }

    /**
     * Get vehicle photo URL
     */
    public function getVehiclePhotoUrlAttribute()
    {
        return $this->vehicle_photo ? asset('storage/vehicle_photos/' . $this->vehicle_photo) : null;
    }

    /**
     * Delete vehicle photo file when K3 checklist is deleted
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($k3checklist) {
            if ($k3checklist->vehicle_photo) {
                \Storage::disk('public')->delete('vehicle_photos/' . $k3checklist->vehicle_photo);
            }
        });
    }
}
