<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Customer;
use Illuminate\Validation\Rule;

class CustomerManagement extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $statusFilter = '';

    // Form properties
    public $showModal = false;
    public $editMode = false;
    public $customerId;
    public $currentCustomer;
    public $nama_toko;
    public $phone;
    public $alamat;
    public $foto_ktp;
    public $limit_hari_piutang = 30;
    public $limit_amount_piutang = 0;
    public $latitude;
    public $longitude;
    public $is_active = true;

    protected $paginationTheme = 'bootstrap';

    public function rules()
    {
        return [
            'nama_toko' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'alamat' => 'required|string',
            'foto_ktp' => $this->editMode ? 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048' : 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'limit_hari_piutang' => 'required|integer|min:1|max:365',
            'limit_amount_piutang' => 'required|numeric|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
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

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editMode = false;
    }

    public function openEditModal($customerId)
    {
        $customer = Customer::where('sales_id', auth()->id())
                           ->findOrFail($customerId);

        $this->currentCustomer = $customer;
        $this->customerId = $customer->id;
        $this->nama_toko = $customer->nama_toko;
        $this->phone = $customer->phone;
        $this->alamat = $customer->alamat;
        $this->limit_hari_piutang = $customer->limit_hari_piutang;
        $this->limit_amount_piutang = $customer->limit_amount_piutang;
        $this->latitude = $customer->latitude;
        $this->longitude = $customer->longitude;
        $this->is_active = $customer->is_active;

        $this->showModal = true;
        $this->editMode = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'customerId', 'currentCustomer', 'nama_toko', 'phone', 'alamat', 'foto_ktp',
            'limit_hari_piutang', 'limit_amount_piutang', 'latitude', 'longitude', 'is_active'
        ]);
        $this->is_active = true;
        $this->limit_hari_piutang = 30;
        $this->limit_amount_piutang = 0;
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->editMode) {
                $customer = Customer::where('sales_id', auth()->id())
                                  ->findOrFail($this->customerId);

                $customerData = [
                    'nama_toko' => $this->nama_toko,
                    'phone' => $this->phone,
                    'alamat' => $this->alamat,
                    'limit_hari_piutang' => $this->limit_hari_piutang,
                    'limit_amount_piutang' => $this->limit_amount_piutang,
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                    'is_active' => $this->is_active,
                ];

                $customer->update($customerData);
            } else {
                $customer = Customer::create([
                    'sales_id' => auth()->id(),
                    'nama_toko' => $this->nama_toko,
                    'phone' => $this->phone,
                    'alamat' => $this->alamat,
                    'limit_hari_piutang' => $this->limit_hari_piutang,
                    'limit_amount_piutang' => $this->limit_amount_piutang,
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                    'is_active' => $this->is_active,
                ]);
            }

            // Handle KTP photo upload
            if ($this->foto_ktp) {
                // Remove old photo if editing
                if ($this->editMode && $customer->foto_ktp) {
                    \Storage::disk('public')->delete('ktp_photos/' . $customer->foto_ktp);
                }

                // Store the uploaded file
                $fileName = 'ktp_' . $customer->id . '_' . time() . '.' . $this->foto_ktp->getClientOriginalExtension();
                $filePath = $this->foto_ktp->storeAs('ktp_photos', $fileName, 'public');
                
                // Update customer dengan path file
                $customer->update(['foto_ktp' => $fileName]);
            }

            $message = $this->editMode ? 'Data pelanggan berhasil diperbarui!' : 'Data pelanggan berhasil ditambahkan!';
            session()->flash('success', $message);
            
            $this->closeModal();

        } catch (\Exception $e) {
            \Log::error('Customer save error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'customer_data' => [
                    'nama_toko' => $this->nama_toko,
                    'phone' => $this->phone
                ]
            ]);
            session()->flash('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function toggleStatus($customerId)
    {
        $customer = Customer::where('sales_id', auth()->id())
                           ->findOrFail($customerId);
        $customer->update(['is_active' => !$customer->is_active]);

        $status = $customer->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Pelanggan berhasil {$status}!");
    }

    public function deleteCustomer($customerId)
    {
        try {
            $customer = Customer::where('sales_id', auth()->id())
                               ->findOrFail($customerId);

            $customer->delete();
            session()->flash('success', 'Data pelanggan berhasil dihapus!');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    public function render()
    {
        $customers = Customer::where('sales_id', auth()->id())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nama_toko', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('alamat', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.sales.customer-management', [
            'customers' => $customers
        ]);
    }
}
