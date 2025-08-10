<?php

namespace App\Livewire\Gudang;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Backorder;
use App\Models\InventoryLog;

class OrderConfirmation extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'pending';

    // Confirmation modal
    public $showConfirmModal = false;
    public $confirmOrderId;
    public $confirmItems = [];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openConfirmModal($orderId)
    {
        $order = Order::with(['orderItems.product', 'customer', 'sales'])
                     ->findOrFail($orderId);

        if ($order->status !== 'pending') {
            session()->flash('error', 'Order sudah dikonfirmasi atau dibatalkan!');
            return;
        }

        $this->confirmOrderId = $order->id;
        $this->confirmItems = $order->orderItems->map(function ($item) {
            $product = $item->product;
            $availableStock = $product->stok_tersedia;
            $requestedQty = $item->jumlah_pesan;

            return [
                'id' => $item->id,
                'product_id' => $product->id,
                'product_name' => $product->nama_barang,
                'requested_qty' => $requestedQty,
                'available_stock' => $availableStock,
                'confirmed_qty' => min($requestedQty, $availableStock),
                'backorder_qty' => max(0, $requestedQty - $availableStock),
                'price' => $item->harga_satuan,
                'status' => $availableStock >= $requestedQty ? 'available' : ($availableStock > 0 ? 'partial' : 'backorder'),
            ];
        })->toArray();

        $this->showConfirmModal = true;
    }

    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->reset(['confirmOrderId', 'confirmItems']);
    }

    public function updateConfirmedQty($index, $qty)
    {
        $item = &$this->confirmItems[$index];
        $maxQty = $item['available_stock'];

        $confirmedQty = max(0, min($qty, $maxQty));
        $item['confirmed_qty'] = $confirmedQty;
        $item['backorder_qty'] = max(0, $item['requested_qty'] - $confirmedQty);

        // Update status
        if ($confirmedQty == 0) {
            $item['status'] = 'backorder';
        } elseif ($confirmedQty < $item['requested_qty']) {
            $item['status'] = 'partial';
        } else {
            $item['status'] = 'available';
        }
    }

    public function confirmOrder()
    {
        try {
            $order = Order::findOrFail($this->confirmOrderId);

            if ($order->status !== 'pending') {
                session()->flash('error', 'Order sudah dikonfirmasi!');
                return;
            }

            // Update order status
            $order->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'confirmed_by' => auth()->id(),
            ]);

            // Process each order item
            foreach ($this->confirmItems as $confirmItem) {
                $orderItem = OrderItem::find($confirmItem['id']);
                $product = Product::find($confirmItem['product_id']);

                $confirmedQty = $confirmItem['confirmed_qty'];
                $backorderQty = $confirmItem['backorder_qty'];

                // Update order item
                $orderItem->update([
                    'jumlah_tersedia' => $confirmedQty,
                    'jumlah_backorder' => $backorderQty,
                    'status' => $confirmItem['status'],
                ]);

                // Reduce stock if confirmed
                if ($confirmedQty > 0) {
                    $newStock = $product->stok_tersedia - $confirmedQty;
                    $product->update(['stok_tersedia' => $newStock]);

                    // Log inventory change
                    InventoryLog::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'type' => 'out',
                        'quantity' => $confirmedQty,
                        'stock_before' => $product->stok_tersedia + $confirmedQty,
                        'stock_after' => $newStock,
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'notes' => "Order confirmation: {$order->nomor_order}",
                    ]);
                }

                // Create backorder if needed
                if ($backorderQty > 0) {
                    Backorder::create([
                        'order_item_id' => $orderItem->id,
                        'product_id' => $product->id,
                        'jumlah_backorder' => $backorderQty,
                        'status' => 'pending',
                    ]);
                }
            }

            // Check if order is ready for delivery
            $hasAvailableItems = collect($this->confirmItems)->where('confirmed_qty', '>', 0)->count() > 0;
            if ($hasAvailableItems) {
                $order->update(['status' => 'ready']);
            }

            $this->closeConfirmModal();
            session()->flash('success', 'Order berhasil dikonfirmasi!');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function rejectOrder($orderId)
    {
        try {
            $order = Order::findOrFail($orderId);

            if ($order->status !== 'pending') {
                session()->flash('error', 'Order tidak dapat ditolak!');
                return;
            }

            $order->update(['status' => 'cancelled']);
            session()->flash('success', 'Order berhasil ditolak!');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $orders = Order::with(['customer', 'sales', 'orderItems.product'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nomor_order', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', function ($customer) {
                          $customer->where('nama_toko', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('sales', function ($sales) {
                          $sales->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.gudang.order-confirmation', [
            'orders' => $orders
        ]);
    }
}
