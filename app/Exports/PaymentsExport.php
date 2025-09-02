<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    use Exportable;

    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Payment::with(['order.customer', 'sales'])
            ->when($this->filters['search'] ?? null, function ($q) {
                $q->where(function ($query) {
                    $query->where('nomor_nota', 'like', '%' . $this->filters['search'] . '%')
                          ->orWhere('catatan', 'like', '%' . $this->filters['search'] . '%')
                          ->orWhereHas('order', function ($orderQuery) {
                              $orderQuery->where('nomor_order', 'like', '%' . $this->filters['search'] . '%')
                                         ->orWhereHas('customer', function ($customerQuery) {
                                             $customerQuery->where('nama_toko', 'like', '%' . $this->filters['search'] . '%');
                                         });
                          });
                });
            })
            ->when($this->filters['status'] ?? null, function ($q) {
                $q->where('status', $this->filters['status']);
            })
            ->when($this->filters['method'] ?? null, function ($q) {
                $q->where('jenis_pembayaran', $this->filters['method']);
            })
            ->when($this->filters['jenis_pembayaran'] ?? null, function ($q) {
                $q->where('jenis_pembayaran', $this->filters['jenis_pembayaran']);
            })
            ->when($this->filters['sales_id'] ?? null, function ($q) {
                $q->where('sales_id', $this->filters['sales_id']);
            })
            ->when($this->filters['customer_id'] ?? null, function ($q) {
                $q->whereHas('order', function ($order) {
                    $order->where('customer_id', $this->filters['customer_id']);
                });
            })
            ->when($this->filters['date_filter'] ?? null, function ($q) {
                $q->whereDate('tanggal_bayar', $this->filters['date_filter']);
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
            'Nomor Nota',
            'Nomor Order',
            'Tanggal Invoice',
            'Sales',
            'Customer/Toko',
            'Telepon Customer',
            'Jumlah Tagihan',
            'Jumlah Bayar',
            'Sisa Tagihan',
            'Jenis Pembayaran',
            'Status',
            'Tanggal Jatuh Tempo',
            'Tanggal Bayar',
            'Catatan',
        ];
    }

    public function map($payment): array
    {
        $sisaTagihan = $payment->jumlah_tagihan - $payment->jumlah_bayar;
        
        // Safely access nested relationships
        $customerName = '-';
        $customerPhone = '-';
        $orderNumber = '-';
        
        if ($payment->order) {
            $orderNumber = $payment->order->nomor_order ?? '-';
            if ($payment->order->customer) {
                $customerName = $payment->order->customer->nama_toko ?? '-';
                $customerPhone = $payment->order->customer->phone ?? '-';
            }
        }

        return [
            $payment->nomor_nota,
            $orderNumber,
            $payment->created_at->format('d/m/Y H:i'),
            $payment->sales->name ?? '-',
            $customerName,
            $customerPhone,
            'Rp ' . number_format($payment->jumlah_tagihan, 0, ',', '.'),
            'Rp ' . number_format($payment->jumlah_bayar, 0, ',', '.'),
            'Rp ' . number_format($sisaTagihan, 0, ',', '.'),
            ucfirst($payment->jenis_pembayaran),
            ucfirst(str_replace('_', ' ', $payment->status)),
            $payment->tanggal_jatuh_tempo ? $payment->tanggal_jatuh_tempo->format('d/m/Y') : '-',
            $payment->tanggal_bayar ? $payment->tanggal_bayar->format('d/m/Y H:i') : '-',
            $payment->catatan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:N' => ['alignment' => ['vertical' => 'top']],
        ];
    }
}
