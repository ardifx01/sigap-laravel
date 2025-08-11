@section('title', 'Dashboard Admin')
<x-layouts.app :title="'Dashboard Admin'">
    @php
        $totalSales = \App\Models\Order::where('status', 'delivered')->sum('total_amount');
        $activeOrders = \App\Models\Order::whereIn('status', ['pending', 'confirmed', 'ready', 'assigned', 'shipped'])->count();
        $activeDeliveries = \App\Models\Order::whereIn('status', ['assigned', 'shipped'])->count();
        $criticalStock = \App\Models\Product::whereColumn('stok_tersedia', '<=', 'stok_minimum')->count();
        $totalUsers = \App\Models\User::where('is_active', true)->count();
        $totalCustomers = \App\Models\Customer::where('is_active', true)->count();
        $totalProducts = \App\Models\Product::where('is_active', true)->count();
        $recentOrders = \App\Models\Order::with(['customer', 'sales'])->latest()->take(5)->get();
    @endphp

    <div class="row g-4">
        <!-- Welcome Card -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="bx bx-user-check fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-1">Selamat Datang, {{ auth()->user()->name }}!</h5>
                            <p class="mb-0 text-muted">Dashboard Administrator - Aplikasi Sales Gudang</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="bx bx-dollar-circle"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Penjualan</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">Rp {{ number_format($totalSales, 0, ',', '.') }}</h6>
                                <small class="text-success fw-semibold">
                                    <i class="bx bx-chevron-up"></i>
                                    {{ $totalSales > 0 ? '+' : '' }}{{ number_format(($totalSales / max(1, $totalSales)) * 100, 1) }}%
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-warning">
                                <i class="bx bx-package"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Order Aktif</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">{{ $activeOrders }}</h6>
                                <small class="text-muted fw-semibold">orders</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="bx bx-car"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Pengiriman</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">{{ $activeDeliveries }}</h6>
                                <small class="text-muted fw-semibold">aktif</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-danger">
                                <i class="bx bx-error-circle"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Stok Kritis</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1 {{ $criticalStock > 0 ? 'text-danger' : '' }}">{{ $criticalStock }}</h6>
                                <small class="text-muted fw-semibold">items</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="{{ route('admin.users') }}" class="btn btn-outline-primary w-100">
                                <i class="bx bx-user-plus me-1"></i>
                                Kelola User
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.reports') }}" class="btn btn-outline-success w-100">
                                <i class="bx bx-bar-chart-alt-2 me-1"></i>
                                Laporan
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.activity-logs') }}" class="btn btn-outline-info w-100">
                                <i class="bx bx-history me-1"></i>
                                Activity Log
                            </a>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-warning w-100">
                                <i class="bx bx-cog me-1"></i>
                                Pengaturan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Map Placeholder -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Live Tracking Supir</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="text-center">
                            <i class="bx bx-map text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">Live Map akan ditampilkan di sini</p>
                            <small class="text-muted">Fitur akan aktif setelah implementasi GPS tracking</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Order Terbaru</h5>
                </div>
                <div class="card-body">
                    @if($recentOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Sales</th>
                                        <th>Pelanggan</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td>{{ $order->nomor_order }}</td>
                                            <td>{{ $order->sales->name }}</td>
                                            <td>{{ $order->customer->nama_toko }}</td>
                                            <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                            <td>
                                                <span class="badge bg-label-{{
                                                    $order->status === 'pending' ? 'warning' :
                                                    ($order->status === 'confirmed' ? 'info' :
                                                    ($order->status === 'delivered' ? 'success' : 'secondary'))
                                                }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $order->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bx bx-time text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">Belum ada order</p>
                            <small class="text-muted">Order terbaru akan ditampilkan di sini</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
