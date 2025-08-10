<?php

namespace App\Livewire\Gudang;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Product;
use App\Models\InventoryLog;
use Illuminate\Validation\Rule;

class ProductManagement extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $statusFilter = '';
    public $jenisFilter = '';

    // Form properties
    public $showModal = false;
    public $editMode = false;
    public $productId;
    public $kode_item;
    public $nama_barang;
    public $keterangan;
    public $jenis = 'pack';
    public $harga_jual = 0;
    public $stok_tersedia = 0;
    public $stok_minimum = 0;
    public $foto_produk;
    public $is_active = true;

    // Stock adjustment
    public $showStockModal = false;
    public $stockProductId;
    public $stockType = 'in';
    public $stockQuantity;
    public $stockNotes;

    protected $paginationTheme = 'bootstrap';

    public function rules()
    {
        $rules = [
            'kode_item' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products')->ignore($this->productId)
            ],
            'nama_barang' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'jenis' => 'required|in:pack,ball,dus',
            'harga_jual' => 'required|numeric|min:0',
            'stok_tersedia' => 'required|integer|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'foto_produk' => $this->editMode ? 'nullable|image|max:2048' : 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ];

        return $rules;
    }

    public function stockRules()
    {
        return [
            'stockType' => 'required|in:in,out,adjustment',
            'stockQuantity' => 'required|integer|min:1',
            'stockNotes' => 'nullable|string|max:255',
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

    public function updatingJenisFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editMode = false;
    }

    public function openEditModal($productId)
    {
        $product = Product::findOrFail($productId);

        $this->productId = $product->id;
        $this->kode_item = $product->kode_item;
        $this->nama_barang = $product->nama_barang;
        $this->keterangan = $product->keterangan;
        $this->jenis = $product->jenis;
        $this->harga_jual = $product->harga_jual;
        $this->stok_tersedia = $product->stok_tersedia;
        $this->stok_minimum = $product->stok_minimum;
        $this->is_active = $product->is_active;

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
            'productId', 'kode_item', 'nama_barang', 'keterangan', 'jenis',
            'harga_jual', 'stok_tersedia', 'stok_minimum', 'foto_produk', 'is_active'
        ]);
        $this->is_active = true;
        $this->jenis = 'pack';
        $this->harga_jual = 0;
        $this->stok_tersedia = 0;
        $this->stok_minimum = 0;
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->editMode) {
                $product = Product::findOrFail($this->productId);
                $oldStock = $product->stok_tersedia;

                $productData = [
                    'kode_item' => $this->kode_item,
                    'nama_barang' => $this->nama_barang,
                    'keterangan' => $this->keterangan,
                    'jenis' => $this->jenis,
                    'harga_jual' => $this->harga_jual,
                    'stok_tersedia' => $this->stok_tersedia,
                    'stok_minimum' => $this->stok_minimum,
                    'is_active' => $this->is_active,
                ];

                $product->update($productData);

                // Log stock change if different
                if ($oldStock != $this->stok_tersedia) {
                    $this->logInventoryChange($product, $oldStock, $this->stok_tersedia, 'adjustment', 'Manual adjustment via product edit');
                }

                session()->flash('success', 'Data produk berhasil diperbarui!');
            } else {
                $product = Product::create([
                    'kode_item' => $this->kode_item,
                    'nama_barang' => $this->nama_barang,
                    'keterangan' => $this->keterangan,
                    'jenis' => $this->jenis,
                    'harga_jual' => $this->harga_jual,
                    'stok_tersedia' => $this->stok_tersedia,
                    'stok_minimum' => $this->stok_minimum,
                    'is_active' => $this->is_active,
                ]);

                // Log initial stock
                if ($this->stok_tersedia > 0) {
                    $this->logInventoryChange($product, 0, $this->stok_tersedia, 'in', 'Initial stock');
                }

                session()->flash('success', 'Data produk berhasil ditambahkan!');
            }

            // Handle product photo upload
            if ($this->foto_produk) {
                // Remove old photo if editing
                if ($this->editMode) {
                    $product->clearMediaCollection('product_photos');
                }

                $product->addMediaFromDisk($this->foto_produk->getRealPath())
                    ->usingName($this->nama_barang . ' - Photo')
                    ->toMediaCollection('product_photos');
            }

            $this->closeModal();

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function toggleStatus($productId)
    {
        $product = Product::findOrFail($productId);
        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Produk berhasil {$status}!");
    }

    public function deleteProduct($productId)
    {
        try {
            $product = Product::findOrFail($productId);
            $product->delete();
            session()->flash('success', 'Data produk berhasil dihapus!');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function openStockModal($productId)
    {
        $this->stockProductId = $productId;
        $this->resetStockForm();
        $this->showStockModal = true;
    }

    public function closeStockModal()
    {
        $this->showStockModal = false;
        $this->resetStockForm();
    }

    public function resetStockForm()
    {
        $this->reset(['stockType', 'stockQuantity', 'stockNotes']);
        $this->stockType = 'in';
    }

    public function adjustStock()
    {
        $this->validate($this->stockRules());

        try {
            $product = Product::findOrFail($this->stockProductId);
            $oldStock = $product->stok_tersedia;

            $newStock = match($this->stockType) {
                'in' => $oldStock + $this->stockQuantity,
                'out' => max(0, $oldStock - $this->stockQuantity),
                'adjustment' => $this->stockQuantity,
            };

            $product->update(['stok_tersedia' => $newStock]);

            // Log inventory change
            $this->logInventoryChange($product, $oldStock, $newStock, $this->stockType, $this->stockNotes);

            $this->closeStockModal();
            session()->flash('success', 'Stok berhasil diperbarui!');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function logInventoryChange($product, $oldStock, $newStock, $type, $notes = null)
    {
        InventoryLog::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => $type,
            'quantity' => abs($newStock - $oldStock),
            'stock_before' => $oldStock,
            'stock_after' => $newStock,
            'notes' => $notes,
        ]);
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('kode_item', 'like', '%' . $this->search . '%')
                      ->orWhere('nama_barang', 'like', '%' . $this->search . '%')
                      ->orWhere('keterangan', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter);
            })
            ->when($this->jenisFilter, function ($query) {
                $query->where('jenis', $this->jenisFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.gudang.product-management', [
            'products' => $products
        ]);
    }
}
