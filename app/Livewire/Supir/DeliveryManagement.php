<?php

namespace App\Livewire\Supir;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Delivery;
use App\Models\DeliveryTracking;

class DeliveryManagement extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $statusFilter = '';

    // Start delivery modal
    public $showStartModal = false;
    public $startDeliveryId;
    public $startLatitude;
    public $startLongitude;
    public $startLocationAccuracy;
    public $isStartLocationValid = false;

    // Complete delivery modal
    public $showCompleteModal = false;
    public $completeDeliveryId;
    public $deliveryLatitude;
    public $deliveryLongitude;
    public $deliveryLocationAccuracy;
    public $deliveryNotes;
    public $deliveryProofPhoto;

    public $isDeliveryLocationValid = false;

    // GPS tracking
    public $isTrackingActive = false;
    public $currentDelivery;

    protected $paginationTheme = 'bootstrap';

    public function rules()
    {
        return [
            'deliveryLatitude' => 'required|numeric|between:-90,90',
            'deliveryLongitude' => 'required|numeric|between:-180,180',
            'deliveryNotes' => 'nullable|string|max:500',
            'deliveryProofPhoto' => 'required|image|max:2048',

        ];
    }

    public function mount()
    {
        // Check if there's an active delivery
        $this->currentDelivery = Delivery::where('driver_id', auth()->id())
                                        ->where('status', 'in_progress')
                                        ->first();

        if ($this->currentDelivery) {
            $this->isTrackingActive = true;
            $this->startGPSTracking();
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openStartModal($deliveryId)
    {
        $delivery = Delivery::where('driver_id', auth()->id())
                           ->findOrFail($deliveryId);

        if (!$delivery->canBeStarted()) {
            session()->flash('error', 'Delivery tidak dapat dimulai. Pastikan K3 checklist sudah diapprove!');
            return;
        }

        $this->startDeliveryId = $delivery->id;
        $this->resetStartForm();
        $this->showStartModal = true;
    }

    public function closeStartModal()
    {
        $this->showStartModal = false;
        $this->resetStartForm();
    }

    public function resetStartForm()
    {
        $this->reset(['startLatitude', 'startLongitude', 'startLocationAccuracy', 'isStartLocationValid']);
    }

    public function getCurrentLocationForStart()
    {
        $this->dispatch('getCurrentLocationForStart');
    }

    #[\Livewire\Attributes\On('setStartLocation')]
    public function setStartLocation($latitude, $longitude, $accuracy = null)
    {
        $this->startLatitude = $latitude;
        $this->startLongitude = $longitude;
        $this->startLocationAccuracy = $accuracy;
        $this->isStartLocationValid = true;

        session()->flash('success', 'Lokasi start berhasil diambil!');
    }

    public function startDelivery()
    {
        if (!$this->isStartLocationValid) {
            session()->flash('error', 'Ambil lokasi GPS terlebih dahulu!');
            return;
        }

        try {
            $delivery = Delivery::where('driver_id', auth()->id())
                               ->findOrFail($this->startDeliveryId);

            if (!$delivery->canBeStarted()) {
                session()->flash('error', 'Delivery tidak dapat dimulai!');
                return;
            }

            // Update delivery status
            $delivery->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            // Create initial tracking log
            DeliveryTracking::create([
                'delivery_id' => $delivery->id,
                'latitude' => $this->startLatitude,
                'longitude' => $this->startLongitude,
                'accuracy' => $this->startLocationAccuracy,
                'tracked_at' => now(),
                'is_online' => true,
            ]);

            $this->currentDelivery = $delivery;
            $this->isTrackingActive = true;
            $this->startGPSTracking();

            $this->closeStartModal();
            session()->flash('success', 'Delivery berhasil dimulai! GPS tracking aktif.');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function openCompleteModal($deliveryId)
    {
        $delivery = Delivery::where('driver_id', auth()->id())
                           ->findOrFail($deliveryId);

        if (!$delivery->canBeCompleted()) {
            session()->flash('error', 'Delivery tidak dapat diselesaikan!');
            return;
        }

        $this->completeDeliveryId = $delivery->id;
        $this->resetCompleteForm();
        $this->showCompleteModal = true;
    }

    public function closeCompleteModal()
    {
        $this->showCompleteModal = false;
        $this->resetCompleteForm();
    }

    public function resetCompleteForm()
    {
        $this->reset([
            'deliveryLatitude', 'deliveryLongitude', 'deliveryLocationAccuracy',
            'deliveryNotes', 'deliveryProofPhoto', 'isDeliveryLocationValid'
        ]);
    }

    public function getCurrentLocationForDelivery()
    {
        $this->dispatch('getCurrentLocationForDelivery');
    }

    #[\Livewire\Attributes\On('setDeliveryLocation')]
    public function setDeliveryLocation($latitude, $longitude, $accuracy = null)
    {
        $this->deliveryLatitude = $latitude;
        $this->deliveryLongitude = $longitude;
        $this->deliveryLocationAccuracy = $accuracy;
        $this->isDeliveryLocationValid = true;

        session()->flash('success', 'Lokasi delivery berhasil diambil!');
    }

    public function completeDelivery()
    {
        $this->validate();

        try {
            $delivery = Delivery::where('driver_id', auth()->id())
                               ->findOrFail($this->completeDeliveryId);

            if (!$delivery->canBeCompleted()) {
                session()->flash('error', 'Delivery tidak dapat diselesaikan!');
                return;
            }

            // Update delivery
            $delivery->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                'delivery_latitude' => $this->deliveryLatitude,
                'delivery_longitude' => $this->deliveryLongitude,
                'delivery_notes' => $this->deliveryNotes,

            ]);

            // Upload proof photo
            if ($this->deliveryProofPhoto) {
                $filename = time() . '_' . $this->deliveryProofPhoto->getClientOriginalName();
                $path = $this->deliveryProofPhoto->storeAs('delivery_proofs', $filename, 'public');
                $delivery->update(['delivery_proof_photo' => $filename]);
            }

            // Create final tracking log
            DeliveryTracking::create([
                'delivery_id' => $delivery->id,
                'latitude' => $this->deliveryLatitude,
                'longitude' => $this->deliveryLongitude,
                'accuracy' => $this->deliveryLocationAccuracy,
                'tracked_at' => now(),
                'is_online' => false,
            ]);

            // Update order status
            $delivery->order->update(['status' => 'delivered']);

            $this->currentDelivery = null;
            $this->isTrackingActive = false;
            $this->stopGPSTracking();

            $this->closeCompleteModal();
            session()->flash('success', 'Delivery berhasil diselesaikan!');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function startGPSTracking()
    {
        $this->dispatch('startGPSTracking', deliveryId: $this->currentDelivery->id);
    }

    private function stopGPSTracking()
    {
        $this->dispatch('stopGPSTracking');
    }

    #[\Livewire\Attributes\On('updateGPSLocation')]
    public function updateGPSLocation($latitude, $longitude, $accuracy = null, $speed = null, $heading = null)
    {
        if ($this->currentDelivery && $this->isTrackingActive) {
            DeliveryTracking::create([
                'delivery_id' => $this->currentDelivery->id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy' => $accuracy,
                'speed' => $speed,
                'heading' => $heading,
                'tracked_at' => now(),
                'is_online' => true,
            ]);
        }
    }

    public function render()
    {
        $deliveries = Delivery::where('driver_id', auth()->id())
            ->with(['order.customer', 'k3Checklist'])
            ->when($this->search, function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('nomor_order', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', function ($customer) {
                          $customer->where('nama_toko', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('assigned_at', 'desc')
            ->paginate(10);

        $assignedDeliveries = Delivery::where('driver_id', auth()->id())
                                    ->where('status', 'assigned')
                                    ->count();

        $inProgressDeliveries = Delivery::where('driver_id', auth()->id())
                                       ->where('status', 'in_progress')
                                       ->count();

        $completedToday = Delivery::where('driver_id', auth()->id())
                                 ->where('status', 'delivered')
                                 ->whereDate('delivered_at', today())
                                 ->count();

        return view('livewire.supir.delivery-management', [
            'deliveries' => $deliveries,
            'assignedDeliveries' => $assignedDeliveries,
            'inProgressDeliveries' => $inProgressDeliveries,
            'completedToday' => $completedToday,
        ]);
    }
}
