<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Manajemen Pengiriman</h4>
            <p class="text-muted mb-0">Kelola assignment pengiriman ke supir dan monitor status delivery</p>
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
                            <small class="text-muted d-block">Menunggu Assignment</small>
                            <h6 class="mb-0">{{ $confirmedOrders->count() }}</h6>
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
                                <i class="bx bx-user-check"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Assigned</small>
                            <h6 class="mb-0">{{ $deliveries->where('status', 'assigned')->count() }}</h6>
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
                                <i class="bx bx-car"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Dalam Perjalanan</small>
                            <h6 class="mb-0">{{ $deliveries->where('status', 'in_progress')->count() }}</h6>
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
                            <small class="text-muted d-block">Delivered Hari Ini</small>
                            <h6 class="mb-0">{{ $deliveries->where('status', 'delivered')->whereDate('delivered_at', today())->count() }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Waiting for Assignment -->
    @if($confirmedOrders->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Order Menunggu Assignment ({{ $confirmedOrders->count() }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Sales</th>
                                <th>Total</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($confirmedOrders as $order)
                                <tr>
                                    <td>
                                        <span class="fw-medium">{{ $order->nomor_order }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="fw-medium">{{ $order->customer->nama_toko }}</span>
                                            <br>
                                            <small class="text-muted">{{ $order->customer->alamat }}</small>
                                        </div>
                                    </td>
                                    <td>{{ $order->sales->name }}</td>
                                    <td>
                                        <span class="fw-medium">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                    </td>
                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <button wire:click="openAssignModal({{ $order->id }})"
                                                class="btn btn-sm btn-primary">
                                            <i class="bx bx-user-plus"></i> Assign Supir
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari customer, supir, atau rute...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="assigned">Assigned</option>
                        <option value="k3_checked">K3 Checked</option>
                        <option value="in_progress">In Progress</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Supir</label>
                    <select wire:model.live="supirFilter" class="form-select">
                        <option value="">Semua Supir</option>
                        @foreach($availableSupirs as $supir)
                            <option value="{{ $supir->id }}">{{ $supir->name }}</option>
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
                            <th>Supir</th>
                            <th>Rute</th>
                            <th>Status</th>
                            <th>Assigned</th>
                            <th>ETA</th>
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
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ substr($delivery->supir->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <span>{{ $delivery->supir->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $delivery->rute_kota }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'assigned' => 'info',
                                            'k3_checked' => 'warning',
                                            'in_progress' => 'primary',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $statusLabels = [
                                            'assigned' => 'Assigned',
                                            'k3_checked' => 'K3 Checked',
                                            'in_progress' => 'In Progress',
                                            'delivered' => 'Delivered',
                                            'cancelled' => 'Cancelled'
                                        ];
                                    @endphp
                                    <span class="badge bg-label-{{ $statusColors[$delivery->status] ?? 'secondary' }}">
                                        {{ $statusLabels[$delivery->status] ?? $delivery->status }}
                                    </span>
                                </td>
                                <td>{{ $delivery->assigned_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($delivery->estimated_arrival)
                                        {{ \Carbon\Carbon::parse($delivery->estimated_arrival)->format('d/m/Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click="viewDelivery({{ $delivery->id }})">
                                                    <i class="bx bx-show me-1"></i> Lihat Detail
                                                </a>
                                            </li>
                                            @if(in_array($delivery->status, ['assigned', 'k3_checked']))
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#"
                                                       wire:click="cancelDelivery({{ $delivery->id }})"
                                                       onclick="return confirm('Yakin ingin membatalkan pengiriman ini?')">
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
                                <td colspan="8" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-package text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-0">Tidak ada data pengiriman</p>
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

    <!-- Assign Delivery Modal -->
    @if($showAssignModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Assign Pengiriman</h5>
                        <button type="button" class="btn-close" wire:click="$set('showAssignModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        @if($selectedOrder)
                            <div class="mb-3">
                                <h6>Detail Order</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Nomor Order</small>
                                                <p class="mb-1 fw-medium">{{ $selectedOrder->nomor_order }}</p>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Customer</small>
                                                <p class="mb-1 fw-medium">{{ $selectedOrder->customer->nama_toko }}</p>
                                            </div>
                                            <div class="col-12">
                                                <small class="text-muted">Alamat</small>
                                                <p class="mb-0">{{ $selectedOrder->customer->alamat }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form wire:submit="assignDelivery">
                                <div class="mb-3">
                                    <label class="form-label">Pilih Supir <span class="text-danger">*</span></label>
                                    <select wire:model="selectedSupir" class="form-select @error('selectedSupir') is-invalid @enderror">
                                        <option value="">Pilih Supir</option>
                                        @foreach($availableSupirs as $supir)
                                            <option value="{{ $supir->id }}">{{ $supir->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedSupir')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Rute/Kota Tujuan <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="rutaKota" class="form-control @error('rutaKota') is-invalid @enderror" placeholder="Contoh: Jakarta Selatan">
                                    @error('rutaKota')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Estimasi Waktu Tiba <span class="text-danger">*</span></label>
                                    <input type="datetime-local" wire:model="estimatedArrival" class="form-control @error('estimatedArrival') is-invalid @enderror">
                                    @error('estimatedArrival')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" wire:click="$set('showAssignModal', false)">
                                        Batal
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-check"></i> Assign Pengiriman
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- View Delivery Modal -->
    @if($showViewModal && $viewDelivery)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Pengiriman</h5>
                        <button type="button" class="btn-close" wire:click="$set('showViewModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Informasi Order</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Nomor Order</td>
                                        <td>{{ $viewDelivery->order->nomor_order }}</td>
                                    </tr>
                                    <tr>
                                        <td>Customer</td>
                                        <td>{{ $viewDelivery->order->customer->nama_toko }}</td>
                                    </tr>
                                    <tr>
                                        <td>Sales</td>
                                        <td>{{ $viewDelivery->order->sales->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>Total Amount</td>
                                        <td>Rp {{ number_format($viewDelivery->order->total_amount, 0, ',', '.') }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Informasi Pengiriman</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Supir</td>
                                        <td>{{ $viewDelivery->supir->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>Rute</td>
                                        <td>{{ $viewDelivery->rute_kota }}</td>
                                    </tr>
                                    <tr>
                                        <td>Status</td>
                                        <td>
                                            <span class="badge bg-label-{{ $statusColors[$viewDelivery->status] ?? 'secondary' }}">
                                                {{ $statusLabels[$viewDelivery->status] ?? $viewDelivery->status }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Assigned At</td>
                                        <td>{{ $viewDelivery->assigned_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    @if($viewDelivery->estimated_arrival)
                                        <tr>
                                            <td>ETA</td>
                                            <td>{{ \Carbon\Carbon::parse($viewDelivery->estimated_arrival)->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>

                        @if($viewDelivery->trackingLogs->count() > 0)
                            <hr>
                            <h6>Tracking History</h6>
                            <div class="timeline">
                                @foreach($viewDelivery->trackingLogs->sortByDesc('created_at') as $log)
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">{{ $log->status }}</h6>
                                            <p class="timeline-text">{{ $log->notes }}</p>
                                            <small class="text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showViewModal', false)">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
