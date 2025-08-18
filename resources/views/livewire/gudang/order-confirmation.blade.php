<div>
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1">Konfirmasi Order</h4>
            <p class="text-muted mb-0">Konfirmasi ketersediaan stok untuk order masuk</p>
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
                <div class="col-12 col-md-6">
                    <label class="form-label">Cari Order</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Nomor order, toko, atau sales...">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Filter Status</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu Konfirmasi</option>
                        <option value="confirmed">Dikonfirmasi</option>
                        <option value="ready">Siap Kirim</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Order</h5>
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
                    .mobile-cards .order-info-cell {
                        display: block; /* Override the flex for the main order info */
                        padding-bottom: 1rem;
                        margin-bottom: 1rem;
                        border-bottom: 1px solid #eee;
                    }
                    .mobile-cards .order-info-cell:before {
                        display: none; /* No "Order:" label */
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
                            <th>Order</th>
                            <th>Sales</th>
                            <th>Pelanggan</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td data-label="Order" class="order-info-cell">
                                    <div>
                                        <span class="fw-medium">{{ $order->nomor_order }}</span>
                                        <br>
                                        <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                </td>
                                <td data-label="Sales">
                                    <div>
                                        <span class="fw-medium">{{ $order->sales->name }}</span>
                                        <br>
                                        <small class="text-muted">{{ $order->sales->phone }}</small>
                                    </div>
                                </td>
                                <td data-label="Pelanggan">
                                    <div>
                                        <span class="fw-medium">{{ $order->customer->nama_toko }}</span>
                                        <br>
                                        <small class="text-muted">{{ $order->customer->phone }}</small>
                                    </div>
                                </td>
                                <td data-label="Items">
                                    <div>
                                        <span class="fw-medium">{{ $order->orderItems->count() }} item</span>
                                        @foreach($order->orderItems->take(2) as $item)
                                            <br><small class="text-muted">{{ $item->product->nama_barang }} ({{ $item->jumlah_pesan }})</small>
                                        @endforeach
                                        @if($order->orderItems->count() > 2)
                                            <br><small class="text-muted">+{{ $order->orderItems->count() - 2 }} lainnya</small>
                                        @endif
                                    </div>
                                </td>
                                <td data-label="Total">
                                    <span class="fw-medium">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                </td>
                                <td data-label="Status">
                                    <span class="badge bg-label-{{
                                        $order->status === 'pending' ? 'warning' :
                                        ($order->status === 'confirmed' ? 'info' :
                                        ($order->status === 'ready' ? 'success' :
                                        ($order->status === 'cancelled' ? 'danger' : 'secondary')))
                                    }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td data-label="Aksi" class="actions-cell">
                                    @if($order->status === 'pending')
                                        <div class="btn-group">
                                            <button wire:click="openConfirmModal({{ $order->id }})" class="btn btn-sm btn-primary">
                                                <i class="bx bx-check me-1"></i> Konfirmasi
                                            </button>
                                            <button wire:click="rejectOrder({{ $order->id }})"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Yakin ingin menolak order ini?')">
                                                <i class="bx bx-x me-1"></i> Tolak
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-package text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-1">Tidak ada order untuk dikonfirmasi</p>
                                        <small class="text-muted">Order akan muncul di sini setelah sales membuat pesanan.</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
            <div class="card-footer">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <!-- Confirmation Modal -->
    @if($showConfirmModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Ketersediaan Stok</h5>
                        <button type="button" class="btn-close" wire:click="closeConfirmModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            Periksa ketersediaan stok untuk setiap item dan tentukan jumlah yang dapat dipenuhi.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th>Diminta</th>
                                        <th>Stok Tersedia</th>
                                        <th>Dikonfirmasi</th>
                                        <th>Backorder</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($confirmItems as $index => $item)
                                        <tr class="{{ $item['status'] === 'backorder' ? 'table-warning' : ($item['status'] === 'partial' ? 'table-info' : '') }}">
                                            <td>
                                                <div class="fw-medium">{{ $item['product_name'] }}</div>
                                                <small class="text-muted">Rp {{ number_format($item['price'], 0, ',', '.') }}</small>
                                            </td>
                                            <td>
                                                <span class="fw-medium">{{ $item['requested_qty'] }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-medium {{ $item['available_stock'] <= 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ $item['available_stock'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <input type="number"
                                                       wire:change="updateConfirmedQty({{ $index }}, $event.target.value)"
                                                       value="{{ $item['confirmed_qty'] }}"
                                                       class="form-control form-control-sm"
                                                       style="width: 100px;"
                                                       min="0"
                                                       max="{{ $item['available_stock'] }}">
                                            </td>
                                            <td>
                                                <span class="fw-medium {{ $item['backorder_qty'] > 0 ? 'text-warning' : 'text-muted' }}">
                                                    {{ $item['backorder_qty'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-{{
                                                    $item['status'] === 'available' ? 'success' :
                                                    ($item['status'] === 'partial' ? 'warning' : 'danger')
                                                }}">
                                                    {{ ucfirst($item['status']) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Tersedia Penuh</h6>
                                        <h4>{{ collect($confirmItems)->where('status', 'available')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Sebagian</h6>
                                        <h4>{{ collect($confirmItems)->where('status', 'partial')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Backorder</h6>
                                        <h4>{{ collect($confirmItems)->where('status', 'backorder')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeConfirmModal">Batal</button>
                        <button type="button" wire:click="confirmOrder" class="btn btn-primary">
                            <i class="bx bx-check me-1"></i> Konfirmasi Order
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
