@section('title', 'Dashboard Gudang')
<x-layouts.app :title="'Dashboard Gudang'">
    <div class="row g-4">
        <!-- Welcome Card -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="bx bx-package fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-1">Selamat Datang, {{ auth()->user()->name }}!</h5>
                            <p class="mb-0 text-muted">Dashboard Gudang - Aplikasi Sales Gudang</p>
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
                                <i class="bx bx-box"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Produk</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">0</h6>
                                <small class="text-muted fw-semibold">items</small>
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
                                <i class="bx bx-time"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Order Menunggu</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">0</h6>
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
                                <i class="bx bx-error-circle"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Stok Kritis</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">0</h6>
                                <small class="text-muted fw-semibold">items</small>
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
                                <i class="bx bx-car"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Siap Kirim</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">0</h6>
                                <small class="text-muted fw-semibold">orders</small>
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
                            <a href="{{ route('gudang.orders') }}" class="btn btn-outline-primary w-100">
                                <i class="bx bx-check-circle me-1"></i>
                                Konfirmasi Order
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('gudang.products') }}" class="btn btn-outline-success w-100">
                                <i class="bx bx-plus-circle me-1"></i>
                                Kelola Produk
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('gudang.deliveries') }}" class="btn btn-outline-info w-100">
                                <i class="bx bx-truck me-1"></i>
                                Atur Pengiriman
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('gudang.backorders') }}" class="btn btn-outline-warning w-100">
                                <i class="bx bx-error-circle me-1"></i>
                                Stok Kosong
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Alert -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Alert Stok</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="bx bx-error-circle text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Semua stok aman</p>
                        <small class="text-muted">Alert stok kritis akan ditampilkan di sini</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Order Menunggu Konfirmasi</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="bx bx-time text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Tidak ada order menunggu</p>
                        <small class="text-muted">Order yang perlu dikonfirmasi akan ditampilkan di sini</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backorder Status -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Status Backorder</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="bx bx-package text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Tidak ada backorder</p>
                        <small class="text-muted">Item backorder akan ditampilkan di sini</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
