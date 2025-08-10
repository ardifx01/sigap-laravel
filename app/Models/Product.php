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
        'jenis',
        'harga_jual',
        'stok_tersedia',
        'stok_minimum',
        'foto_produk',
        'is_active',
    ];

    protected $casts = [
        'harga_jual' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['kode_item', 'nama_barang', 'jenis', 'harga_jual', 'stok_tersedia', 'stok_minimum', 'is_active'])
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
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stok_tersedia', '<=', 'stok_minimum');
    }

    /**
     * Helper methods
     */
    public function isLowStock()
    {
        return $this->stok_tersedia <= $this->stok_minimum;
    }

    public function isOutOfStock()
    {
        return $this->stok_tersedia <= 0;
    }
}
