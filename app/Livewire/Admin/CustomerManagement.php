<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use App\Models\User;

class CustomerManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $salesFilter = '';
    public $perPage = 15;

    // Customer form
    public $showCustomerModal = false;
    public $customerId;
    public $nama_toko;
    public $nama_pemilik;
    public $alamat;
    public $telepon;
    public $email;
    public $sales_id;
    public $credit_limit;
    public $is_active = true;
    public $latitude;
    public $longitude;

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'salesFilter' => ['except' => ''],
    ];

    public function rules()
    {
        return [
            'nama_toko' => 'required|string|max:255',
            'nama_pemilik' => 'required|string|max:255',
            'alamat' => 'required|string',
            'telepon' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'sales_id' => 'required|exists:users,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
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

    public function updatingSalesFilter()
    {
        $this->resetPage();
    }

    public function openCustomerModal($customerId = null)
    {
        $this->resetCustomerForm();
        
        if ($customerId) {
            $customer = Customer::find($customerId);
            $this->customerId = $customer->id;
            $this->nama_toko = $customer->nama_toko;
            $this->nama_pemilik = $customer->nama_pemilik;
            $this->alamat = $customer->alamat;
            $this->telepon = $customer->telepon;
            $this->email = $customer->email;
            $this->sales_id = $customer->sales_id;
            $this->credit_limit = $customer->credit_limit;
            $this->is_active = $customer->is_active;
            $this->latitude = $customer->latitude;
            $this->longitude = $customer->longitude;
        }
        
        $this->showCustomerModal = true;
    }

    public function resetCustomerForm()
    {
        $this->reset([
            'customerId', 'nama_toko', 'nama_pemilik', 'alamat', 'telepon',
            'email', 'sales_id', 'credit_limit', 'latitude', 'longitude'
        ]);
        $this->is_active = true;
    }

    public function saveCustomer()
    {
        $this->validate();

        try {
            $data = [
                'nama_toko' => $this->nama_toko,
                'nama_pemilik' => $this->nama_pemilik,
                'alamat' => $this->alamat,
                'telepon' => $this->telepon,
                'email' => $this->email,
                'sales_id' => $this->sales_id,
                'credit_limit' => $this->credit_limit,
                'is_active' => $this->is_active,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ];

            if ($this->customerId) {
                Customer::find($this->customerId)->update($data);
                session()->flash('success', 'Customer berhasil diupdate!');
            } else {
                Customer::create($data);
                session()->flash('success', 'Customer berhasil ditambahkan!');
            }

            $this->showCustomerModal = false;
            $this->resetCustomerForm();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan customer: ' . $e->getMessage());
        }
    }

    public function deleteCustomer($customerId)
    {
        try {
            Customer::find($customerId)->delete();
            session()->flash('success', 'Customer berhasil dihapus!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus customer: ' . $e->getMessage());
        }
    }

    public function toggleStatus($customerId)
    {
        try {
            $customer = Customer::find($customerId);
            $customer->update(['is_active' => !$customer->is_active]);
            
            $status = $customer->is_active ? 'diaktifkan' : 'dinonaktifkan';
            session()->flash('success', "Customer berhasil {$status}!");
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengubah status customer: ' . $e->getMessage());
        }
    }

    public function getCustomersProperty()
    {
        $query = Customer::with(['sales'])
            ->when($this->search, function ($query) {
                $query->where('nama_toko', 'like', '%' . $this->search . '%')
                      ->orWhere('nama_pemilik', 'like', '%' . $this->search . '%')
                      ->orWhere('telepon', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter);
            })
            ->when($this->salesFilter, function ($query) {
                $query->where('sales_id', $this->salesFilter);
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

    public function render()
    {
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('is_active', true)->count();
        $inactiveCustomers = Customer::where('is_active', false)->count();
        $totalCreditLimit = Customer::sum('credit_limit');

        return view('livewire.admin.customer-management', [
            'customers' => $this->customers,
            'salesUsers' => $this->salesUsers,
            'totalCustomers' => $totalCustomers,
            'activeCustomers' => $activeCustomers,
            'inactiveCustomers' => $inactiveCustomers,
            'totalCreditLimit' => $totalCreditLimit,
        ]);
    }
}
