<div>
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1">Data Master Barang</h4>
            <p class="text-muted mb-0">Kelola data produk dan inventory</p>
        </div>
        <div class="d-flex flex-column flex-md-row gap-2">
            <!-- Export Dropdown -->
            <div class="dropdown">
                <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bx bx-export me-1"></i>
                    Export Data
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <h6 class="dropdown-header">Export Stock</h6>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" wire:click.prevent="exportOutOfStock">
                            <i class="bx bx-error-circle text-danger me-2"></i>
                            <div>
                                <strong>Stock Kosong</strong>
                                <br><small class="text-muted">Produk yang stok-nya 0</small>
                                @if($outOfStockCount > 0)
                                    <span class="badge bg-danger ms-1">{{ $outOfStockCount }}</span>
                                @endif
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" wire:click.prevent="exportLowStock">
                            <i class="bx bx-error text-warning me-2"></i>
                            <div>
                                <strong>Stock Rendah</strong>
                                <br><small class="text-muted">Stok <= minimum</small>
                                @if($lowStockCount > 0)
                                    <span class="badge bg-warning ms-1">{{ $lowStockCount }}</span>
                                @endif
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="#" wire:click.prevent="exportInventoryReport">
                            <i class="bx bx-file-blank text-primary me-2"></i>
                            <div>
                                <strong>Laporan Lengkap</strong>
                                <br><small class="text-muted">3 sheet dalam 1 file</small>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
            <button wire:click="openCreateModal" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i>
                Tambah Produk
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label">Cari Produk</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Kode, nama, atau keterangan...">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Filter Jenis</label>
                    <select wire:model.live="jenisFilter" class="form-select">
                        <option value="">Semua Jenis</option>
                        <option value="pack">Pack</option>
                        <option value="ball">Ball</option>
                        <option value="dus">Dus</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Filter Status</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Produk</h5>
        </div>
        <div class="card-body p-0">
            <style>
                @media (max-width: 767.98px) {
                    .mobile-cards tbody tr {
                        display: block;
                        border: 1px solid #ddd;
                        border-radius: 0.5rem;
                        margin-bottom: 1rem;
                        padding: 1rem;
                    }
                    .mobile-cards thead {
                        display: none;
                    }
                    .mobile-cards tbody td {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        border: none;
                        padding: 0.5rem 0;
                    }
                    .mobile-cards tbody td:before {
                        content: attr(data-label);
                        font-weight: 600;
                        margin-right: 1rem;
                    }
                    .mobile-cards .product-info-cell {
                        display: block; /* Override the flex for the main product info */
                        padding-bottom: 1rem;
                        margin-bottom: 1rem;
                        border-bottom: 1px solid #eee;
                    }
                    .mobile-cards .product-info-cell:before {
                        display: none; /* No "Produk:" label */
                    }
                    .mobile-cards .actions-cell {
                        justify-content: flex-end; /* Align actions to the right */
                    }
                    .mobile-cards .actions-cell:before {
                        display: none; /* No "Aksi:" label */
                    }
                }
            </style>
            <div class="table-responsive">
                <table class="table table-hover mb-0 mobile-cards">
                    <thead class="table-light d-none d-md-table-header-group">
                        <tr>
                            <th>Produk</th>
                            <th>Jenis</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr class="{{ $product->stok_tersedia <= $product->stok_minimum ? 'table-warning' : '' }}">
                                <td data-label="Produk" class="product-info-cell">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            @if($product->getFirstMediaUrl('product_photos'))
                                                <img src="{{ $product->getFirstMediaUrl('product_photos') }}" alt="Product" class="rounded">
                                            @else
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    <i class="bx bx-package"></i>
                                                </span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="fw-medium">{{ $product->nama_barang }}</span>
                                            <br>
                                            <small class="text-muted">{{ $product->kode_item }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Jenis">
                                    @if($product->uses_multiple_units && $product->units->count() > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($product->units->take(2) as $unit)
                                                <span class="badge bg-label-{{ $unit->is_base_unit ? 'primary' : 'info' }}" title="{{ $unit->formatted_unit }}">
                                                    {{ $unit->unit_code }}
                                                </span>
                                            @endforeach
                                            @if($product->units->count() > 2)
                                                <span class="badge bg-label-secondary">+{{ $product->units->count() - 2 }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge bg-label-{{ $product->jenis === 'pack' ? 'primary' : ($product->jenis === 'ball' ? 'success' : 'info') }}">
                                            {{ ucfirst($product->jenis) }}
                                        </span>
                                    @endif
                                </td>
                                <td data-label="Harga">
                                    @if($product->uses_multiple_units && $product->units->count() > 0)
                                        @php $primaryUnit = $product->getPrimaryUnit(); @endphp
                                        @if($primaryUnit)
                                            <span class="fw-medium">Rp {{ number_format($primaryUnit->price_per_unit, 0, ',', '.') }}</span>
                                            <br><small class="text-muted">per {{ $primaryUnit->unit_code }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    @else
                                        <span class="fw-medium">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td data-label="Stok">
                                    @if($product->uses_multiple_units && $product->units->count() > 0)
                                        @php $primaryUnit = $product->getPrimaryUnit(); @endphp
                                        @if($primaryUnit)
                                            <div>
                                                <span class="fw-medium {{ $primaryUnit->stock_available <= $primaryUnit->stock_minimum ? 'text-warning' : 'text-success' }}">
                                                    {{ number_format($primaryUnit->stock_available) }} {{ $primaryUnit->unit_code }}
                                                </span>
                                                <br>
                                                <small class="text-muted">Min: {{ number_format($primaryUnit->stock_minimum) }}</small>
                                                @if($primaryUnit->stock_available <= $primaryUnit->stock_minimum)
                                                    <br><small class="text-warning"><i class="bx bx-error-circle"></i> Stok Kritis</small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    @else
                                        <div>
                                            <span class="fw-medium {{ $product->stok_tersedia <= $product->stok_minimum ? 'text-warning' : 'text-success' }}">
                                                {{ number_format($product->stok_tersedia) }}
                                            </span>
                                            <br>
                                            <small class="text-muted">Min: {{ number_format($product->stok_minimum) }}</small>
                                            @if($product->stok_tersedia <= $product->stok_minimum)
                                                <br><small class="text-warning"><i class="bx bx-error-circle"></i> Stok Kritis</small>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td data-label="Status">
                                    <span class="badge bg-label-{{ $product->is_active ? 'success' : 'secondary' }}">
                                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td data-label="Aksi" class="actions-cell">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click.prevent="openEditModal({{ $product->id }})">
                                                    <i class="bx bx-edit me-1"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click.prevent="openStockModal({{ $product->id }})">
                                                    <i class="bx bx-package me-1"></i> Atur Stok
                                                </a>
                                            </li>
                                            @if($product->uses_multiple_units)
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click.prevent="openUnitsModal({{ $product->id }})">
                                                        <i class="bx bx-cube me-1"></i> Kelola Satuan
                                                    </a>
                                                </li>
                                            @else
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click.prevent="convertToMultipleUnits({{ $product->id }})">
                                                        <i class="bx bx-plus-circle me-1"></i> Aktifkan Multi-Satuan
                                                    </a>
                                                </li>
                                            @endif
                                            @if($product->getFirstMediaUrl('product_photos'))
                                                <li>
                                                    <a class="dropdown-item" href="{{ $product->getFirstMediaUrl('product_photos') }}" target="_blank">
                                                        <i class="bx bx-image me-1"></i> Lihat Foto
                                                    </a>
                                                </li>
                                            @endif
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click.prevent="toggleStatus({{ $product->id }})">
                                                    <i class="bx bx-{{ $product->is_active ? 'x' : 'check' }}-circle me-1"></i>
                                                    {{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#"
                                                   wire:click.prevent="deleteProduct({{ $product->id }})"
                                                   onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-package text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-1">Belum ada data produk</p>
                                        <small class="text-muted">Tambah produk pertama Anda.</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($products->hasPages())
            <div class="card-footer">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Product Modal -->
    @if($showModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editMode ? 'Edit Produk' : 'Tambah Produk Baru' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Kode Item <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="kode_item" class="form-control @error('kode_item') is-invalid @enderror">
                                    @error('kode_item') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="nama_barang" class="form-control @error('nama_barang') is-invalid @enderror">
                                    @error('nama_barang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Keterangan</label>
                                    <textarea wire:model="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="2"></textarea>
                                    @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Jenis <span class="text-danger">*</span></label>
                                    <select wire:model="jenis" class="form-select @error('jenis') is-invalid @enderror">
                                        <option value="pack">Pack</option>
                                        <option value="ball">Ball</option>
                                        <option value="dus">Dus</option>
                                    </select>
                                    @error('jenis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" wire:model="harga_jual" class="form-control @error('harga_jual') is-invalid @enderror" min="0" step="100">
                                    </div>
                                    @error('harga_jual') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" wire:model="is_active" id="is_active_product">
                                        <label class="form-check-label" for="is_active_product">
                                            {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Stok Tersedia <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="stok_tersedia" class="form-control @error('stok_tersedia') is-invalid @enderror" min="0">
                                    @error('stok_tersedia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Stok Minimum <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="stok_minimum" class="form-control @error('stok_minimum') is-invalid @enderror" min="0">
                                    @error('stok_minimum') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Foto Produk</label>
                                    <input type="file" wire:model="foto_produk" class="form-control @error('foto_produk') is-invalid @enderror" accept="image/*">
                                    @error('foto_produk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($foto_produk)
                                        <div class="mt-2">
                                            <img src="{{ $foto_produk->temporaryUrl() }}" alt="Preview" class="img-thumbnail" style="max-height: 150px;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                {{ $editMode ? 'Update' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Stock Adjustment Modal -->
    @if($showStockModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Atur Stok</h5>
                        <button type="button" class="btn-close" wire:click="closeStockModal"></button>
                    </div>
                    <form wire:submit.prevent="adjustStock">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Jenis Perubahan <span class="text-danger">*</span></label>
                                    <select wire:model="stockType" class="form-select @error('stockType') is-invalid @enderror">
                                        <option value="in">Stok Masuk</option>
                                        <option value="out">Stok Keluar</option>
                                        <option value="adjustment">Adjustment Manual</option>
                                    </select>
                                    @error('stockType') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">
                                        {{ $stockType === 'adjustment' ? 'Stok Baru' : 'Jumlah' }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" wire:model="stockQuantity" class="form-control @error('stockQuantity') is-invalid @enderror" min="1">
                                    @error('stockQuantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($stockType === 'adjustment')
                                        <small class="text-muted">Masukkan jumlah stok yang baru</small>
                                    @else
                                        <small class="text-muted">Masukkan jumlah yang akan {{ $stockType === 'in' ? 'ditambah' : 'dikurangi' }}</small>
                                    @endif
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Catatan</label>
                                    <textarea wire:model="stockNotes" class="form-control @error('stockNotes') is-invalid @enderror" rows="2" placeholder="Alasan perubahan stok..."></textarea>
                                    @error('stockNotes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeStockModal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                Update Stok
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Multiple Units Management Modal -->
    @if($showUnitsModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Kelola Satuan Produk</h5>
                        <button type="button" class="btn-close" wire:click="closeUnitsModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info mb-4">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Petunjuk:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Minimal harus ada satu satuan yang aktif</li>
                                <li>Hanya boleh ada satu satuan dasar (base unit)</li>
                                <li>Nilai konversi menunjukkan berapa unit dasar dalam satuan ini</li>
                                <li>Contoh: 1 Karton = 12 Pcs, maka nilai konversi Karton = 12</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <button type="button" wire:click="addUnit" class="btn btn-outline-primary">
                                <i class="bx bx-plus"></i> Tambah Satuan
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Satuan</th>
                                        <th>Kode</th>
                                        <th>Konversi</th>
                                        <th>Harga/Unit</th>
                                        <th>Stok</th>
                                        <th>Min. Stok</th>
                                        <th>Base Unit</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($units as $index => $unit)
                                        <tr>
                                            <td>
                                                <input type="text" wire:model="units.{{ $index }}.unit_name" 
                                                       class="form-control form-control-sm" 
                                                       placeholder="Nama satuan...">
                                            </td>
                                            <td>
                                                <input type="text" wire:model="units.{{ $index }}.unit_code" 
                                                       class="form-control form-control-sm" 
                                                       placeholder="Kode..." style="width: 80px;">
                                            </td>
                                            <td>
                                                <input type="number" wire:model="units.{{ $index }}.conversion_value" 
                                                       class="form-control form-control-sm" 
                                                       min="0.01" step="0.01" style="width: 90px;">
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="number" wire:model="units.{{ $index }}.price_per_unit" 
                                                           class="form-control" 
                                                           min="0" step="100">
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" wire:model="units.{{ $index }}.stock_available" 
                                                       class="form-control form-control-sm" 
                                                       min="0" style="width: 90px;">
                                            </td>
                                            <td>
                                                <input type="number" wire:model="units.{{ $index }}.stock_minimum" 
                                                       class="form-control form-control-sm" 
                                                       min="0" style="width: 90px;">
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" 
                                                           name="base_unit" 
                                                           wire:click="setBaseUnit({{ $index }})" 
                                                           {{ $unit['is_base_unit'] ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" 
                                                           wire:model="units.{{ $index }}.is_active">
                                                </div>
                                            </td>
                                            <td>
                                                @if(count($units) > 1)
                                                    <button type="button" wire:click="removeUnit({{ $index }})" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Hapus satuan">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(empty($units))
                            <div class="text-center py-4">
                                <i class="bx bx-cube text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">Belum ada satuan</p>
                                <button type="button" wire:click="addUnit" class="btn btn-primary">
                                    <i class="bx bx-plus"></i> Tambah Satuan Pertama
                                </button>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeUnitsModal">Batal</button>
                        <button type="button" wire:click="saveUnits" class="btn btn-primary">
                            <i class="bx bx-save"></i> Simpan Satuan
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
</div>
