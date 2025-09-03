<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Payment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'nomor_nota',
        'order_id',
        'sales_id',
        'jumlah_tagihan',
        'jumlah_bayar',
        'jenis_pembayaran',
        'bukti_transfer',
        'status',
        'tanggal_jatuh_tempo',
        'tanggal_bayar',
        'catatan',
    ];

    protected $casts = [
        'jumlah_tagihan' => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_bayar' => 'datetime',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'jumlah_bayar', 'tanggal_bayar', 'jenis_pembayaran'])
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

    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    /**
     * Scopes
     */
    public function scopeBySales($query, $salesId)
    {
        return $query->where('sales_id', $salesId);
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
        return $query->where('status', 'overdue');
    }

    /**
     * Helper methods
     */
    public function generateNotaNumber()
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', now())->count() + 1;
        return 'NOTA-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function isOverdue()
    {
        return $this->status === 'overdue';
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
        return $this->jumlah_bayar > 0 && $this->jumlah_bayar < $this->jumlah_tagihan;
    }

    public function calculateSisaTagihan()
    {
        return $this->jumlah_tagihan - $this->jumlah_bayar;
    }

    public function updatePaymentStatus()
    {
        if ($this->jumlah_bayar >= $this->jumlah_tagihan) {
            $this->status = 'lunas';
        } elseif ($this->jumlah_bayar > 0) {
            $this->status = 'belum_lunas';
        } else {
            $this->status = 'belum_lunas';
        }

        $this->save();
    }

    /**
     * Get payment proof URL
     */
    public function getPaymentProofUrlAttribute()
    {
        return $this->bukti_transfer ? asset('storage/payment_proofs/' . $this->bukti_transfer) : null;
    }

    /**
     * Delete payment proof file when payment is deleted
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($payment) {
            if ($payment->bukti_transfer) {
                \Storage::disk('public')->delete('payment_proofs/' . $payment->bukti_transfer);
            }
        });
    }
}
