<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Manajemen Pengiriman</h4>
            <p class="text-muted mb-0">Monitor dan kelola semua pengiriman</p>
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
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="bx bx-list-ol"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Pengiriman</small>
                            <h6 class="mb-0">{{ $totalDeliveries }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
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
                            <h6 class="mb-0">{{ $pendingDeliveries }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="bx bx-user-check"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Assigned</small>
                            <h6 class="mb-0">{{ $assignedDeliveries }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="bx bx-trip"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">In Progress</small>
                            <h6 class="mb-0">{{ $inProgressDeliveries }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="bx bx-check-circle"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Delivered</small>
                            <h6 class="mb-0">{{ $deliveredDeliveries }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-danger">
                                <i class="bx bx-x-circle"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Failed</small>
                            <h6 class="mb-0">{{ $failedDeliveries }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="bx bx-car"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Pengiriman Hari Ini</small>
                            <h6 class="mb-0">{{ $todayDeliveries }} pengiriman</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="bx bx-check-double"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Selesai Hari Ini</small>
                            <h6 class="mb-0">{{ $todayDelivered }} pengiriman</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmed Orders Alert -->
    @if($confirmedOrders->count() > 0)
        <div class="alert alert-info mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="bx bx-info-circle me-1"></i>
                    <strong>{{ $confirmedOrders->count() }} order terkonfirmasi</strong> menunggu untuk dibuat pengiriman.
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Buat Pengiriman
                    </button>
                    <ul class="dropdown-menu">
                        @foreach($confirmedOrders as $order)
                            <li>
                                <a class="dropdown-item" href="#" wire:click="createDeliveryFromOrder({{ $order->id }})">
                                    {{ $order->nomor_order }} - {{ $order->customer->nama_toko }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Pencarian</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari nomor order atau nama toko...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="assigned">Assigned</option>
                        <option value="k3_checked">K3 Checked</option>
                        <option value="in_progress">In Progress</option>
                        <option value="delivered">Delivered</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Driver</label>
                    <select wire:model.live="driverFilter" class="form-select">
                        <option value="">Semua Driver</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
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

    <!-- Deliveries Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Pengiriman</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Driver</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliveries as $delivery)
                            <tr>
                                <td>
                                    <span class="fw-medium">{{ $delivery->order->nomor_order }}</span>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-medium">{{ $delivery->order->customer->nama_toko }}</span>
                                        <br>
                                        <small class="text-muted">{{ Str::limit($delivery->order->customer->alamat, 30) }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($delivery->driver)
                                        <span class="fw-medium">{{ $delivery->driver->name }}</span>
                                    @else
                                        <span class="text-muted">Belum ditugaskan</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'assigned' => 'info',
                                            'k3_checked' => 'primary',
                                            'in_progress' => 'primary',
                                            'delivered' => 'success',
                                            'failed' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-label-{{ $statusColors[$delivery->status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-medium">{{ $delivery->created_at->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $delivery->created_at->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click.prevent="viewDelivery({{ $delivery->id }})">
                                                    <i class="bx bx-show me-1"></i> Lihat Detail
                                                </a>
                                            </li>
                                            @if($delivery->status === 'pending')
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click="openAssignModal({{ $delivery->id }})">
                                                        <i class="bx bx-user-plus me-1"></i> Tugaskan Driver
                                                    </a>
                                                </li>
                                            @endif
                                            @if($delivery->status === 'assigned')
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click="updateDeliveryStatus({{ $delivery->id }}, 'k3_checked')">
                                                        <i class="bx bx-check me-1"></i> K3 Checked
                                                    </a>
                                                </li>
                                            @endif
                                            @if($delivery->status === 'k3_checked')
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click="updateDeliveryStatus({{ $delivery->id }}, 'in_progress')">
                                                        <i class="bx bx-play me-1"></i> Mulai Pengiriman
                                                    </a>
                                                </li>
                                            @endif
                                            @if($delivery->status === 'in_progress')
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click="updateDeliveryStatus({{ $delivery->id }}, 'delivered')">
                                                        <i class="bx bx-check-circle me-1"></i> Selesai
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" wire:click="updateDeliveryStatus({{ $delivery->id }}, 'failed')">
                                                        <i class="bx bx-x me-1"></i> Gagal
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-car text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-0">Tidak ada pengiriman ditemukan</p>
                                        <small class="text-muted">Pengiriman akan muncul setelah order dikonfirmasi</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($deliveries->hasPages())
            <div class="card-footer">
                {{ $deliveries->links() }}
            </div>
        @endif
    </div>

    <!-- Assign Driver Modal -->
    @if($showAssignModal && $selectedDelivery)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tugaskan Driver</h5>
                        <button type="button" class="btn-close" wire:click="$set('showAssignModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6>Order: {{ $selectedDelivery->order->nomor_order }}</h6>
                            <p class="text-muted mb-0">{{ $selectedDelivery->order->customer->nama_toko }}</p>
                            <small class="text-muted">{{ $selectedDelivery->order->customer->alamat }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pilih Driver <span class="text-danger">*</span></label>
                            <select wire:model="selectedDriver" class="form-select">
                                <option value="">Pilih Driver</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showAssignModal', false)">Batal</button>
                        <button type="button" wire:click="assignDriver" class="btn btn-primary">
                            <i class="bx bx-check"></i> Tugaskan
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Delivery Detail Modal -->
    @if($showDeliveryModal && $viewDeliveryData)
        <div class="modal fade show" style="display: block;" tabindex="-1" wire:click.self="closeDeliveryModal">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Pengiriman - {{ $viewDeliveryData->order->nomor_order }}</h5>
                        <button type="button" class="btn-close" wire:click="closeDeliveryModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-4">
                            <!-- Delivery Info -->
                            <div class="col-md-6">
                                <h6>Informasi Pengiriman</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Order:</td>
                                        <td><strong>{{ $viewDeliveryData->order->nomor_order }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Status:</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'assigned' => 'info',
                                                    'k3_checked' => 'primary',
                                                    'in_progress' => 'primary',
                                                    'delivered' => 'success',
                                                    'failed' => 'danger'
                                                ];
                                            @endphp
                                            <span class="badge bg-label-{{ $statusColors[$viewDeliveryData->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $viewDeliveryData->status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Driver:</td>
                                        <td>{{ $viewDeliveryData->driver->name ?? 'Belum ditugaskan' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Tanggal Dibuat:</td>
                                        <td>{{ $viewDeliveryData->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    @if($viewDeliveryData->assigned_at)
                                        <tr>
                                            <td>Ditugaskan:</td>
                                            <td>{{ $viewDeliveryData->assigned_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endif
                                    @if($viewDeliveryData->delivered_at)
                                        <tr>
                                            <td>Selesai:</td>
                                            <td>{{ $viewDeliveryData->delivered_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>

                            <!-- Customer Info -->
                            <div class="col-md-6">
                                <h6>Informasi Customer</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Nama Toko:</td>
                                        <td><strong>{{ $viewDeliveryData->order->customer->nama_toko }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Pemilik:</td>
                                        <td>{{ $viewDeliveryData->order->customer->nama_pemilik ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Alamat:</td>
                                        <td>{{ $viewDeliveryData->order->customer->alamat }}</td>
                                    </tr>
                                    <tr>
                                        <td>Telepon:</td>
                                        <td>{{ $viewDeliveryData->order->customer->phone }}</td>
                                    </tr>
                                    @if($viewDeliveryData->order->customer->latitude && $viewDeliveryData->order->customer->longitude)
                                        <tr>
                                            <td>Lokasi:</td>
                                            <td>
                                                <a href="https://maps.google.com/?q={{ $viewDeliveryData->order->customer->latitude }},{{ $viewDeliveryData->order->customer->longitude }}"
                                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bx bx-map"></i> Lihat di Maps
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>

                            <!-- Order Items -->
                            <div class="col-12">
                                <h6>Item Order</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Produk</th>
                                                <th>Jumlah</th>
                                                <th>Harga</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($viewDeliveryData->order->orderItems as $item)
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <span class="fw-medium">{{ $item->product->nama_barang }}</span>
                                                            <br>
                                                            <small class="text-muted">{{ $item->product->kode_item }}</small>
                                                        </div>
                                                    </td>
                                                    <td>{{ $item->jumlah_pesan }} {{ $item->product->jenis }}</td>
                                                    <td>Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                                    <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="3">Total</th>
                                                <th>Rp {{ number_format($viewDeliveryData->order->total_amount, 0, ',', '.') }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Tracking History -->
                            @if($viewDeliveryData->trackingHistory && $viewDeliveryData->trackingHistory->count() > 0)
                                <div class="col-12">
                                    <h6>Riwayat Tracking</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Waktu</th>
                                                    <th>Koordinat</th>
                                                    <th>Catatan</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($viewDeliveryData->trackingHistory->take(5) as $track)
                                                    <tr>
                                                        <td>{{ $track->recorded_at->format('d/m/Y H:i:s') }}</td>
                                                        <td>
                                                            <small>{{ number_format($track->latitude, 6) }}, {{ number_format($track->longitude, 6) }}</small>
                                                        </td>
                                                        <td>{{ $track->notes ?? '-' }}</td>
                                                        <td>
                                                            <a href="https://maps.google.com/?q={{ $track->latitude }},{{ $track->longitude }}"
                                                               target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="bx bx-map"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeDeliveryModal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
