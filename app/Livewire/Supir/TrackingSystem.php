<?php

namespace App\Livewire\Supir;

use Livewire\Component;
use App\Models\Delivery;
use App\Models\DeliveryTracking;
use Carbon\Carbon;

class TrackingSystem extends Component
{
    public $currentDelivery;
    public $isTracking = false;
    public $currentLocation = [];
    public $trackingHistory = [];
    
    // Manual location update
    public $showLocationModal = false;
    public $manualLatitude;
    public $manualLongitude;
    public $locationNotes;

    protected $listeners = [
        'locationUpdated' => 'handleLocationUpdate',
        'startTracking' => 'startTracking',
        'stopTracking' => 'stopTracking',
    ];

    public function rules()
    {
        return [
            'manualLatitude' => 'required|numeric|between:-90,90',
            'manualLongitude' => 'required|numeric|between:-180,180',
            'locationNotes' => 'nullable|string|max:255',
        ];
    }

    public function mount()
    {
        $this->loadCurrentDelivery();
        $this->loadTrackingHistory();
    }

    public function loadCurrentDelivery()
    {
        $this->currentDelivery = Delivery::where('driver_id', auth()->id())
                                        ->whereIn('status', ['k3_checked', 'in_progress'])
                                        ->with(['order.customer'])
                                        ->first();
        
        if ($this->currentDelivery && $this->currentDelivery->status === 'in_progress') {
            $this->isTracking = true;
        }
    }

    public function loadTrackingHistory()
    {
        if ($this->currentDelivery) {
            $this->trackingHistory = DeliveryTracking::where('delivery_id', $this->currentDelivery->id)
                                                   ->orderBy('created_at', 'desc')
                                                   ->limit(10)
                                                   ->get()
                                                   ->toArray();
        }
    }

    public function startTracking()
    {
        if (!$this->currentDelivery) {
            session()->flash('error', 'Tidak ada pengiriman aktif untuk dilacak.');
            return;
        }

        try {
            // Update delivery status to in_progress
            $this->currentDelivery->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            $this->isTracking = true;
            $this->loadCurrentDelivery();
            
            session()->flash('success', 'Tracking dimulai! Pastikan GPS aktif.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memulai tracking: ' . $e->getMessage());
        }
    }

    public function stopTracking()
    {
        if (!$this->currentDelivery || !$this->isTracking) {
            session()->flash('error', 'Tidak ada tracking aktif.');
            return;
        }

        try {
            $this->isTracking = false;
            session()->flash('success', 'Tracking dihentikan.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghentikan tracking: ' . $e->getMessage());
        }
    }

    public function handleLocationUpdate($latitude, $longitude, $accuracy = null)
    {
        if (!$this->currentDelivery || !$this->isTracking) {
            return;
        }

        try {
            // Save tracking point
            DeliveryTracking::create([
                'delivery_id' => $this->currentDelivery->id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy' => $accuracy,
                'recorded_at' => now(),
                'notes' => 'Auto GPS update',
            ]);

            // Update current location
            $this->currentLocation = [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy' => $accuracy,
                'timestamp' => now()->format('H:i:s'),
            ];

            // Reload tracking history
            $this->loadTrackingHistory();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan lokasi: ' . $e->getMessage());
        }
    }

    public function openLocationModal()
    {
        $this->resetLocationForm();
        $this->showLocationModal = true;
    }

    public function resetLocationForm()
    {
        $this->manualLatitude = '';
        $this->manualLongitude = '';
        $this->locationNotes = '';
    }

    public function updateLocationManually()
    {
        $this->validate();

        if (!$this->currentDelivery) {
            session()->flash('error', 'Tidak ada pengiriman aktif.');
            return;
        }

        try {
            // Save manual tracking point
            DeliveryTracking::create([
                'delivery_id' => $this->currentDelivery->id,
                'latitude' => $this->manualLatitude,
                'longitude' => $this->manualLongitude,
                'recorded_at' => now(),
                'notes' => $this->locationNotes ?: 'Manual location update',
            ]);

            // Update current location
            $this->currentLocation = [
                'latitude' => $this->manualLatitude,
                'longitude' => $this->manualLongitude,
                'timestamp' => now()->format('H:i:s'),
            ];

            $this->showLocationModal = false;
            $this->resetLocationForm();
            $this->loadTrackingHistory();
            
            session()->flash('success', 'Lokasi berhasil diupdate secara manual!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal update lokasi: ' . $e->getMessage());
        }
    }

    public function refreshLocation()
    {
        $this->dispatch('requestLocation');
    }

    public function getDeliveryStatsProperty()
    {
        if (!$this->currentDelivery) {
            return [
                'total_distance' => 0,
                'tracking_points' => 0,
                'duration' => '00:00:00',
            ];
        }

        $trackingPoints = DeliveryTracking::where('delivery_id', $this->currentDelivery->id)->count();
        
        $duration = '00:00:00';
        if ($this->currentDelivery->started_at) {
            $start = Carbon::parse($this->currentDelivery->started_at);
            $end = $this->currentDelivery->delivered_at ? 
                   Carbon::parse($this->currentDelivery->delivered_at) : 
                   now();
            $duration = $start->diff($end)->format('%H:%I:%S');
        }

        return [
            'total_distance' => $this->currentDelivery->actual_distance ?? 0,
            'tracking_points' => $trackingPoints,
            'duration' => $duration,
        ];
    }

    public function render()
    {
        return view('livewire.supir.tracking-system', [
            'deliveryStats' => $this->deliveryStats,
        ]);
    }
}
