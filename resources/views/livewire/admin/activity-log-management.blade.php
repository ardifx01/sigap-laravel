<div>
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1">Activity Logs</h4>
            <p class="text-muted mb-0">Monitor semua aktivitas sistem dan perubahan data</p>
        </div>
        <div class="d-grid d-md-flex gap-2">
            <button wire:click="clearFilters" class="btn btn-outline-secondary">
                <i class="bx bx-refresh"></i> Reset Filter
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-2">
                    <label class="form-label">Pencarian</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari aktivitas...">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Log Name</label>
                    <select wire:model.live="logName" class="form-select">
                        <option value="">Semua</option>
                        @foreach($logNames as $name)
                            <option value="{{ $name }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">User Type</label>
                    <select wire:model.live="causerType" class="form-select">
                        <option value="">Semua</option>
                        @foreach($causerTypes as $type)
                            <option value="App\Models\{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Subject Type</label>
                    <select wire:model.live="subjectType" class="form-select">
                        <option value="">Semua</option>
                        @foreach($subjectTypes as $type)
                            <option value="App\Models\{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Rentang Tanggal</label>
                    <div class="d-flex gap-2">
                        <input type="date" wire:model.live="dateFrom" class="form-control">
                        <input type="date" wire:model.live="dateTo" class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Logs Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Activity Logs</h5>
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
                    .mobile-cards .log-info-cell {
                        display: block;
                        padding-bottom: 1rem;
                        margin-bottom: 1rem;
                        border-bottom: 1px solid #eee;
                    }
                    .mobile-cards .log-info-cell:before {
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
                            <th>User</th>
                            <th>Aktivitas</th>
                            <th>Subject</th>
                            <th>Log Name</th>
                            <th>Properties</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activityLogs as $log)
                            <tr>
                                <td data-label="Waktu" class="log-info-cell">
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium">{{ $log->created_at->format('d/m/Y') }}</span>
                                        <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                    </div>
                                </td>
                                <td data-label="User">
                                    @if($log->causer)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    {{ substr($log->causer->name ?? 'U', 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="fw-medium">{{ $log->causer->name ?? 'Unknown' }}</span>
                                                <br>
                                                <small class="text-muted">{{ $log->causer->role ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td data-label="Aktivitas">
                                    <span class="badge bg-label-info">{{ $log->description }}</span>
                                </td>
                                <td data-label="Subject">
                                    @if($log->subject)
                                        <div>
                                            <span class="fw-medium">{{ class_basename($log->subject_type) }}</span>
                                            <br>
                                            <small class="text-muted">ID: {{ $log->subject_id }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td data-label="Log Name">
                                    @if($log->log_name)
                                        <span class="badge bg-label-secondary">{{ $log->log_name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td data-label="Properties" class="actions-cell">
                                    @if($log->properties && count($log->properties) > 0)
                                        <button class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#propertiesModal{{ $log->id }}">
                                            <i class="bx bx-show"></i> Lihat
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-history text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-0">Tidak ada activity log ditemukan</p>
                                        <small class="text-muted">Coba ubah filter pencarian</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($activityLogs->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $activityLogs->links('pagination::simple-bootstrap-5') }}
            </div>
        @endif
    </div>

    <!-- Properties Modals -->
    @foreach($activityLogs as $log)
        @if($log->properties && count($log->properties) > 0)
            <div class="modal fade" id="propertiesModal{{ $log->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Properties Detail - {{ $log->description }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <h6>Old Values</h6>
                                    @if(isset($log->properties['old']) && count($log->properties['old']) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                @foreach($log->properties['old'] as $key => $value)
                                                    <tr>
                                                        <td class="fw-medium">{{ $key }}</td>
                                                        <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted">No old values</p>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6">
                                    <h6>New Values</h6>
                                    @if(isset($log->properties['attributes']) && count($log->properties['attributes']) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                @foreach($log->properties['attributes'] as $key => $value)
                                                    <tr>
                                                        <td class="fw-medium">{{ $key }}</td>
                                                        <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted">No new values</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>
