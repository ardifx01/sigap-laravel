<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'kode_item',
        'nama_barang',
        'keterangan',
        'jenis', // Keep for backward compatibility
        'harga_jual', // Keep for backward compatibility  
        'stok_tersedia', // Keep for backward compatibility
        'stok_minimum', // Keep for backward compatibility
        'foto_produk',
        'is_active',
        'uses_multiple_units', // New field to indicate if product uses multiple units
    ];

    protected $casts = [
        'harga_jual' => 'decimal:2',
        'is_active' => 'boolean',
        'uses_multiple_units' => 'boolean',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['kode_item', 'nama_barang', 'jenis', 'harga_jual', 'stok_tersedia', 'stok_minimum', 'is_active', 'uses_multiple_units'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function backorders()
    {
        return $this->hasMany(Backorder::class);
    }

    /**
     * Product Units Relationship
     */
    public function units()
    {
        return $this->hasMany(ProductUnit::class)->orderedBySortOrder();
    }

    public function activeUnits()
    {
        return $this->hasMany(ProductUnit::class)->active()->orderedBySortOrder();
    }

    public function baseUnit()
    {
        return $this->hasOne(ProductUnit::class)->where('is_base_unit', true);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->where(function($q) {
            $q->whereColumn('stok_tersedia', '<=', 'stok_minimum')
              ->orWhereHas('units', function($unitQuery) {
                  $unitQuery->whereColumn('stock_available', '<=', 'stock_minimum');
              });
        });
    }

    public function scopeUsingMultipleUnits($query)
    {
        return $query->where('uses_multiple_units', true);
    }

    public function scopeUsingSingleUnit($query)
    {
        return $query->where('uses_multiple_units', false);
    }

    /**
     * Helper methods
     */
    public function isLowStock()
    {
        if ($this->uses_multiple_units) {
            return $this->units->where('is_active', true)->some(function($unit) {
                return $unit->isLowStock();
            });
        }
        
        return $this->stok_tersedia <= $this->stok_minimum;
    }

    public function isOutOfStock()
    {
        if ($this->uses_multiple_units) {
            return $this->units->where('is_active', true)->every(function($unit) {
                return $unit->isOutOfStock();
            });
        }
        
        return $this->stok_tersedia <= 0;
    }

    /**
     * Get primary unit for display (base unit or first active unit)
     */
    public function getPrimaryUnit()
    {
        if ($this->uses_multiple_units) {
            return $this->baseUnit ?? $this->activeUnits->first();
        }
        
        return null;
    }

    /**
     * Get all available units for selection
     */
    public function getAvailableUnits()
    {
        if ($this->uses_multiple_units) {
            return $this->activeUnits;
        }
        
        // Return legacy unit format for backward compatibility
        return collect([
            (object)[
                'id' => 'legacy',
                'unit_name' => ucfirst($this->jenis ?? 'Pcs'),
                'unit_code' => strtoupper(substr($this->jenis ?? 'pcs', 0, 3)),
                'price_per_unit' => $this->harga_jual,
                'stock_available' => $this->stok_tersedia,
                'stock_minimum' => $this->stok_minimum,
                'is_base_unit' => true,
                'formatted_unit' => ucfirst($this->jenis ?? 'Pcs') . ' (' . strtoupper(substr($this->jenis ?? 'pcs', 0, 3)) . ')'
            ]
        ]);
    }

    /**
     * Convert to multiple units system
     */
    public function convertToMultipleUnits()
    {
        if ($this->uses_multiple_units) {
            return; // Already using multiple units
        }

        // Create base unit from existing data
        $this->units()->create([
            'unit_name' => ucfirst($this->jenis ?? 'Pcs'),
            'unit_code' => strtoupper(substr($this->jenis ?? 'pcs', 0, 3)),
            'conversion_value' => 1,
            'price_per_unit' => $this->harga_jual,
            'stock_available' => $this->stok_tersedia,
            'stock_minimum' => $this->stok_minimum,
            'is_base_unit' => true,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->update(['uses_multiple_units' => true]);
    }
}
