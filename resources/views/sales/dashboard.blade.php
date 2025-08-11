@section('title', 'Dashboard Sales')
<x-layouts.app :title="'Dashboard Sales'">
    @php
        $totalCustomers = \App\Models\Customer::where('sales_id', auth()->id())->where('is_active', true)->count();
        $todayCheckins = \App\Models\CheckIn::where('sales_id', auth()->id())->whereDate('checked_in_at', today())->count();
        $pendingOrders = \App\Models\Order::where('sales_id', auth()->id())->where('status', 'pending')->count();
        $unpaidAmount = \App\Models\Payment::where('sales_id', auth()->id())->where('status', 'belum_lunas')->get()->sum(function($payment) {
            return $payment->jumlah_tagihan - $payment->jumlah_bayar;
        });
        $myOrders = \App\Models\Order::where('sales_id', auth()->id())->with('customer')->latest()->take(5)->get();
        $myCustomers = \App\Models\Customer::where('sales_id', auth()->id())->where('is_active', true)->latest()->take(5)->get();
    @endphp

    <div class="row g-4">
        <!-- Welcome Card -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3">
                            <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="bx bx-user-voice fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-1">Selamat Datang, {{ auth()->user()->name }}!</h5>
                            <p class="mb-0 text-muted">Dashboard Sales - Aplikasi Sales Gudang</p>
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
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="bx bx-store"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Pelanggan</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">{{ $totalCustomers }}</h6>
                                <small class="text-muted fw-semibold">toko</small>
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
                            <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="bx bx-check-circle"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Check-in Hari Ini</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">{{ $todayCheckins }}</h6>
                                <small class="text-muted fw-semibold">kunjungan</small>
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
                            <small class="text-muted d-block">Order Pending</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">{{ $pendingOrders }}</h6>
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
                            <span class="avatar-initial rounded-circle bg-label-danger">
                                <i class="bx bx-money"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Piutang Belum Lunas</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1 {{ $unpaidAmount > 0 ? 'text-warning' : '' }}">Rp {{ number_format($unpaidAmount, 0, ',', '.') }}</h6>
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
                            <a href="{{ route('sales.check-in') }}" class="btn btn-outline-primary w-100">
                                <i class="bx bx-map-pin me-1"></i>
                                Check-in Toko
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('sales.orders') }}" class="btn btn-outline-success w-100">
                                <i class="bx bx-plus-circle me-1"></i>
                                Buat Pre Order
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('sales.customers') }}" class="btn btn-outline-info w-100">
                                <i class="bx bx-user-plus me-1"></i>
                                Kelola Pelanggan
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('sales.payments') }}" class="btn btn-outline-warning w-100">
                                <i class="bx bx-credit-card me-1"></i>
                                Penagihan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Schedule -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Jadwal Hari Ini</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="bx bx-calendar text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Belum ada jadwal</p>
                        <small class="text-muted">Jadwal kunjungan akan ditampilkan di sini</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Order Terbaru</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="bx bx-package text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Belum ada order</p>
                        <small class="text-muted">Order terbaru akan ditampilkan di sini</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Status Pembayaran</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="bx bx-credit-card text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Belum ada tagihan</p>
                        <small class="text-muted">Status pembayaran akan ditampilkan di sini</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
