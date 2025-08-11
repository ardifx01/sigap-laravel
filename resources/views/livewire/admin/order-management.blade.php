<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Manajemen Order</h4>
            <p class="text-muted mb-0">Monitor dan kelola semua pesanan pelanggan</p>
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
                                <i class="bx bx-cart"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Order</small>
                            <h6 class="mb-0">{{ $totalOrders }}</h6>
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
                            <h6 class="mb-0">{{ $pendingOrders }}</h6>
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
                                <i class="bx bx-check"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Confirmed</small>
                            <h6 class="mb-0">{{ $confirmedOrders }}</h6>
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
                                <i class="bx bx-car"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Shipped</small>
                            <h6 class="mb-0">{{ $shippedOrders }}</h6>
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
                            <h6 class="mb-0">{{ $deliveredOrders }}</h6>
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
                                <i class="bx bx-x"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Cancelled</small>
                            <h6 class="mb-0">{{ $cancelledOrders }}</h6>
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
                            <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="bx  bx-list-ol"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Order Hari Ini</small>
                            <h6 class="mb-0">{{ $todayOrders }} order</h6>
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
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="bx bx-money"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Nilai Order Hari Ini</small>
                            <h6 class="mb-0">Rp {{ number_format($todayValue, 0, ',', '.') }}</h6>
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
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari nomor order atau nama toko...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sales</label>
                    <select wire:model.live="salesFilter" class="form-select">
                        <option value="">Semua Sales</option>
                        @foreach($salesUsers as $sales)
                            <option value="{{ $sales->id }}">{{ $sales->name }}</option>
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
                <div class="col-md-1">
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

    <!-- Orders Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Order</h5>
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
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>
                                    <span class="fw-medium">{{ $order->nomor_order }}</span>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-medium">{{ $order->customer->nama_toko }}</span>
                                        <br>
                                        <small class="text-muted">{{ $order->customer->nama_pemilik }}</small>
                                    </div>
                                </td>
                                <td>{{ $order->sales->name }}</td>
                                <td>
                                    <span class="fw-medium">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'confirmed' => 'info',
                                            'shipped' => 'primary',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-label-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-medium">{{ $order->created_at->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click="viewOrder({{ $order->id }})">
                                                    <i class="bx bx-show me-1"></i> Lihat Detail
                                                </a>
                                            </li>
                                            @if($order->status === 'pending')
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click="updateOrderStatus({{ $order->id }}, 'confirmed')">
                                                        <i class="bx bx-check me-1"></i> Konfirmasi
                                                    </a>
                                                </li>
                                            @endif
                                            @if($order->status === 'confirmed')
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click="updateOrderStatus({{ $order->id }}, 'shipped')">
                                                        <i class="bx bx-truck me-1"></i> Kirim
                                                    </a>
                                                </li>
                                            @endif
                                            @if($order->status === 'shipped')
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click="updateOrderStatus({{ $order->id }}, 'delivered')">
                                                        <i class="bx bx-check-circle me-1"></i> Selesai
                                                    </a>
                                                </li>
                                            @endif
                                            @if(in_array($order->status, ['pending', 'confirmed']))
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#"
                                                       wire:click="cancelOrder({{ $order->id }})"
                                                       onclick="return confirm('Yakin ingin membatalkan order ini?')">
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
                                <td colspan="7" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-cart text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-0">Tidak ada order ditemukan</p>
                                        <small class="text-muted">Order akan muncul setelah sales membuat pesanan</small>
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

    <!-- Order Detail Modal -->
    @if($showOrderModal && $viewOrder)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Order - {{ $viewOrder->nomor_order }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showOrderModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-4">
                            <!-- Order Info -->
                            <div class="col-md-6">
                                <h6>Informasi Order</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Nomor Order:</td>
                                        <td><strong>{{ $viewOrder->nomor_order }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Tanggal:</td>
                                        <td>{{ $viewOrder->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Status:</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'confirmed' => 'info',
                                                    'shipped' => 'primary',
                                                    'delivered' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                            @endphp
                                            <span class="badge bg-label-{{ $statusColors[$viewOrder->status] ?? 'secondary' }}">
                                                {{ ucfirst($viewOrder->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Total Amount:</td>
                                        <td><strong>Rp {{ number_format($viewOrder->total_amount, 0, ',', '.') }}</strong></td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Customer Info -->
                            <div class="col-md-6">
                                <h6>Informasi Customer</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Nama Toko:</td>
                                        <td><strong>{{ $viewOrder->customer->nama_toko }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Pemilik:</td>
                                        <td>{{ $viewOrder->customer->nama_pemilik }}</td>
                                    </tr>
                                    <tr>
                                        <td>Alamat:</td>
                                        <td>{{ $viewOrder->customer->alamat }}</td>
                                    </tr>
                                    <tr>
                                        <td>Telepon:</td>
                                        <td>{{ $viewOrder->customer->telepon }}</td>
                                    </tr>
                                    <tr>
                                        <td>Sales:</td>
                                        <td>{{ $viewOrder->sales->name }}</td>
                                    </tr>
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
                                                <th>Harga</th>
                                                <th>Jumlah</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($viewOrder->orderItems as $item)
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <span class="fw-medium">{{ $item->product->nama_barang }}</span>
                                                            <br>
                                                            <small class="text-muted">{{ $item->product->kode_item }}</small>
                                                        </div>
                                                    </td>
                                                    <td>Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                                    <td>{{ $item->jumlah }} {{ $item->product->satuan }}</td>
                                                    <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="3">Total</th>
                                                <th>Rp {{ number_format($viewOrder->total_amount, 0, ',', '.') }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Delivery Info -->
                            @if($viewOrder->delivery)
                                <div class="col-12">
                                    <h6>Informasi Pengiriman</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Driver:</td>
                                            <td>{{ $viewOrder->delivery->driver->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Status Pengiriman:</td>
                                            <td>
                                                <span class="badge bg-label-info">
                                                    {{ ucfirst($viewOrder->delivery->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Tanggal Kirim:</td>
                                            <td>{{ $viewOrder->delivery->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif

                            <!-- Payment Info -->
                            @if($viewOrder->payments->count() > 0)
                                <div class="col-12">
                                    <h6>Informasi Pembayaran</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Jumlah</th>
                                                    <th>Metode</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($viewOrder->payments as $payment)
                                                    <tr>
                                                        <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                                        <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                                        <td>{{ ucfirst($payment->payment_method) }}</td>
                                                        <td>
                                                            <span class="badge bg-label-{{ $payment->status === 'paid' ? 'success' : 'warning' }}">
                                                                {{ ucfirst($payment->status) }}
                                                            </span>
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
                        <button type="button" class="btn btn-secondary" wire:click="$set('showOrderModal', false)">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
