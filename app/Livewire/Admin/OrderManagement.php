<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;

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
    public $viewOrder;

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
        $this->viewOrder = Order::with(['customer', 'sales', 'orderItems.product', 'delivery', 'payments'])
                                ->find($orderId);
        $this->showOrderModal = true;
    }

    public function updateOrderStatus($orderId, $status)
    {
        try {
            $order = Order::find($orderId);
            $oldStatus = $order->status;
            
            $order->update(['status' => $status]);
            
            // Update timestamps based on status
            switch ($status) {
                case 'confirmed':
                    $order->update(['confirmed_at' => now()]);
                    break;
                case 'shipped':
                    $order->update(['shipped_at' => now()]);
                    break;
                case 'delivered':
                    $order->update(['delivered_at' => now()]);
                    break;
                case 'cancelled':
                    $order->update(['cancelled_at' => now()]);
                    break;
            }
            
            session()->flash('success', "Status order berhasil diubah dari {$oldStatus} ke {$status}!");
            
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
        $query = Order::with(['customer', 'sales'])
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
