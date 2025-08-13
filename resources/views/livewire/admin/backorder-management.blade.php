<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Manajemen Backorder</h4>
            <p class="text-muted mb-0">Kelola pesanan yang tertunda karena stok habis</p>
        </div>
        <button wire:click="openBackorderModal" class="btn btn-primary">
            <i class="bx bx-plus"></i> Tambah Backorder
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

    <!-- Alert Cards -->
    @if($overdueBackorders > 0)
        <div class="row g-3 mb-4">
            <div class="col-md-12">
                <div class="alert alert-danger">
                    <i class="bx bx-time me-1"></i>
                    <strong>{{ $overdueBackorders }} backorder terlambat</strong> dari tanggal yang diharapkan!
                </div>
            </div>
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
                                <i class="bx bx-time"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Backorder</small>
                            <h6 class="mb-0">{{ $totalBackorders }}</h6>
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
                                <i class="bx bx-time"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Pending</small>
                            <h6 class="mb-0">{{ $pendingBackorders }}</h6>
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
                                <i class="bx bx-loader"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Partial</small>
                            <h6 class="mb-0">{{ $partialBackorders }}</h6>
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
                            <small class="text-muted d-block">Fulfilled</small>
                            <h6 class="mb-0">{{ $fulfilledBackorders }}</h6>
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
                            <small class="text-muted d-block">Cancelled</small>
                            <h6 class="mb-0">{{ $cancelledBackorders }}</h6>
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
                <div class="col-md-2">
                    <label class="form-label">Pencarian</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari produk atau customer...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="partial">Partial</option>
                        <option value="fulfilled">Fulfilled</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Produk</label>
                    <select wire:model.live="productFilter" class="form-select">
                        <option value="">Semua Produk</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->nama_barang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Expected</label>
                    <input type="date" wire:model.live="dateFilter" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <!-- Backorders Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Backorder</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Customer</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Target</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backorders as $backorder)
                            <tr class="{{ $backorder->expected_date && $backorder->expected_date->isPast() && in_array($backorder->status, ['pending', 'partial']) ? 'table-danger' : '' }}">
                                <td>
                                    <div>
                                        <span class="fw-medium">{{ $backorder->product->nama_barang }}</span>
                                        <br>
                                        <small class="text-muted">{{ $backorder->product->kode_item }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-medium">{{ $backorder->orderItem->order->customer->nama_toko }}</span>
                                        <br>
                                        <small class="text-muted">Order: {{ $backorder->orderItem->order->nomor_order }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $backorder->jumlah_backorder }} {{ $backorder->product->jenis }}</span>
                                    @if($backorder->jumlah_terpenuhi > 0)
                                        <br>
                                        <small class="text-success">Dipenuhi: {{ $backorder->jumlah_terpenuhi }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'partial' => 'info',
                                            'fulfilled' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-label-{{ $statusColors[$backorder->status] ?? 'secondary' }}">
                                        {{ ucfirst($backorder->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($backorder->expected_date)
                                        <div>
                                            <div class="fw-medium {{ $backorder->expected_date->isPast() ? 'text-danger' : '' }}">
                                                {{ $backorder->expected_date->format('d/m/Y') }}
                                            </div>
                                            @if($backorder->expected_date->isPast())
                                                <small class="text-danger">Terlambat</small>
                                            @else
                                                <small class="text-muted">{{ $backorder->expected_date->diffForHumans() }}</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click.prevent="viewBackorder({{ $backorder->id }})">
                                                    <i class="bx bx-show me-1"></i> Lihat Detail
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click="openBackorderModal({{ $backorder->id }})">
                                                    <i class="bx bx-edit me-1"></i> Edit
                                                </a>
                                            </li>
                                            @if($backorder->status === 'pending')
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click="updateBackorderStatus({{ $backorder->id }}, 'processing')">
                                                        <i class="bx bx-play me-1"></i> Proses
                                                    </a>
                                                </li>
                                            @endif
                                            @if(in_array($backorder->status, ['pending', 'processing']))
                                                <li>
                                                    <a class="dropdown-item text-success" href="#" wire:click="fulfillBackorder({{ $backorder->id }})">
                                                        <i class="bx bx-check me-1"></i> Penuhi
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" wire:click="updateBackorderStatus({{ $backorder->id }}, 'cancelled')">
                                                        <i class="bx bx-x me-1"></i> Batalkan
                                                    </a>
                                                </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#"
                                                   wire:click="deleteBackorder({{ $backorder->id }})"
                                                   onclick="return confirm('Yakin ingin menghapus backorder ini?')">
                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-time text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-0">Tidak ada backorder ditemukan</p>
                                        <small class="text-muted">Backorder akan muncul ketika stok produk habis</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($backorders->hasPages())
            <div class="card-footer">
                {{ $backorders->links() }}
            </div>
        @endif
    </div>

    <!-- Backorder Modal -->
    @if($showBackorderModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $backorderId ? 'Edit Backorder' : 'Tambah Backorder' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showBackorderModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="saveBackorder">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Produk <span class="text-danger">*</span></label>
                                    <select wire:model="product_id" class="form-select @error('product_id') is-invalid @enderror">
                                        <option value="">Pilih Produk</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">
                                                {{ $product->nama_barang }} ({{ $product->kode_item }})
                                                - Stok: {{ $product->stok_tersedia }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Order Item <span class="text-danger">*</span></label>
                                    <select wire:model="order_item_id" class="form-select @error('order_item_id') is-invalid @enderror">
                                        <option value="">Pilih Order Item</option>
                                        @foreach($orderItems as $orderItem)
                                            <option value="{{ $orderItem->id }}">
                                                {{ $orderItem->order->nomor_order }} - {{ $orderItem->order->customer->nama_toko }}
                                                ({{ $orderItem->product->nama_barang }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('order_item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jumlah Backorder <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="jumlah_backorder" class="form-control @error('jumlah_backorder') is-invalid @enderror" min="1">
                                    @error('jumlah_backorder') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Diharapkan</label>
                                    <input type="date" wire:model="expected_date" class="form-control @error('expected_date') is-invalid @enderror">
                                    @error('expected_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Catatan</label>
                                    <textarea wire:model="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3" placeholder="Catatan tambahan (opsional)"></textarea>
                                    @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showBackorderModal', false)">Batal</button>
                        <button type="button" wire:click="saveBackorder" class="btn btn-primary">
                            <i class="bx bx-save"></i> {{ $backorderId ? 'Update' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Backorder Detail Modal -->
    @if($showViewModal && $selectedBackorder)
        <div class="modal fade show" style="display: block;" tabindex="-1" wire:click.self="closeViewModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Backorder</h5>
                        <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-4">
                            <!-- Backorder Info -->
                            <div class="col-md-6">
                                <h6>Informasi Backorder</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Status:</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'processing' => 'info',
                                                    'fulfilled' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                            @endphp
                                            <span class="badge bg-label-{{ $statusColors[$selectedBackorder->status] ?? 'secondary' }}">
                                                {{ ucfirst($selectedBackorder->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Jumlah Backorder:</td>
                                        <td><strong>{{ $selectedBackorder->jumlah_backorder }} {{ $selectedBackorder->product->jenis }}</strong></td>
                                    </tr>
                                    @if($selectedBackorder->jumlah_terpenuhi > 0)
                                        <tr>
                                            <td>Jumlah Terpenuhi:</td>
                                            <td><strong>{{ $selectedBackorder->jumlah_terpenuhi }} {{ $selectedBackorder->product->jenis }}</strong></td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td>Tanggal Dibuat:</td>
                                        <td>{{ $selectedBackorder->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    @if($selectedBackorder->expected_date)
                                        <tr>
                                            <td>Tanggal Diharapkan:</td>
                                            <td class="{{ $selectedBackorder->expected_date->isPast() ? 'text-danger' : '' }}">
                                                {{ $selectedBackorder->expected_date->format('d/m/Y') }}
                                                @if($selectedBackorder->expected_date->isPast())
                                                    <small>(Terlambat)</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                    @if($selectedBackorder->fulfilled_at)
                                        <tr>
                                            <td>Tanggal Dipenuhi:</td>
                                            <td>{{ $selectedBackorder->fulfilled_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>

                            <!-- Product & Customer Info -->
                            <div class="col-md-6">
                                <h6>Informasi Produk & Customer</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Produk:</td>
                                        <td><strong>{{ $selectedBackorder->product->nama_barang }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Kode Item:</td>
                                        <td>{{ $selectedBackorder->product->kode_item }}</td>
                                    </tr>
                                    <tr>
                                        <td>Stok Tersedia:</td>
                                        <td>
                                            <span class="{{ $selectedBackorder->product->stok_tersedia <= $selectedBackorder->product->stok_minimum ? 'text-danger' : 'text-success' }}">
                                                {{ $selectedBackorder->product->stok_tersedia }} {{ $selectedBackorder->product->jenis }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Customer:</td>
                                        <td><strong>{{ $selectedBackorder->orderItem->order->customer->nama_toko }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Pemilik:</td>
                                        <td>{{ $selectedBackorder->orderItem->order->customer->nama_pemilik ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Telepon:</td>
                                        <td>{{ $selectedBackorder->orderItem->order->customer->phone }}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Notes -->
                            @if($selectedBackorder->catatan)
                                <div class="col-12">
                                    <h6>Catatan</h6>
                                    <p class="text-muted">{{ $selectedBackorder->catatan }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeViewModal">Tutup</button>
                        @if(in_array($selectedBackorder->status, ['pending', 'partial']) && $selectedBackorder->product->stok_tersedia >= $selectedBackorder->jumlah_backorder)
                            <button type="button" wire:click="fulfillBackorder({{ $selectedBackorder->id }})" class="btn btn-success">
                                <i class="bx bx-check"></i> Penuhi Backorder
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
