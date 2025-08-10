<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Customer;

class PaymentManagement extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $statusFilter = '';
    public $customerFilter = '';

    // Payment form
    public $showPaymentModal = false;
    public $editMode = false;
    public $paymentId;
    public $order_id;
    public $customer_id;
    public $jumlah_tagihan = 0;
    public $jumlah_dibayar = 0;
    public $metode_pembayaran = 'transfer';
    public $tanggal_jatuh_tempo;
    public $tanggal_pembayaran;
    public $catatan;
    public $bukti_transfer;

    // Upload payment proof modal
    public $showProofModal = false;
    public $proofPaymentId;
    public $proofAmount = 0;
    public $proofPhoto;
    public $proofNotes;

    protected $paginationTheme = 'bootstrap';

    public function rules()
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'customer_id' => 'required|exists:customers,id',
            'jumlah_tagihan' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:cash,transfer,credit',
            'tanggal_jatuh_tempo' => 'required|date|after_or_equal:today',
            'catatan' => 'nullable|string|max:500',
        ];
    }

    public function proofRules()
    {
        return [
            'proofAmount' => 'required|numeric|min:1',
            'proofPhoto' => 'required|image|max:2048',
            'proofNotes' => 'nullable|string|max:255',
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

    public function updatingCustomerFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showPaymentModal = true;
        $this->editMode = false;
    }

    public function openEditModal($paymentId)
    {
        $payment = Payment::where('sales_id', auth()->id())
                         ->findOrFail($paymentId);

        $this->paymentId = $payment->id;
        $this->order_id = $payment->order_id;
        $this->customer_id = $payment->customer_id;
        $this->jumlah_tagihan = $payment->jumlah_tagihan;
        $this->jumlah_dibayar = $payment->jumlah_dibayar;
        $this->metode_pembayaran = $payment->metode_pembayaran;
        $this->tanggal_jatuh_tempo = $payment->tanggal_jatuh_tempo->format('Y-m-d');
        $this->tanggal_pembayaran = $payment->tanggal_pembayaran?->format('Y-m-d');
        $this->catatan = $payment->catatan;

        $this->showPaymentModal = true;
        $this->editMode = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'paymentId', 'order_id', 'customer_id', 'jumlah_tagihan', 'jumlah_dibayar',
            'metode_pembayaran', 'tanggal_jatuh_tempo', 'tanggal_pembayaran', 'catatan', 'bukti_transfer'
        ]);
        $this->metode_pembayaran = 'transfer';
        $this->jumlah_tagihan = 0;
        $this->jumlah_dibayar = 0;
    }

    public function updatedOrderId()
    {
        if ($this->order_id) {
            $order = Order::where('sales_id', auth()->id())
                         ->with('customer')
                         ->find($this->order_id);

            if ($order) {
                $this->customer_id = $order->customer_id;
                $this->jumlah_tagihan = $order->total_amount;
            }
        }
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->editMode) {
                $payment = Payment::where('sales_id', auth()->id())
                                 ->findOrFail($this->paymentId);

                $payment->update([
                    'order_id' => $this->order_id,
                    'customer_id' => $this->customer_id,
                    'jumlah_tagihan' => $this->jumlah_tagihan,
                    'metode_pembayaran' => $this->metode_pembayaran,
                    'tanggal_jatuh_tempo' => $this->tanggal_jatuh_tempo,
                    'catatan' => $this->catatan,
                ]);

                session()->flash('success', 'Data pembayaran berhasil diperbarui!');
            } else {
                $payment = Payment::create([
                    'nomor_invoice' => $this->generateInvoiceNumber(),
                    'order_id' => $this->order_id,
                    'customer_id' => $this->customer_id,
                    'sales_id' => auth()->id(),
                    'jumlah_tagihan' => $this->jumlah_tagihan,
                    'jumlah_dibayar' => 0,
                    'sisa_tagihan' => $this->jumlah_tagihan,
                    'metode_pembayaran' => $this->metode_pembayaran,
                    'tanggal_jatuh_tempo' => $this->tanggal_jatuh_tempo,
                    'status' => 'belum_lunas',
                    'catatan' => $this->catatan,
                ]);

                session()->flash('success', 'Invoice berhasil dibuat!');
            }

            $this->closePaymentModal();

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function openProofModal($paymentId)
    {
        $payment = Payment::where('sales_id', auth()->id())
                         ->findOrFail($paymentId);

        $this->proofPaymentId = $payment->id;
        $this->proofAmount = $payment->sisa_tagihan;
        $this->resetProofForm();
        $this->showProofModal = true;
    }

    public function closeProofModal()
    {
        $this->showProofModal = false;
        $this->resetProofForm();
    }

    public function resetProofForm()
    {
        $this->reset(['proofPhoto', 'proofNotes']);
    }

    public function uploadPaymentProof()
    {
        $this->validate($this->proofRules());

        try {
            $payment = Payment::where('sales_id', auth()->id())
                             ->findOrFail($this->proofPaymentId);

            // Update payment
            $payment->jumlah_dibayar += $this->proofAmount;
            $payment->tanggal_pembayaran = now();
            $payment->updatePaymentStatus();

            // Upload proof photo
            if ($this->proofPhoto) {
                $payment->addMediaFromDisk($this->proofPhoto->getRealPath())
                    ->usingName('Payment Proof - ' . $payment->nomor_invoice)
                    ->usingFileName('payment_proof_' . time() . '.' . $this->proofPhoto->getClientOriginalExtension())
                    ->toMediaCollection('payment_proofs');
            }

            $this->closeProofModal();
            session()->flash('success', 'Bukti pembayaran berhasil diupload!');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function deletePayment($paymentId)
    {
        try {
            $payment = Payment::where('sales_id', auth()->id())
                             ->findOrFail($paymentId);

            // Only allow deletion if not paid yet
            if ($payment->jumlah_dibayar > 0) {
                session()->flash('error', 'Tidak dapat menghapus invoice yang sudah ada pembayaran!');
                return;
            }

            $payment->delete();
            session()->flash('success', 'Invoice berhasil dihapus!');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function generateInvoiceNumber()
    {
        $date = now()->format('Ymd');
        $count = Payment::whereDate('created_at', now())->count() + 1;
        return 'INV-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        $payments = Payment::where('sales_id', auth()->id())
            ->with(['order', 'customer'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nomor_invoice', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', function ($customer) {
                          $customer->where('nama_toko', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('order', function ($order) {
                          $order->where('nomor_order', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->customerFilter, function ($query) {
                $query->where('customer_id', $this->customerFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $customers = Customer::where('sales_id', auth()->id())
                           ->where('is_active', true)
                           ->orderBy('nama_toko')
                           ->get();

        $orders = Order::where('sales_id', auth()->id())
                     ->whereNotIn('id', Payment::where('sales_id', auth()->id())->pluck('order_id'))
                     ->with('customer')
                     ->orderBy('created_at', 'desc')
                     ->get();

        $totalTagihan = Payment::where('sales_id', auth()->id())
                              ->where('status', 'belum_lunas')
                              ->sum('sisa_tagihan');

        $overdueCount = Payment::where('sales_id', auth()->id())
                              ->where('status', 'belum_lunas')
                              ->where('tanggal_jatuh_tempo', '<', now())
                              ->count();

        return view('livewire.sales.payment-management', [
            'payments' => $payments,
            'customers' => $customers,
            'orders' => $orders,
            'totalTagihan' => $totalTagihan,
            'overdueCount' => $overdueCount,
        ]);
    }
}
