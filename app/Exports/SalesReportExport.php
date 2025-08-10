<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Order;
use Carbon\Carbon;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $salesId;

    public function __construct($startDate, $endDate, $salesId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->salesId = $salesId;
    }

    public function collection()
    {
        return Order::with(['customer', 'sales', 'orderItems.product'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->when($this->salesId, function ($query) {
                $query->where('sales_id', $this->salesId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nomor Order',
            'Tanggal',
            'Sales',
            'Customer',
            'Phone',
            'Total Items',
            'Total Amount',
            'Status',
            'Confirmed At',
            'Delivered At'
        ];
    }

    public function map($order): array
    {
        return [
            $order->nomor_order,
            $order->created_at->format('d/m/Y H:i'),
            $order->sales->name,
            $order->customer->nama_toko,
            $order->customer->phone,
            $order->orderItems->sum('jumlah_pesan'),
            $order->total_amount,
            ucfirst(str_replace('_', ' ', $order->status)),
            $order->confirmed_at ? $order->confirmed_at->format('d/m/Y H:i') : '-',
            $order->delivered_at ? $order->delivered_at->format('d/m/Y H:i') : '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
