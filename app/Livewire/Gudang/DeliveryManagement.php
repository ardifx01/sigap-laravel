<?php

namespace App\Livewire\Gudang;

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
    public $supirFilter = '';
    public $dateFilter = '';
    public $perPage = 15;

    // Assign delivery modal
    public $showAssignModal = false;
    public $selectedOrder;
    public $selectedSupir;
    public $rutaKota = '';
    public $estimatedArrival = '';

    // View delivery modal
    public $showViewModal = false;
    public $selectedDelivery;

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'supirFilter' => ['except' => ''],
        'dateFilter' => ['except' => ''],
    ];

    public function rules()
    {
        return [
            'selectedSupir' => 'required|exists:users,id',
            'rutaKota' => 'required|string|max:255',
            'estimatedArrival' => 'required|date|after:now',
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

    public function updatingSupirFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function openAssignModal($orderId)
    {
        $this->selectedOrder = Order::with(['customer', 'sales'])->find($orderId);
        $this->resetAssignForm();
        $this->showAssignModal = true;
    }

    public function resetAssignForm()
    {
        $this->selectedSupir = '';
        $this->rutaKota = '';
        $this->estimatedArrival = now()->addHours(2)->format('Y-m-d\TH:i');
    }

    public function assignDelivery()
    {
        $this->validate();

        try {
            $delivery = Delivery::create([
                'order_id' => $this->selectedOrder->id,
                'driver_id' => $this->selectedSupir,
                'assigned_by' => auth()->id(),
                'rute_kota' => $this->rutaKota,
                'status' => 'assigned',
                'assigned_at' => now(),
                'estimated_arrival' => $this->estimatedArrival,
            ]);

            // Update order status
            $this->selectedOrder->update([
                'status' => 'assigned',
                'shipped_at' => now(),
            ]);

            $this->showAssignModal = false;
            $this->resetAssignForm();

            session()->flash('success', 'Pengiriman berhasil diassign ke supir!');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal assign pengiriman: ' . $e->getMessage());
        }
    }

    public function viewDelivery($deliveryId)
    {
        $this->selectedDelivery = Delivery::with(['order.customer', 'order.sales', 'supir', 'trackingLogs'])
                                         ->find($deliveryId);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedDelivery = null;
    }

    public function cancelDelivery($deliveryId)
    {
        try {
            $delivery = Delivery::find($deliveryId);

            if ($delivery && in_array($delivery->status, ['assigned', 'k3_checked'])) {
                $delivery->update(['status' => 'cancelled']);

                // Update order status back to confirmed
                $delivery->order->update(['status' => 'confirmed']);

                session()->flash('success', 'Pengiriman berhasil dibatalkan!');
            } else {
                session()->flash('error', 'Pengiriman tidak dapat dibatalkan!');
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membatalkan pengiriman: ' . $e->getMessage());
        }
    }

    public function getDeliveriesProperty()
    {
        $query = Delivery::with(['order.customer', 'order.sales', 'supir'])
            ->when($this->search, function ($query) {
                $query->whereHas('order.customer', function ($q) {
                    $q->where('nama_toko', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('supir', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhere('rute_kota', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->supirFilter, function ($query) {
                $query->where('driver_id', $this->supirFilter);
            })
            ->when($this->dateFilter, function ($query) {
                $query->whereDate('assigned_at', $this->dateFilter);
            })
            ->latest('assigned_at');

        return $query->paginate($this->perPage);
    }

    public function getConfirmedOrdersProperty()
    {
        return Order::with(['customer', 'sales'])
                   ->where('status', 'confirmed')
                   ->whereDoesntHave('delivery')
                   ->latest()
                   ->get();
    }

    public function getAvailableSupirsProperty()
    {
        return User::where('role', 'supir')
                  ->where('is_active', true)
                  ->orderBy('name')
                  ->get();
    }

    public function getStatsProperty()
    {
        return [
            'assigned' => Delivery::where('status', 'assigned')->count(),
            'in_progress' => Delivery::where('status', 'in_progress')->count(),
            'delivered_today' => Delivery::where('status', 'delivered')
                                        ->whereDate('delivered_at', today())
                                        ->count(),
        ];
    }

    public function render()
    {
        return view('livewire.gudang.delivery-management', [
            'deliveries' => $this->deliveries,
            'confirmedOrders' => $this->confirmedOrders,
            'availableSupirs' => $this->availableSupirs,
            'stats' => $this->stats,
        ]);
    }
}
