<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Backorder;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;

class BackorderManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $productFilter = '';
    public $dateFilter = '';
    public $perPage = 15;

    // Backorder form
    public $showBackorderModal = false;
    public $backorderId;
    public $order_item_id;
    public $product_id;
    public $jumlah_backorder;
    public $expected_date;
    public $catatan;

    // View backorder modal
    public $showViewModal = false;
    public $viewBackorder;

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
            'order_item_id' => 'required|exists:order_items,id',
            'product_id' => 'required|exists:products,id',
            'jumlah_backorder' => 'required|integer|min:1',
            'expected_date' => 'nullable|date|after:today',
            'catatan' => 'nullable|string|max:500',
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

    public function openBackorderModal($backorderId = null)
    {
        $this->resetBackorderForm();

        if ($backorderId) {
            $backorder = Backorder::find($backorderId);
            $this->backorderId = $backorder->id;
            $this->order_item_id = $backorder->order_item_id;
            $this->product_id = $backorder->product_id;
            $this->jumlah_backorder = $backorder->jumlah_backorder;
            $this->expected_date = $backorder->expected_date?->format('Y-m-d');
            $this->catatan = $backorder->catatan;
        }

        $this->showBackorderModal = true;
    }

    public function resetBackorderForm()
    {
        $this->reset([
            'backorderId', 'order_item_id', 'product_id', 'jumlah_backorder',
            'expected_date', 'catatan'
        ]);
    }

    public function saveBackorder()
    {
        $this->validate();

        try {
            $data = [
                'order_item_id' => $this->order_item_id,
                'product_id' => $this->product_id,
                'jumlah_backorder' => $this->jumlah_backorder,
                'expected_date' => $this->expected_date,
                'catatan' => $this->catatan,
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

            if ($backorder->product->stok_tersedia < $backorder->jumlah_backorder) {
                session()->flash('error', 'Stok tidak mencukupi untuk memenuhi backorder!');
                return;
            }

            // Reduce stock
            $backorder->product->decrement('stok_tersedia', $backorder->jumlah_backorder);

            // Update backorder status
            $backorder->update([
                'status' => 'fulfilled',
                'jumlah_terpenuhi' => $backorder->jumlah_backorder,
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
        $query = Backorder::with(['product', 'orderItem.order.customer'])
            ->when($this->search, function ($query) {
                $query->whereHas('product', function ($q) {
                    $q->where('nama_barang', 'like', '%' . $this->search . '%')
                      ->orWhere('kode_item', 'like', '%' . $this->search . '%');
                })->orWhereHas('orderItem.order.customer', function ($q) {
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
                $query->whereDate('expected_date', $this->dateFilter);
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

    public function getOrderItemsProperty()
    {
        return OrderItem::with(['order.customer', 'product'])
                       ->where('status', 'backorder')
                       ->orderBy('created_at', 'desc')
                       ->get();
    }

    public function render()
    {
        $totalBackorders = Backorder::count();
        $pendingBackorders = Backorder::where('status', 'pending')->count();
        $processingBackorders = Backorder::where('status', 'processing')->count();
        $fulfilledBackorders = Backorder::where('status', 'fulfilled')->count();
        $cancelledBackorders = Backorder::where('status', 'cancelled')->count();

        $partialBackorders = Backorder::where('status', 'partial')->count();

        $overdueBackorders = Backorder::where('expected_date', '<', now())
                                    ->whereIn('status', ['pending', 'partial'])
                                    ->count();

        return view('livewire.admin.backorder-management', [
            'backorders' => $this->backorders,
            'products' => $this->products,
            'orderItems' => $this->orderItems,
            'totalBackorders' => $totalBackorders,
            'pendingBackorders' => $pendingBackorders,
            'partialBackorders' => $partialBackorders,
            'fulfilledBackorders' => $fulfilledBackorders,
            'cancelledBackorders' => $cancelledBackorders,
            'overdueBackorders' => $overdueBackorders,
        ]);
    }
}
