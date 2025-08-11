<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Backorder;
use App\Models\Product;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;

class BackorderManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $productFilter = '';
    public $customerFilter = '';
    public $priorityFilter = '';
    public $dateFilter = '';
    public $perPage = 15;

    // Backorder form
    public $showBackorderModal = false;
    public $backorderId;
    public $product_id;
    public $customer_id;
    public $quantity_requested;
    public $priority = 'medium';
    public $expected_date;
    public $notes;

    // View backorder modal
    public $showViewModal = false;
    public $viewBackorder;

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'productFilter' => ['except' => ''],
        'customerFilter' => ['except' => ''],
        'priorityFilter' => ['except' => ''],
        'dateFilter' => ['except' => ''],
    ];

    public function rules()
    {
        return [
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'required|exists:customers,id',
            'quantity_requested' => 'required|integer|min:1',
            'priority' => 'required|in:low,medium,high,urgent',
            'expected_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:500',
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

    public function updatingProductFilter()
    {
        $this->resetPage();
    }

    public function updatingCustomerFilter()
    {
        $this->resetPage();
    }

    public function updatingPriorityFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function openBackorderModal($backorderId = null)
    {
        $this->resetBackorderForm();
        
        if ($backorderId) {
            $backorder = Backorder::find($backorderId);
            $this->backorderId = $backorder->id;
            $this->product_id = $backorder->product_id;
            $this->customer_id = $backorder->customer_id;
            $this->quantity_requested = $backorder->quantity_requested;
            $this->priority = $backorder->priority;
            $this->expected_date = $backorder->expected_date?->format('Y-m-d');
            $this->notes = $backorder->notes;
        }
        
        $this->showBackorderModal = true;
    }

    public function resetBackorderForm()
    {
        $this->reset([
            'backorderId', 'product_id', 'customer_id', 'quantity_requested',
            'expected_date', 'notes'
        ]);
        $this->priority = 'medium';
    }

    public function saveBackorder()
    {
        $this->validate();

        try {
            $data = [
                'product_id' => $this->product_id,
                'customer_id' => $this->customer_id,
                'quantity_requested' => $this->quantity_requested,
                'priority' => $this->priority,
                'expected_date' => $this->expected_date,
                'notes' => $this->notes,
                'created_by' => auth()->id(),
            ];

            if ($this->backorderId) {
                Backorder::find($this->backorderId)->update($data);
                session()->flash('success', 'Backorder berhasil diupdate!');
            } else {
                $data['status'] = 'pending';
                Backorder::create($data);
                session()->flash('success', 'Backorder berhasil ditambahkan!');
            }

            $this->showBackorderModal = false;
            $this->resetBackorderForm();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan backorder: ' . $e->getMessage());
        }
    }

    public function viewBackorder($backorderId)
    {
        $this->viewBackorder = Backorder::with(['product', 'customer', 'createdBy'])->find($backorderId);
        $this->showViewModal = true;
    }

    public function updateBackorderStatus($backorderId, $status)
    {
        try {
            $backorder = Backorder::find($backorderId);
            $backorder->update(['status' => $status]);
            
            // Update timestamps based on status
            switch ($status) {
                case 'processing':
                    $backorder->update(['processed_at' => now()]);
                    break;
                case 'fulfilled':
                    $backorder->update(['fulfilled_at' => now()]);
                    break;
                case 'cancelled':
                    $backorder->update(['cancelled_at' => now()]);
                    break;
            }
            
            session()->flash('success', "Status backorder berhasil diubah ke {$status}!");
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengubah status backorder: ' . $e->getMessage());
        }
    }

    public function fulfillBackorder($backorderId)
    {
        try {
            $backorder = Backorder::with('product')->find($backorderId);
            
            if ($backorder->product->stok_tersedia < $backorder->quantity_requested) {
                session()->flash('error', 'Stok tidak mencukupi untuk memenuhi backorder!');
                return;
            }
            
            // Reduce stock
            $backorder->product->decrement('stok_tersedia', $backorder->quantity_requested);
            
            // Update backorder status
            $backorder->update([
                'status' => 'fulfilled',
                'quantity_fulfilled' => $backorder->quantity_requested,
                'fulfilled_at' => now(),
            ]);
            
            session()->flash('success', 'Backorder berhasil dipenuhi dan stok telah dikurangi!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memenuhi backorder: ' . $e->getMessage());
        }
    }

    public function deleteBackorder($backorderId)
    {
        try {
            Backorder::find($backorderId)->delete();
            session()->flash('success', 'Backorder berhasil dihapus!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus backorder: ' . $e->getMessage());
        }
    }

    public function getBackordersProperty()
    {
        $query = Backorder::with(['product', 'customer'])
            ->when($this->search, function ($query) {
                $query->whereHas('product', function ($q) {
                    $q->where('nama_barang', 'like', '%' . $this->search . '%')
                      ->orWhere('kode_item', 'like', '%' . $this->search . '%');
                })->orWhereHas('customer', function ($q) {
                    $q->where('nama_toko', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->productFilter, function ($query) {
                $query->where('product_id', $this->productFilter);
            })
            ->when($this->customerFilter, function ($query) {
                $query->where('customer_id', $this->customerFilter);
            })
            ->when($this->priorityFilter, function ($query) {
                $query->where('priority', $this->priorityFilter);
            })
            ->when($this->dateFilter, function ($query) {
                $query->whereDate('created_at', $this->dateFilter);
            })
            ->latest();

        return $query->paginate($this->perPage);
    }

    public function getProductsProperty()
    {
        return Product::where('is_active', true)
                     ->orderBy('nama_barang')
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
        $totalBackorders = Backorder::count();
        $pendingBackorders = Backorder::where('status', 'pending')->count();
        $processingBackorders = Backorder::where('status', 'processing')->count();
        $fulfilledBackorders = Backorder::where('status', 'fulfilled')->count();
        $cancelledBackorders = Backorder::where('status', 'cancelled')->count();
        
        $urgentBackorders = Backorder::where('priority', 'urgent')
                                   ->where('status', 'pending')
                                   ->count();
        
        $overdueBackorders = Backorder::where('expected_date', '<', now())
                                    ->whereIn('status', ['pending', 'processing'])
                                    ->count();

        return view('livewire.admin.backorder-management', [
            'backorders' => $this->backorders,
            'products' => $this->products,
            'customers' => $this->customers,
            'totalBackorders' => $totalBackorders,
            'pendingBackorders' => $pendingBackorders,
            'processingBackorders' => $processingBackorders,
            'fulfilledBackorders' => $fulfilledBackorders,
            'cancelledBackorders' => $cancelledBackorders,
            'urgentBackorders' => $urgentBackorders,
            'overdueBackorders' => $overdueBackorders,
        ]);
    }
}
