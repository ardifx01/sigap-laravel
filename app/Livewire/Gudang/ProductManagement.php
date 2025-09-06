<?php

namespace App\Livewire\Gudang;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\InventoryLog;
use App\Exports\OutOfStockExport;
use App\Exports\LowStockExport;
use App\Exports\InventoryReportExport;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

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
    public $uses_multiple_units = false;

    // Multiple units management
    public $units = [];
    public $showUnitsModal = false;
    public $unitsProductId;

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

    public function messages()
    {
        return [
            'foto_produk.image' => 'File harus berupa gambar (jpg, jpeg, png, bmp, gif, svg, webp)',
            'foto_produk.max' => 'Ukuran file maksimal 2MB',
        ];
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
        $this->foto_produk = null; // Reset foto upload

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
        \Log::info('Product save started', [
            'edit_mode' => $this->editMode,
            'product_id' => $this->productId,
            'has_foto_produk' => !empty($this->foto_produk),
            'foto_produk_type' => $this->foto_produk ? get_class($this->foto_produk) : null
        ]);

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
            }

            // Handle product photo upload BEFORE setting success message
            if ($this->foto_produk) {
                try {
                    \Log::info('Product photo upload started', [
                        'product_id' => $product->id,
                        'file_name' => $this->foto_produk->getClientOriginalName(),
                        'file_size' => $this->foto_produk->getSize(),
                        'edit_mode' => $this->editMode
                    ]);

                    // Remove old photo if editing
                    if ($this->editMode && $product->foto_produk) {
                        \Storage::disk('public')->delete('product_photos/' . $product->foto_produk);
                        \Log::info('Old product photo deleted', ['old_filename' => $product->foto_produk]);
                    }

                    // Store new photo
                    $filename = time() . '_' . $this->foto_produk->getClientOriginalName();
                    $path = $this->foto_produk->storeAs('product_photos', $filename, 'public');

                    if (!$path) {
                        throw new \Exception('Failed to store product photo');
                    }

                    $product->update(['foto_produk' => $filename]);

                    \Log::info('Product photo uploaded successfully', [
                        'product_id' => $product->id,
                        'filename' => $filename,
                        'path' => $path
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Product photo upload failed', [
                        'product_id' => $product->id,
                        'error_message' => $e->getMessage(),
                        'error_file' => $e->getFile(),
                        'error_line' => $e->getLine()
                    ]);
                    session()->flash('error', 'Gagal upload foto produk: ' . $e->getMessage());
                    return;
                }
            }

            // Set success message after photo upload is successful
            if ($this->editMode) {
                session()->flash('success', 'Data produk berhasil diperbarui!');
            } else {
                session()->flash('success', 'Data produk berhasil ditambahkan!');
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

    /**
     * Export Out of Stock Products
     */
    public function exportOutOfStock()
    {
        try {
            $filters = [
                'search' => $this->search,
                'jenis' => $this->jenisFilter,
            ];

            return Excel::download(
                new OutOfStockExport($filters),
                'stock-kosong-' . now()->format('Y-m-d-H-i-s') . '.xlsx'
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengekspor data stock kosong: ' . $e->getMessage());
        }
    }

    /**
     * Export Low Stock Products
     */
    public function exportLowStock()
    {
        try {
            $filters = [
                'search' => $this->search,
                'jenis' => $this->jenisFilter,
            ];

            return Excel::download(
                new LowStockExport($filters),
                'stock-rendah-' . now()->format('Y-m-d-H-i-s') . '.xlsx'
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengekspor data stock rendah: ' . $e->getMessage());
        }
    }

    /**
     * Export Comprehensive Inventory Report (Multiple Sheets)
     */
    public function exportInventoryReport()
    {
        try {
            $filters = [
                'search' => $this->search,
                'jenis' => $this->jenisFilter,
            ];

            return Excel::download(
                new InventoryReportExport($filters),
                'laporan-inventory-lengkap-' . now()->format('Y-m-d-H-i-s') . '.xlsx'
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengekspor laporan inventory: ' . $e->getMessage());
        }
    }


    public function render()
    {
        $products = Product::with(['units' => function($query) {
            $query->active()->orderedBySortOrder();
        }])
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

        // Calculate stock statistics
        $outOfStockCount = Product::where('stok_tersedia', '=', 0)
            ->where('is_active', true)
            ->count();

        $lowStockCount = Product::whereRaw('stok_tersedia <= stok_minimum')
            ->where('stok_tersedia', '>', 0)
            ->where('is_active', true)
            ->count();

        $totalActiveProducts = Product::where('is_active', true)->count();

        $averageStock = Product::where('is_active', true)
            ->avg('stok_tersedia') ?? 0;

        return view('livewire.gudang.product-management', [
            'products' => $products,
            'outOfStockCount' => $outOfStockCount,
            'lowStockCount' => $lowStockCount,
            'totalActiveProducts' => $totalActiveProducts,
            'averageStock' => round($averageStock, 2),
        ]);
    }
}
