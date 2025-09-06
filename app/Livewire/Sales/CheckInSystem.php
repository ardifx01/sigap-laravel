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
    public $selectedCheckIn;

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

    public function messages()
    {
        return [
            'customer_id.required' => 'Pilih toko terlebih dahulu.',
            'customer_id.exists' => 'Toko yang dipilih tidak valid.',
            'latitude.required' => 'Lokasi GPS diperlukan. Klik tombol GPS untuk mengambil lokasi.',
            'longitude.required' => 'Lokasi GPS diperlukan. Klik tombol GPS untuk mengambil lokasi.',
            'latitude.numeric' => 'Format latitude tidak valid.',
            'longitude.numeric' => 'Format longitude tidak valid.',
            'foto_selfie.required' => 'Foto selfie diperlukan untuk check-in.',
            'foto_selfie.image' => 'File harus berupa gambar.',
            'foto_selfie.max' => 'Ukuran foto maksimal 2MB.',
            'catatan.max' => 'Catatan maksimal 500 karakter.',
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
        try {
            \Log::info('Closing check-in modal - User: ' . auth()->id());
            $this->showCheckInModal = false;
            $this->resetCheckInForm();
            \Log::info('Check-in modal closed successfully');
        } catch (\Exception $e) {
            // Log error but don't show to user to prevent refresh
            \Log::error('Error closing check-in modal: ' . $e->getMessage());
            $this->showCheckInModal = false;
        }
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
        \Log::info('GPS location received', [
            'user_id' => auth()->id(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy
        ]);

        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->locationAccuracy = $accuracy;
        $this->isLocationValid = true;

        session()->flash('success', 'Lokasi berhasil diambil! Akurasi: ' . round($accuracy ?? 0) . ' meter');
    }

    #[\Livewire\Attributes\On('locationError')]
    public function locationError($message)
    {
        \Log::error('GPS location error', [
            'user_id' => auth()->id(),
            'error_message' => $message
        ]);

        session()->flash('error', 'Error GPS: ' . $message);
    }

    public function updatedCustomerId($value)
    {
        // This will be called when customer_id is updated
        $this->customer_id = $value;
    }

    public function checkIn()
    {
        \Log::info('Check-in process started', [
            'user_id' => auth()->id(),
            'customer_id' => $this->customer_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_location_valid' => $this->isLocationValid
        ]);

        try {
            $this->validate();
            \Log::info('Check-in validation passed');

            // Verify customer belongs to current sales
            $customer = Customer::where('id', $this->customer_id)
                              ->where('sales_id', auth()->id())
                              ->first();

            if (!$customer) {
                \Log::warning('Check-in failed: Customer not found or not owned by user', [
                    'user_id' => auth()->id(),
                    'customer_id' => $this->customer_id
                ]);
                session()->flash('error', 'Pelanggan tidak ditemukan atau bukan milik Anda!');
                return;
            }

            \Log::info('Customer verification passed', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->nama_toko
            ]);

            // Check if already checked in today
            $existingCheckIn = CheckIn::where('sales_id', auth()->id())
                                    ->where('customer_id', $this->customer_id)
                                    ->whereDate('checked_in_at', today())
                                    ->first();

            if ($existingCheckIn) {
                \Log::warning('Check-in failed: Already checked in today', [
                    'user_id' => auth()->id(),
                    'customer_id' => $this->customer_id,
                    'existing_checkin_id' => $existingCheckIn->id
                ]);
                session()->flash('error', 'Anda sudah check-in ke toko ini hari ini!');
                return;
            }

            \Log::info('Creating check-in record', [
                'sales_id' => auth()->id(),
                'customer_id' => $this->customer_id,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ]);

            // Upload selfie photo first
            $filename = null;
            if ($this->foto_selfie) {
                try {
                    \Log::info('Uploading selfie photo', [
                        'file_name' => $this->foto_selfie->getClientOriginalName(),
                        'file_size' => $this->foto_selfie->getSize(),
                        'mime_type' => $this->foto_selfie->getMimeType()
                    ]);

                    $filename = time() . '_' . $this->foto_selfie->getClientOriginalName();
                    $path = $this->foto_selfie->storeAs('selfie_photos', $filename, 'public');

                    if (!$path) {
                        throw new \Exception('Failed to store file');
                    }

                    \Log::info('Selfie photo uploaded successfully', [
                        'filename' => $filename,
                        'path' => $path
                    ]);
                } catch (\Exception $e) {
                    \Log::error('File upload failed', [
                        'error_message' => $e->getMessage(),
                        'file_name' => $this->foto_selfie->getClientOriginalName() ?? 'unknown'
                    ]);
                    session()->flash('error', 'Gagal upload foto: ' . $e->getMessage());
                    return;
                }
            }

            // Create check-in record with foto_selfie
            $checkIn = CheckIn::create([
                'sales_id' => auth()->id(),
                'customer_id' => $this->customer_id,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'catatan' => $this->catatan,
                'foto_selfie' => $filename,
                'checked_in_at' => now(),
            ]);

            \Log::info('Check-in record created successfully', [
                'checkin_id' => $checkIn->id,
                'has_photo' => !empty($filename)
            ]);

            \Log::info('Check-in process completed successfully', [
                'checkin_id' => $checkIn->id,
                'user_id' => auth()->id()
            ]);

            $this->closeCheckInModal();
            session()->flash('success', 'Check-in berhasil! Selamat bekerja.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Check-in validation failed', [
                'user_id' => auth()->id(),
                'errors' => $e->errors(),
                'input_data' => [
                    'customer_id' => $this->customer_id,
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                    'has_foto_selfie' => !empty($this->foto_selfie)
                ]
            ]);
            throw $e; // Re-throw validation exception to show field errors
        } catch (\Exception $e) {
            \Log::error('Check-in process failed with exception', [
                'user_id' => auth()->id(),
                'customer_id' => $this->customer_id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function viewCheckIn($checkInId)
    {
        $this->selectedCheckIn = CheckIn::with(['customer', 'sales'])
                                      ->where('sales_id', auth()->id())
                                      ->findOrFail($checkInId);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedCheckIn = null;
    }

    public function deleteCheckIn($checkInId)
    {
        \Log::info('Delete check-in process started', [
            'user_id' => auth()->id(),
            'checkin_id' => $checkInId
        ]);

        try {
            $checkIn = CheckIn::where('sales_id', auth()->id())
                             ->findOrFail($checkInId);

            \Log::info('Check-in found for deletion', [
                'checkin_id' => $checkIn->id,
                'customer_id' => $checkIn->customer_id,
                'checked_in_at' => $checkIn->checked_in_at
            ]);

            // Only allow deletion if check-in is from today
            if (!$checkIn->checked_in_at->isToday()) {
                \Log::warning('Delete check-in failed: Not from today', [
                    'checkin_id' => $checkIn->id,
                    'checked_in_at' => $checkIn->checked_in_at,
                    'is_today' => $checkIn->checked_in_at->isToday()
                ]);
                session()->flash('error', 'Hanya check-in hari ini yang dapat dihapus!');
                return;
            }

            $checkIn->delete();
            session()->flash('success', 'Check-in berhasil dihapus!');

            \Log::info('Check-in deleted successfully', [
                'checkin_id' => $checkInId,
                'user_id' => auth()->id()
            ]);

        } catch (\Exception $e) {
            \Log::error('Delete check-in failed with exception', [
                'user_id' => auth()->id(),
                'checkin_id' => $checkInId,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString()
            ]);
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
