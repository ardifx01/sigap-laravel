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
    @if($urgentBackorders > 0 || $overdueBackorders > 0)
        <div class="row g-3 mb-4">
            @if($urgentBackorders > 0)
                <div class="col-md-6">
                    <div class="alert alert-warning">
                        <i class="bx bx-error-circle me-1"></i>
                        <strong>{{ $urgentBackorders }} backorder urgent</strong> memerlukan perhatian segera!
                    </div>
                </div>
            @endif
            @if($overdueBackorders > 0)
                <div class="col-md-6">
                    <div class="alert alert-danger">
                        <i class="bx bx-time me-1"></i>
                        <strong>{{ $overdueBackorders }} backorder terlambat</strong> dari tanggal yang diharapkan!
                    </div>
                </div>
            @endif
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
                                <i class="bx bx-clock"></i>
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
                            <small class="text-muted d-block">Processing</small>
                            <h6 class="mb-0">{{ $processingBackorders }}</h6>
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
                        <option value="processing">Processing</option>
                        <option value="fulfilled">Fulfilled</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Prioritas</label>
                    <select wire:model.live="priorityFilter" class="form-select">
                        <option value="">Semua Prioritas</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Produk</label>
                    <select wire:model.live="productFilter" class="form-select">
                        <option value="">Semua Produk</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->nama_barang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Customer</label>
                    <select wire:model.live="customerFilter" class="form-select">
                        <option value="">Semua Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->nama_toko }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tanggal</label>
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
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Target</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backorders as $backorder)
                            <tr class="{{ $backorder->expected_date && $backorder->expected_date->isPast() && in_array($backorder->status, ['pending', 'processing']) ? 'table-danger' : '' }}">
                                <td>
                                    <div>
                                        <span class="fw-medium">{{ $backorder->product->nama_barang }}</span>
                                        <br>
                                        <small class="text-muted">{{ $backorder->product->kode_item }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-medium">{{ $backorder->customer->nama_toko }}</span>
                                        <br>
                                        <small class="text-muted">{{ $backorder->customer->nama_pemilik }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $backorder->quantity_requested }} {{ $backorder->product->satuan }}</span>
                                    @if($backorder->quantity_fulfilled)
                                        <br>
                                        <small class="text-success">Dipenuhi: {{ $backorder->quantity_fulfilled }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $priorityColors = [
                                            'low' => 'secondary',
                                            'medium' => 'info',
                                            'high' => 'warning',
                                            'urgent' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-label-{{ $priorityColors[$backorder->priority] ?? 'secondary' }}">
                                        {{ ucfirst($backorder->priority) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'processing' => 'info',
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
                                                <a class="dropdown-item" href="#" wire:click="viewBackorder({{ $backorder->id }})">
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
                                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                                    <select wire:model="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                        <option value="">Pilih Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->nama_toko }}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jumlah Diminta <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="quantity_requested" class="form-control @error('quantity_requested') is-invalid @enderror" min="1">
                                    @error('quantity_requested') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Prioritas <span class="text-danger">*</span></label>
                                    <select wire:model="priority" class="form-select @error('priority') is-invalid @enderror">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                    @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Diharapkan</label>
                                    <input type="date" wire:model="expected_date" class="form-control @error('expected_date') is-invalid @enderror">
                                    @error('expected_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Catatan</label>
                                    <textarea wire:model="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Catatan tambahan (opsional)"></textarea>
                                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
    @if($showViewModal && $viewBackorder)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Backorder</h5>
                        <button type="button" class="btn-close" wire:click="$set('showViewModal', false)"></button>
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
                                            <span class="badge bg-label-{{ $statusColors[$viewBackorder->status] ?? 'secondary' }}">
                                                {{ ucfirst($viewBackorder->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Prioritas:</td>
                                        <td>
                                            @php
                                                $priorityColors = [
                                                    'low' => 'secondary',
                                                    'medium' => 'info',
                                                    'high' => 'warning',
                                                    'urgent' => 'danger'
                                                ];
                                            @endphp
                                            <span class="badge bg-label-{{ $priorityColors[$viewBackorder->priority] ?? 'secondary' }}">
                                                {{ ucfirst($viewBackorder->priority) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Jumlah Diminta:</td>
                                        <td><strong>{{ $viewBackorder->quantity_requested }} {{ $viewBackorder->product->satuan }}</strong></td>
                                    </tr>
                                    @if($viewBackorder->quantity_fulfilled)
                                        <tr>
                                            <td>Jumlah Dipenuhi:</td>
                                            <td><strong>{{ $viewBackorder->quantity_fulfilled }} {{ $viewBackorder->product->satuan }}</strong></td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td>Tanggal Dibuat:</td>
                                        <td>{{ $viewBackorder->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    @if($viewBackorder->expected_date)
                                        <tr>
                                            <td>Tanggal Diharapkan:</td>
                                            <td class="{{ $viewBackorder->expected_date->isPast() ? 'text-danger' : '' }}">
                                                {{ $viewBackorder->expected_date->format('d/m/Y') }}
                                                @if($viewBackorder->expected_date->isPast())
                                                    <small>(Terlambat)</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td>Dibuat Oleh:</td>
                                        <td>{{ $viewBackorder->createdBy->name ?? 'System' }}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Product & Customer Info -->
                            <div class="col-md-6">
                                <h6>Informasi Produk & Customer</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Produk:</td>
                                        <td><strong>{{ $viewBackorder->product->nama_barang }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Kode Item:</td>
                                        <td>{{ $viewBackorder->product->kode_item }}</td>
                                    </tr>
                                    <tr>
                                        <td>Stok Tersedia:</td>
                                        <td>
                                            <span class="{{ $viewBackorder->product->stok_tersedia <= $viewBackorder->product->stok_minimum ? 'text-danger' : 'text-success' }}">
                                                {{ $viewBackorder->product->stok_tersedia }} {{ $viewBackorder->product->satuan }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Customer:</td>
                                        <td><strong>{{ $viewBackorder->customer->nama_toko }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Pemilik:</td>
                                        <td>{{ $viewBackorder->customer->nama_pemilik }}</td>
                                    </tr>
                                    <tr>
                                        <td>Telepon:</td>
                                        <td>{{ $viewBackorder->customer->telepon }}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Notes -->
                            @if($viewBackorder->notes)
                                <div class="col-12">
                                    <h6>Catatan</h6>
                                    <p class="text-muted">{{ $viewBackorder->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showViewModal', false)">Tutup</button>
                        @if(in_array($viewBackorder->status, ['pending', 'processing']) && $viewBackorder->product->stok_tersedia >= $viewBackorder->quantity_requested)
                            <button type="button" wire:click="fulfillBackorder({{ $viewBackorder->id }})" class="btn btn-success">
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
