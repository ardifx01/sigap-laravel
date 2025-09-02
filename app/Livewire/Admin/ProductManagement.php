<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;

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

    // Multiple units management
    public $units = [];
    public $showUnitsModal = false;
    public $unitsProductId;

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

    public function exportToExcel()
    {
        try {
            $filters = [
                'search' => $this->search,
                'is_active' => $this->statusFilter,
                'stock_filter' => $this->stockFilter,
            ];

            return Excel::download(
                new ProductsExport($filters), 
                'products-export-' . now()->format('Y-m-d-H-i-s') . '.xlsx'
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }

    public function getJenisOptionsProperty()
    {
        return [
            'pack' => 'Pack',
            'ball' => 'Ball',
            'dus' => 'Dus'
        ];
    }

    /**
     * Multiple Units Management Methods
     */
    public function openUnitsModal($productId)
    {
        $this->unitsProductId = $productId;
        $this->loadProductUnits();
        $this->showUnitsModal = true;
    }

    public function closeUnitsModal()
    {
        $this->showUnitsModal = false;
        $this->resetUnitsForm();
    }

    public function loadProductUnits()
    {
        $product = Product::with('units')->findOrFail($this->unitsProductId);
        
        if ($product->uses_multiple_units) {
            $this->units = $product->units->map(function($unit) {
                return [
                    'id' => $unit->id,
                    'unit_name' => $unit->unit_name,
                    'unit_code' => $unit->unit_code,
                    'conversion_value' => $unit->conversion_value,
                    'price_per_unit' => $unit->price_per_unit,
                    'stock_available' => $unit->stock_available,
                    'stock_minimum' => $unit->stock_minimum,
                    'is_base_unit' => $unit->is_base_unit,
                    'is_active' => $unit->is_active,
                    'sort_order' => $unit->sort_order,
                ];
            })->toArray();
        } else {
            // Provide default single unit setup
            $this->units = [[
                'id' => null,
                'unit_name' => ucfirst($product->jenis ?? 'Pcs'),
                'unit_code' => strtoupper(substr($product->jenis ?? 'pcs', 0, 3)),
                'conversion_value' => 1,
                'price_per_unit' => $product->harga_jual,
                'stock_available' => $product->stok_tersedia,
                'stock_minimum' => $product->stok_minimum,
                'is_base_unit' => true,
                'is_active' => true,
                'sort_order' => 0,
            ]];
        }
    }

    public function addUnit()
    {
        $this->units[] = [
            'id' => null,
            'unit_name' => '',
            'unit_code' => '',
            'conversion_value' => 1,
            'price_per_unit' => 0,
            'stock_available' => 0,
            'stock_minimum' => 0,
            'is_base_unit' => false,
            'is_active' => true,
            'sort_order' => count($this->units),
        ];
    }

    public function removeUnit($index)
    {
        if (isset($this->units[$index])) {
            // Don't allow removing if it's the only base unit
            if ($this->units[$index]['is_base_unit'] && count(array_filter($this->units, fn($unit) => $unit['is_base_unit'])) === 1) {
                session()->flash('error', 'Tidak bisa menghapus satuan dasar terakhir!');
                return;
            }
            
            unset($this->units[$index]);
            $this->units = array_values($this->units); // Re-index array
        }
    }

    public function saveUnits()
    {
        // Validate units data
        $this->validateUnits();

        try {
            $product = Product::findOrFail($this->unitsProductId);
            
            // Convert to multiple units system if not already
            if (!$product->uses_multiple_units) {
                $product->update(['uses_multiple_units' => true]);
            }

            // Delete existing units if they exist
            $product->units()->delete();

            // Create new units
            foreach ($this->units as $index => $unitData) {
                ProductUnit::create([
                    'product_id' => $product->id,
                    'unit_name' => $unitData['unit_name'],
                    'unit_code' => strtoupper($unitData['unit_code']),
                    'conversion_value' => $unitData['conversion_value'],
                    'price_per_unit' => $unitData['price_per_unit'],
                    'stock_available' => $unitData['stock_available'],
                    'stock_minimum' => $unitData['stock_minimum'],
                    'is_base_unit' => $unitData['is_base_unit'],
                    'is_active' => $unitData['is_active'],
                    'sort_order' => $index,
                ]);
            }

            $this->closeUnitsModal();
            session()->flash('success', 'Satuan produk berhasil disimpan!');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function validateUnits()
    {
        // Check if there's at least one unit
        if (empty($this->units)) {
            throw new \Exception('Produk harus memiliki minimal satu satuan.');
        }

        // Check if there's exactly one base unit
        $baseUnits = array_filter($this->units, fn($unit) => $unit['is_base_unit']);
        if (count($baseUnits) !== 1) {
            throw new \Exception('Produk harus memiliki tepat satu satuan dasar.');
        }

        // Validate each unit
        foreach ($this->units as $index => $unit) {
            if (empty($unit['unit_name'])) {
                throw new \Exception("Nama satuan pada baris " . ($index + 1) . " harus diisi.");
            }
            if (empty($unit['unit_code'])) {
                throw new \Exception("Kode satuan pada baris " . ($index + 1) . " harus diisi.");
            }
            if ($unit['conversion_value'] <= 0) {
                throw new \Exception("Nilai konversi pada baris " . ($index + 1) . " harus lebih dari 0.");
            }
            if ($unit['price_per_unit'] < 0) {
                throw new \Exception("Harga per satuan pada baris " . ($index + 1) . " tidak boleh negatif.");
            }
        }
    }

    public function setBaseUnit($index)
    {
        // Reset all units to non-base
        foreach ($this->units as $i => $unit) {
            $this->units[$i]['is_base_unit'] = false;
        }
        
        // Set selected unit as base
        if (isset($this->units[$index])) {
            $this->units[$index]['is_base_unit'] = true;
        }
    }

    public function resetUnitsForm()
    {
        $this->reset(['units', 'unitsProductId']);
    }

    /**
     * Convert existing product to use multiple units
     */
    public function convertToMultipleUnits($productId)
    {
        try {
            $product = Product::findOrFail($productId);
            $product->convertToMultipleUnits();
            
            session()->flash('success', 'Produk berhasil dikonversi ke sistem multi-satuan!');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    public function render()
    {
        $query = Product::with(['units' => function($query) {
            $query->active()->orderedBySortOrder();
        }])
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

        $products = $query->paginate($this->perPage);
        
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();
        $lowStockProducts = Product::whereRaw('stok_tersedia <= stok_minimum')->count();
        $outOfStockProducts = Product::where('stok_tersedia', 0)->count();
        $totalValue = Product::selectRaw('SUM(stok_tersedia * harga_jual) as total')->value('total') ?? 0;

        return view('livewire.admin.product-management', [
            'products' => $products,
            'jenisOptions' => $this->jenisOptions,
            'totalProducts' => $totalProducts,
            'activeProducts' => $activeProducts,
            'lowStockProducts' => $lowStockProducts,
            'outOfStockProducts' => $outOfStockProducts,
            'totalValue' => $totalValue,
        ]);
    }
}
