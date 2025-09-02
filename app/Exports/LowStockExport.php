<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LowStockExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    use Exportable;

    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Product::query()
            ->whereRaw('stok_tersedia <= stok_minimum')
            ->where('stok_tersedia', '>', 0) // Not completely out of stock
            ->where('is_active', true)
            ->when($this->filters['search'] ?? null, function ($q) {
                $q->where(function ($query) {
                    $query->where('nama_barang', 'like', '%' . $this->filters['search'] . '%')
                          ->orWhere('kode_item', 'like', '%' . $this->filters['search'] . '%')
                          ->orWhere('keterangan', 'like', '%' . $this->filters['search'] . '%');
                });
            })
            ->when($this->filters['jenis'] ?? null, function ($q) {
                $q->where('jenis', $this->filters['jenis']);
            });

        return $query->orderBy('stok_tersedia', 'asc');
    }

    public function headings(): array
    {
        return [
            'Kode Item',
            'Nama Barang',
            'Jenis',
            'Keterangan',
            'Harga Jual',
            'Stok Tersedia',
            'Stok Minimum',
            'Selisih Stock',
            'Status Prioritas',
            'Tanggal Terakhir Update',
            'Total Terjual (Bulan Ini)',
            'Perkiraan Habis (Hari)',
        ];
    }

    public function map($product): array
    {
        // Calculate stock difference
        $stockDiff = $product->stok_minimum - $product->stok_tersedia;
        
        // Determine priority
        $priority = 'Normal';
        if ($product->stok_tersedia <= $product->stok_minimum * 0.5) {
            $priority = 'URGENT';
        } elseif ($product->stok_tersedia <= $product->stok_minimum * 0.8) {
            $priority = 'Tinggi';
        }

        // Get total sold this month
        $totalSoldThisMonth = 0;
        try {
            $totalSoldThisMonth = $product->orderItems()
                ->whereHas('order', function($query) {
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year)
                          ->whereIn('status', ['confirmed', 'shipped', 'delivered']);
                })
                ->sum('jumlah') ?? 0;
        } catch (\Exception $e) {
            // If relationship doesn't exist, use 0
        }

        // Calculate estimated days until out of stock
        $estimatedDays = '-';
        if ($totalSoldThisMonth > 0) {
            $dailyAverage = $totalSoldThisMonth / now()->day;
            if ($dailyAverage > 0) {
                $estimatedDays = ceil($product->stok_tersedia / $dailyAverage) . ' hari';
            }
        }

        return [
            $product->kode_item,
            $product->nama_barang,
            ucfirst($product->jenis ?? '-'),
            $product->keterangan ?? '-',
            'Rp ' . number_format($product->harga_jual, 0, ',', '.'),
            $product->stok_tersedia,
            $product->stok_minimum,
            $stockDiff,
            $priority,
            $product->updated_at->format('d/m/Y H:i'),
            $totalSoldThisMonth,
            $estimatedDays,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE6E6E6']
                ]
            ],
            'A:L' => ['alignment' => ['vertical' => 'top']],
            // Highlight stock columns
            'F:H' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFFFF99']
                ]
            ],
        ];
    }
}
