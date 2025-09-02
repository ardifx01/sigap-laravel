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

class OutOfStockExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
            ->where('stok_tersedia', '=', 0)
            ->where('is_active', true) // Only active products
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

        return $query->orderBy('kode_item', 'asc');
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
            'Status',
            'Tanggal Terakhir Update',
            'Lama Stock Kosong (Hari)',
            'Total Terjual (Bulan Ini)',
            'Tanggal Dibuat',
        ];
    }

    public function map($product): array
    {
        // Calculate days since stock became zero
        $daysOutOfStock = 0;
        $lastInventoryLog = \App\Models\InventoryLog::where('product_id', $product->id)
            ->where('stock_after', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastInventoryLog) {
            $daysOutOfStock = now()->diffInDays($lastInventoryLog->created_at);
        }

        // Get total sold this month (assuming there's order relationship)
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

        return [
            $product->kode_item,
            $product->nama_barang,
            ucfirst($product->jenis ?? '-'),
            $product->keterangan ?? '-',
            'Rp ' . number_format($product->harga_jual, 0, ',', '.'),
            $product->stok_tersedia,
            $product->stok_minimum,
            $product->is_active ? 'Aktif' : 'Tidak Aktif',
            $product->updated_at->format('d/m/Y H:i'),
            $daysOutOfStock . ' hari',
            $totalSoldThisMonth,
            $product->created_at->format('d/m/Y H:i'),
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
            // Highlight urgent items (high minimum stock)
            'F:G' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFFCCCC']
                ]
            ],
        ];
    }
}
