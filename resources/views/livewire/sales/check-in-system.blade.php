<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">GPS Check-in System</h4>
            <p class="text-muted mb-0">Check-in ke toko pelanggan dengan GPS dan selfie</p>
        </div>
        <button wire:click="openCheckInModal" class="btn btn-primary">
            <i class="bx bx-map-pin me-1"></i>
            Check-in Sekarang
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
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
                                <h6 class="mb-0 me-1">{{ $todayCheckIns }}</h6>
                                <small class="text-muted fw-semibold">kunjungan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="bx bx-calendar-week"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Check-in Minggu Ini</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">{{ $thisWeekCheckIns }}</h6>
                                <small class="text-muted fw-semibold">kunjungan</small>
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
                <div class="col-md-6">
                    <label class="form-label">Cari Check-in</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Nama toko atau telepon...">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Filter Tanggal</label>
                    <input type="date" wire:model.live="dateFilter" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <!-- Check-ins Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Toko</th>
                            <th>Lokasi</th>
                            <th>Waktu Check-in</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checkIns as $checkIn)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            @if($checkIn->getFirstMediaUrl('selfie_photos'))
                                                <img src="{{ $checkIn->getFirstMediaUrl('selfie_photos') }}" alt="Selfie" class="rounded-circle">
                                            @else
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    <i class="bx bx-user"></i>
                                                </span>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $checkIn->customer->nama_toko }}</h6>
                                            <small class="text-muted">{{ $checkIn->customer->phone }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <small class="text-muted d-block">
                                            <i class="bx bx-map-pin"></i>
                                            {{ number_format($checkIn->latitude, 6) }}, {{ number_format($checkIn->longitude, 6) }}
                                        </small>
                                        @if($checkIn->customer->latitude && $checkIn->customer->longitude)
                                            @php
                                                $distance = $checkIn->getDistanceFromCustomer();
                                            @endphp
                                            @if($distance !== null)
                                                <small class="badge bg-label-{{ $distance <= 100 ? 'success' : ($distance <= 500 ? 'warning' : 'danger') }}">
                                                    {{ round($distance) }}m dari toko
                                                </small>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-medium">{{ $checkIn->checked_in_at->format('H:i') }}</div>
                                        <small class="text-muted">{{ $checkIn->checked_in_at->format('d/m/Y') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ Str::limit($checkIn->catatan ?? '-', 30) }}</small>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#" wire:click.prevent="viewCheckIn({{ $checkIn->id }})">
                                                <i class="bx bx-show me-1"></i> Detail
                                            </a>
                                            @if($checkIn->getFirstMediaUrl('selfie_photos'))
                                                <a class="dropdown-item" href="{{ $checkIn->getFirstMediaUrl('selfie_photos') }}" target="_blank">
                                                    <i class="bx bx-image me-1"></i> Lihat Selfie
                                                </a>
                                            @endif
                                            @if($checkIn->checked_in_at->isToday())
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-danger" href="#"
                                                   wire:click.prevent="deleteCheckIn({{ $checkIn->id }})"
                                                   onclick="return confirm('Yakin ingin menghapus check-in ini?')">
                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="bx bx-map-pin text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">Belum ada check-in</p>
                                    <button wire:click="openCheckInModal" class="btn btn-primary btn-sm">
                                        <i class="bx bx-map-pin me-1"></i> Check-in Pertama
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $checkIns->links() }}
            </div>
        </div>
    </div>

    <!-- Check-in Modal -->
    @if($showCheckInModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Check-in ke Toko</h5>
                        <button type="button" class="btn-close" wire:click="closeCheckInModal"></button>
                    </div>
                    <form wire:submit.prevent="checkIn">
                        <div class="modal-body">
                            <div class="row g-3">
                                <!-- Customer Selection -->
                                <div class="col-12">
                                    <label class="form-label">Pilih Toko <span class="text-danger">*</span></label>
                                    <select wire:model="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                        <option value="">-- Pilih Toko --</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">
                                                {{ $customer->nama_toko }} - {{ $customer->phone }}
                                                @if($customer->latitude && $customer->longitude)
                                                    (GPS tersedia)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- GPS Location -->
                                <div class="col-12">
                                    <label class="form-label">Lokasi GPS <span class="text-danger">*</span></label>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <input type="number" wire:model="latitude" class="form-control @error('latitude') is-invalid @enderror" placeholder="Latitude" step="any" readonly>
                                            @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" wire:model="longitude" class="form-control @error('longitude') is-invalid @enderror" placeholder="Longitude" step="any" readonly>
                                            @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" wire:click="getCurrentLocation" class="btn btn-primary w-100">
                                                <i class="bx bx-current-location me-1"></i> Ambil Lokasi
                                            </button>
                                        </div>
                                    </div>
                                    @if($isLocationValid)
                                        <div class="alert alert-success mt-2">
                                            <i class="bx bx-check-circle me-1"></i>
                                            Lokasi berhasil diambil!
                                            @if($locationAccuracy)
                                                Akurasi: {{ round($locationAccuracy) }} meter
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- Selfie Photo -->
                                <div class="col-12">
                                    <label class="form-label">Foto Selfie <span class="text-danger">*</span></label>
                                    <input type="file" wire:model="foto_selfie" class="form-control @error('foto_selfie') is-invalid @enderror" accept="image/*" capture="user">
                                    @error('foto_selfie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($foto_selfie)
                                        <div class="mt-2">
                                            <img src="{{ $foto_selfie->temporaryUrl() }}" alt="Preview Selfie" class="img-thumbnail" style="max-height: 200px;">
                                        </div>
                                    @endif
                                    <small class="text-muted">Ambil foto selfie Anda di lokasi toko</small>
                                </div>

                                <!-- Notes -->
                                <div class="col-12">
                                    <label class="form-label">Catatan</label>
                                    <textarea wire:model="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3" placeholder="Catatan kunjungan (opsional)..."></textarea>
                                    @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeCheckInModal">Batal</button>
                            <button type="submit" class="btn btn-primary" {{ !$isLocationValid ? 'disabled' : '' }}>
                                <i class="bx bx-check me-1"></i> Check-in
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- View Check-in Modal -->
    @if($showViewModal && $viewCheckIn)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Check-in</h5>
                        <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6>Informasi Toko</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Nama Toko:</td>
                                        <td><strong>{{ $viewCheckIn->customer->nama_toko }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Telepon:</td>
                                        <td>{{ $viewCheckIn->customer->phone }}</td>
                                    </tr>
                                    <tr>
                                        <td>Alamat:</td>
                                        <td>{{ $viewCheckIn->customer->alamat }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Informasi Check-in</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Waktu:</td>
                                        <td><strong>{{ $viewCheckIn->checked_in_at->format('d/m/Y H:i:s') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Koordinat:</td>
                                        <td>{{ number_format($viewCheckIn->latitude, 6) }}, {{ number_format($viewCheckIn->longitude, 6) }}</td>
                                    </tr>
                                    @if($viewCheckIn->customer->latitude && $viewCheckIn->customer->longitude)
                                        <tr>
                                            <td>Jarak dari Toko:</td>
                                            <td>
                                                @php
                                                    $distance = $viewCheckIn->getDistanceFromCustomer();
                                                @endphp
                                                @if($distance !== null)
                                                    <span class="badge bg-label-{{ $distance <= 100 ? 'success' : ($distance <= 500 ? 'warning' : 'danger') }}">
                                                        {{ round($distance) }} meter
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                            @if($viewCheckIn->catatan)
                                <div class="col-12">
                                    <h6>Catatan</h6>
                                    <p class="text-muted">{{ $viewCheckIn->catatan }}</p>
                                </div>
                            @endif
                            @if($viewCheckIn->getFirstMediaUrl('selfie_photos'))
                                <div class="col-12">
                                    <h6>Foto Selfie</h6>
                                    <img src="{{ $viewCheckIn->getFirstMediaUrl('selfie_photos') }}" alt="Selfie" class="img-fluid rounded" style="max-height: 300px;">
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

    <!-- GPS Script -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('getCurrentLocation', () => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            Livewire.dispatch('setLocation', {
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
                            Livewire.dispatch('locationError', { message: message });
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 60000
                        }
                    );
                } else {
                    Livewire.dispatch('locationError', {
                        message: 'Geolocation tidak didukung oleh browser ini.'
                    });
                }
            });
        });
    </script>
</div>
