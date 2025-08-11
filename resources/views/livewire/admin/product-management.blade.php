<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Manajemen Produk</h4>
            <p class="text-muted mb-0">Kelola katalog produk dan inventory</p>
        </div>
        <button wire:click="openProductModal" class="btn btn-primary">
            <i class="bx bx-plus"></i> Tambah Produk
        </button>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2.4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="bx bx-package"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Produk</small>
                            <h6 class="mb-0">{{ $totalProducts }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2.4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="bx bx-check-circle"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Produk Aktif</small>
                            <h6 class="mb-0">{{ $activeProducts }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2.4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-warning">
                                <i class="bx bx-error"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Stok Rendah</small>
                            <h6 class="mb-0">{{ $lowStockProducts }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2.4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-danger">
                                <i class="bx bx-x-circle"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Stok Habis</small>
                            <h6 class="mb-0">{{ $outOfStockProducts }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2.4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="bx bx-money"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Nilai Inventory</small>
                            <h6 class="mb-0">Rp {{ number_format($totalValue, 0, ',', '.') }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Pencarian</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari nama produk, kode, atau jenis...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Stok</label>
                    <select wire:model.live="stockFilter" class="form-select">
                        <option value="">Semua Stok</option>
                        <option value="available">Tersedia</option>
                        <option value="low">Stok Rendah</option>
                        <option value="out">Habis</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Per Halaman</label>
                    <select wire:model.live="perPage" class="form-select">
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
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
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
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
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            @if($product->foto_produk)
                                                <img src="{{ asset('storage/' . $product->foto_produk) }}" alt="Product" class="rounded">
                                            @else
                                                <span class="avatar-initial rounded bg-label-secondary">
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
                                <td>
                                    <span class="badge bg-label-info">{{ ucfirst($product->jenis) }}</span>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-medium">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-medium {{ $product->stok_tersedia <= $product->stok_minimum ? 'text-danger' : ($product->stok_tersedia == 0 ? 'text-danger' : 'text-success') }}">
                                            {{ $product->stok_tersedia }} {{ $product->jenis }}
                                        </span>
                                        <br>
                                        <small class="text-muted">Min: {{ $product->stok_minimum }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $product->is_active ? 'success' : 'danger' }}">
                                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click="openProductModal({{ $product->id }})">
                                                    <i class="bx bx-edit me-1"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click="toggleStatus({{ $product->id }})">
                                                    <i class="bx bx-{{ $product->is_active ? 'x' : 'check' }}-circle me-1"></i>
                                                    {{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="adjustStock({{ $product->id }}, 'add')">
                                                    <i class="bx bx-plus me-1"></i> Tambah Stok
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="adjustStock({{ $product->id }}, 'subtract')">
                                                    <i class="bx bx-minus me-1"></i> Kurangi Stok
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#"
                                                   wire:click="deleteProduct({{ $product->id }})"
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
                                <td colspan="6" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-package text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-0">Tidak ada produk ditemukan</p>
                                        <small class="text-muted">Tambah produk baru atau ubah filter pencarian</small>
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
    @if($showProductModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $productId ? 'Edit Produk' : 'Tambah Produk' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showProductModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="saveProduct">
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
                                <div class="col-md-6">
                                    <label class="form-label">Jenis <span class="text-danger">*</span></label>
                                    <select wire:model="jenis" class="form-select @error('jenis') is-invalid @enderror">
                                        @foreach($jenisOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('jenis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="harga_jual" class="form-control @error('harga_jual') is-invalid @enderror" min="0">
                                    @error('harga_jual') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                    <label class="form-label">Keterangan</label>
                                    <textarea wire:model="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3"></textarea>
                                    @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" wire:model="is_active" id="is_active">
                                        <label class="form-check-label" for="is_active">
                                            Produk Aktif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showProductModal', false)">Batal</button>
                        <button type="button" wire:click="saveProduct" class="btn btn-primary">
                            <i class="bx bx-save"></i> {{ $productId ? 'Update' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>

<script>
function adjustStock(productId, type) {
    const quantity = prompt(`Masukkan jumlah stok yang akan ${type === 'add' ? 'ditambah' : 'dikurangi'}:`);
    if (quantity && !isNaN(quantity) && quantity > 0) {
        const adjustment = type === 'add' ? parseInt(quantity) : -parseInt(quantity);
        @this.call('adjustStock', productId, adjustment, `Manual ${type === 'add' ? 'addition' : 'reduction'} by admin`);
    }
}
</script>
