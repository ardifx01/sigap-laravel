<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Payment extends Model implements HasMedia
{
    use HasFactory, LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'order_id',
        'customer_id',
        'sales_id',
        'nomor_invoice',
        'jumlah_tagihan',
        'jumlah_dibayar',
        'sisa_tagihan',
        'metode_pembayaran',
        'tanggal_jatuh_tempo',
        'tanggal_pembayaran',
        'status',
        'catatan',
        'verified_by',
        'verified_at',
        'bukti_transfer',
    ];

    protected $casts = [
        'jumlah_tagihan' => 'decimal:2',
        'jumlah_dibayar' => 'decimal:2',
        'sisa_tagihan' => 'decimal:2',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_pembayaran' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'jumlah_dibayar', 'tanggal_pembayaran', 'verified_by', 'verified_at'])
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scopes
     */
    public function scopeBySales($query, $salesId)
    {
        return $query->where('sales_id', $salesId);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'belum_lunas');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'lunas');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'belum_lunas')
                    ->where('tanggal_jatuh_tempo', '<', now());
    }

    /**
     * Helper methods
     */
    public function generateInvoiceNumber()
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', now())->count() + 1;
        return 'INV-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function isOverdue()
    {
        return $this->status === 'belum_lunas' && $this->tanggal_jatuh_tempo < now();
    }

    public function getDaysOverdue()
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return now()->diffInDays($this->tanggal_jatuh_tempo);
    }

    public function isFullyPaid()
    {
        return $this->status === 'lunas';
    }

    public function isPartiallyPaid()
    {
        return $this->jumlah_dibayar > 0 && $this->jumlah_dibayar < $this->jumlah_tagihan;
    }

    public function calculateSisaTagihan()
    {
        return $this->jumlah_tagihan - $this->jumlah_dibayar;
    }

    public function updatePaymentStatus()
    {
        $this->sisa_tagihan = $this->calculateSisaTagihan();

        if ($this->sisa_tagihan <= 0) {
            $this->status = 'lunas';
        } elseif ($this->jumlah_dibayar > 0) {
            $this->status = 'sebagian';
        } else {
            $this->status = 'belum_lunas';
        }

        $this->save();
    }
}
