<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    use Exportable;

    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Order::with(['customer', 'sales', 'orderItems.product'])
            ->when($this->filters['status'] ?? null, function ($q) {
                $q->where('status', $this->filters['status']);
            })
            ->when($this->filters['sales_id'] ?? null, function ($q) {
                $q->where('sales_id', $this->filters['sales_id']);
            })
            ->when($this->filters['customer_id'] ?? null, function ($q) {
                $q->where('customer_id', $this->filters['customer_id']);
            })
            ->when($this->filters['date_from'] ?? null, function ($q) {
                $q->whereDate('created_at', '>=', $this->filters['date_from']);
            })
            ->when($this->filters['date_to'] ?? null, function ($q) {
                $q->whereDate('created_at', '<=', $this->filters['date_to']);
            });

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Nomor Order',
            'Tanggal',
            'Sales',
            'Customer/Toko',
            'Telepon Customer',
            'Alamat',
            'Status',
            'Total Items',
            'Total Amount',
            'Catatan',
            'Detail Items',
        ];
    }

    public function map($order): array
    {
        // Format detail items
        $itemsDetail = $order->orderItems->map(function ($item) {
            return $item->product->nama_barang . ' (' . $item->jumlah_pesan . 'x @ Rp ' . number_format($item->harga_satuan, 0, ',', '.') . ')';
        })->join('; ');

        return [
            $order->nomor_order,
            $order->created_at->format('d/m/Y H:i'),
            $order->sales->name ?? '-',
            $order->customer->nama_toko,
            $order->customer->phone,
            $order->customer->alamat,
            ucfirst($order->status),
            $order->orderItems->count(),
            'Rp ' . number_format($order->total_amount, 0, ',', '.'),
            $order->catatan ?? '-',
            $itemsDetail,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:K' => ['alignment' => ['vertical' => 'top']],
        ];
    }
}
