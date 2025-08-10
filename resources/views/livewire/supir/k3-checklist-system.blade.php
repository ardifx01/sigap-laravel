<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">K3 Checklist System</h4>
            <p class="text-muted mb-0">Checklist Keselamatan dan Kesehatan Kerja sebelum pengiriman</p>
        </div>
        <button wire:click="openChecklistModal" class="btn btn-primary">
            <i class="bx bx-shield-check me-1"></i>
            Buat Checklist
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
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-warning">
                                <i class="bx bx-time-five"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Menunggu Approval</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">{{ $pendingChecklists }}</h6>
                                <small class="text-muted fw-semibold">pending</small>
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
                                <i class="bx bx-shield-check me-1"></i> Checklist
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
                <div class="col-md-4">
                    <label class="form-label">Cari Checklist</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Cari catatan...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filter Status</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filter Tanggal</label>
                    <input type="date" wire:model.live="dateFilter" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <!-- Checklists Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pengiriman</th>
                            <th>Completion</th>
                            <th>Status</th>
                            <th>Approved By</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checklists as $checklist)
                            <tr>
                                <td>
                                    <div>
                                        <div class="fw-medium">{{ $checklist->tanggal_checklist->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $checklist->tanggal_checklist->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($checklist->delivery)
                                        <div>
                                            <div class="fw-medium">{{ $checklist->delivery->order->customer->nama_toko }}</div>
                                            <small class="text-muted">{{ $checklist->delivery->order->nomor_order }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">General Checklist</span>
                                    @endif
                                </td>
                                <td>
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
                                <td>
                                    <span class="badge bg-label-{{
                                        $checklist->status === 'pending' ? 'warning' :
                                        ($checklist->status === 'approved' ? 'success' : 'danger')
                                    }}">
                                        {{ ucfirst($checklist->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($checklist->approvedBy)
                                        <div>
                                            <div class="fw-medium">{{ $checklist->approvedBy->name }}</div>
                                            <small class="text-muted">{{ $checklist->approved_at->format('d/m/Y H:i') }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#" wire:click.prevent="viewChecklist({{ $checklist->id }})">
                                                <i class="bx bx-show me-1"></i> Detail
                                            </a>
                                            @if($checklist->getFirstMediaUrl('vehicle_photos'))
                                                <a class="dropdown-item" href="{{ $checklist->getFirstMediaUrl('vehicle_photos') }}" target="_blank">
                                                    <i class="bx bx-image me-1"></i> Lihat Foto
                                                </a>
                                            @endif
                                            @if($checklist->status === 'pending' && $checklist->tanggal_checklist->isToday())
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-danger" href="#"
                                                   wire:click.prevent="deleteChecklist({{ $checklist->id }})"
                                                   onclick="return confirm('Yakin ingin menghapus checklist ini?')">
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
                                    <i class="bx bx-shield-check text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">Belum ada K3 checklist</p>
                                    <button wire:click="openChecklistModal" class="btn btn-primary btn-sm">
                                        <i class="bx bx-shield-check me-1"></i> Buat Checklist Pertama
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $checklists->links() }}
            </div>
        </div>
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
                    <form wire:submit.prevent="createChecklist">
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
                                                <input class="form-check-input" type="checkbox" wire:model="kondisi_ban" id="kondisi_ban">
                                                <label class="form-check-label" for="kondisi_ban">
                                                    <i class="bx bx-car me-1"></i> Kondisi Ban Baik
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="kondisi_rem" id="kondisi_rem">
                                                <label class="form-check-label" for="kondisi_rem">
                                                    <i class="bx bx-stop-circle me-1"></i> Kondisi Rem Baik
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="level_oli_mesin" id="level_oli_mesin">
                                                <label class="form-check-label" for="level_oli_mesin">
                                                    <i class="bx bx-droplet me-1"></i> Level Oli Mesin Cukup
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="level_bbm" id="level_bbm">
                                                <label class="form-check-label" for="level_bbm">
                                                    <i class="bx bx-gas-station me-1"></i> Level BBM Cukup
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="kondisi_lampu" id="kondisi_lampu">
                                                <label class="form-check-label" for="kondisi_lampu">
                                                    <i class="bx bx-bulb me-1"></i> Kondisi Lampu Baik
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="kondisi_spion" id="kondisi_spion">
                                                <label class="form-check-label" for="kondisi_spion">
                                                    <i class="bx bx-reflect-horizontal me-1"></i> Kondisi Spion Baik
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="kondisi_klakson" id="kondisi_klakson">
                                                <label class="form-check-label" for="kondisi_klakson">
                                                    <i class="bx bx-volume-full me-1"></i> Kondisi Klakson Baik
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="kondisi_sabuk_pengaman" id="kondisi_sabuk_pengaman">
                                                <label class="form-check-label" for="kondisi_sabuk_pengaman">
                                                    <i class="bx bx-shield me-1"></i> Sabuk Pengaman Berfungsi
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="kondisi_kaca" id="kondisi_kaca">
                                                <label class="form-check-label" for="kondisi_kaca">
                                                    <i class="bx bx-window me-1"></i> Kondisi Kaca Baik
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="kondisi_wiper" id="kondisi_wiper">
                                                <label class="form-check-label" for="kondisi_wiper">
                                                    <i class="bx bx-wind me-1"></i> Kondisi Wiper Baik
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Safety Equipment -->
                                <div class="col-12">
                                    <hr>
                                    <h6 class="mb-3">Kelengkapan Keselamatan</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="kelengkapan_p3k" id="kelengkapan_p3k">
                                                <label class="form-check-label" for="kelengkapan_p3k">
                                                    <i class="bx bx-plus-medical me-1"></i> Kotak P3K Lengkap
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="kelengkapan_apar" id="kelengkapan_apar">
                                                <label class="form-check-label" for="kelengkapan_apar">
                                                    <i class="bx bx-spray-can me-1"></i> APAR Tersedia
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="kelengkapan_segitiga" id="kelengkapan_segitiga">
                                                <label class="form-check-label" for="kelengkapan_segitiga">
                                                    <i class="bx bx-shape-triangle me-1"></i> Segitiga Pengaman
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cargo Condition -->
                                <div class="col-12">
                                    <hr>
                                    <h6 class="mb-3">Kondisi Muatan</h6>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" wire:model="kondisi_muatan" id="kondisi_muatan">
                                        <label class="form-check-label" for="kondisi_muatan">
                                            <i class="bx bx-package me-1"></i> Muatan Aman dan Terikat dengan Baik
                                        </label>
                                    </div>
                                </div>

                                <!-- Vehicle Photo -->
                                <div class="col-12">
                                    <hr>
                                    <label class="form-label">Foto Kendaraan (Opsional)</label>
                                    <input type="file" wire:model="foto_kendaraan" class="form-control @error('foto_kendaraan') is-invalid @enderror" accept="image/*">
                                    @error('foto_kendaraan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($foto_kendaraan)
                                        <div class="mt-2">
                                            <img src="{{ $foto_kendaraan->temporaryUrl() }}" alt="Preview" class="img-thumbnail" style="max-height: 150px;">
                                        </div>
                                    @endif
                                </div>

                                <!-- Additional Notes -->
                                <div class="col-12">
                                    <label class="form-label">Catatan Tambahan</label>
                                    <textarea wire:model="catatan_tambahan" class="form-control @error('catatan_tambahan') is-invalid @enderror" rows="3" placeholder="Catatan atau temuan khusus..."></textarea>
                                    @error('catatan_tambahan') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
    @if($showViewModal && $viewChecklist)
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
                                        <td><strong>{{ $viewChecklist->tanggal_checklist->format('d/m/Y H:i') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Status:</td>
                                        <td>
                                            <span class="badge bg-label-{{
                                                $viewChecklist->status === 'pending' ? 'warning' :
                                                ($viewChecklist->status === 'approved' ? 'success' : 'danger')
                                            }}">
                                                {{ ucfirst($viewChecklist->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Completion:</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                    <div class="progress-bar bg-{{ $viewChecklist->getCompletionPercentage() >= 80 ? 'success' : ($viewChecklist->getCompletionPercentage() >= 60 ? 'warning' : 'danger') }}"
                                                         style="width: {{ $viewChecklist->getCompletionPercentage() }}%"></div>
                                                </div>
                                                <small>{{ $viewChecklist->getCompletionPercentage() }}%</small>
                                            </div>
                                        </td>
                                    </tr>
                                    @if($viewChecklist->approvedBy)
                                        <tr>
                                            <td>Approved By:</td>
                                            <td>
                                                <strong>{{ $viewChecklist->approvedBy->name }}</strong><br>
                                                <small class="text-muted">{{ $viewChecklist->approved_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>

                            <!-- Delivery Info -->
                            <div class="col-md-6">
                                @if($viewChecklist->delivery)
                                    <h6>Informasi Pengiriman</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Order:</td>
                                            <td><strong>{{ $viewChecklist->delivery->order->nomor_order }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Customer:</td>
                                            <td>{{ $viewChecklist->delivery->order->customer->nama_toko }}</td>
                                        </tr>
                                        <tr>
                                            <td>Alamat:</td>
                                            <td>{{ $viewChecklist->delivery->order->customer->alamat }}</td>
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
                                    @foreach($viewChecklist->getChecklistItems() as $key => $label)
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-{{ $viewChecklist->$key ? 'check-circle text-success' : 'x-circle text-danger' }} me-2"></i>
                                                <span class="{{ $viewChecklist->$key ? 'text-success' : 'text-danger' }}">{{ $label }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Additional Notes -->
                            @if($viewChecklist->catatan_tambahan)
                                <div class="col-12">
                                    <hr>
                                    <h6>Catatan Tambahan</h6>
                                    <p class="text-muted">{{ $viewChecklist->catatan_tambahan }}</p>
                                </div>
                            @endif

                            <!-- Vehicle Photo -->
                            @if($viewChecklist->getFirstMediaUrl('vehicle_photos'))
                                <div class="col-12">
                                    <hr>
                                    <h6>Foto Kendaraan</h6>
                                    <img src="{{ $viewChecklist->getFirstMediaUrl('vehicle_photos') }}" alt="Vehicle Photo" class="img-fluid rounded" style="max-height: 300px;">
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
