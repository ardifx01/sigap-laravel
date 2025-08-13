<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

class DeliveryManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $driverFilter = '';
    public $dateFilter = '';
    public $perPage = 15;

    // Assign driver modal
    public $showAssignModal = false;
    public $assignDelivery;
    public $selectedDriver;

    // View delivery modal
    public $showDeliveryModal = false;
    public $viewDeliveryData;

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'driverFilter' => ['except' => ''],
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

    public function updatingDriverFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function openAssignModal($deliveryId)
    {
        $this->assignDelivery = Delivery::with(['order.customer'])->find($deliveryId);
        $this->selectedDriver = $this->assignDelivery->driver_id;
        $this->showAssignModal = true;
    }

    public function assignDriver()
    {
        if (!$this->selectedDriver) {
            session()->flash('error', 'Pilih driver terlebih dahulu!');
            return;
        }

        try {
            $this->assignDelivery->update([
                'driver_id' => $this->selectedDriver,
                'assigned_at' => now(),
            ]);

            session()->flash('success', 'Driver berhasil ditugaskan!');
            $this->showAssignModal = false;
            $this->reset(['assignDelivery', 'selectedDriver']);

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menugaskan driver: ' . $e->getMessage());
        }
    }

    public function viewDelivery($deliveryId)
    {
        try {
            $this->viewDeliveryData = Delivery::with([
                'order.customer',
                'order.orderItems.product',
                'driver',
                'trackingHistory'
            ])->find($deliveryId);

            if ($this->viewDeliveryData) {
                $this->showDeliveryModal = true;
            } else {
                session()->flash('error', 'Delivery tidak ditemukan!');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function closeDeliveryModal()
    {
        $this->showDeliveryModal = false;
        $this->viewDeliveryData = null;
    }

    public function updateDeliveryStatus($deliveryId, $status)
    {
        try {
            $delivery = Delivery::find($deliveryId);
            $oldStatus = $delivery->status;

            $delivery->update(['status' => $status]);

            // Update timestamps and order status based on delivery status
            switch ($status) {
                case 'assigned':
                    $delivery->update(['assigned_at' => now()]);
                    break;
                case 'k3_checked':
                    $delivery->update(['k3_checked_at' => now()]);
                    break;
                case 'in_progress':
                    $delivery->update(['started_at' => now()]);
                    $delivery->order->update(['status' => 'shipped']);
                    break;
                case 'delivered':
                    $delivery->update(['delivered_at' => now()]);
                    $delivery->order->update(['status' => 'delivered']);
                    break;
                case 'failed':
                    $delivery->update(['failed_at' => now()]);
                    break;
            }

            session()->flash('success', "Status pengiriman berhasil diubah dari {$oldStatus} ke {$status}!");

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengubah status pengiriman: ' . $e->getMessage());
        }
    }

    public function createDeliveryFromOrder($orderId)
    {
        try {
            $order = Order::find($orderId);

            if ($order->status !== 'confirmed') {
                session()->flash('error', 'Order harus dikonfirmasi terlebih dahulu!');
                return;
            }

            if ($order->delivery) {
                session()->flash('error', 'Order sudah memiliki pengiriman!');
                return;
            }

            Delivery::create([
                'order_id' => $order->id,
                'status' => 'pending',
                'estimated_distance' => 0, // Could be calculated based on customer location
                'created_at' => now(),
            ]);

            session()->flash('success', 'Pengiriman berhasil dibuat untuk order ' . $order->nomor_order);

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat pengiriman: ' . $e->getMessage());
        }
    }

    public function getDeliveriesProperty()
    {
        $query = Delivery::with(['order.customer', 'driver'])
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
            ->when($this->driverFilter, function ($query) {
                $query->where('driver_id', $this->driverFilter);
            })
            ->when($this->dateFilter, function ($query) {
                $query->whereDate('created_at', $this->dateFilter);
            })
            ->latest();

        return $query->paginate($this->perPage);
    }

    public function getDriversProperty()
    {
        return User::where('role', 'supir')
                  ->where('is_active', true)
                  ->orderBy('name')
                  ->get();
    }

    public function getConfirmedOrdersProperty()
    {
        return Order::where('status', 'confirmed')
                   ->whereDoesntHave('delivery')
                   ->with('customer')
                   ->latest()
                   ->take(10)
                   ->get();
    }

    public function render()
    {
        $totalDeliveries = Delivery::count();
        $pendingDeliveries = Delivery::where('status', 'pending')->count();
        $assignedDeliveries = Delivery::where('status', 'assigned')->count();
        $inProgressDeliveries = Delivery::where('status', 'in_progress')->count();
        $deliveredDeliveries = Delivery::where('status', 'delivered')->count();
        $failedDeliveries = Delivery::where('status', 'failed')->count();

        $todayDeliveries = Delivery::whereDate('created_at', today())->count();
        $todayDelivered = Delivery::whereDate('delivered_at', today())->count();

        return view('livewire.admin.delivery-management', [
            'deliveries' => $this->deliveries,
            'drivers' => $this->drivers,
            'confirmedOrders' => $this->confirmedOrders,
            'totalDeliveries' => $totalDeliveries,
            'pendingDeliveries' => $pendingDeliveries,
            'assignedDeliveries' => $assignedDeliveries,
            'inProgressDeliveries' => $inProgressDeliveries,
            'deliveredDeliveries' => $deliveredDeliveries,
            'failedDeliveries' => $failedDeliveries,
            'todayDeliveries' => $todayDeliveries,
            'todayDelivered' => $todayDelivered,
        ]);
    }
}
