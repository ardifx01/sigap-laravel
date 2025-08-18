<div>
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1">Manajemen Pembayaran</h4>
            <p class="text-muted mb-0">Monitor dan kelola semua pembayaran</p>
        </div>
        <button wire:click="openPaymentModal" class="btn btn-primary align-self-md-auto align-self-stretch">
            <i class="bx bx-plus"></i> Catat Pembayaran
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

    <!-- Payment Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary"><i class="bx bx-credit-card"></i></span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total</small>
                            <h6 class="mb-0">{{ $totalPayments }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-success"><i class="bx bx-check-circle"></i></span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Lunas</small>
                            <h6 class="mb-0">{{ $lunasPayments }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-warning"><i class="bx bx-time"></i></span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Belum Lunas</small>
                            <h6 class="mb-0">{{ $belumLunasPayments }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-danger"><i class="bx bx-x-circle"></i></span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Overdue</small>
                            <h6 class="mb-0">{{ $overduePayments }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-info"><i class="bx bx-money"></i></span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Bayar</small>
                            <h6 class="mb-0">Rp {{ number_format($totalBayar, 0, ',', '.') }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-success"><i class="bx bx-calendar-check"></i></span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Hari Ini</small>
                            <h6 class="mb-0">{{ $todayPayments }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="mb-4"><i class="bx bx-credit-card me-2"></i>Metode Pembayaran</h6>
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <div class="text-center">
                        <div class="avatar avatar-lg mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-primary"><i class="bx bx-money fs-4"></i></span>
                        </div>
                        <h6 class="mb-1">Tunai</h6>
                        <p class="text-muted mb-0">Rp {{ number_format($tunaiPayments, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="text-center">
                        <div class="avatar avatar-lg mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-info"><i class="bx bx-transfer fs-4"></i></span>
                        </div>
                        <h6 class="mb-1">Transfer</h6>
                        <p class="text-muted mb-0">Rp {{ number_format($transferPayments, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="text-center">
                        <div class="avatar avatar-lg mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-warning"><i class="bx bx-receipt fs-4"></i></span>
                        </div>
                        <h6 class="mb-1">Giro</h6>
                        <p class="text-muted mb-0">Rp {{ number_format($giroPayments, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-3">
                    <label class="form-label">Pencarian</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari nomor order atau nama toko...">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Status</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="lunas">Lunas</option>
                        <option value="belum_lunas">Belum Lunas</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Metode</label>
                    <select wire:model.live="methodFilter" class="form-select">
                        <option value="">Semua Metode</option>
                        <option value="tunai">Tunai</option>
                        <option value="transfer">Transfer</option>
                        <option value="giro">Giro</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Customer</label>
                    <select wire:model.live="customerFilter" class="form-select">
                        <option value="">Semua Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->nama_toko }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Tanggal</label>
                    <input type="date" wire:model.live="dateFilter" class="form-control">
                </div>
                <div class="col-12 col-md-1">
                    <label class="form-label">Per Page</label>
                    <select wire:model.live="perPage" class="form-select">
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Pembayaran</h5>
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
                    .mobile-cards .payment-info-cell {
                        display: block;
                        padding-bottom: 1rem;
                        margin-bottom: 1rem;
                        border-bottom: 1px solid #eee;
                    }
                    .mobile-cards .payment-info-cell:before {
                        display: none;
                    }
                    .mobile-cards .actions-cell {
                        justify-content: flex-end;
                    }
                    .mobile-cards .actions-cell:before {
                        display: none;
                    }
                }
            </style>
            <div class="table-responsive">
                <table class="table table-hover mb-0 mobile-cards">
                    <thead class="table-light d-none d-md-table-header-group">
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Jumlah</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td data-label="Order" class="payment-info-cell">
                                    <span class="fw-medium">{{ $payment->order->nomor_order }}</span>
                                </td>
                                <td data-label="Customer">
                                    <div>
                                        <span class="fw-medium">{{ $payment->order->customer->nama_toko }}</span>
                                    </div>
                                </td>
                                <td data-label="Jumlah">
                                    <div>
                                        <span class="fw-medium">Rp {{ number_format($payment->jumlah_tagihan, 0, ',', '.') }}</span>
                                        <br>
                                        <small class="text-muted">Bayar: Rp {{ number_format($payment->jumlah_bayar, 0, ',', '.') }}</small>
                                    </div>
                                </td>
                                <td data-label="Metode">
                                    @php
                                        $methodColors = [
                                            'tunai' => 'success',
                                            'transfer' => 'info',
                                            'giro' => 'warning'
                                        ];
                                    @endphp
                                    <span class="badge bg-label-{{ $methodColors[$payment->jenis_pembayaran] ?? 'secondary' }}">
                                        {{ ucfirst($payment->jenis_pembayaran) }}
                                    </span>
                                </td>
                                <td data-label="Status">
                                    @php
                                        $statusColors = [
                                            'lunas' => 'success',
                                            'belum_lunas' => 'warning',
                                            'overdue' => 'danger'
                                        ];
                                        $statusLabels = [
                                            'lunas' => 'Lunas',
                                            'belum_lunas' => 'Belum Lunas',
                                            'overdue' => 'Overdue'
                                        ];
                                    @endphp
                                    <span class="badge bg-label-{{ $statusColors[$payment->status] ?? 'secondary' }}">
                                        {{ $statusLabels[$payment->status] ?? ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td data-label="Tanggal">
                                    <div>
                                        @if($payment->tanggal_bayar)
                                            <div class="fw-medium">{{ $payment->tanggal_bayar->format('d/m/Y') }}</div>
                                            <small class="text-muted">{{ $payment->tanggal_bayar->format('H:i') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td data-label="Aksi" class="actions-cell">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click.prevent="viewPayment({{ $payment->id }})">
                                                    <i class="bx bx-show me-1"></i> Lihat Detail
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click="openPaymentModal({{ $payment->id }})">
                                                    <i class="bx bx-edit me-1"></i> Edit
                                                </a>
                                            </li>
                                            @if($payment->status === 'pending')
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click="updatePaymentStatus({{ $payment->id }}, 'paid')">
                                                        <i class="bx bx-check me-1"></i> Tandai Lunas
                                                    </a>
                                                </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#"
                                                   wire:click="deletePayment({{ $payment->id }})"
                                                   onclick="return confirm('Yakin ingin menghapus pembayaran ini?')">
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
                                        <i class="bx bx-credit-card text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-0">Tidak ada pembayaran ditemukan</p>
                                        <small class="text-muted">Catat pembayaran baru atau ubah filter pencarian</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payments->hasPages())
            <div class="card-footer">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

    <!-- Payment Modal -->
    @if($showPaymentModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $paymentId ? 'Edit Pembayaran' : 'Catat Pembayaran' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showPaymentModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="savePayment">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Order <span class="text-danger">*</span></label>
                                    <select wire:model="order_id" class="form-select @error('order_id') is-invalid @enderror">
                                        <option value="">Pilih Order</option>
                                        @foreach($orders as $order)
                                            <option value="{{ $order->id }}">
                                                {{ $order->nomor_order }} - {{ $order->customer->nama_toko }}
                                                (Rp {{ number_format($order->total_amount, 0, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('order_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Jumlah Tagihan <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="jumlah_tagihan" class="form-control @error('jumlah_tagihan') is-invalid @enderror" min="1">
                                    @error('jumlah_tagihan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Jumlah Bayar <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="jumlah_bayar" class="form-control @error('jumlah_bayar') is-invalid @enderror" min="0">
                                    @error('jumlah_bayar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Jenis Pembayaran <span class="text-danger">*</span></label>
                                    <select wire:model="jenis_pembayaran" class="form-select @error('jenis_pembayaran') is-invalid @enderror">
                                        <option value="tunai">Tunai</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="giro">Giro</option>
                                    </select>
                                    @error('jenis_pembayaran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Tanggal Bayar <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="tanggal_bayar" class="form-control @error('tanggal_bayar') is-invalid @enderror">
                                    @error('tanggal_bayar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Bukti Transfer</label>
                                    <input type="file" wire:model="bukti_transfer" class="form-control @error('bukti_transfer') is-invalid @enderror" accept="image/*">
                                    @error('bukti_transfer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($bukti_transfer)
                                        <div class="mt-2">
                                            <img src="{{ $bukti_transfer->temporaryUrl() }}" alt="Preview" class="img-thumbnail" style="max-height: 150px;">
                                        </div>
                                    @endif
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
                        <button type="button" class="btn btn-secondary" wire:click="$set('showPaymentModal', false)">Batal</button>
                        <button type="button" wire:click="savePayment" class="btn btn-primary">
                            <i class="bx bx-save"></i> {{ $paymentId ? 'Update' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Payment Detail Modal -->
    @if($showViewModal && $selectedPayment)
        <div class="modal fade show" style="display: block;" tabindex="-1" wire:click.self="closeViewModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Pembayaran</h5>
                        <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-4">
                            <!-- Payment Info -->
                            <div class="col-12 col-md-6">
                                <h6>Informasi Pembayaran</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Order:</td>
                                        <td><strong>{{ $selectedPayment->order->nomor_order }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Jumlah:</td>
                                        <td><strong>Rp {{ number_format($selectedPayment->jumlah_bayar, 0, ',', '.') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Metode:</td>
                                        <td>
                                            @php
                                                $methodColors = [
                                                    'tunai' => 'success',
                                                    'transfer' => 'info',
                                                    'giro' => 'warning'
                                                ];
                                            @endphp
                                            <span class="badge bg-label-{{ $methodColors[$selectedPayment->jenis_pembayaran] ?? 'secondary' }}">
                                                {{ ucfirst($selectedPayment->jenis_pembayaran) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Status:</td>
                                        <td>
                                            <span class="badge bg-label-{{ $selectedPayment->status === 'lunas' ? 'success' : 'warning' }}">
                                                {{ ucfirst($selectedPayment->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Tanggal Bayar:</td>
                                        <td>{{ $selectedPayment->tanggal_bayar ? $selectedPayment->tanggal_bayar->format('d/m/Y') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Dicatat Oleh:</td>
                                        <td>{{ $selectedPayment->order->sales->name ?? 'System' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Dicatat Pada:</td>
                                        <td>{{ $selectedPayment->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Customer & Order Info -->
                            <div class="col-12 col-md-6">
                                <h6>Informasi Customer & Order</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Nama Toko:</td>
                                        <td><strong>{{ $selectedPayment->order->customer->nama_toko }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Pemilik:</td>
                                        <td>{{ $selectedPayment->order->customer->nama_pemilik ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Telepon:</td>
                                        <td>{{ $selectedPayment->order->customer->phone }}</td>
                                    </tr>
                                    <tr>
                                        <td>Total Order:</td>
                                        <td><strong>Rp {{ number_format($selectedPayment->order->total_amount, 0, ',', '.') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Status Order:</td>
                                        <td>
                                            <span class="badge bg-label-info">
                                                {{ ucfirst($selectedPayment->order->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Notes -->
                            @if($selectedPayment->catatan)
                                <div class="col-12">
                                    <h6>Catatan</h6>
                                    <p class="text-muted">{{ $selectedPayment->catatan }}</p>
                                </div>
                            @endif

                            <!-- Bukti Transfer -->
                            @if($selectedPayment->bukti_transfer)
                                <div class="col-12">
                                    <h6>Bukti Transfer</h6>
                                    <img src="{{ asset('storage/' . $selectedPayment->bukti_transfer) }}" alt="Bukti Transfer" class="img-fluid rounded" style="max-height: 300px;">
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeViewModal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
