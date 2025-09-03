<div>
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1">K3 Checklist System</h4>
            <p class="text-muted mb-0">Checklist Keselamatan dan Kesehatan Kerja sebelum pengiriman</p>
        </div>
        <button wire:click="openChecklistModal" class="btn btn-primary align-self-md-auto align-self-stretch">
            <i class="bx bx-shield me-1"></i>
            Buat Checklist
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

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="bx bx-check-shield"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Checklist Hari Ini</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">{{ $todayChecklists }}</h6>
                                <small class="text-muted fw-semibold">checklist</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="bx bx-list-check"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Checklist</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">{{ $checklists->total() }}</h6>
                                <small class="text-muted fw-semibold">total</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Deliveries Alert -->
    @if($availableDeliveries->count() > 0)
        <div class="alert alert-info mb-4">
            <h6 class="alert-heading">
                <i class="bx bx-info-circle me-1"></i>
                Pengiriman Menunggu
            </h6>
            <p class="mb-2">Anda memiliki {{ $availableDeliveries->count() }} pengiriman yang menunggu. Pastikan untuk melakukan K3 checklist sebelum berangkat.</p>
            <div class="row g-2">
                @foreach($availableDeliveries->take(3) as $delivery)
                    <div class="col-md-4">
                        <div class="card card-body p-2">
                            <small class="fw-medium">{{ $delivery->order->customer->nama_toko }}</small>
                            <small class="text-muted">{{ $delivery->order->nomor_order }}</small>
                            <button wire:click="openChecklistModal({{ $delivery->id }})" class="btn btn-sm btn-primary mt-1">
                                <i class="bx bx-shield me-1"></i> Checklist
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">Cari Checklist</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari catatan...">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Filter Tanggal</label>
                    <input type="date" wire:model.live="dateFilter" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <!-- Checklists Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Riwayat K3 Checklist</h5>
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
                    .mobile-cards .checklist-info-cell {
                        display: block;
                        padding-bottom: 1rem;
                        margin-bottom: 1rem;
                        border-bottom: 1px solid #eee;
                    }
                    .mobile-cards .checklist-info-cell:before {
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
                            <th>Tanggal</th>
                            <th>Pengiriman</th>
                            <th>Completion</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checklists as $checklist)
                            <tr>
                                <td data-label="Tanggal" class="checklist-info-cell">
                                    <div>
                                        <span class="fw-medium">{{ $checklist->checked_at->format('d/m/Y') }}</span>
                                        <br>
                                        <small class="text-muted">{{ $checklist->checked_at->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td data-label="Pengiriman">
                                    @if($checklist->delivery)
                                        <div>
                                            <span class="fw-medium">{{ $checklist->delivery->order->customer->nama_toko }}</span>
                                            <br>
                                            <small class="text-muted">{{ $checklist->delivery->order->nomor_order }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">General Checklist</span>
                                    @endif
                                </td>
                                <td data-label="Completion">
                                    <div>
                                        <div class="d-flex align-items-center mb-1">
                                            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                <div class="progress-bar bg-{{ $checklist->getCompletionPercentage() >= 80 ? 'success' : ($checklist->getCompletionPercentage() >= 60 ? 'warning' : 'danger') }}"
                                                     style="width: {{ $checklist->getCompletionPercentage() }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $checklist->getCompletionPercentage() }}%</small>
                                        </div>
                                        <small class="text-muted">{{ $checklist->getPassedItemsCount() }}/{{ $checklist->getTotalItemsCount() }} items</small>
                                    </div>
                                </td>

                                <td data-label="Aksi" class="actions-cell">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click.prevent="viewChecklist({{ $checklist->id }})">
                                                    <i class="bx bx-show me-1"></i> Detail
                                                </a>
                                            </li>
                                            @if($checklist->vehicle_photo)
                                                <li>
                                                    <a class="dropdown-item" href="{{ asset('storage/vehicle_photos/' . $checklist->vehicle_photo) }}" target="_blank">
                                                        <i class="bx bx-image me-1"></i> Lihat Foto
                                                    </a>
                                                </li>
                                            @endif
                                            @if($checklist->checked_at->isToday())
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#"
                                                       wire:click.prevent="deleteChecklist({{ $checklist->id }})"
                                                       onclick="return confirm('Yakin ingin menghapus checklist ini?')">
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
                                        <i class="bx bx-shield text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-1">Belum ada K3 checklist</p>
                                        <small class="text-muted">Buat checklist keselamatan sebelum pengiriman.</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($checklists->hasPages())
            <div class="card-footer">
                {{ $checklists->links() }}
            </div>
        @endif
    </div>

    <!-- Create Checklist Modal -->
    @if($showChecklistModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">K3 Checklist - Keselamatan dan Kesehatan Kerja</h5>
                        <button type="button" class="btn-close" wire:click="closeChecklistModal"></button>
                    </div>
                    <form wire:submit.prevent="{{ $checklistId ? 'updateChecklist' : 'createChecklist' }}">
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                Pastikan semua item checklist telah diperiksa sebelum memulai perjalanan. Keselamatan adalah prioritas utama.
                            </div>

                            @if($delivery_id)
                                <div class="alert alert-primary">
                                    <h6 class="alert-heading">Pengiriman Terkait</h6>
                                    @php
                                        $delivery = $availableDeliveries->where('id', $delivery_id)->first();
                                    @endphp
                                    @if($delivery)
                                        <p class="mb-0">
                                            <strong>{{ $delivery->order->customer->nama_toko }}</strong><br>
                                            Order: {{ $delivery->order->nomor_order }}
                                        </p>
                                    @endif
                                </div>
                            @endif

                            <div class="row g-3">
                                <!-- Vehicle Condition Checks -->
                                <div class="col-12">
                                    <h6 class="mb-3">Kondisi Kendaraan</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="cek_ban" id="cek_ban">
                                                <label class="form-check-label" for="cek_ban">
                                                    <i class="bx bx-car me-1"></i> Kondisi Ban Baik
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="cek_rem" id="cek_rem">
                                                <label class="form-check-label" for="cek_rem">
                                                    <i class="bx bx-stop-circle me-1"></i> Kondisi Rem Baik
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="cek_oli" id="cek_oli">
                                                <label class="form-check-label" for="cek_oli">
                                                    <i class="bx bx-droplet me-1"></i> Kondisi Oli Baik
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="cek_bbm" id="cek_bbm">
                                                <label class="form-check-label" for="cek_bbm">
                                                    <i class="bx bx-gas-station me-1"></i> Level BBM Cukup
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="cek_air_radiator" id="cek_air_radiator">
                                                <label class="form-check-label" for="cek_air_radiator">
                                                    <i class="bx bx-droplet me-1"></i> Air Radiator Cukup
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="cek_terpal" id="cek_terpal">
                                                <label class="form-check-label" for="cek_terpal">
                                                    <i class="bx bx-shield me-1"></i> Kondisi Terpal Baik
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Notes -->
                                <div class="col-12">
                                    <hr>
                                    <label class="form-label">Catatan</label>
                                    <textarea wire:model="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3" placeholder="Catatan atau temuan khusus..."></textarea>
                                    @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeChecklistModal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-check me-1"></i> Simpan Checklist
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- View Checklist Modal -->
    @if($showViewModal && $selectedChecklist)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail K3 Checklist</h5>
                        <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Checklist Info -->
                            <div class="col-md-6">
                                <h6>Informasi Checklist</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Tanggal:</td>
                                        <td><strong>{{ $selectedChecklist->checked_at->format('d/m/Y H:i') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Completion:</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                    <div class="progress-bar bg-{{ $selectedChecklist->getCompletionPercentage() >= 80 ? 'success' : ($selectedChecklist->getCompletionPercentage() >= 60 ? 'warning' : 'danger') }}"
                                                         style="width: {{ $selectedChecklist->getCompletionPercentage() }}%"></div>
                                                </div>
                                                <small>{{ $selectedChecklist->getCompletionPercentage() }}%</small>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Delivery Info -->
                            <div class="col-md-6">
                                @if($selectedChecklist->delivery)
                                    <h6>Informasi Pengiriman</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Order:</td>
                                            <td><strong>{{ $selectedChecklist->delivery->order->nomor_order }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Customer:</td>
                                            <td>{{ $selectedChecklist->delivery->order->customer->nama_toko }}</td>
                                        </tr>
                                        <tr>
                                            <td>Alamat:</td>
                                            <td>{{ $selectedChecklist->delivery->order->customer->alamat }}</td>
                                        </tr>
                                    </table>
                                @else
                                    <h6>General Checklist</h6>
                                    <p class="text-muted">Checklist umum tidak terkait pengiriman spesifik</p>
                                @endif
                            </div>

                            <!-- Checklist Items -->
                            <div class="col-12">
                                <hr>
                                <h6>Detail Checklist Items</h6>
                                <div class="row g-3">
                                    @foreach($selectedChecklist->getChecklistItems() as $key => $label)
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-{{ $selectedChecklist->$key ? 'check-circle text-success' : 'x-circle text-danger' }} me-2"></i>
                                                <span class="{{ $selectedChecklist->$key ? 'text-success' : 'text-danger' }}">{{ $label }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Additional Notes -->
                            @if($selectedChecklist->catatan)
                                <div class="col-12">
                                    <hr>
                                    <h6>Catatan</h6>
                                    <p class="text-muted">{{ $selectedChecklist->catatan }}</p>
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
</div>
