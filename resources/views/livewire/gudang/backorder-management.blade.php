<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Manajemen Backorder</h4>
            <p class="text-muted mb-0">Kelola item yang belum tersedia dan monitor pemenuhan backorder</p>
        </div>
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
        <div class="col-md-3">
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
                            <h6 class="mb-0">{{ $pendingCount }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="bx bx-package"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Partial</small>
                            <h6 class="mb-0">{{ $partialCount }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
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
                            <h6 class="mb-0">{{ $fulfilledCount }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="bx bx-money"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Value</small>
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
                <div class="col-md-3">
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
                    <label class="form-label">Tanggal</label>
                    <input type="date" wire:model.live="dateFilter" class="form-control">
                </div>
                <div class="col-md-3">
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
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Qty Backorder</th>
                            <th>Qty Terpenuhi</th>
                            <th>Status</th>
                            <th>Expected Date</th>
                            <th>Created</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backorders as $backorder)
                            <tr>
                                <td>
                                    <div>
                                        <span class="fw-medium">{{ $backorder->product->nama_barang }}</span>
                                        <br>
                                        <small class="text-muted">{{ $backorder->product->kode_item }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $backorder->orderItem->order->nomor_order }}</span>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-medium">{{ $backorder->orderItem->order->customer->nama_toko }}</span>
                                        <br>
                                        <small class="text-muted">Sales: {{ $backorder->orderItem->order->sales->name }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-warning">{{ $backorder->jumlah_backorder }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-label-success">{{ $backorder->jumlah_terpenuhi }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'partial' => 'info',
                                            'fulfilled' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Pending',
                                            'partial' => 'Partial',
                                            'fulfilled' => 'Fulfilled',
                                            'cancelled' => 'Cancelled'
                                        ];
                                    @endphp
                                    <span class="badge bg-label-{{ $statusColors[$backorder->status] ?? 'secondary' }}">
                                        {{ $statusLabels[$backorder->status] ?? $backorder->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($backorder->expected_date)
                                        {{ \Carbon\Carbon::parse($backorder->expected_date)->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $backorder->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <ul class="dropdown-menu">
                                            @if($backorder->status === 'pending' || $backorder->status === 'partial')
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click="openFulfillModal({{ $backorder->id }})">
                                                        <i class="bx bx-check me-1"></i> Penuhi Backorder
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click="openUpdateModal({{ $backorder->id }})">
                                                        <i class="bx bx-edit me-1"></i> Update Info
                                                    </a>
                                                </li>
                                            @endif
                                            @if($backorder->status === 'pending')
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#"
                                                       wire:click="cancelBackorder({{ $backorder->id }})"
                                                       onclick="return confirm('Yakin ingin membatalkan backorder ini?')">
                                                        <i class="bx bx-x me-1"></i> Batalkan
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-package text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-0">Tidak ada backorder</p>
                                        <small class="text-muted">Backorder akan muncul ketika stok tidak mencukupi</small>
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

    <!-- Fulfill Backorder Modal -->
    @if($showFulfillModal && $selectedBackorder)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Penuhi Backorder</h5>
                        <button type="button" class="btn-close" wire:click="$set('showFulfillModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6>Detail Backorder</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Produk</small>
                                            <p class="mb-1 fw-medium">{{ $selectedBackorder->product->nama_barang }}</p>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Customer</small>
                                            <p class="mb-1 fw-medium">{{ $selectedBackorder->orderItem->order->customer->nama_toko }}</p>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Qty Backorder</small>
                                            <p class="mb-0 fw-medium">{{ $selectedBackorder->jumlah_backorder }}</p>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Stok Tersedia</small>
                                            <p class="mb-0 fw-medium">{{ $selectedBackorder->product->stok_tersedia }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form wire:submit="fulfillBackorder">
                            <div class="mb-3">
                                <label class="form-label">Jumlah yang Dipenuhi <span class="text-danger">*</span></label>
                                <input type="number" wire:model="fulfillQuantity"
                                       class="form-control @error('fulfillQuantity') is-invalid @enderror"
                                       min="1" max="{{ $selectedBackorder->jumlah_backorder }}">
                                @error('fulfillQuantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Maksimal: {{ $selectedBackorder->jumlah_backorder }}</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea wire:model="fulfillNotes" class="form-control @error('fulfillNotes') is-invalid @enderror"
                                          rows="3" placeholder="Catatan pemenuhan backorder..."></textarea>
                                @error('fulfillNotes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" wire:click="$set('showFulfillModal', false)">
                                    Batal
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bx bx-check"></i> Penuhi Backorder
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Update Backorder Modal -->
    @if($showUpdateModal && $updateBackorder)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Backorder</h5>
                        <button type="button" class="btn-close" wire:click="$set('showUpdateModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="updateBackorder">
                            <div class="mb-3">
                                <label class="form-label">Expected Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="expectedDate"
                                       class="form-control @error('expectedDate') is-invalid @enderror">
                                @error('expectedDate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea wire:model="updateNotes" class="form-control @error('updateNotes') is-invalid @enderror"
                                          rows="3" placeholder="Update informasi backorder..."></textarea>
                                @error('updateNotes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" wire:click="$set('showUpdateModal', false)">
                                    Batal
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save"></i> Update Backorder
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
