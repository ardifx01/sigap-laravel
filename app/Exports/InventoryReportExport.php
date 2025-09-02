<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryReportExport implements WithMultipleSheets
{
    use Exportable;

    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            new OutOfStockSheet($this->filters),
            new LowStockSheet($this->filters),
            new AllProductsSheet($this->filters),
        ];
    }
}

// Sheet for Out of Stock Products
class OutOfStockSheet extends OutOfStockExport
{
    public function title(): string
    {
        return 'Stock Kosong';
    }
}

// Sheet for Low Stock Products
class LowStockSheet extends LowStockExport
{
    public function title(): string
    {
        return 'Stock Rendah';
    }
}

// Sheet for All Products Summary
class AllProductsSheet implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    use Exportable;

    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Semua Produk';
    }

    public function query()
    {
        $query = Product::query()
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
            'Harga Jual',
            'Stok Tersedia',
            'Stok Minimum',
            'Status Stok',
            'Nilai Stok',
            'Tanggal Update',
            'Keterangan'
        ];
    }

    public function map($product): array
    {
        // Determine stock status
        $stockStatus = 'Normal';
        if ($product->stok_tersedia == 0) {
            $stockStatus = 'KOSONG';
        } elseif ($product->stok_tersedia <= $product->stok_minimum) {
            $stockStatus = 'RENDAH';
        }

        // Calculate stock value
        $stockValue = $product->stok_tersedia * $product->harga_jual;

        return [
            $product->kode_item,
            $product->nama_barang,
            ucfirst($product->jenis ?? '-'),
            'Rp ' . number_format($product->harga_jual, 0, ',', '.'),
            $product->stok_tersedia,
            $product->stok_minimum,
            $stockStatus,
            'Rp ' . number_format($stockValue, 0, ',', '.'),
            $product->updated_at->format('d/m/Y H:i'),
            $product->keterangan ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4CAF50']
                ]
            ],
            'A:J' => ['alignment' => ['vertical' => 'top']],
        ];
    }
}
