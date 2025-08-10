<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'jumlah_pesan',
        'jumlah_tersedia',
        'jumlah_backorder',
        'harga_satuan',
        'total_harga',
        'status',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'total_harga' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function backorder()
    {
        return $this->hasOne(Backorder::class);
    }

    /**
     * Helper methods
     */
    public function calculateTotal()
    {
        $this->total_harga = $this->jumlah_pesan * $this->harga_satuan;
        return $this->total_harga;
    }

    public function isFullyAvailable()
    {
        return $this->jumlah_tersedia >= $this->jumlah_pesan;
    }

    public function hasBackorder()
    {
        return $this->jumlah_backorder > 0;
    }
}
