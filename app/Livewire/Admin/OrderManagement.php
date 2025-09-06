<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use App\Models\Customer;
use App\Models\User;
use App\Exports\OrdersExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class OrderManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $salesFilter = '';
    public $customerFilter = '';
    public $dateFilter = '';
    public $perPage = 15;

    // View order modal
    public $showOrderModal = false;
    public $selectedOrder;

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'salesFilter' => ['except' => ''],
        'customerFilter' => ['except' => ''],
        'dateFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingSalesFilter()
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

    public function viewOrder($orderId)
    {
        try {
            $this->selectedOrder = Order::with(['customer', 'sales', 'orderItems.product', 'delivery', 'payments'])
                                    ->find($orderId);

            if ($this->selectedOrder) {
                $this->showOrderModal = true;
                $this->dispatch('modal-opened'); // Dispatch event for debugging
            } else {
                session()->flash('error', 'Order tidak ditemukan!');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function closeOrderModal()
    {
        $this->showOrderModal = false;
        $this->selectedOrder = null;
    }

    public function updateOrderStatus($orderId, $status)
    {
        try {
            $order = Order::with('payment')->find($orderId);
            $oldStatus = $order->status;

            // Validate payment status before shipping
            if ($status === 'shipped') {
                if (!$order->payment) {
                    session()->flash('error', 'Order belum memiliki invoice/payment!');
                    return;
                }

                if ($order->payment->status !== 'lunas') {
                    session()->flash('error', 'Order hanya bisa dikirim setelah pembayaran lunas!');
                    return;
                }

                // Log the shipping action
                \Log::info('Order shipping initiated', [
                    'order_id' => $order->id,
                    'order_number' => $order->nomor_order,
                    'current_status' => $oldStatus,
                    'payment_status' => $order->payment->status
                ]);
            }

            $order->update(['status' => $status]);

            // Update timestamps based on status
            switch ($status) {
                case 'confirmed':
                    $order->update(['confirmed_at' => now()]);
                    break;
                case 'shipped':
                    $order->update(['shipped_at' => now()]);

                    // Auto-create delivery when order is shipped
                    if (!$order->delivery) {
                        // Find first available driver (supir)
                        $defaultDriver = \App\Models\User::where('role', 'supir')->where('is_active', true)->first();

                        if (!$defaultDriver) {
                            session()->flash('error', 'Tidak ada supir aktif yang tersedia untuk delivery!');
                            return;
                        }

                        \App\Models\Delivery::create([
                            'order_id' => $order->id,
                            'driver_id' => $defaultDriver->id,
                            'assigned_by' => auth()->id(),
                            'rute_kota' => 'TBD', // To Be Determined by Gudang
                            'status' => 'assigned',
                            'assigned_at' => now(),
                        ]);

                        \Log::info('Delivery auto-created for shipped order', [
                            'order_id' => $order->id,
                            'order_number' => $order->nomor_order
                        ]);
                    }
                    break;
                case 'delivered':
                    $order->update(['delivered_at' => now()]);
                    break;
                case 'cancelled':
                    $order->update(['cancelled_at' => now()]);
                    break;
            }

            $message = "Status order berhasil diubah dari {$oldStatus} ke {$status}!";

            // Add delivery creation message if applicable
            if ($status === 'shipped') {
                $delivery = $order->fresh()->delivery;
                if ($delivery) {
                    $driverName = $delivery->driver->name ?? 'Unknown';
                    $message .= " Delivery telah dibuat dan di-assign ke {$driverName}.";
                }
            }

            session()->flash('success', $message);

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengubah status order: ' . $e->getMessage());
        }
    }

    public function cancelOrder($orderId, $reason = 'Cancelled by admin')
    {
        try {
            $order = Order::find($orderId);

            if (!in_array($order->status, ['pending', 'confirmed'])) {
                session()->flash('error', 'Order tidak dapat dibatalkan karena sudah dalam proses pengiriman!');
                return;
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ]);

            // Restore product stock
            foreach ($order->orderItems as $item) {
                $item->product->increment('stok_tersedia', $item->jumlah);
            }

            session()->flash('success', 'Order berhasil dibatalkan dan stok dikembalikan!');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membatalkan order: ' . $e->getMessage());
        }
    }

    public function getOrdersProperty()
    {
        $query = Order::with(['customer', 'sales', 'payment'])
            ->when($this->search, function ($query) {
                $query->where('nomor_order', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', function ($q) {
                          $q->where('nama_toko', 'like', '%' . $this->search . '%');
                      });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->salesFilter, function ($query) {
                $query->where('sales_id', $this->salesFilter);
            })
            ->when($this->customerFilter, function ($query) {
                $query->where('customer_id', $this->customerFilter);
            })
            ->when($this->dateFilter, function ($query) {
                $query->whereDate('created_at', $this->dateFilter);
            })
            ->latest();

        return $query->paginate($this->perPage);
    }

    public function exportToExcel()
    {
        try {
            $filters = [
                'search' => $this->search,
                'status' => $this->statusFilter,
                'sales_id' => $this->salesFilter,
                'customer_id' => $this->customerFilter,
                'date_filter' => $this->dateFilter,
            ];

            return Excel::download(
                new OrdersExport($filters),
                'orders-export-' . now()->format('Y-m-d-H-i-s') . '.xlsx'
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }

    public function getSalesUsersProperty()
    {
        return User::where('role', 'sales')
                  ->where('is_active', true)
                  ->orderBy('name')
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
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $confirmedOrders = Order::where('status', 'confirmed')->count();
        $shippedOrders = Order::where('status', 'shipped')->count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();

        $totalValue = Order::whereIn('status', ['confirmed', 'shipped', 'delivered'])
                          ->sum('total_amount');

        $todayOrders = Order::whereDate('created_at', today())->count();
        $todayValue = Order::whereDate('created_at', today())
                          ->whereIn('status', ['confirmed', 'shipped', 'delivered'])
                          ->sum('total_amount');

        return view('livewire.admin.order-management', [
            'orders' => $this->orders,
            'salesUsers' => $this->salesUsers,
            'customers' => $this->customers,
            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'confirmedOrders' => $confirmedOrders,
            'shippedOrders' => $shippedOrders,
            'deliveredOrders' => $deliveredOrders,
            'cancelledOrders' => $cancelledOrders,
            'totalValue' => $totalValue,
            'todayOrders' => $todayOrders,
            'todayValue' => $todayValue,
        ]);
    }
}
