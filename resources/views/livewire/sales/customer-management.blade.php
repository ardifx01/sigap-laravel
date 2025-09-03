<div>
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1">Manajemen Pelanggan</h4>
            <p class="text-muted mb-0">Kelola data pelanggan dan toko Anda.</p>
        </div>
        <button wire:click="openCreateModal" class="btn btn-primary align-self-md-auto align-self-stretch">
            <i class="bx bx-plus me-1"></i>
            Tambah Pelanggan
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

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <label class="form-label">Cari Pelanggan</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari berdasarkan nama toko, telepon, atau alamat...">
                </div>
                <div class="col-12 col-md-4">
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
        <div class="card-header">
            <h5 class="mb-0">Daftar Pelanggan Anda</h5>
        </div>
        <div class="card-body p-0">
            <style>
                /* Modal scroll fix */
                .modal-dialog-scrollable {
                    height: calc(100vh - 10vh);
                }
                .modal-dialog-scrollable .modal-content {
                    max-height: calc(100vh - 10vh);
                    overflow: hidden;
                }
                .modal-dialog-scrollable .modal-body {
                    overflow-y: auto;
                    max-height: calc(90vh - 180px);
                    -webkit-overflow-scrolling: touch;
                }
                .modal-dialog-scrollable .modal-footer {
                    position: sticky;
                    bottom: 0;
                    background: white;
                    z-index: 10;
                    border-top: 1px solid #dee2e6;
                    flex-shrink: 0;
                    padding: 1rem 1.5rem 1.5rem 1.5rem;
                }
                
                /* Mobile modal fixes */
                @media (max-width: 767.98px) {
                    .modal-dialog {
                        margin: 1rem;
                        max-height: calc(100vh - 2rem);
                    }
                    .modal-dialog-scrollable {
                        height: calc(100vh - 2rem);
                    }
                    .modal-dialog-scrollable .modal-content {
                        max-height: calc(100vh - 2rem);
                    }
                    .modal-dialog-scrollable .modal-body {
                        max-height: calc(100vh - 200px);
                        overflow-y: auto;
                    }
                    .modal-dialog-scrollable .modal-footer {
                        position: sticky;
                        bottom: 0;
                        background: white;
                        z-index: 10;
                        border-top: 1px solid #dee2e6;
                        padding: 1rem 1rem 1.5rem 1rem;
                    }
                }
                
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
                    .mobile-cards .customer-info-cell {
                        display: block; /* Override the flex for the main user info */
                        padding-bottom: 1rem;
                        margin-bottom: 1rem;
                        border-bottom: 1px solid #eee;
                    }
                    .mobile-cards .customer-info-cell:before {
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
                            <th>Kontak</th>
                            <th>Limit Piutang</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td data-label="Toko" class="customer-info-cell">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            @if($customer->getFirstMediaUrl('ktp_photos'))
                                                <img src="{{ $customer->getFirstMediaUrl('ktp_photos') }}" alt="KTP" class="rounded-circle">
                                            @else
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    <i class="bx bx-store"></i>
                                                </span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="fw-medium">{{ $customer->nama_toko }}</span>
                                            <br>
                                            <small class="text-muted">{{ Str::limit($customer->alamat, 35) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Kontak">
                                    <div>
                                        <span class="fw-medium">{{ $customer->phone }}</span>
                                        <br>
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
                                <td data-label="Limit Piutang">
                                    <div>
                                        <span class="fw-medium">Rp {{ number_format($customer->limit_amount_piutang, 0, ',', '.') }}</span>
                                        <br>
                                        <small class="text-muted">{{ $customer->limit_hari_piutang }} hari</small>
                                    </div>
                                </td>
                                <td data-label="Status">
                                    <span class="badge bg-label-{{ $customer->is_active ? 'success' : 'danger' }}" wire:click="toggleStatus({{ $customer->id }})" style="cursor:pointer;">
                                        {{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td data-label="Aksi" class="actions-cell">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0);" wire:click="openEditModal({{ $customer->id }})">
                                                    <i class="bx bx-edit me-1"></i> Edit
                                                </a>
                                            </li>
                                            @if($customer->getFirstMediaUrl('ktp_photos'))
                                                <li>
                                                    <a class="dropdown-item" href="{{ $customer->getFirstMediaUrl('ktp_photos') }}" target="_blank">
                                                        <i class="bx bx-id-card me-1"></i> Lihat KTP
                                                    </a>
                                                </li>
                                            @endif
                                             @if($customer->latitude && $customer->longitude)
                                                <li>
                                                    <a class="dropdown-item" href="https://maps.google.com/?q={{ $customer->latitude }},{{ $customer->longitude }}" target="_blank">
                                                        <i class="bx bx-map me-1"></i> Lihat Lokasi
                                                    </a>
                                                </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                   wire:click="deleteCustomer({{ $customer->id }})"
                                                   onclick="return confirm('Anda yakin ingin menghapus pelanggan ini?')">
                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-user-x text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-1">Belum ada data pelanggan</p>
                                        <small class="text-muted">Buat pelanggan baru untuk memulai.</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($customers->hasPages())
            <div class="card-footer">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show" style="display: block; overflow-y: auto;" tabindex="-1" wire:ignore.self>
            <div class="modal-dialog modal-lg modal-dialog-scrollable" style="max-height: 90vh; margin: 5vh auto;">
                <div class="modal-content" style="max-height: 90vh;">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editMode ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="save" id="customer-form">
                        <div class="modal-body" style="overflow-y: auto; max-height: calc(90vh - 180px); padding: 1.5rem;">
                            <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Toko <span class="text-danger">*</span></label>
                                <input type="text" wire:model="nama_toko" class="form-control @error('nama_toko') is-invalid @enderror" placeholder="Contoh: Toko Berkah Jaya">
                                @error('nama_toko') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. Telepon <span class="text-danger">*</span></label>
                                <input type="text" wire:model="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="Contoh: 081234567890">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                                <textarea wire:model="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3" placeholder="Masukkan alamat lengkap toko"></textarea>
                                @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Foto KTP <span class="text-danger">{{ $editMode ? '' : '*' }}</span></label>
                                <input type="file" wire:model="foto_ktp" class="form-control @error('foto_ktp') is-invalid @enderror" accept="image/*">
                                @if(!$editMode) <small class="text-muted">Wajib diisi untuk pelanggan baru.</small> @else <small class="text-muted">Kosongkan jika tidak ingin mengubah.</small> @endif
                                @error('foto_ktp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                
                                <div wire:loading wire:target="foto_ktp" class="mt-2">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span>Uploading...</span>
                                </div>

                                @if ($foto_ktp)
                                    <div class="mt-2">
                                        <img src="{{ $foto_ktp->temporaryUrl() }}" class="img-thumbnail" style="max-height: 150px;">
                                    </div>
                                @elseif($editMode && $customer->getFirstMediaUrl('ktp_photos'))
                                     <div class="mt-2">
                                        <img src="{{ $customer->getFirstMediaUrl('ktp_photos') }}" class="img-thumbnail" style="max-height: 150px;">
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
                            <hr class="my-3">
                            <div class="col-12">
                                 <p class="fw-medium">Pengaturan Piutang</p>
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
                            <hr class="my-3">
                            <div class="col-12">
                                 <p class="fw-medium">Lokasi GPS (Opsional)</p>
                            </div>
                            <div class="col-12">
                                <div class="input-group">
                                    <input type="text" wire:model="latitude" class="form-control @error('latitude') is-invalid @enderror" placeholder="Latitude">
                                    <input type="text" wire:model="longitude" class="form-control @error('longitude') is-invalid @enderror" placeholder="Longitude">
                                    <button class="btn btn-outline-primary" type="button" wire:click="getCurrentLocation">
                                        <i class="bx bx-current-location"></i>
                                    </button>
                                </div>
                                 @error('latitude') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                 @error('longitude') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                <small class="text-muted">Klik tombol untuk mengambil lokasi saat ini atau isi manual.</small>
                            </div>
                        </div>
                            <!-- Extra padding untuk scroll -->
                            <div class="pb-3"></div>
                        </div>
                        <div class="modal-footer" style="position: sticky; bottom: 0; background: white; z-index: 10; border-top: 1px solid #dee2e6; padding: 1rem 1.5rem 1.5rem 1.5rem;">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                {{ $editMode ? 'Update' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" wire:ignore.self style="position: fixed;"></div>
    @endif

    <!-- GPS Script -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('getCurrentLocation', () => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        @this.dispatch('setLocation', {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        });
                    }, function(error) {
                        alert('Error: ' + error.message);
                    });
                } else {
                    alert('Geolocation tidak didukung oleh browser ini.');
                }
            });
        });
    </script>
</div>