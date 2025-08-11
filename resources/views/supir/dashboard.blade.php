@section('title', 'Dashboard Supir')
<x-layouts.app :title="'Dashboard Supir'">
    <div class="row g-4">
        <!-- Welcome Card -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3">
                            <span class="avatar-initial rounded-circle bg-label-warning">
                                <i class="bx bx-car fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-1">Selamat Datang, {{ auth()->user()->name }}!</h5>
                            <p class="mb-0 text-muted">Dashboard Supir - Aplikasi Sales Gudang</p>
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
                                <i class="bx bx-package"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Tugas Hari Ini</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">0</h6>
                                <small class="text-muted fw-semibold">pengiriman</small>
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
                            <small class="text-muted d-block">Dalam Perjalanan</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">0</h6>
                                <small class="text-muted fw-semibold">pengiriman</small>
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
                            <small class="text-muted d-block">Selesai Hari Ini</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">0</h6>
                                <small class="text-muted fw-semibold">pengiriman</small>
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
                                <i class="bx bx-shield"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">K3 Status</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">Belum</h6>
                                <small class="text-muted fw-semibold">dicek</small>
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
                            <a href="{{ route('supir.k3-checklist') }}" class="btn btn-outline-primary w-100">
                                <i class="bx bx-shield me-1"></i>
                                K3 Checklist
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('supir.deliveries') }}" class="btn btn-outline-success w-100">
                                <i class="bx bx-package me-1"></i>
                                Lihat Tugas
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('supir.tracking') }}" class="btn btn-outline-info w-100">
                                <i class="bx bx-map me-1"></i>
                                Update Lokasi
                            </a>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-warning w-100">
                                <i class="bx bx-phone me-1"></i>
                                Hubungi Gudang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- K3 Status -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Status K3</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="bx bx-shield text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Belum melakukan K3 check</p>
                        <small class="text-muted">Lakukan K3 checklist sebelum berangkat</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Deliveries -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Pengiriman Hari Ini</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="bx bx-package text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Belum ada tugas pengiriman</p>
                        <small class="text-muted">Tugas pengiriman akan ditampilkan di sini</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Route Map -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Rute Pengiriman</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="bx bx-map text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Belum ada rute</p>
                        <small class="text-muted">Rute pengiriman akan ditampilkan di sini</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
