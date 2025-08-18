<div>
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1">GPS Check-in System</h4>
            <p class="text-muted mb-0">Lakukan check-in ke lokasi pelanggan dengan GPS dan foto selfie.</p>
        </div>
        <button wire:click="openCheckInModal" class="btn btn-primary align-self-md-auto align-self-stretch">
            <i class="bx bx-map-pin me-1"></i>
            Check-in Sekarang
        </button>
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

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="bx bx-check-circle"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Check-in Hari Ini</small>
                            <h6 class="mb-0">{{ $todayCheckIns }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="bx bx-calendar-week"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Check-in Minggu Ini</small>
                            <h6 class="mb-0">{{ $thisWeekCheckIns }}</h6>
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
                <div class="col-12 col-md-8">
                    <label class="form-label">Cari Check-in</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari berdasarkan nama toko...">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Filter Tanggal</label>
                    <input type="date" wire:model.live="dateFilter" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <!-- Check-ins Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Riwayat Check-in</h5>
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
                    .mobile-cards .check-in-info-cell {
                        display: block; /* Override the flex for the main user info */
                        padding-bottom: 1rem;
                        margin-bottom: 1rem;
                        border-bottom: 1px solid #eee;
                    }
                    .mobile-cards .check-in-info-cell:before {
                        display: none; /* No "User:" label */
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
                            <th>Toko</th>
                            <th>Waktu Check-in</th>
                            <th>Jarak</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checkIns as $checkIn)
                            <tr>
                                <td data-label="Toko" class="check-in-info-cell">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <img src="{{ $checkIn->getFirstMediaUrl('selfie_photos') ?: asset('assets/img/avatars/1.png') }}" alt="Selfie" class="rounded-circle">
                                        </div>
                                        <div>
                                            <span class="fw-medium">{{ $checkIn->customer->nama_toko }}</span>
                                            <br>
                                            <small class="text-muted">{{ Str::limit($checkIn->catatan ?? 'Tidak ada catatan', 35) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Waktu Check-in">
                                    <div>
                                        <span class="fw-medium">{{ $checkIn->checked_in_at->format('H:i') }}</span>
                                        <br>
                                        <small class="text-muted">{{ $checkIn->checked_in_at->format('d M Y') }}</small>
                                    </div>
                                </td>
                                <td data-label="Jarak">
                                    @if($checkIn->customer->latitude && $checkIn->customer->longitude)
                                        @php
                                            $distance = $checkIn->getDistanceFromCustomer();
                                        @endphp
                                        @if($distance !== null)
                                            <span class="badge bg-label-{{ $distance <= 100 ? 'success' : ($distance <= 500 ? 'warning' : 'danger') }}">
                                                ~{{ round($distance) }}m dari toko
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td data-label="Aksi" class="actions-cell">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0);" wire:click="viewCheckIn({{ $checkIn->id }})">
                                                    <i class="bx bx-show me-1"></i> Detail
                                                </a>
                                            </li>
                                            @if($checkIn->checked_in_at->isToday())
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                       wire:click="deleteCheckIn({{ $checkIn->id }})"
                                                       onclick="return confirm('Anda yakin ingin menghapus check-in ini?')">
                                                        <i class="bx bx-trash me-1"></i> Hapus
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-map-pin text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-1">Belum ada riwayat check-in</p>
                                        <small class="text-muted">Lakukan check-in pertama Anda.</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($checkIns->hasPages())
            <div class="card-footer">
                {{ $checkIns->links() }}
            </div>
        @endif
    </div>

    <!-- Check-in Modal -->
    @if($showCheckInModal)
        <div class="modal fade show" style="display: block;" tabindex="-1" wire:ignore.self>
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Formulir Check-in</h5>
                        <button type="button" class="btn-close" wire:click="closeCheckInModal"></button>
                    </div>
                    <form wire:submit.prevent="checkIn">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Pilih Toko <span class="text-danger">*</span></label>
                                    <div wire:ignore>
                                        <select id="customer-select" class="form-select @error('customer_id') is-invalid @enderror">
                                            <option value="">Pilih pelanggan...</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->nama_toko }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('customer_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Lokasi GPS <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" wire:model="latitude" class="form-control" placeholder="Latitude" readonly>
                                        <input type="text" wire:model="longitude" class="form-control" placeholder="Longitude" readonly>
                                        <button class="btn btn-outline-primary" type="button" wire:click="getCurrentLocation" wire:loading.attr="disabled">
                                            <span wire:loading.remove wire:target="getCurrentLocation"><i class="bx bx-current-location"></i></span>
                                            <span wire:loading wire:target="getCurrentLocation" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                    @if($isLocationValid && $locationAccuracy)
                                        <small class="text-success">Akurasi: {{ round($locationAccuracy) }} meter</small>
                                    @endif
                                    @error('latitude') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Foto Selfie <span class="text-danger">*</span></label>
                                    <input type="file" wire:model="foto_selfie" class="form-control @error('foto_selfie') is-invalid @enderror" accept="image/*" capture="user">
                                    <small class="text-muted">Ambil foto selfie di lokasi toko.</small>
                                    @error('foto_selfie') <div class="invalid-feedback">{{ $message }}</div> @enderror

                                    <div wire:loading wire:target="foto_selfie" class="mt-2">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                        <span>Uploading...</span>
                                    </div>

                                    @if ($foto_selfie)
                                        <div class="mt-2">
                                            <img src="{{ $foto_selfie->temporaryUrl() }}" class="img-thumbnail" style="max-height: 200px;">
                                        </div>
                                    @endif
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Catatan Kunjungan</label>
                                    <textarea wire:model="catatan" class="form-control" rows="3" placeholder="Contoh: Diskusi produk baru, penagihan, dll."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeCheckInModal">Batal</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" {{ !$isLocationValid ? 'disabled' : '' }}>
                                <span wire:loading wire:target="checkIn" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Check-in
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" wire:ignore.self></div>
    @endif

    <!-- View Check-in Modal -->
    @if($showViewModal && $selectedCheckIn)
        <div class="modal fade show" style="display: block;" tabindex="-1" wire:ignore.self>
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Check-in</h5>
                        <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-4">
                            <div class="col-12 text-center">
                                <img src="{{ $selectedCheckIn->getFirstMediaUrl('selfie_photos') }}" alt="Selfie" class="img-fluid rounded" style="max-height: 300px;">
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-2">Informasi Toko</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-1"><span class="fw-medium">Nama:</span> {{ $selectedCheckIn->customer->nama_toko }}</li>
                                    <li class="mb-1"><span class="fw-medium">Telepon:</span> {{ $selectedCheckIn->customer->phone }}</li>
                                    <li><span class="fw-medium">Alamat:</span> {{ $selectedCheckIn->customer->alamat }}</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-2">Informasi Kunjungan</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-1"><span class="fw-medium">Waktu:</span> {{ $selectedCheckIn->checked_in_at->format('d M Y, H:i') }}</li>
                                    <li class="mb-1"><span class="fw-medium">Koordinat:</span> {{ number_format($selectedCheckIn->latitude, 5) }}, {{ number_format($selectedCheckIn->longitude, 5) }}</li>
                                    @if($selectedCheckIn->customer->latitude && $selectedCheckIn->customer->longitude)
                                        <li><span class="fw-medium">Jarak:</span>
                                            @php $distance = $selectedCheckIn->getDistanceFromCustomer(); @endphp
                                            @if($distance !== null)
                                                <span class="badge bg-label-{{ $distance <= 100 ? 'success' : 'warning' }}">~{{ round($distance) }} meter dari toko</span>
                                            @endif
                                        </li>
                                    @endif
                                </ul>
                            </div>
                            @if($selectedCheckIn->catatan)
                                <div class="col-12">
                                    <h6 class="mb-2">Catatan</h6>
                                    <p class="text-muted mb-0">{{ $selectedCheckIn->catatan }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeViewModal">Tutup</button>
                        <a href="https://maps.google.com/?q={{ $selectedCheckIn->latitude }},{{ $selectedCheckIn->longitude }}" target="_blank" class="btn btn-primary">
                            <i class="bx bx-map-alt me-1"></i> Buka di Peta
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" wire:ignore.self></div>
    @endif

    <!-- Scripts -->
    <script>
        document.addEventListener('livewire:init', () => {
            let tomSelectInstance = null;

            // Initialize TomSelect when modal opens
            Livewire.on('show-check-in-modal', () => {
                setTimeout(() => {
                    const customerSelect = document.getElementById('customer-select');
                    if (customerSelect && !tomSelectInstance) {
                        tomSelectInstance = new TomSelect(customerSelect, {
                            create: false,
                            sortField: { field: "text", direction: "asc" },
                            placeholder: 'Pilih pelanggan...'
                        });

                        tomSelectInstance.on('change', (value) => {
                            @this.set('customer_id', value);
                        });
                    }
                }, 100);
            });

            // Clean up TomSelect when modal closes
            Livewire.on('close-check-in-modal', () => {
                if (tomSelectInstance) {
                    tomSelectInstance.destroy();
                    tomSelectInstance = null;
                }
            });

            Livewire.on('getCurrentLocation', () => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            @this.dispatch('setLocation', {
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude,
                                accuracy: position.coords.accuracy
                            });
                        },
                        (error) => {
                            let message = 'Terjadi kesalahan tidak diketahui.';
                            switch(error.code) {
                                case error.PERMISSION_DENIED: message = 'Izin akses lokasi ditolak.'; break;
                                case error.POSITION_UNAVAILABLE: message = 'Informasi lokasi tidak tersedia.'; break;
                                case error.TIMEOUT: message = 'Waktu permintaan lokasi habis.'; break;
                            }
                            @this.dispatch('locationError', { message: message });
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                } else {
                     @this.dispatch('locationError', { message: 'Geolocation tidak didukung oleh browser ini.' });
                }
            });
        });
    </script>
</div>
