<div>
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1">Manajemen User</h4>
            <p class="text-muted mb-0">Kelola user Sales, Gudang, dan Supir</p>
        </div>
        <button wire:click="openCreateModal" class="btn btn-primary align-self-md-auto align-self-stretch">
            <i class="bx bx-plus me-1"></i>
            Tambah User
        </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label">Cari User</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Nama, email, atau telepon...">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Filter Role</label>
                    <select wire:model.live="roleFilter" class="form-select">
                        <option value="">Semua Role</option>
                        <option value="admin">Admin</option>
                        <option value="sales">Sales</option>
                        <option value="gudang">Gudang</option>
                        <option value="supir">Supir</option>
                    </select>
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

    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
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
                    .mobile-cards .user-info-cell {
                        display: block; /* Override the flex for the main user info */
                        padding-bottom: 1rem;
                        margin-bottom: 1rem;
                        border-bottom: 1px solid #eee;
                    }
                    .mobile-cards .user-info-cell:before {
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
                <table class="table table-hover mobile-cards">
                    <thead class="d-none d-md-table-header-group">
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Kontak</th>
                            <th>Status</th>
                            <th>Terakhir Login</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td data-label="User" class="user-info-cell">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            @if($user->photo)
                                                <img src="{{ $user->getFirstMediaUrl('photos') }}" alt="{{ $user->name }}" class="rounded-circle">
                                            @else
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    {{ $user->initials() }}
                                                </span>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $user->name }}</h6>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Role">
                                    <span class="badge bg-label-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'sales' ? 'success' : ($user->role === 'gudang' ? 'info' : 'warning')) }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td data-label="Kontak">
                                    <small class="text-muted">{{ $user->phone }}</small>
                                </td>
                                <td data-label="Status">
                                    <span class="badge bg-label-{{ $user->is_active ? 'success' : 'secondary' }}">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td data-label="Terakhir Login">
                                    <small class="text-muted">
                                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah' }}
                                    </small>
                                </td>
                                <td data-label="Aksi" class="actions-cell">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#" wire:click.prevent="openEditModal({{ $user->id }})">
                                                <i class="bx bx-edit me-1"></i> Edit
                                            </a>
                                            <a class="dropdown-item" href="#" wire:click.prevent="toggleStatus({{ $user->id }})">
                                                <i class="bx bx-{{ $user->is_active ? 'x' : 'check' }}-circle me-1"></i>
                                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </a>
                                            @if($user->id !== auth()->id())
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-danger" href="#"
                                                   wire:click.prevent="deleteUser({{ $user->id }})"
                                                   onclick="return confirm('Yakin ingin menghapus user ini?')">
                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bx bx-user text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">Tidak ada user ditemukan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editMode ? 'Edit User' : 'Tambah User Baru' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">No. Telepon <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="phone" class="form-control @error('phone') is-invalid @enderror">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Role <span class="text-danger">*</span></label>
                                    <select wire:model="role" class="form-select @error('role') is-invalid @enderror">
                                        <option value="sales">Sales</option>
                                        <option value="gudang">Gudang</option>
                                        <option value="supir">Supir</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Password {{ $editMode ? '(kosongkan jika tidak diubah)' : '' }} <span class="text-danger">{{ $editMode ? '' : '*' }}</span></label>
                                    <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror">
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Konfirmasi Password {{ $editMode ? '(kosongkan jika tidak diubah)' : '' }} <span class="text-danger">{{ $editMode ? '' : '*' }}</span></label>
                                    <input type="password" wire:model="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror">
                                    @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Foto Profil</label>
                                    <input type="file" wire:model="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                                    @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($photo)
                                        <div class="mt-2">
                                            <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Status</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" wire:model="is_active" id="is_active">
                                        <label class="form-check-label" for="is_active">
                                            {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                                        </label>
                                    </div>
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
</div>
