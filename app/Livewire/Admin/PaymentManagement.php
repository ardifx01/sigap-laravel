<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Customer;
use App\Exports\PaymentsExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class PaymentManagement extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $statusFilter = '';
    public $methodFilter = '';
    public $customerFilter = '';
    public $dateFilter = '';
    public $perPage = 15;

    // Payment form
    public $showPaymentModal = false;
    public $paymentId;
    public $order_id;
    public $jumlah_tagihan;
    public $jumlah_bayar;
    public $jenis_pembayaran = 'tunai';
    public $tanggal_bayar;
    public $catatan;
    public $bukti_transfer;

    // View payment modal
    public $showViewModal = false;
    public $selectedPayment;

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'methodFilter' => ['except' => ''],
        'customerFilter' => ['except' => ''],
        'dateFilter' => ['except' => ''],
    ];

    public function rules()
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'jumlah_tagihan' => 'required|numeric|min:1',
            'jumlah_bayar' => 'required|numeric|min:0',
            'jenis_pembayaran' => 'required|in:tunai,transfer,giro',
            'tanggal_bayar' => 'required|date',
            'catatan' => 'nullable|string|max:500',
            'bukti_transfer' => 'nullable|image|max:2048',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingMethodFilter()
    {
        $this->resetPage();
    }

    public function updatingCustomerFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function openPaymentModal($paymentId = null)
    {
        $this->resetPaymentForm();

        if ($paymentId) {
            $payment = Payment::find($paymentId);
            $this->paymentId = $payment->id;
            $this->order_id = $payment->order_id;
            $this->jumlah_tagihan = $payment->jumlah_tagihan;
            $this->jumlah_bayar = $payment->jumlah_bayar;
            $this->jenis_pembayaran = $payment->jenis_pembayaran;
            $this->tanggal_bayar = $payment->tanggal_bayar ? $payment->tanggal_bayar->format('Y-m-d') : null;
            $this->catatan = $payment->catatan;
        } else {
            $this->tanggal_bayar = now()->format('Y-m-d');
        }

        $this->showPaymentModal = true;
    }

    public function resetPaymentForm()
    {
        $this->reset([
            'paymentId', 'order_id', 'jumlah_tagihan', 'jumlah_bayar', 'catatan', 'bukti_transfer'
        ]);
        $this->jenis_pembayaran = 'tunai';
        $this->tanggal_bayar = now()->format('Y-m-d');
    }

    public function savePayment()
    {
        $this->validate();

        try {
            // Calculate status based on payment
            $status = 'belum_lunas';
            if ($this->jumlah_bayar >= $this->jumlah_tagihan) {
                $status = 'lunas';
            } elseif ($this->jumlah_bayar > 0) {
                $status = 'belum_lunas';
            }

            $data = [
                'order_id' => $this->order_id,
                'jumlah_tagihan' => $this->jumlah_tagihan,
                'jumlah_bayar' => $this->jumlah_bayar,
                'jenis_pembayaran' => $this->jenis_pembayaran,
                'tanggal_bayar' => $this->tanggal_bayar,
                'catatan' => $this->catatan,
                'status' => $status,
            ];

            if ($this->paymentId) {
                $payment = Payment::find($this->paymentId);
                $payment->update($data);

                if ($this->bukti_transfer) {
                    // Store as file path string
                    $path = $this->bukti_transfer->store('payments', 'public');
                    $payment->update(['bukti_transfer' => $path]);
                }

                session()->flash('success', 'Pembayaran berhasil diupdate!');
            } else {
                // Generate nomor nota
                $data['nomor_nota'] = $this->generateNotaNumber();

                $payment = Payment::create($data);

                if ($this->bukti_transfer) {
                    // Store as file path string
                    $path = $this->bukti_transfer->store('payments', 'public');
                    $payment->update(['bukti_transfer' => $path]);
                }

                session()->flash('success', 'Pembayaran berhasil dicatat!');
            }

            $this->showPaymentModal = false;
            $this->resetPaymentForm();

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan pembayaran: ' . $e->getMessage());
        }
    }

    public function viewPayment($paymentId)
    {
        try {
            $this->selectedPayment = Payment::with(['order.customer', 'order.sales'])->find($paymentId);

            if ($this->selectedPayment) {
                $this->showViewModal = true;
            } else {
                session()->flash('error', 'Payment tidak ditemukan!');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedPayment = null;
    }

    public function updatePaymentStatus($paymentId, $status)
    {
        try {
            $payment = Payment::find($paymentId);
            $payment->update(['status' => $status]);

            session()->flash('success', "Status pembayaran berhasil diubah ke {$status}!");

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengubah status pembayaran: ' . $e->getMessage());
        }
    }

    public function deletePayment($paymentId)
    {
        try {
            Payment::find($paymentId)->delete();
            session()->flash('success', 'Pembayaran berhasil dihapus!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus pembayaran: ' . $e->getMessage());
        }
    }

    public function getPaymentsProperty()
    {
        $query = Payment::with(['order.customer'])
            ->when($this->search, function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('nomor_order', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', function ($customerQuery) {
                          $customerQuery->where('nama_toko', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->methodFilter, function ($query) {
                $query->where('jenis_pembayaran', $this->methodFilter);
            })
            ->when($this->customerFilter, function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('customer_id', $this->customerFilter);
                });
            })
            ->when($this->dateFilter, function ($query) {
                $query->whereDate('tanggal_bayar', $this->dateFilter);
            })
            ->latest();

        return $query->paginate($this->perPage);
    }

    public function exportToExcel()
    {
        try {
            // Check if there are any payments with missing relationships
            $paymentsWithoutOrder = Payment::whereNull('order_id')->count();
            $paymentsWithoutCustomer = Payment::whereHas('order', function($query) {
                $query->whereNull('customer_id');
            })->count();
            
            if ($paymentsWithoutOrder > 0) {
                session()->flash('warning', "Ada {$paymentsWithoutOrder} pembayaran tanpa order yang akan dilewati dalam export.");
            }
            
            if ($paymentsWithoutCustomer > 0) {
                session()->flash('warning', "Ada {$paymentsWithoutCustomer} pembayaran dengan order tanpa customer.");
            }

            $filters = [
                'search' => $this->search,
                'status' => $this->statusFilter,
                'method' => $this->methodFilter,
                'customer_id' => $this->customerFilter,
                'date_filter' => $this->dateFilter,
            ];

            return Excel::download(
                new PaymentsExport($filters), 
                'payments-export-' . now()->format('Y-m-d-H-i-s') . '.xlsx'
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengekspor data: ' . $e->getMessage() . ' Line: ' . $e->getLine());
            \Log::error('Export error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    public function getOrdersProperty()
    {
        return Order::with('customer')
                   ->whereIn('status', ['confirmed', 'shipped', 'delivered'])
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    public function getCustomersProperty()
    {
        return Customer::where('is_active', true)
                      ->orderBy('nama_toko')
                      ->get();
    }

    public function render()
    {
        $totalPayments = Payment::count();
        $lunasPayments = Payment::where('status', 'lunas')->count();
        $belumLunasPayments = Payment::where('status', 'belum_lunas')->count();
        $overduePayments = Payment::where('status', 'overdue')->count();
        $totalTagihan = Payment::sum('jumlah_tagihan');
        $totalBayar = Payment::sum('jumlah_bayar');

        $todayPayments = Payment::whereDate('tanggal_bayar', today())->count();
        $todayAmount = Payment::whereDate('tanggal_bayar', today())
                             ->sum('jumlah_bayar');

        $tunaiPayments = Payment::where('jenis_pembayaran', 'tunai')
                              ->sum('jumlah_bayar');
        $transferPayments = Payment::where('jenis_pembayaran', 'transfer')
                                  ->sum('jumlah_bayar');
        $giroPayments = Payment::where('jenis_pembayaran', 'giro')
                                ->sum('jumlah_bayar');

        return view('livewire.admin.payment-management', [
            'payments' => $this->payments,
            'orders' => $this->orders,
            'customers' => $this->customers,
            'totalPayments' => $totalPayments,
            'lunasPayments' => $lunasPayments,
            'belumLunasPayments' => $belumLunasPayments,
            'overduePayments' => $overduePayments,
            'totalTagihan' => $totalTagihan,
            'totalBayar' => $totalBayar,
            'todayPayments' => $todayPayments,
            'todayAmount' => $todayAmount,
            'tunaiPayments' => $tunaiPayments,
            'transferPayments' => $transferPayments,
            'giroPayments' => $giroPayments,
        ]);
    }

    private function generateNotaNumber()
    {
        $date = now()->format('Ymd');
        $count = Payment::whereDate('created_at', now())->count() + 1;
        return 'NOTA-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
