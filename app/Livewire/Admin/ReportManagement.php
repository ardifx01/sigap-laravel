<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Exports\SalesReportExport;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Payment;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportManagement extends Component
{
    public $reportType = 'sales';
    public $startDate;
    public $endDate;
    public $salesFilter = '';
    public $customerFilter = '';
    public $format = 'excel';

    // Report generation status
    public $isGenerating = false;
    public $generationProgress = 0;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function generateReport()
    {
        $this->validate([
            'reportType' => 'required|in:sales,inventory,payments,customers,deliveries',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'format' => 'required|in:excel,pdf'
        ]);

        $this->isGenerating = true;
        $this->generationProgress = 0;

        try {
            $filename = $this->getFilename();

            switch ($this->reportType) {
                case 'sales':
                    return $this->generateSalesReport($filename);
                case 'inventory':
                    return $this->generateInventoryReport($filename);
                case 'payments':
                    return $this->generatePaymentsReport($filename);
                case 'customers':
                    return $this->generateCustomersReport($filename);
                case 'deliveries':
                    return $this->generateDeliveriesReport($filename);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error generating report: ' . $e->getMessage());
        } finally {
            $this->isGenerating = false;
            $this->generationProgress = 0;
        }
    }

    private function generateSalesReport($filename)
    {
        $this->generationProgress = 25;

        if ($this->format === 'excel') {
            $this->generationProgress = 50;
            return Excel::download(
                new SalesReportExport($this->startDate, $this->endDate, $this->salesFilter),
                $filename . '.xlsx'
            );
        } else {
            $this->generationProgress = 50;
            $orders = Order::with(['customer', 'sales', 'orderItems.product'])
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->when($this->salesFilter, function ($query) {
                    $query->where('sales_id', $this->salesFilter);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            $this->generationProgress = 75;
            $pdf = Pdf::loadView('reports.sales-pdf', [
                'orders' => $orders,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'totalAmount' => $orders->where('status', 'delivered')->sum('total_amount'),
                'totalOrders' => $orders->count()
            ]);

            $this->generationProgress = 100;
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename . '.pdf');
        }
    }

    private function generateInventoryReport($filename)
    {
        $this->generationProgress = 25;

        $products = Product::with(['inventoryLogs' => function ($query) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }])->get();

        $this->generationProgress = 50;

        if ($this->format === 'excel') {
            $data = $products->map(function ($product) {
                return [
                    'Kode Item' => $product->kode_item,
                    'Nama Barang' => $product->nama_barang,
                    'Jenis' => $product->jenis,
                    'Stok Tersedia' => $product->stok_tersedia,
                    'Stok Minimum' => $product->stok_minimum,
                    'Harga Jual' => $product->harga_jual,
                    'Status' => $product->stok_tersedia <= $product->stok_minimum ? 'Low Stock' : 'Normal',
                    'Total Value' => $product->stok_tersedia * $product->harga_jual
                ];
            });

            $this->generationProgress = 75;
            return $this->downloadExcel($data, $filename, [
                'Kode Item', 'Nama Barang', 'Jenis', 'Stok Tersedia',
                'Stok Minimum', 'Harga Jual', 'Status', 'Total Value'
            ]);
        } else {
            $this->generationProgress = 75;
            $pdf = Pdf::loadView('reports.inventory-pdf', [
                'products' => $products,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'lowStockCount' => $products->filter(function ($p) { return $p->stok_tersedia <= $p->stok_minimum; })->count(),
                'totalValue' => $products->sum(function ($p) { return $p->stok_tersedia * $p->harga_jual; })
            ]);

            $this->generationProgress = 100;
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename . '.pdf');
        }
    }

    private function generatePaymentsReport($filename)
    {
        $this->generationProgress = 25;

        $payments = Payment::with(['order', 'customer', 'sales'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->when($this->salesFilter, function ($query) {
                $query->where('sales_id', $this->salesFilter);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $this->generationProgress = 50;

        if ($this->format === 'excel') {
            $data = $payments->map(function ($payment) {
                return [
                    'Invoice' => $payment->nomor_invoice,
                    'Order' => $payment->order->nomor_order,
                    'Customer' => $payment->customer->nama_toko,
                    'Sales' => $payment->sales->name,
                    'Jumlah Tagihan' => $payment->jumlah_tagihan,
                    'Jumlah Dibayar' => $payment->jumlah_dibayar,
                    'Sisa Tagihan' => $payment->sisa_tagihan,
                    'Status' => ucfirst(str_replace('_', ' ', $payment->status)),
                    'Jatuh Tempo' => $payment->tanggal_jatuh_tempo->format('d/m/Y'),
                    'Tanggal Bayar' => $payment->tanggal_pembayaran ? $payment->tanggal_pembayaran->format('d/m/Y') : '-'
                ];
            });

            $this->generationProgress = 75;
            return $this->downloadExcel($data, $filename, [
                'Invoice', 'Order', 'Customer', 'Sales', 'Jumlah Tagihan',
                'Jumlah Dibayar', 'Sisa Tagihan', 'Status', 'Jatuh Tempo', 'Tanggal Bayar'
            ]);
        } else {
            $this->generationProgress = 75;
            $pdf = Pdf::loadView('reports.payments-pdf', [
                'payments' => $payments,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'totalTagihan' => $payments->sum('jumlah_tagihan'),
                'totalDibayar' => $payments->sum('jumlah_dibayar'),
                'totalOutstanding' => $payments->sum('sisa_tagihan')
            ]);

            $this->generationProgress = 100;
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename . '.pdf');
        }
    }

    private function downloadExcel($data, $filename, $headings)
    {
        return Excel::download(new class($data, $headings) implements
            \Maatwebsite\Excel\Concerns\FromCollection,
            \Maatwebsite\Excel\Concerns\WithHeadings,
            \Maatwebsite\Excel\Concerns\ShouldAutoSize
        {
            private $data;
            private $headings;

            public function __construct($data, $headings)
            {
                $this->data = collect($data);
                $this->headings = $headings;
            }

            public function collection()
            {
                return $this->data;
            }

            public function headings(): array
            {
                return $this->headings;
            }
        }, $filename . '.xlsx');
    }

    private function getFilename()
    {
        $date = now()->format('Y-m-d_H-i-s');
        return "{$this->reportType}_report_{$date}";
    }

    public function render()
    {
        $salesUsers = User::where('role', 'sales')->where('is_active', true)->get();
        $customers = Customer::where('is_active', true)->get();

        return view('livewire.admin.report-management', [
            'salesUsers' => $salesUsers,
            'customers' => $customers
        ]);
    }
}
