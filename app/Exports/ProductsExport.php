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

class ProductsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
            ->when($this->filters['is_active'] !== '', function ($q) {
                $q->where('is_active', $this->filters['is_active']);
            })
            ->when($this->filters['search'] ?? null, function ($q) {
                $q->where(function ($query) {
                    $query->where('nama_barang', 'like', '%' . $this->filters['search'] . '%')
                          ->orWhere('kode_item', 'like', '%' . $this->filters['search'] . '%')
                          ->orWhere('keterangan', 'like', '%' . $this->filters['search'] . '%');
                });
            })
            ->when($this->filters['stock_filter'] ?? null, function ($q) {
                if ($this->filters['stock_filter'] === 'low') {
                    $q->whereRaw('stok_tersedia <= stok_minimum');
                } elseif ($this->filters['stock_filter'] === 'out') {
                    $q->where('stok_tersedia', 0);
                } elseif ($this->filters['stock_filter'] === 'available') {
                    $q->where('stok_tersedia', '>', 0);
                }
            });

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Kode Item',
            'Nama Barang',
            'Keterangan',
            'Jenis',
            'Harga Jual',
            'Stok Tersedia',
            'Stok Minimum',
            'Status',
            'Total Terjual',
            'Tanggal Dibuat',
        ];
    }

    public function map($product): array
    {
        // Get product statistics - assuming relationship exists
        $totalSold = $product->orderItems()->sum('jumlah') ?? 0;

        return [
            $product->kode_item,
            $product->nama_barang,
            $product->keterangan ?? '-',
            ucfirst($product->jenis ?? '-'),
            'Rp ' . number_format($product->harga_jual, 0, ',', '.'),
            $product->stok_tersedia,
            $product->stok_minimum,
            $product->is_active ? 'Aktif' : 'Tidak Aktif',
            $totalSold,
            $product->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:J' => ['alignment' => ['vertical' => 'top']],
        ];
    }
}
