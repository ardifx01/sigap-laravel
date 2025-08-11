<?php

namespace App\Livewire\Gudang;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Backorder;
use App\Models\Product;
use App\Models\OrderItem;
use Carbon\Carbon;

class BackorderManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $productFilter = '';
    public $dateFilter = '';
    public $perPage = 15;

    // Fulfill backorder modal
    public $showFulfillModal = false;
    public $selectedBackorder;
    public $fulfillQuantity = 0;
    public $fulfillNotes = '';

    // Update backorder modal
    public $showUpdateModal = false;
    public $updateBackorder;
    public $expectedDate = '';
    public $updateNotes = '';

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'productFilter' => ['except' => ''],
        'dateFilter' => ['except' => ''],
    ];

    public function rules()
    {
        return [
            'fulfillQuantity' => 'required|integer|min:1|max:' . ($this->selectedBackorder->jumlah_backorder ?? 1),
            'fulfillNotes' => 'nullable|string|max:500',
            'expectedDate' => 'required|date|after:today',
            'updateNotes' => 'nullable|string|max:500',
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

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function openFulfillModal($backorderId)
    {
        $this->selectedBackorder = Backorder::with(['product', 'orderItem.order.customer'])->find($backorderId);
        $this->fulfillQuantity = $this->selectedBackorder->jumlah_backorder;
        $this->fulfillNotes = '';
        $this->showFulfillModal = true;
    }

    public function fulfillBackorder()
    {
        $this->validate([
            'fulfillQuantity' => 'required|integer|min:1|max:' . $this->selectedBackorder->jumlah_backorder,
            'fulfillNotes' => 'nullable|string|max:500',
        ]);

        try {
            // Update backorder
            $this->selectedBackorder->update([
                'jumlah_terpenuhi' => $this->selectedBackorder->jumlah_terpenuhi + $this->fulfillQuantity,
                'status' => $this->fulfillQuantity >= $this->selectedBackorder->jumlah_backorder ? 'fulfilled' : 'partial',
                'fulfilled_at' => $this->fulfillQuantity >= $this->selectedBackorder->jumlah_backorder ? now() : null,
                'catatan' => $this->fulfillNotes,
            ]);

            // Update product stock
            $product = $this->selectedBackorder->product;
            $product->decrement('stok_tersedia', $this->fulfillQuantity);

            // Update order item
            $orderItem = $this->selectedBackorder->orderItem;
            $orderItem->increment('jumlah_tersedia', $this->fulfillQuantity);
            $orderItem->decrement('jumlah_backorder', $this->fulfillQuantity);

            // Log inventory change
            \App\Models\InventoryLog::create([
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => $this->fulfillQuantity,
                'reason' => 'Backorder fulfillment',
                'reference_type' => 'App\Models\Backorder',
                'reference_id' => $this->selectedBackorder->id,
                'user_id' => auth()->id(),
                'notes' => $this->fulfillNotes,
            ]);

            $this->showFulfillModal = false;
            $this->reset(['fulfillQuantity', 'fulfillNotes']);
            
            session()->flash('success', 'Backorder berhasil dipenuhi!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memenuhi backorder: ' . $e->getMessage());
        }
    }

    public function openUpdateModal($backorderId)
    {
        $this->updateBackorder = Backorder::find($backorderId);
        $this->expectedDate = $this->updateBackorder->expected_date ? 
                             Carbon::parse($this->updateBackorder->expected_date)->format('Y-m-d') : 
                             now()->addDays(7)->format('Y-m-d');
        $this->updateNotes = $this->updateBackorder->catatan ?? '';
        $this->showUpdateModal = true;
    }

    public function updateBackorder()
    {
        $this->validate([
            'expectedDate' => 'required|date|after:today',
            'updateNotes' => 'nullable|string|max:500',
        ]);

        try {
            $this->updateBackorder->update([
                'expected_date' => $this->expectedDate,
                'catatan' => $this->updateNotes,
            ]);

            $this->showUpdateModal = false;
            $this->reset(['expectedDate', 'updateNotes']);
            
            session()->flash('success', 'Backorder berhasil diupdate!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal update backorder: ' . $e->getMessage());
        }
    }

    public function cancelBackorder($backorderId)
    {
        try {
            $backorder = Backorder::find($backorderId);
            
            if ($backorder && $backorder->status === 'pending') {
                $backorder->update(['status' => 'cancelled']);
                
                // Update order item - remove backorder quantity
                $orderItem = $backorder->orderItem;
                $orderItem->update([
                    'jumlah_backorder' => 0,
                    'status' => 'partial'
                ]);
                
                session()->flash('success', 'Backorder berhasil dibatalkan!');
            } else {
                session()->flash('error', 'Backorder tidak dapat dibatalkan!');
            }
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membatalkan backorder: ' . $e->getMessage());
        }
    }

    public function getBackordersProperty()
    {
        $query = Backorder::with(['product', 'orderItem.order.customer', 'orderItem.order.sales'])
            ->when($this->search, function ($query) {
                $query->whereHas('product', function ($q) {
                    $q->where('nama_barang', 'like', '%' . $this->search . '%')
                      ->orWhere('kode_item', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('orderItem.order.customer', function ($q) {
                    $q->where('nama_toko', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->productFilter, function ($query) {
                $query->where('product_id', $this->productFilter);
            })
            ->when($this->dateFilter, function ($query) {
                $query->whereDate('created_at', $this->dateFilter);
            })
            ->latest();

        return $query->paginate($this->perPage);
    }

    public function getProductsProperty()
    {
        return Product::whereHas('backorders')
                     ->orderBy('nama_barang')
                     ->get();
    }

    public function render()
    {
        $pendingCount = Backorder::where('status', 'pending')->count();
        $partialCount = Backorder::where('status', 'partial')->count();
        $fulfilledCount = Backorder::where('status', 'fulfilled')->count();
        $totalValue = Backorder::where('status', 'pending')
                              ->join('products', 'backorders.product_id', '=', 'products.id')
                              ->sum(\DB::raw('backorders.jumlah_backorder * products.harga_jual'));

        return view('livewire.gudang.backorder-management', [
            'backorders' => $this->backorders,
            'products' => $this->products,
            'pendingCount' => $pendingCount,
            'partialCount' => $partialCount,
            'fulfilledCount' => $fulfilledCount,
            'totalValue' => $totalValue,
        ]);
    }
}
