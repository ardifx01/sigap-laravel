<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Backorder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'product_id',
        'jumlah_backorder',
        'jumlah_terpenuhi',
        'status',
        'expected_date',
        'fulfilled_at',
        'catatan',
    ];

    protected $casts = [
        'expected_date' => 'datetime',
        'fulfilled_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFulfilled($query)
    {
        return $query->where('status', 'fulfilled');
    }

    /**
     * Helper methods
     */
    public function isFullyFulfilled()
    {
        return $this->jumlah_terpenuhi >= $this->jumlah_backorder;
    }

    public function getRemainingQuantity()
    {
        return $this->jumlah_backorder - $this->jumlah_terpenuhi;
    }
}
