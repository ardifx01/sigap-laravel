<div>
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1">GPS Tracking & Delivery</h4>
            <p class="text-muted mb-0">Kelola pengiriman dengan real-time GPS tracking</p>
        </div>
        @if($isTrackingActive && $currentDelivery)
            <div class="badge bg-success fs-6 align-self-md-auto align-self-start">
                <i class="bx bx-current-location me-1"></i>
                GPS Tracking Aktif
            </div>
        @endif
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

    <!-- Active Delivery Alert -->
    @if($currentDelivery)
        <div class="alert alert-primary mb-4">
            <div class="d-flex align-items-center">
                <i class="bx bx-truck me-3" style="font-size: 2rem;"></i>
                <div class="flex-grow-1">
                    <h6 class="alert-heading mb-1">Pengiriman Aktif</h6>
                    <p class="mb-2">
                        <strong>{{ $currentDelivery->order->customer->nama_toko }}</strong><br>
                        Order: {{ $currentDelivery->order->nomor_order }} |
                        Dimulai: {{ $currentDelivery->started_at->format('H:i') }}
                    </p>
                </div>
                <button wire:click="openCompleteModal({{ $currentDelivery->id }})" class="btn btn-success">
                    <i class="bx bx-check-circle me-1"></i> Selesaikan
                </button>
            </div>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-warning">
                                <i class="bx bx-time"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Menunggu Berangkat</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">{{ $assignedDeliveries }}</h6>
                                <small class="text-muted fw-semibold">delivery</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="bx bx-current-location"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Dalam Perjalanan</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">{{ $inProgressDeliveries }}</h6>
                                <small class="text-muted fw-semibold">delivery</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
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
                                <h6 class="mb-0 me-1">{{ $completedToday }}</h6>
                                <small class="text-muted fw-semibold">delivery</small>
                            </div>
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
                <div class="col-12 col-md-6">
                    <label class="form-label">Cari Delivery</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Nomor order atau nama toko...">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Filter Status</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="assigned">Ditugaskan</option>
                        <option value="in_progress">Dalam Perjalanan</option>
                        <option value="delivered">Terkirim</option>
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
                    .mobile-cards .delivery-info-cell {
                        display: block;
                        padding-bottom: 1rem;
                        margin-bottom: 1rem;
                        border-bottom: 1px solid #eee;
                    }
                    .mobile-cards .delivery-info-cell:before {
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
                            <th>Pelanggan</th>
                            <th>Status</th>
                            <th>K3 Checklist</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliveries as $delivery)
                            <tr>
                                <td data-label="Order" class="delivery-info-cell">
                                    <div>
                                        <span class="fw-medium">{{ $delivery->order->nomor_order }}</span>
                                        <br>
                                        <small class="text-muted">{{ $delivery->order->orderItems->count() }} item</small>
                                    </div>
                                </td>
                                <td data-label="Pelanggan">
                                    <div>
                                        <span class="fw-medium">{{ $delivery->order->customer->nama_toko }}</span>
                                        <br>
                                        <small class="text-muted">{{ $delivery->order->customer->phone }}</small>
                                        @if($delivery->order->customer->latitude && $delivery->order->customer->longitude)
                                            <br><small class="text-success"><i class="bx bx-map-pin"></i> GPS tersedia</small>
                                        @endif
                                    </div>
                                </td>
                                <td data-label="Status">
                                    @php
                                        $statusConfig = [
                                            'assigned' => ['color' => 'warning', 'label' => 'Menunggu K3'],
                                            'k3_checked' => ['color' => 'info', 'label' => 'Siap Berangkat'],
                                            'in_progress' => ['color' => 'primary', 'label' => 'Dalam Perjalanan'],
                                            'delivered' => ['color' => 'success', 'label' => 'Terkirim'],
                                            'cancelled' => ['color' => 'danger', 'label' => 'Dibatalkan']
                                        ];
                                        $config = $statusConfig[$delivery->status] ?? ['color' => 'secondary', 'label' => $delivery->status];
                                    @endphp
                                    <span class="badge bg-label-{{ $config['color'] }}">
                                        {{ $config['label'] }}
                                    </span>
                                </td>
                                <td data-label="K3 Checklist">
                                    @if($delivery->k3Checklist)
                                        <div>
                                            <span class="badge bg-label-{{
                                                $delivery->k3Checklist->isAllItemsPassed() ? 'success' :
                                                ($delivery->k3Checklist->getCompletionPercentage() >= 50 ? 'warning' : 'danger')
                                            }}">
                                                {{ $delivery->k3Checklist->isAllItemsPassed() ? 'Complete' : 'Incomplete' }}
                                            </span>
                                            <br><small class="text-muted">{{ $delivery->k3Checklist->getCompletionPercentage() }}% complete</small>
                                        </div>
                                    @else
                                        <span class="badge bg-label-secondary">Belum ada</span>
                                    @endif
                                </td>
                                <td data-label="Waktu">
                                    <div>
                                        <small class="text-muted d-block">Ditugaskan: {{ $delivery->assigned_at->format('d/m H:i') }}</small>
                                        @if($delivery->started_at)
                                            <small class="text-info d-block">Dimulai: {{ $delivery->started_at->format('d/m H:i') }}</small>
                                        @endif
                                        @if($delivery->delivered_at)
                                            <small class="text-success d-block">Selesai: {{ $delivery->delivered_at->format('d/m H:i') }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td data-label="Aksi" class="actions-cell">
                                    @if($delivery->status === 'assigned')
                                        @if($delivery->needsK3Checklist())
                                            <a href="{{ route('supir.k3-checklist') }}" class="btn btn-sm btn-warning">
                                                <i class="bx bx-shield me-1"></i> K3 Checklist
                                            </a>
                                        @else
                                            <span class="text-muted">Menunggu K3 validasi</span>
                                        @endif
                                    @elseif($delivery->status === 'k3_checked')
                                        <button wire:click="openStartModal({{ $delivery->id }})" class="btn btn-sm btn-primary">
                                            <i class="bx bx-play me-1"></i> Mulai Perjalanan
                                        </button>
                                    @elseif($delivery->status === 'in_progress')
                                        <button wire:click="openCompleteModal({{ $delivery->id }})" class="btn btn-sm btn-success">
                                            <i class="bx bx-check-circle me-1"></i> Selesaikan
                                        </button>
                                    @elseif($delivery->status === 'delivered')
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if($delivery->delivery_proof_photo)
                                                    <li>
                                                        <a class="dropdown-item" href="{{ asset('storage/delivery_proofs/' . $delivery->delivery_proof_photo) }}" target="_blank">
                                                            <i class="bx bx-image me-1"></i> Bukti Delivery
                                                        </a>
                                                    </li>
                                                @endif
                                                @if($delivery->delivery_latitude && $delivery->delivery_longitude)
                                                    <li>
                                                        <a class="dropdown-item" href="https://maps.google.com/?q={{ $delivery->delivery_latitude }},{{ $delivery->delivery_longitude }}" target="_blank">
                                                            <i class="bx bx-map me-1"></i> Lokasi Delivery
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    @else
                                        <span class="text-muted">{{ ucfirst($delivery->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-car text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-1">Belum ada delivery</p>
                                        <small class="text-muted">Delivery akan muncul setelah ditugaskan oleh gudang.</small>
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

    <!-- Start Delivery Modal -->
    @if($showStartModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mulai Pengiriman</h5>
                        <button type="button" class="btn-close" wire:click="closeStartModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            Pastikan K3 checklist sudah diapprove dan ambil lokasi GPS untuk memulai pengiriman.
                        </div>

                        <!-- GPS Location -->
                        <div class="mb-3">
                            <label class="form-label">Lokasi GPS Start <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" wire:model="startLatitude" class="form-control" placeholder="Latitude" step="any" readonly>
                                </div>
                                <div class="col-6">
                                    <input type="number" wire:model="startLongitude" class="form-control" placeholder="Longitude" step="any" readonly>
                                </div>
                            </div>
                            <button type="button" wire:click="getCurrentLocationForStart" class="btn btn-primary w-100 mt-2">
                                <i class="bx bx-current-location me-1"></i> Ambil Lokasi GPS
                            </button>
                            @if($isStartLocationValid)
                                <div class="alert alert-success mt-2">
                                    <i class="bx bx-check-circle me-1"></i>
                                    Lokasi berhasil diambil!
                                    @if($startLocationAccuracy)
                                        Akurasi: {{ round($startLocationAccuracy) }} meter
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeStartModal">Batal</button>
                        <button type="button" wire:click="startDelivery" class="btn btn-primary" {{ !$isStartLocationValid ? 'disabled' : '' }}>
                            <i class="bx bx-play me-1"></i> Mulai Pengiriman
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Complete Delivery Modal -->
    @if($showCompleteModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Selesaikan Pengiriman</h5>
                        <button type="button" class="btn-close" wire:click="closeCompleteModal"></button>
                    </div>
                    <form wire:submit.prevent="completeDelivery">
                        <div class="modal-body">
                            <div class="alert alert-success">
                                <i class="bx bx-check-circle me-2"></i>
                                Konfirmasi pengiriman dengan mengambil lokasi GPS, foto bukti, dan tanda tangan customer.
                            </div>

                            <div class="row g-3">
                                <!-- GPS Location -->
                                <div class="col-12">
                                    <label class="form-label">Lokasi GPS Delivery <span class="text-danger">*</span></label>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <input type="number" wire:model="deliveryLatitude" class="form-control @error('deliveryLatitude') is-invalid @enderror" placeholder="Latitude" step="any" readonly>
                                            @error('deliveryLatitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" wire:model="deliveryLongitude" class="form-control @error('deliveryLongitude') is-invalid @enderror" placeholder="Longitude" step="any" readonly>
                                            @error('deliveryLongitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" wire:click="getCurrentLocationForDelivery" class="btn btn-primary w-100">
                                                <i class="bx bx-current-location me-1"></i> Ambil Lokasi
                                            </button>
                                        </div>
                                    </div>
                                    @if($isDeliveryLocationValid)
                                        <div class="alert alert-success mt-2">
                                            <i class="bx bx-check-circle me-1"></i>
                                            Lokasi delivery berhasil diambil!
                                            @if($deliveryLocationAccuracy)
                                                Akurasi: {{ round($deliveryLocationAccuracy) }} meter
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- Proof Photo -->
                                <div class="col-12">
                                    <label class="form-label">Foto Bukti Pengiriman <span class="text-danger">*</span></label>
                                    <input type="file" wire:model="deliveryProofPhoto" class="form-control @error('deliveryProofPhoto') is-invalid @enderror" accept="image/*" capture="environment">
                                    @error('deliveryProofPhoto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($deliveryProofPhoto)
                                        <div class="mt-2">
                                            <img src="{{ $deliveryProofPhoto->temporaryUrl() }}" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                                        </div>
                                    @endif
                                    <small class="text-muted">Ambil foto barang yang sudah diterima customer</small>
                                </div>

                                <!-- Customer Signature -->
                                <div class="col-12">
                                    <label class="form-label">Tanda Tangan Customer (Opsional)</label>
                                    <div class="border rounded p-3" style="min-height: 150px; background: #f8f9fa;">
                                        <canvas id="signatureCanvas" width="400" height="120" style="width: 100%; height: 120px; border: 1px dashed #dee2e6; background: white; cursor: crosshair;"></canvas>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSignature()">
                                                <i class="bx bx-eraser me-1"></i> Hapus
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="saveSignature()">
                                                <i class="bx bx-save me-1"></i> Simpan Tanda Tangan
                                            </button>
                                        </div>
                                    </div>
                                    <input type="hidden" wire:model="customerSignature">
                                </div>

                                <!-- Delivery Notes -->
                                <div class="col-12">
                                    <label class="form-label">Catatan Pengiriman</label>
                                    <textarea wire:model="deliveryNotes" class="form-control @error('deliveryNotes') is-invalid @enderror" rows="3" placeholder="Catatan kondisi pengiriman, kendala, atau informasi tambahan..."></textarea>
                                    @error('deliveryNotes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeCompleteModal">Batal</button>
                            <button type="submit" class="btn btn-success" {{ !$isDeliveryLocationValid ? 'disabled' : '' }}>
                                <i class="bx bx-check-circle me-1"></i> Selesaikan Pengiriman
                            </button>
                        </div>
                    </form>
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

    <!-- GPS & Signature Scripts -->
    <script>
        let canvas, ctx, isDrawing = false;

        document.addEventListener('livewire:init', () => {
            // Initialize signature canvas
            canvas = document.getElementById('signatureCanvas');
            if (canvas) {
                ctx = canvas.getContext('2d');
                ctx.strokeStyle = '#000';
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';

                // Mouse events
                canvas.addEventListener('mousedown', startDrawing);
                canvas.addEventListener('mousemove', draw);
                canvas.addEventListener('mouseup', stopDrawing);
                canvas.addEventListener('mouseout', stopDrawing);

                // Touch events for mobile
                canvas.addEventListener('touchstart', handleTouch);
                canvas.addEventListener('touchmove', handleTouch);
                canvas.addEventListener('touchend', stopDrawing);
            }

            // GPS Location handlers
            Livewire.on('getCurrentLocationForStart', () => {
                getCurrentLocation('setStartLocation');
            });

            Livewire.on('getCurrentLocationForDelivery', () => {
                getCurrentLocation('setDeliveryLocation');
            });

            // GPS Tracking
            Livewire.on('startGPSTracking', (data) => {
                startGPSTracking(data.deliveryId);
            });

            Livewire.on('stopGPSTracking', () => {
                stopGPSTracking();
            });
        });

        function getCurrentLocation(eventName) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        Livewire.dispatch(eventName, {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            accuracy: position.coords.accuracy
                        });
                    },
                    function(error) {
                        let message = 'Unknown error';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                message = 'Akses lokasi ditolak. Silakan izinkan akses lokasi.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                message = 'Informasi lokasi tidak tersedia.';
                                break;
                            case error.TIMEOUT:
                                message = 'Timeout mendapatkan lokasi.';
                                break;
                        }
                        alert('Error GPS: ' + message);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 60000
                    }
                );
            } else {
                alert('Geolocation tidak didukung oleh browser ini.');
            }
        }

        // GPS Tracking functions
        let trackingInterval;

        function startGPSTracking(deliveryId) {
            if (navigator.geolocation) {
                trackingInterval = setInterval(() => {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            Livewire.dispatch('updateGPSLocation', {
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude,
                                accuracy: position.coords.accuracy,
                                speed: position.coords.speed,
                                heading: position.coords.heading
                            });
                        },
                        function(error) {
                            console.log('GPS tracking error:', error);
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 5000,
                            maximumAge: 30000
                        }
                    );
                }, 30000); // Update every 30 seconds
            }
        }

        function stopGPSTracking() {
            if (trackingInterval) {
                clearInterval(trackingInterval);
                trackingInterval = null;
            }
        }

        // Signature functions
        function startDrawing(e) {
            isDrawing = true;
            const rect = canvas.getBoundingClientRect();
            ctx.beginPath();
            ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
        }

        function draw(e) {
            if (!isDrawing) return;
            const rect = canvas.getBoundingClientRect();
            ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
            ctx.stroke();
        }

        function stopDrawing() {
            isDrawing = false;
        }

        function handleTouch(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent(e.type === 'touchstart' ? 'mousedown' :
                                            e.type === 'touchmove' ? 'mousemove' : 'mouseup', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            canvas.dispatchEvent(mouseEvent);
        }

        function clearSignature() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            @this.set('customerSignature', '');
        }

        function saveSignature() {
            const dataURL = canvas.toDataURL();
            @this.set('customerSignature', dataURL);
            alert('Tanda tangan berhasil disimpan!');
        }
    </script>
</div>
