<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class K3Checklist extends Model implements HasMedia
{
    use HasFactory, LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'supir_id',
        'delivery_id',
        'tanggal_checklist',
        'kondisi_ban',
        'kondisi_rem',
        'level_oli_mesin',
        'level_bbm',
        'kondisi_lampu',
        'kondisi_spion',
        'kondisi_klakson',
        'kondisi_sabuk_pengaman',
        'kondisi_kaca',
        'kondisi_wiper',
        'kelengkapan_p3k',
        'kelengkapan_apar',
        'kelengkapan_segitiga',
        'kondisi_muatan',
        'catatan_tambahan',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal_checklist' => 'datetime',
        'approved_at' => 'datetime',
        'kondisi_ban' => 'boolean',
        'kondisi_rem' => 'boolean',
        'level_oli_mesin' => 'boolean',
        'level_bbm' => 'boolean',
        'kondisi_lampu' => 'boolean',
        'kondisi_spion' => 'boolean',
        'kondisi_klakson' => 'boolean',
        'kondisi_sabuk_pengaman' => 'boolean',
        'kondisi_kaca' => 'boolean',
        'kondisi_wiper' => 'boolean',
        'kelengkapan_p3k' => 'boolean',
        'kelengkapan_apar' => 'boolean',
        'kelengkapan_segitiga' => 'boolean',
        'kondisi_muatan' => 'boolean',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['supir_id', 'status', 'approved_by', 'approved_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */
    public function supir()
    {
        return $this->belongsTo(User::class, 'supir_id');
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scopes
     */
    public function scopeBySupir($query, $supirId)
    {
        return $query->where('supir_id', $supirId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('tanggal_checklist', today());
    }

    /**
     * Helper methods
     */
    public function getChecklistItems()
    {
        return [
            'kondisi_ban' => 'Kondisi Ban',
            'kondisi_rem' => 'Kondisi Rem',
            'level_oli_mesin' => 'Level Oli Mesin',
            'level_bbm' => 'Level BBM',
            'kondisi_lampu' => 'Kondisi Lampu',
            'kondisi_spion' => 'Kondisi Spion',
            'kondisi_klakson' => 'Kondisi Klakson',
            'kondisi_sabuk_pengaman' => 'Sabuk Pengaman',
            'kondisi_kaca' => 'Kondisi Kaca',
            'kondisi_wiper' => 'Kondisi Wiper',
            'kelengkapan_p3k' => 'Kelengkapan P3K',
            'kelengkapan_apar' => 'Kelengkapan APAR',
            'kelengkapan_segitiga' => 'Segitiga Pengaman',
            'kondisi_muatan' => 'Kondisi Muatan',
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

    public function canBeApproved()
    {
        return $this->status === 'pending' && $this->getCompletionPercentage() >= 80;
    }
}
