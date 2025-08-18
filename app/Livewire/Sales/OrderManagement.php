<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;

class OrderManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $customerFilter = '';

    // Form properties
    public $showModal = false;
    public $editMode = false;
    public $orderId;
    public $customer_id;
    public $catatan;
    public $orderItems = [];
    public $viewOrder = null;

    // Order item form
    public $selectedProduct;
    public $quantity = 1;

    protected $paginationTheme = 'bootstrap';

    public function rules()
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'catatan' => 'nullable|string',
            'orderItems' => 'required|array|min:1',
            'orderItems.*.product_id' => 'required|exists:products,id',
            'orderItems.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function mount()
    {
        $this->resetOrderItems();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingCustomerFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editMode = false;
        $this->dispatch('show-modal');
    }

    public function openEditModal($orderId)
    {
        $order = Order::where('sales_id', auth()->id())
                     ->with(['orderItems.product'])
                     ->findOrFail($orderId);

        if (!$order->canBeEdited()) {
            session()->flash('error', 'Order tidak dapat diedit karena sudah dikonfirmasi!');
            return;
        }

        $this->orderId = $order->id;
        $this->customer_id = $order->customer_id;
        $this->catatan = $order->catatan;

        // Load order items
        $this->orderItems = $order->orderItems->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product->nama_barang,
                'quantity' => $item->jumlah_pesan,
                'price' => $item->harga_satuan,
                'total' => $item->total_harga,
            ];
        })->toArray();

        $this->showModal = true;
        $this->editMode = true;
        $this->dispatch('show-modal');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('close-order-modal');
    }

    public function viewOrder($orderId)
    {
        $this->viewOrder = Order::with(['customer', 'orderItems.product'])
                                ->where('sales_id', auth()->id())
                                ->findOrFail($orderId);
    }

    public function closeViewModal()
    {
        $this->viewOrder = null;
    }

    public function resetForm()
    {
        $this->reset(['orderId', 'customer_id', 'catatan', 'selectedProduct', 'quantity']);
        $this->resetOrderItems();
    }

    public function resetOrderItems()
    {
        $this->orderItems = [];
    }

    public function addProduct()
    {
        if (!$this->selectedProduct || $this->quantity < 1) {
            session()->flash('error', 'Pilih produk dan masukkan jumlah yang valid!');
            return;
        }

        $product = Product::find($this->selectedProduct);
        if (!$product) {
            session()->flash('error', 'Produk tidak ditemukan!');
            return;
        }

        // Check if product already exists in order
        $existingIndex = collect($this->orderItems)->search(function ($item) {
            return $item['product_id'] == $this->selectedProduct;
        });

        if ($existingIndex !== false) {
            // Update quantity if product already exists
            $this->orderItems[$existingIndex]['quantity'] += $this->quantity;
            $this->orderItems[$existingIndex]['total'] = $this->orderItems[$existingIndex]['quantity'] * $this->orderItems[$existingIndex]['price'];
        } else {
            // Add new product
            $this->orderItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->nama_barang,
                'quantity' => $this->quantity,
                'price' => $product->harga_jual,
                'total' => $this->quantity * $product->harga_jual,
            ];
        }

        // Reset form
        $this->selectedProduct = null;
        $this->quantity = 1;
    }

    public function removeProduct($index)
    {
        unset($this->orderItems[$index]);
        $this->orderItems = array_values($this->orderItems); // Re-index array
    }

    public function updateQuantity($index, $quantity)
    {
        if ($quantity < 1) {
            $this->removeProduct($index);
            return;
        }

        $this->orderItems[$index]['quantity'] = $quantity;
        $this->orderItems[$index]['total'] = $quantity * $this->orderItems[$index]['price'];
    }

    public function save()
    {
        $this->validate();

        if (empty($this->orderItems)) {
            session()->flash('error', 'Tambahkan minimal satu produk!');
            return;
        }

        try {
            if ($this->editMode) {
                $order = Order::where('sales_id', auth()->id())
                             ->findOrFail($this->orderId);

                if (!$order->canBeEdited()) {
                    session()->flash('error', 'Order tidak dapat diedit!');
                    return;
                }

                // Update order
                $order->update([
                    'customer_id' => $this->customer_id,
                    'catatan' => $this->catatan,
                    'total_amount' => collect($this->orderItems)->sum('total'),
                ]);

                // Delete existing order items
                $order->orderItems()->delete();

                session()->flash('success', 'Order berhasil diperbarui!');
            } else {
                // Create new order
                $order = Order::create([
                    'nomor_order' => $this->generateOrderNumber(),
                    'sales_id' => auth()->id(),
                    'customer_id' => $this->customer_id,
                    'status' => 'pending',
                    'total_amount' => collect($this->orderItems)->sum('total'),
                    'catatan' => $this->catatan,
                ]);

                session()->flash('success', 'Order berhasil dibuat!');
            }

            // Create order items
            foreach ($this->orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'jumlah_pesan' => $item['quantity'],
                    'harga_satuan' => $item['price'],
                    'total_harga' => $item['total'],
                    'status' => 'pending',
                ]);
            }

            $this->closeModal();

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function cancelOrder($orderId)
    {
        try {
            $order = Order::where('sales_id', auth()->id())
                         ->findOrFail($orderId);

            if (!$order->canBeCancelled()) {
                session()->flash('error', 'Order tidak dapat dibatalkan!');
                return;
            }

            $order->update(['status' => 'cancelled']);
            session()->flash('success', 'Order berhasil dibatalkan!');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function generateOrderNumber()
    {
        $date = now()->format('Ymd');
        $count = Order::whereDate('created_at', now())->count() + 1;
        return 'ORD-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        $orders = Order::where('sales_id', auth()->id())
            ->with(['customer', 'orderItems.product'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nomor_order', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', function ($customer) {
                          $customer->where('nama_toko', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->customerFilter, function ($query) {
                $query->where('customer_id', $this->customerFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $customers = Customer::where('sales_id', auth()->id())
                           ->where('is_active', true)
                           ->orderBy('nama_toko')
                           ->get();

        $products = Product::where('is_active', true)
                          ->orderBy('nama_barang')
                          ->get();

        return view('livewire.sales.order-management', [
            'orders' => $orders,
            'customers' => $customers,
            'products' => $products,
        ]);
    }
}
