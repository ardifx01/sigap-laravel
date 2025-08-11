<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Manajemen Pelanggan</h4>
            <p class="text-muted mb-0">Kelola data pelanggan dan informasi toko</p>
        </div>
        <button wire:click="openCustomerModal" class="btn btn-primary">
            <i class="bx bx-plus"></i> Tambah Customer
        </button>
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

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="bx bx-store"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Customer</small>
                            <h6 class="mb-0">{{ $totalCustomers }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="bx bx-check-circle"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Customer Aktif</small>
                            <h6 class="mb-0">{{ $activeCustomers }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-warning">
                                <i class="bx bx-x-circle"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Customer Nonaktif</small>
                            <h6 class="mb-0">{{ $inactiveCustomers }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="bx bx-money"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Credit Limit</small>
                            <h6 class="mb-0">Rp {{ number_format($totalCreditLimit, 0, ',', '.') }}</h6>
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
                <div class="col-md-4">
                    <label class="form-label">Pencarian</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari nama toko, pemilik, atau telepon...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sales</label>
                    <select wire:model.live="salesFilter" class="form-select">
                        <option value="">Semua Sales</option>
                        @foreach($salesUsers as $sales)
                            <option value="{{ $sales->id }}">{{ $sales->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Per Halaman</label>
                    <select wire:model.live="perPage" class="form-select">
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Customer</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Toko</th>
                            <th>Pemilik</th>
                            <th>Kontak</th>
                            <th>Sales</th>
                            <th>Credit Limit</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>
                                    <div>
                                        <span class="fw-medium">{{ $customer->nama_toko }}</span>
                                        <br>
                                        <small class="text-muted">{{ Str::limit($customer->alamat, 30) }}</small>
                                    </div>
                                </td>
                                <td>{{ $customer->nama_pemilik }}</td>
                                <td>
                                    <div>
                                        <span class="fw-medium">{{ $customer->telepon }}</span>
                                        @if($customer->email)
                                            <br>
                                            <small class="text-muted">{{ $customer->email }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $customer->sales->name ?? '-' }}</td>
                                <td>
                                    @if($customer->credit_limit)
                                        <span class="fw-medium">Rp {{ number_format($customer->credit_limit, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $customer->is_active ? 'success' : 'danger' }}">
                                        {{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click="openCustomerModal({{ $customer->id }})">
                                                    <i class="bx bx-edit me-1"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click="toggleStatus({{ $customer->id }})">
                                                    <i class="bx bx-{{ $customer->is_active ? 'x' : 'check' }}-circle me-1"></i>
                                                    {{ $customer->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </a>
                                            </li>
                                            @if($customer->latitude && $customer->longitude)
                                                <li>
                                                    <a class="dropdown-item" href="https://maps.google.com/?q={{ $customer->latitude }},{{ $customer->longitude }}" target="_blank">
                                                        <i class="bx bx-map me-1"></i> Lihat Lokasi
                                                    </a>
                                                </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#"
                                                   wire:click="deleteCustomer({{ $customer->id }})"
                                                   onclick="return confirm('Yakin ingin menghapus customer ini?')">
                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-store text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-0">Tidak ada customer ditemukan</p>
                                        <small class="text-muted">Tambah customer baru atau ubah filter pencarian</small>
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

    <!-- Customer Modal -->
    @if($showCustomerModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $customerId ? 'Edit Customer' : 'Tambah Customer' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showCustomerModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="saveCustomer">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Toko <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="nama_toko" class="form-control @error('nama_toko') is-invalid @enderror">
                                    @error('nama_toko') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nama Pemilik <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="nama_pemilik" class="form-control @error('nama_pemilik') is-invalid @enderror">
                                    @error('nama_pemilik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Alamat <span class="text-danger">*</span></label>
                                    <textarea wire:model="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3"></textarea>
                                    @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Telepon <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="telepon" class="form-control @error('telepon') is-invalid @enderror">
                                    @error('telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sales <span class="text-danger">*</span></label>
                                    <select wire:model="sales_id" class="form-select @error('sales_id') is-invalid @enderror">
                                        <option value="">Pilih Sales</option>
                                        @foreach($salesUsers as $sales)
                                            <option value="{{ $sales->id }}">{{ $sales->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('sales_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Credit Limit</label>
                                    <input type="number" wire:model="credit_limit" class="form-control @error('credit_limit') is-invalid @enderror" min="0">
                                    @error('credit_limit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Latitude</label>
                                    <input type="number" step="any" wire:model="latitude" class="form-control @error('latitude') is-invalid @enderror">
                                    @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Longitude</label>
                                    <input type="number" step="any" wire:model="longitude" class="form-control @error('longitude') is-invalid @enderror">
                                    @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" wire:model="is_active" id="is_active">
                                        <label class="form-check-label" for="is_active">
                                            Customer Aktif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showCustomerModal', false)">Batal</button>
                        <button type="button" wire:click="saveCustomer" class="btn btn-primary">
                            <i class="bx bx-save"></i> {{ $customerId ? 'Update' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
