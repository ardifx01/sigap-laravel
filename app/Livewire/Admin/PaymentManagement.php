<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Customer;
use Carbon\Carbon;

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
    public $amount;
    public $payment_method = 'cash';
    public $payment_date;
    public $notes;
    public $bukti_transfer;

    // View payment modal
    public $showViewModal = false;
    public $viewPayment;

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
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,transfer,credit',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
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
            $this->amount = $payment->amount;
            $this->payment_method = $payment->payment_method;
            $this->payment_date = $payment->payment_date->format('Y-m-d');
            $this->notes = $payment->notes;
        } else {
            $this->payment_date = now()->format('Y-m-d');
        }
        
        $this->showPaymentModal = true;
    }

    public function resetPaymentForm()
    {
        $this->reset([
            'paymentId', 'order_id', 'amount', 'notes', 'bukti_transfer'
        ]);
        $this->payment_method = 'cash';
        $this->payment_date = now()->format('Y-m-d');
    }

    public function savePayment()
    {
        $this->validate();

        try {
            $data = [
                'order_id' => $this->order_id,
                'amount' => $this->amount,
                'payment_method' => $this->payment_method,
                'payment_date' => $this->payment_date,
                'notes' => $this->notes,
                'status' => 'paid',
                'recorded_by' => auth()->id(),
            ];

            if ($this->paymentId) {
                $payment = Payment::find($this->paymentId);
                $payment->update($data);
                
                if ($this->bukti_transfer) {
                    $payment->clearMediaCollection('payment_proofs');
                    $payment->addMediaFromRequest('bukti_transfer')->toMediaCollection('payment_proofs');
                }
                
                session()->flash('success', 'Pembayaran berhasil diupdate!');
            } else {
                $payment = Payment::create($data);
                
                if ($this->bukti_transfer) {
                    $payment->addMediaFromRequest('bukti_transfer')->toMediaCollection('payment_proofs');
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
        $this->viewPayment = Payment::with(['order.customer', 'recordedBy'])->find($paymentId);
        $this->showViewModal = true;
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
                $query->where('payment_method', $this->methodFilter);
            })
            ->when($this->customerFilter, function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('customer_id', $this->customerFilter);
                });
            })
            ->when($this->dateFilter, function ($query) {
                $query->whereDate('payment_date', $this->dateFilter);
            })
            ->latest();

        return $query->paginate($this->perPage);
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
        $paidPayments = Payment::where('status', 'paid')->count();
        $pendingPayments = Payment::where('status', 'pending')->count();
        $totalAmount = Payment::where('status', 'paid')->sum('amount');
        
        $todayPayments = Payment::whereDate('payment_date', today())->count();
        $todayAmount = Payment::whereDate('payment_date', today())
                             ->where('status', 'paid')
                             ->sum('amount');
        
        $cashPayments = Payment::where('payment_method', 'cash')
                              ->where('status', 'paid')
                              ->sum('amount');
        $transferPayments = Payment::where('payment_method', 'transfer')
                                  ->where('status', 'paid')
                                  ->sum('amount');
        $creditPayments = Payment::where('payment_method', 'credit')
                                ->where('status', 'paid')
                                ->sum('amount');

        return view('livewire.admin.payment-management', [
            'payments' => $this->payments,
            'orders' => $this->orders,
            'customers' => $this->customers,
            'totalPayments' => $totalPayments,
            'paidPayments' => $paidPayments,
            'pendingPayments' => $pendingPayments,
            'totalAmount' => $totalAmount,
            'todayPayments' => $todayPayments,
            'todayAmount' => $todayAmount,
            'cashPayments' => $cashPayments,
            'transferPayments' => $transferPayments,
            'creditPayments' => $creditPayments,
        ]);
    }
}
