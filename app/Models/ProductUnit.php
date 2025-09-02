<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProductUnit extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_id',
        'unit_name',
        'unit_code',
        'conversion_value',
        'price_per_unit',
        'stock_available',
        'stock_minimum',
        'is_base_unit',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'conversion_value' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'is_base_unit' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['unit_name', 'unit_code', 'conversion_value', 'price_per_unit', 'stock_available', 'stock_minimum', 'is_base_unit', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBaseUnit($query)
    {
        return $query->where('is_base_unit', true);
    }

    public function scopeOrderedBySortOrder($query)
    {
        return $query->orderBy('sort_order')->orderBy('unit_name');
    }

    /**
     * Helper methods
     */
    public function isLowStock()
    {
        return $this->stock_available <= $this->stock_minimum;
    }

    public function isOutOfStock()
    {
        return $this->stock_available <= 0;
    }

    /**
     * Convert stock from this unit to base unit
     */
    public function convertToBaseUnit($quantity)
    {
        return $quantity * $this->conversion_value;
    }

    /**
     * Convert stock from base unit to this unit
     */
    public function convertFromBaseUnit($baseQuantity)
    {
        return $this->conversion_value > 0 ? $baseQuantity / $this->conversion_value : 0;
    }

    /**
     * Get formatted unit name with code
     */
    public function getFormattedUnitAttribute()
    {
        return $this->unit_name . ' (' . $this->unit_code . ')';
    }
}
