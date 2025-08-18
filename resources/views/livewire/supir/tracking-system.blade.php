<div>
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1">GPS Tracking</h4>
            <p class="text-muted mb-0">Monitor lokasi real-time selama pengiriman</p>
        </div>
        <div class="d-flex gap-2 align-self-md-auto align-self-stretch">
            @if($currentDelivery && !$isTracking)
                <button wire:click="startTracking" class="btn btn-success flex-fill flex-md-grow-0">
                    <i class="bx bx-play"></i> Mulai Tracking
                </button>
            @elseif($isTracking)
                <button wire:click="stopTracking" class="btn btn-danger flex-fill flex-md-grow-0">
                    <i class="bx bx-stop"></i> Stop Tracking
                </button>
            @endif
            <button wire:click="refreshLocation" class="btn btn-outline-primary flex-fill flex-md-grow-0">
                <i class="bx bx-refresh"></i> Refresh
            </button>
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

    @if(!$currentDelivery)
        <!-- No Active Delivery -->
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="bx bx-map text-muted" style="font-size: 4rem;"></i>
                </div>
                <h5 class="text-muted">Tidak Ada Pengiriman Aktif</h5>
                <p class="text-muted mb-0">Tracking akan tersedia ketika ada pengiriman yang sedang berlangsung</p>
            </div>
        </div>
    @else
        <!-- Current Delivery Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Pengiriman Aktif</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    <i class="bx bx-package"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $currentDelivery->order->nomor_order }}</h6>
                                <small class="text-muted">Order Number</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded-circle bg-label-info">
                                    <i class="bx bx-store"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $currentDelivery->order->customer->nama_toko }}</h6>
                                <small class="text-muted">{{ Str::limit($currentDelivery->order->customer->alamat, 30) }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded-circle bg-label-{{ $isTracking ? 'success' : 'warning' }}">
                                    <i class="bx bx-{{ $isTracking ? 'radio' : 'pause' }}"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $isTracking ? 'Tracking Aktif' : 'Tracking Tidak Aktif' }}</h6>
                                <small class="text-muted">Status: {{ ucfirst($currentDelivery->status) }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded-circle bg-label-secondary">
                                    <i class="bx bx-time"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $deliveryStats['duration'] }}</h6>
                                <small class="text-muted">Durasi Perjalanan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3">
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    <i class="bx bx-map-pin"></i>
                                </span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Tracking Points</small>
                                <h6 class="mb-0">{{ $deliveryStats['tracking_points'] }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3">
                                <span class="avatar-initial rounded-circle bg-label-success">
                                    <i class="bx bx-trip"></i>
                                </span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Total Distance</small>
                                <h6 class="mb-0">{{ number_format($deliveryStats['total_distance'], 1) }} km</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3">
                                <span class="avatar-initial rounded-circle bg-label-info">
                                    <i class="bx bx-current-location"></i>
                                </span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Last Update</small>
                                <h6 class="mb-0">
                                    @if(!empty($currentLocation))
                                        {{ $currentLocation['timestamp'] }}
                                    @else
                                        -
                                    @endif
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Location -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lokasi Saat Ini</h5>
                <button wire:click="openLocationModal" class="btn btn-sm btn-outline-primary">
                    <i class="bx bx-edit"></i> Update Manual
                </button>
            </div>
            <div class="card-body">
                @if(!empty($currentLocation))
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-map me-2 text-primary"></i>
                                <div>
                                    <strong>Latitude:</strong> {{ $currentLocation['latitude'] }}<br>
                                    <strong>Longitude:</strong> {{ $currentLocation['longitude'] }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-time me-2 text-success"></i>
                                <div>
                                    <strong>Akurasi:</strong> {{ $currentLocation['accuracy'] ?? 'N/A' }} m<br>
                                    <strong>Update:</strong> {{ $currentLocation['timestamp'] }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Google Maps Link -->
                    <div class="mt-3">
                        <a href="https://maps.google.com/?q={{ $currentLocation['latitude'] }},{{ $currentLocation['longitude'] }}"
                           target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="bx bx-map"></i> Lihat di Google Maps
                        </a>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="bx bx-location-plus text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada data lokasi</p>
                        <small class="text-muted">Mulai tracking untuk mendapatkan lokasi real-time</small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tracking History -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Riwayat Tracking (10 Terakhir)</h5>
            </div>
            <div class="card-body p-0">
                @if(count($trackingHistory) > 0)
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
                            .mobile-cards .tracking-info-cell {
                                display: block;
                                padding-bottom: 1rem;
                                margin-bottom: 1rem;
                                border-bottom: 1px solid #eee;
                            }
                            .mobile-cards .tracking-info-cell:before {
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
                                    <th>Waktu</th>
                                    <th>Koordinat</th>
                                    <th>Akurasi</th>
                                    <th>Catatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trackingHistory as $track)
                                    <tr>
                                        <td data-label="Waktu" class="tracking-info-cell">
                                            <div>
                                                <span class="fw-medium">{{ \Carbon\Carbon::parse($track['recorded_at'])->format('H:i:s') }}</span>
                                                <br>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($track['recorded_at'])->format('d/m/Y') }}</small>
                                            </div>
                                        </td>
                                        <td data-label="Koordinat">
                                            <small class="text-muted">
                                                {{ number_format($track['latitude'], 6) }},<br>
                                                {{ number_format($track['longitude'], 6) }}
                                            </small>
                                        </td>
                                        <td data-label="Akurasi">
                                            @if($track['accuracy'])
                                                <span class="badge bg-label-{{ $track['accuracy'] < 10 ? 'success' : ($track['accuracy'] < 50 ? 'warning' : 'danger') }}">
                                                    {{ $track['accuracy'] }}m
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td data-label="Catatan">
                                            <small class="text-muted">{{ $track['notes'] ?? '-' }}</small>
                                        </td>
                                        <td data-label="Aksi" class="actions-cell">
                                            <a href="https://maps.google.com/?q={{ $track['latitude'] }},{{ $track['longitude'] }}"
                                               target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-map"></i> Lihat
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bx bx-history text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada riwayat tracking</p>
                        <small class="text-muted">Riwayat akan muncul setelah tracking dimulai</small>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Manual Location Update Modal -->
    @if($showLocationModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Lokasi Manual</h5>
                        <button type="button" class="btn-close" wire:click="$set('showLocationModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="updateLocationManually">
                            <div class="mb-3">
                                <label class="form-label">Latitude <span class="text-danger">*</span></label>
                                <input type="number" step="any" wire:model="manualLatitude"
                                       class="form-control @error('manualLatitude') is-invalid @enderror"
                                       placeholder="Contoh: -6.200000">
                                @error('manualLatitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Range: -90 sampai 90</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Longitude <span class="text-danger">*</span></label>
                                <input type="number" step="any" wire:model="manualLongitude"
                                       class="form-control @error('manualLongitude') is-invalid @enderror"
                                       placeholder="Contoh: 106.816666">
                                @error('manualLongitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Range: -180 sampai 180</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea wire:model="locationNotes" class="form-control @error('locationNotes') is-invalid @enderror"
                                          rows="3" placeholder="Catatan lokasi (opsional)"></textarea>
                                @error('locationNotes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Tips:</strong> Anda bisa mendapatkan koordinat dari Google Maps dengan klik kanan pada lokasi dan pilih koordinat yang muncul.
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" wire:click="$set('showLocationModal', false)">
                                    Batal
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-check"></i> Update Lokasi
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

<script>
    // GPS Tracking JavaScript
    let watchId = null;
    let isWatching = false;

    // Listen for Livewire events
    document.addEventListener('livewire:init', () => {
        Livewire.on('requestLocation', () => {
            getCurrentLocation();
        });
    });

    function getCurrentLocation() {
        if (!navigator.geolocation) {
            alert('Geolocation tidak didukung oleh browser ini.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const latitude = position.coords.latitude;
                const longitude = position.coords.longitude;
                const accuracy = position.coords.accuracy;

                // Send to Livewire
                Livewire.dispatch('locationUpdated', {
                    latitude: latitude,
                    longitude: longitude,
                    accuracy: accuracy
                });
            },
            function(error) {
                console.error('Error getting location:', error);
                alert('Gagal mendapatkan lokasi: ' + error.message);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            }
        );
    }

    function startWatchingLocation() {
        if (!navigator.geolocation || isWatching) {
            return;
        }

        watchId = navigator.geolocation.watchPosition(
            function(position) {
                const latitude = position.coords.latitude;
                const longitude = position.coords.longitude;
                const accuracy = position.coords.accuracy;

                // Send to Livewire
                Livewire.dispatch('locationUpdated', {
                    latitude: latitude,
                    longitude: longitude,
                    accuracy: accuracy
                });
            },
            function(error) {
                console.error('Error watching location:', error);
            },
            {
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 60000
            }
        );

        isWatching = true;
    }

    function stopWatchingLocation() {
        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
            isWatching = false;
        }
    }

    // Auto start watching when tracking is active
    @if($isTracking)
        startWatchingLocation();
    @else
        stopWatchingLocation();
    @endif
</script>
