<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Data Master Barang</h4>
            <p class="text-muted mb-0">Kelola data produk dan inventory</p>
        </div>
        <button wire:click="openCreateModal" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i>
            Tambah Produk
        </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari Produk</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Kode, nama, atau keterangan...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filter Jenis</label>
                    <select wire:model.live="jenisFilter" class="form-select">
                        <option value="">Semua Jenis</option>
                        <option value="pack">Pack</option>
                        <option value="ball">Ball</option>
                        <option value="dus">Dus</option>
                    </select>
                </div>
                <div class="col-md-4">
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
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
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
                                <td>
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
                                            <h6 class="mb-0">{{ $product->nama_barang }}</h6>
                                            <small class="text-muted">{{ $product->kode_item }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $product->jenis === 'pack' ? 'primary' : ($product->jenis === 'ball' ? 'success' : 'info') }}">
                                        {{ ucfirst($product->jenis) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-medium">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</div>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-medium {{ $product->stok_tersedia <= $product->stok_minimum ? 'text-warning' : 'text-success' }}">
                                            {{ number_format($product->stok_tersedia) }}
                                        </div>
                                        <small class="text-muted">Min: {{ number_format($product->stok_minimum) }}</small>
                                        @if($product->stok_tersedia <= $product->stok_minimum)
                                            <br><small class="text-warning"><i class="bx bx-error-circle"></i> Stok Kritis</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $product->is_active ? 'success' : 'secondary' }}">
                                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#" wire:click.prevent="openEditModal({{ $product->id }})">
                                                <i class="bx bx-edit me-1"></i> Edit
                                            </a>
                                            <a class="dropdown-item" href="#" wire:click.prevent="openStockModal({{ $product->id }})">
                                                <i class="bx bx-package me-1"></i> Atur Stok
                                            </a>
                                            @if($product->getFirstMediaUrl('product_photos'))
                                                <a class="dropdown-item" href="{{ $product->getFirstMediaUrl('product_photos') }}" target="_blank">
                                                    <i class="bx bx-image me-1"></i> Lihat Foto
                                                </a>
                                            @endif
                                            <a class="dropdown-item" href="#" wire:click.prevent="toggleStatus({{ $product->id }})">
                                                <i class="bx bx-{{ $product->is_active ? 'x' : 'check' }}-circle me-1"></i>
                                                {{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#"
                                               wire:click.prevent="deleteProduct({{ $product->id }})"
                                               onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                                <i class="bx bx-trash me-1"></i> Hapus
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bx bx-package text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">Belum ada data produk</p>
                                    <button wire:click="openCreateModal" class="btn btn-primary btn-sm">
                                        <i class="bx bx-plus me-1"></i> Tambah Produk Pertama
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $products->links() }}
            </div>
        </div>
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
