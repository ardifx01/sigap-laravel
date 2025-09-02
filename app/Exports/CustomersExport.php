<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    use Exportable;

    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Customer::with(['sales'])
            ->when($this->filters['sales_id'] ?? null, function ($q) {
                $q->where('sales_id', $this->filters['sales_id']);
            })
            ->when($this->filters['is_active'] ?? null, function ($q) {
                $q->where('is_active', $this->filters['is_active']);
            })
            ->when($this->filters['search'] ?? null, function ($q) {
                $q->where(function ($query) {
                    $query->where('nama_toko', 'like', '%' . $this->filters['search'] . '%')
                          ->orWhere('phone', 'like', '%' . $this->filters['search'] . '%')
                          ->orWhere('alamat', 'like', '%' . $this->filters['search'] . '%');
                });
            });

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Nama Toko',
            'Telepon',
            'Alamat',
            'Sales',
            'Limit Hari Piutang',
            'Limit Amount Piutang',
            'Koordinat (Lat, Lng)',
            'Status',
            'Tanggal Bergabung',
            'Total Orders',
            'Total Pembayaran',
        ];
    }

    public function map($customer): array
    {
        // Get customer statistics safely
        $totalOrders = 0;
        $totalPayments = 0;
        
        try {
            $totalOrders = $customer->orders()->count();
            $orders = $customer->orders()->with('payments')->get();
            $totalPayments = $orders->sum(function($order) {
                return $order->payments->sum('jumlah_bayar');
            });
        } catch (\Exception $e) {
            // If there's an issue with relationships, use default values
        }

        $coordinates = '';
        if ($customer->latitude && $customer->longitude) {
            $coordinates = $customer->latitude . ', ' . $customer->longitude;
        }

        return [
            $customer->nama_toko,
            $customer->phone,
            $customer->alamat,
            $customer->sales->name ?? '-',
            $customer->limit_hari_piutang . ' hari',
            'Rp ' . number_format($customer->limit_amount_piutang ?? 0, 0, ',', '.'),
            $coordinates ?: '-',
            $customer->is_active ? 'Aktif' : 'Tidak Aktif',
            $customer->created_at->format('d/m/Y H:i'),
            $totalOrders,
            'Rp ' . number_format($totalPayments, 0, ',', '.'),
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
