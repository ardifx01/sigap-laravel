<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Product;

class ProductManagement extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $statusFilter = '';
    public $stockFilter = '';
    public $perPage = 15;

    // Product form
    public $showProductModal = false;
    public $productId;
    public $kode_item;
    public $nama_barang;
    public $keterangan;
    public $jenis = 'pack';
    public $harga_jual;
    public $stok_tersedia;
    public $stok_minimum;
    public $is_active = true;
    public $foto_produk;

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'stockFilter' => ['except' => ''],
    ];

    public function rules()
    {
        return [
            'kode_item' => 'required|string|max:50|unique:products,kode_item,' . $this->productId,
            'nama_barang' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'jenis' => 'required|in:pack,ball,dus',
            'harga_jual' => 'required|numeric|min:0',
            'stok_tersedia' => 'required|integer|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'foto_produk' => 'nullable|image|max:2048',
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

    public function updatingStockFilter()
    {
        $this->resetPage();
    }

    public function openProductModal($productId = null)
    {
        $this->resetProductForm();

        if ($productId) {
            $product = Product::find($productId);
            $this->productId = $product->id;
            $this->kode_item = $product->kode_item;
            $this->nama_barang = $product->nama_barang;
            $this->keterangan = $product->keterangan;
            $this->jenis = $product->jenis;
            $this->harga_jual = $product->harga_jual;
            $this->stok_tersedia = $product->stok_tersedia;
            $this->stok_minimum = $product->stok_minimum;
            $this->is_active = $product->is_active;
        }

        $this->showProductModal = true;
    }

    public function resetProductForm()
    {
        $this->reset([
            'productId', 'kode_item', 'nama_barang', 'keterangan',
            'harga_jual', 'stok_tersedia', 'stok_minimum', 'foto_produk'
        ]);
        $this->is_active = true;
        $this->jenis = 'pack';
    }

    public function saveProduct()
    {
        $this->validate();

        try {
            $data = [
                'kode_item' => $this->kode_item,
                'nama_barang' => $this->nama_barang,
                'keterangan' => $this->keterangan,
                'jenis' => $this->jenis,
                'harga_jual' => $this->harga_jual,
                'stok_tersedia' => $this->stok_tersedia,
                'stok_minimum' => $this->stok_minimum,
                'is_active' => $this->is_active,
            ];

            if ($this->productId) {
                $product = Product::find($this->productId);
                $product->update($data);

                if ($this->foto_produk) {
                    // Store as file path string
                    $path = $this->foto_produk->store('products', 'public');
                    $product->update(['foto_produk' => $path]);
                }

                session()->flash('success', 'Produk berhasil diupdate!');
            } else {
                $product = Product::create($data);

                if ($this->foto_produk) {
                    // Store as file path string
                    $path = $this->foto_produk->store('products', 'public');
                    $product->update(['foto_produk' => $path]);
                }

                session()->flash('success', 'Produk berhasil ditambahkan!');
            }

            $this->showProductModal = false;
            $this->resetProductForm();

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan produk: ' . $e->getMessage());
        }
    }

    public function deleteProduct($productId)
    {
        try {
            Product::find($productId)->delete();
            session()->flash('success', 'Produk berhasil dihapus!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    public function toggleStatus($productId)
    {
        try {
            $product = Product::find($productId);
            $product->update(['is_active' => !$product->is_active]);

            $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';
            session()->flash('success', "Produk berhasil {$status}!");
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengubah status produk: ' . $e->getMessage());
        }
    }

    public function adjustStock($productId, $adjustment, $reason = 'Manual adjustment')
    {
        try {
            $product = Product::find($productId);
            $stockBefore = $product->stok_tersedia;
            $newStock = $stockBefore + $adjustment;

            if ($newStock < 0) {
                session()->flash('error', 'Stok tidak boleh negatif!');
                return;
            }

            $product->update(['stok_tersedia' => $newStock]);

            // Log inventory change
            \App\Models\InventoryLog::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => $adjustment > 0 ? 'in' : 'out',
                'quantity' => abs($adjustment),
                'stock_before' => $stockBefore,
                'stock_after' => $newStock,
                'reference_type' => 'adjustment',
                'notes' => "Stock adjustment by admin: {$reason}",
            ]);

            session()->flash('success', 'Stok berhasil disesuaikan!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyesuaikan stok: ' . $e->getMessage());
        }
    }

    public function getProductsProperty()
    {
        $query = Product::query()
            ->when($this->search, function ($query) {
                $query->where('nama_barang', 'like', '%' . $this->search . '%')
                      ->orWhere('kode_item', 'like', '%' . $this->search . '%')
                      ->orWhere('jenis', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter);
            })
            ->when($this->stockFilter, function ($query) {
                if ($this->stockFilter === 'low') {
                    $query->whereRaw('stok_tersedia <= stok_minimum');
                } elseif ($this->stockFilter === 'out') {
                    $query->where('stok_tersedia', 0);
                } elseif ($this->stockFilter === 'available') {
                    $query->where('stok_tersedia', '>', 0);
                }
            })
            ->latest();

        return $query->paginate($this->perPage);
    }

    public function getJenisOptionsProperty()
    {
        return [
            'pack' => 'Pack',
            'ball' => 'Ball',
            'dus' => 'Dus'
        ];
    }

    public function render()
    {
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();
        $lowStockProducts = Product::whereRaw('stok_tersedia <= stok_minimum')->count();
        $outOfStockProducts = Product::where('stok_tersedia', 0)->count();
        $totalValue = Product::selectRaw('SUM(stok_tersedia * harga_jual) as total')->value('total') ?? 0;

        return view('livewire.admin.product-management', [
            'products' => $this->products,
            'jenisOptions' => $this->jenisOptions,
            'totalProducts' => $totalProducts,
            'activeProducts' => $activeProducts,
            'lowStockProducts' => $lowStockProducts,
            'outOfStockProducts' => $outOfStockProducts,
            'totalValue' => $totalValue,
        ]);
    }
}
