<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Data Master Pelanggan</h4>
            <p class="text-muted mb-0">Kelola data pelanggan dan toko</p>
        </div>
        <button wire:click="openCreateModal" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i>
            Tambah Pelanggan
        </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Cari Pelanggan</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Nama toko, telepon, atau alamat...">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Filter Status</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Toko</th>
                            <th>Kontak</th>
                            <th>Alamat</th>
                            <th>Limit Piutang</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            @if($customer->getFirstMediaUrl('ktp_photos'))
                                                <img src="{{ $customer->getFirstMediaUrl('ktp_photos') }}" alt="KTP" class="rounded">
                                            @else
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    <i class="bx bx-store"></i>
                                                </span>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $customer->nama_toko }}</h6>
                                            <small class="text-muted">ID: {{ $customer->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-medium">{{ $customer->phone }}</div>
                                        @if($customer->latitude && $customer->longitude)
                                            <small class="text-success">
                                                <i class="bx bx-map-pin"></i> GPS tersedia
                                            </small>
                                        @else
                                            <small class="text-muted">
                                                <i class="bx bx-map-pin"></i> GPS belum diset
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ Str::limit($customer->alamat, 50) }}</small>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-medium">{{ $customer->limit_hari_piutang }} hari</div>
                                        <small class="text-muted">Rp {{ number_format($customer->limit_amount_piutang, 0, ',', '.') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $customer->is_active ? 'success' : 'secondary' }}">
                                        {{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#" wire:click.prevent="openEditModal({{ $customer->id }})">
                                                <i class="bx bx-edit me-1"></i> Edit
                                            </a>
                                            @if($customer->getFirstMediaUrl('ktp_photos'))
                                                <a class="dropdown-item" href="{{ $customer->getFirstMediaUrl('ktp_photos') }}" target="_blank">
                                                    <i class="bx bx-image me-1"></i> Lihat KTP
                                                </a>
                                            @endif
                                            <a class="dropdown-item" href="#" wire:click.prevent="toggleStatus({{ $customer->id }})">
                                                <i class="bx bx-{{ $customer->is_active ? 'x' : 'check' }}-circle me-1"></i>
                                                {{ $customer->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#"
                                               wire:click.prevent="deleteCustomer({{ $customer->id }})"
                                               onclick="return confirm('Yakin ingin menghapus pelanggan ini?')">
                                                <i class="bx bx-trash me-1"></i> Hapus
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bx bx-store text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">Belum ada data pelanggan</p>
                                    <button wire:click="openCreateModal" class="btn btn-primary btn-sm">
                                        <i class="bx bx-plus me-1"></i> Tambah Pelanggan Pertama
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $customers->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editMode ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Toko <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="nama_toko" class="form-control @error('nama_toko') is-invalid @enderror">
                                    @error('nama_toko') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No. Telepon <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="phone" class="form-control @error('phone') is-invalid @enderror">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                                    <textarea wire:model="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3"></textarea>
                                    @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Foto KTP <span class="text-danger">{{ $editMode ? '' : '*' }}</span></label>
                                    <input type="file" wire:model="foto_ktp" class="form-control @error('foto_ktp') is-invalid @enderror" accept="image/*">
                                    @error('foto_ktp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($foto_ktp)
                                        <div class="mt-2">
                                            <img src="{{ $foto_ktp->temporaryUrl() }}" alt="Preview KTP" class="img-thumbnail" style="max-height: 150px;">
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" wire:model="is_active" id="is_active_customer">
                                        <label class="form-check-label" for="is_active_customer">
                                            {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Limit Hari Piutang <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" wire:model="limit_hari_piutang" class="form-control @error('limit_hari_piutang') is-invalid @enderror" min="1" max="365">
                                        <span class="input-group-text">hari</span>
                                    </div>
                                    @error('limit_hari_piutang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Limit Amount Piutang <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" wire:model="limit_amount_piutang" class="form-control @error('limit_amount_piutang') is-invalid @enderror" min="0" step="1000">
                                    </div>
                                    @error('limit_amount_piutang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Koordinat GPS (Opsional)</label>
                                    <div class="row g-2">
                                        <div class="col-md-5">
                                            <input type="number" wire:model="latitude" class="form-control @error('latitude') is-invalid @enderror" placeholder="Latitude" step="any">
                                            @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-5">
                                            <input type="number" wire:model="longitude" class="form-control @error('longitude') is-invalid @enderror" placeholder="Longitude" step="any">
                                            @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-primary w-100" wire:click="getCurrentLocation">
                                                <i class="bx bx-current-location"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted">Klik tombol GPS untuk mengambil lokasi saat ini</small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                {{ $editMode ? 'Update' : 'Simpan' }}
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

    <!-- GPS Script -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('getCurrentLocation', () => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        Livewire.dispatch('setLocation', {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        });
                    }, function(error) {
                        alert('Error getting location: ' + error.message);
                    });
                } else {
                    alert('Geolocation is not supported by this browser.');
                }
            });
        });
    </script>
</div>
