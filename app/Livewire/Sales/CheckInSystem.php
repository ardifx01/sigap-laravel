<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\CheckIn;
use App\Models\Customer;

class CheckInSystem extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $dateFilter = '';

    // Check-in form
    public $showCheckInModal = false;
    public $customer_id;
    public $latitude;
    public $longitude;
    public $foto_selfie;
    public $catatan;
    public $locationAccuracy;
    public $isLocationValid = false;

    // View check-in modal
    public $showViewModal = false;
    public $viewCheckIn;

    protected $paginationTheme = 'bootstrap';

    public function rules()
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'foto_selfie' => 'required|image|max:2048',
            'catatan' => 'nullable|string|max:500',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function openCheckInModal()
    {
        $this->resetCheckInForm();
        $this->showCheckInModal = true;
    }

    public function closeCheckInModal()
    {
        $this->showCheckInModal = false;
        $this->resetCheckInForm();
    }

    public function resetCheckInForm()
    {
        $this->reset([
            'customer_id', 'latitude', 'longitude', 'foto_selfie',
            'catatan', 'locationAccuracy', 'isLocationValid'
        ]);
    }

    public function getCurrentLocation()
    {
        $this->dispatch('getCurrentLocation');
    }

    #[\Livewire\Attributes\On('setLocation')]
    public function setLocation($latitude, $longitude, $accuracy = null)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->locationAccuracy = $accuracy;
        $this->isLocationValid = true;

        session()->flash('success', 'Lokasi berhasil diambil! Akurasi: ' . round($accuracy ?? 0) . ' meter');
    }

    #[\Livewire\Attributes\On('locationError')]
    public function locationError($message)
    {
        session()->flash('error', 'Error GPS: ' . $message);
    }

    public function checkIn()
    {
        $this->validate();

        try {
            // Verify customer belongs to current sales
            $customer = Customer::where('id', $this->customer_id)
                              ->where('sales_id', auth()->id())
                              ->first();

            if (!$customer) {
                session()->flash('error', 'Pelanggan tidak ditemukan atau bukan milik Anda!');
                return;
            }

            // Check if already checked in today
            $existingCheckIn = CheckIn::where('sales_id', auth()->id())
                                    ->where('customer_id', $this->customer_id)
                                    ->whereDate('checked_in_at', today())
                                    ->first();

            if ($existingCheckIn) {
                session()->flash('error', 'Anda sudah check-in ke toko ini hari ini!');
                return;
            }

            // Create check-in record
            $checkIn = CheckIn::create([
                'sales_id' => auth()->id(),
                'customer_id' => $this->customer_id,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'catatan' => $this->catatan,
                'checked_in_at' => now(),
            ]);

            // Upload selfie photo
            if ($this->foto_selfie) {
                $checkIn->addMediaFromDisk($this->foto_selfie->getRealPath())
                    ->usingName('Check-in Selfie - ' . $customer->nama_toko)
                    ->toMediaCollection('selfie_photos');
            }

            $this->closeCheckInModal();
            session()->flash('success', 'Check-in berhasil! Selamat bekerja.');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function viewCheckIn($checkInId)
    {
        $this->viewCheckIn = CheckIn::with(['customer', 'sales'])
                                  ->where('sales_id', auth()->id())
                                  ->findOrFail($checkInId);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewCheckIn = null;
    }

    public function deleteCheckIn($checkInId)
    {
        try {
            $checkIn = CheckIn::where('sales_id', auth()->id())
                             ->findOrFail($checkInId);

            // Only allow deletion if check-in is from today
            if (!$checkIn->checked_in_at->isToday()) {
                session()->flash('error', 'Hanya check-in hari ini yang dapat dihapus!');
                return;
            }

            $checkIn->delete();
            session()->flash('success', 'Check-in berhasil dihapus!');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $checkIns = CheckIn::where('sales_id', auth()->id())
            ->with(['customer'])
            ->when($this->search, function ($query) {
                $query->whereHas('customer', function ($q) {
                    $q->where('nama_toko', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->dateFilter, function ($query) {
                $query->whereDate('checked_in_at', $this->dateFilter);
            })
            ->orderBy('checked_in_at', 'desc')
            ->paginate(10);

        $customers = Customer::where('sales_id', auth()->id())
                           ->where('is_active', true)
                           ->orderBy('nama_toko')
                           ->get();

        $todayCheckIns = CheckIn::where('sales_id', auth()->id())
                              ->whereDate('checked_in_at', today())
                              ->count();

        $thisWeekCheckIns = CheckIn::where('sales_id', auth()->id())
                                 ->whereBetween('checked_in_at', [now()->startOfWeek(), now()->endOfWeek()])
                                 ->count();

        return view('livewire.sales.check-in-system', [
            'checkIns' => $checkIns,
            'customers' => $customers,
            'todayCheckIns' => $todayCheckIns,
            'thisWeekCheckIns' => $thisWeekCheckIns,
        ]);
    }
}
