<div>
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1">Payment & Billing System</h4>
            <p class="text-muted mb-0">Kelola invoice dan pembayaran pelanggan</p>
        </div>
        <button wire:click="openCreateModal" class="btn btn-primary align-self-md-auto align-self-stretch">
            <i class="bx bx-plus me-1"></i>
            Buat Invoice
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
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-warning">
                                <i class="bx bx-money"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Piutang</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</h6>
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
                            <span class="avatar-initial rounded-circle bg-label-danger">
                                <i class="bx bx-time-five"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Overdue</small>
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-1 text-danger">{{ $overdueCount }}</h6>
                                <small class="text-muted fw-semibold">invoice</small>
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
                <div class="col-12 col-md-4">
                    <label class="form-label">Cari Invoice</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Nomor invoice, order, atau toko...">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Filter Status</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="belum_lunas">Belum Lunas</option>
                        <option value="sebagian">Sebagian</option>
                        <option value="lunas">Lunas</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Filter Pelanggan</label>
                    <select wire:model.live="customerFilter" class="form-select">
                        <option value="">Semua Pelanggan</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->nama_toko }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Invoice & Pembayaran</h5>
        </div>
        <div class="card-body p-0">
            <style>
                /* Searchable dropdown suggestions */
                .dropdown-suggestions {
                    position: absolute;
                    top: 100%;
                    left: 0;
                    right: 0;
                    background: white;
                    border: 1px solid #ddd;
                    border-radius: 0.375rem;
                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                    max-height: 250px;
                    overflow-y: auto;
                    z-index: 1050;
                    margin-top: 2px;
                }
                
                .suggestion-item {
                    padding: 12px 16px;
                    border-bottom: 1px solid #f0f0f0;
                    cursor: pointer;
                    transition: background-color 0.15s ease;
                }
                
                .suggestion-item:hover {
                    background-color: #f8f9fa;
                }
                
                .suggestion-item:last-child {
                    border-bottom: none;
                }
                
                .suggestion-item .fw-medium {
                    color: #374151;
                    margin-bottom: 2px;
                }
                
                .suggestion-item .text-muted {
                    color: #6b7280 !important;
                    font-size: 0.875rem;
                }
                
                .suggestion-item .text-success {
                    color: #059669 !important;
                    font-size: 0.875rem;
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
                    .mobile-cards .payment-info-cell {
                        display: block; /* Override the flex for the main payment info */
                        padding-bottom: 1rem;
                        margin-bottom: 1rem;
                        border-bottom: 1px solid #eee;
                    }
                    .mobile-cards .payment-info-cell:before {
                        display: none; /* No "Nota:" label */
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
                            <th>Nota</th>
                            <th>Pelanggan</th>
                            <th>Tagihan</th>
                            <th>Dibayar</th>
                            <th>Sisa</th>
                            <th>Status</th>
                            <th>Jatuh Tempo</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr class="{{ $payment->isOverdue() ? 'table-danger' : '' }}">
                                <td data-label="Nota" class="payment-info-cell">
                                    <div>
                                        <span class="fw-medium">{{ $payment->nomor_nota }}</span>
                                        <br>
                                        <small class="text-muted">{{ $payment->order->nomor_order }}</small>
                                        <br>
                                        <small class="text-muted">{{ $payment->created_at->format('d M Y, H:i') }}</small>
                                    </div>
                                </td>
                                <td data-label="Pelanggan">
                                    <div>
                                        <span class="fw-medium">{{ $payment->order->customer->nama_toko }}</span>
                                        <br>
                                        <small class="text-muted">{{ $payment->order->customer->phone }}</small>
                                    </div>
                                </td>
                                <td data-label="Tagihan">
                                    <span class="fw-medium">Rp {{ number_format($payment->jumlah_tagihan, 0, ',', '.') }}</span>
                                </td>
                                <td data-label="Dibayar">
                                    <span class="fw-medium text-success">Rp {{ number_format($payment->jumlah_bayar, 0, ',', '.') }}</span>
                                </td>
                                <td data-label="Sisa">
                                    @php
                                        $sisaTagihan = $payment->jumlah_tagihan - $payment->jumlah_bayar;
                                    @endphp
                                    <span class="fw-medium {{ $sisaTagihan > 0 ? 'text-warning' : 'text-success' }}">
                                        Rp {{ number_format($sisaTagihan, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td data-label="Status">
                                    <span class="badge bg-label-{{
                                        $payment->status === 'lunas' ? 'success' :
                                        ($payment->status === 'sebagian' ? 'warning' : 'danger')
                                    }}">
                                        {{ ucfirst(str_replace('_', ' ', $payment->status)) }}
                                    </span>
                                    @if($payment->isOverdue())
                                        <br><small class="text-danger">{{ $payment->getDaysOverdue() }} hari terlambat</small>
                                    @endif
                                </td>
                                <td data-label="Jatuh Tempo">
                                    <div>
                                        <span class="fw-medium">{{ $payment->tanggal_jatuh_tempo->format('d/m/Y') }}</span>
                                        @if($payment->tanggal_pembayaran)
                                            <br><small class="text-success">Dibayar: {{ $payment->tanggal_pembayaran->format('d/m/Y') }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td data-label="Aksi" class="actions-cell">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @if($payment->status !== 'lunas')
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click.prevent="openProofModal({{ $payment->id }})">
                                                        <i class="bx bx-upload me-1"></i> Upload Bukti
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click.prevent="openEditModal({{ $payment->id }})">
                                                        <i class="bx bx-edit me-1"></i> Edit
                                                    </a>
                                                </li>
                                            @endif
                                            @if($payment->bukti_transfer)
                                                <li>
                                                    <a class="dropdown-item" href="{{ asset('storage/payment_proofs/' . $payment->bukti_transfer) }}" target="_blank">
                                                        <i class="bx bx-image me-1"></i> Lihat Bukti
                                                    </a>
                                                </li>
                                            @endif
                                            @if($payment->jumlah_bayar == 0)
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#"
                                                       wire:click.prevent="deletePayment({{ $payment->id }})"
                                                       onclick="return confirm('Yakin ingin menghapus invoice ini?')">
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
                                <td colspan="8" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-1">Belum ada invoice</p>
                                        <small class="text-muted">Buat invoice pertama Anda.</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payments->hasPages())
            <div class="card-footer">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Payment Modal -->
    @if($showPaymentModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editMode ? 'Edit Invoice' : 'Buat Invoice Baru' }}</h5>
                        <button type="button" class="btn-close" wire:click="closePaymentModal"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="row g-3">
                                <!-- Order Selection - Searchable -->
                                <div class="col-md-6">
                                    <label class="form-label">Pilih Order <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" 
                                               wire:model.live.debounce.300ms="orderSearch"
                                               class="form-control @error('order_id') is-invalid @enderror" 
                                               placeholder="Ketik nomor order atau nama toko..."
                                               autocomplete="off"
                                               {{ $editMode ? 'disabled' : '' }}>
                                        
                                        @if($this->orderSuggestions->count() > 0 && $showOrderSuggestions)
                                            <div class="dropdown-suggestions">
                                                @foreach($this->orderSuggestions as $order)
                                                    <div class="suggestion-item" wire:click="selectOrder({{ $order->id }})">
                                                        <div class="fw-medium">{{ $order->nomor_order }}</div>
                                                        <div class="text-muted small">{{ $order->customer->nama_toko }}</div>
                                                        <div class="text-success small">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    @error('order_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Customer - Searchable -->
                                <div class="col-md-6">
                                    <label class="form-label">Pelanggan <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" 
                                               wire:model.live.debounce.300ms="customerSearch"
                                               class="form-control @error('customer_id') is-invalid @enderror" 
                                               placeholder="Ketik nama toko..."
                                               autocomplete="off">
                                        
                                        @if($this->customerSuggestions->count() > 0 && $showCustomerSuggestions)
                                            <div class="dropdown-suggestions">
                                                @foreach($this->customerSuggestions as $customer)
                                                    <div class="suggestion-item" wire:click="selectCustomer({{ $customer->id }})">
                                                        <div class="fw-medium">{{ $customer->nama_toko }}</div>
                                                        <div class="text-muted small">{{ $customer->phone }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Amount -->
                                <div class="col-md-6">
                                    <label class="form-label">Jumlah Tagihan <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" wire:model="jumlah_tagihan" class="form-control @error('jumlah_tagihan') is-invalid @enderror" min="0" step="100">
                                    </div>
                                    @error('jumlah_tagihan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Payment Method -->
                                <div class="col-md-6">
                                    <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                                    <select wire:model="metode_pembayaran" class="form-select @error('metode_pembayaran') is-invalid @enderror">
                                        <option value="tunai">Tunai</option>
                                        <option value="transfer">Transfer Bank</option>
                                        <option value="giro">Giro</option>
                                    </select>
                                    @error('metode_pembayaran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Due Date -->
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="tanggal_jatuh_tempo" class="form-control @error('tanggal_jatuh_tempo') is-invalid @enderror" min="{{ date('Y-m-d') }}">
                                    @error('tanggal_jatuh_tempo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Notes -->
                                <div class="col-12">
                                    <label class="form-label">Catatan</label>
                                    <textarea wire:model="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3" placeholder="Catatan tambahan untuk invoice..."></textarea>
                                    @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closePaymentModal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                {{ $editMode ? 'Update Invoice' : 'Buat Invoice' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Upload Payment Proof Modal -->
    @if($showProofModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Bukti Pembayaran</h5>
                        <button type="button" class="btn-close" wire:click="closeProofModal"></button>
                    </div>
                    <form wire:submit.prevent="uploadPaymentProof">
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                Upload bukti transfer atau pembayaran dari customer untuk memperbarui status invoice.
                            </div>

                            <div class="row g-3">
                                <!-- Payment Amount -->
                                <div class="col-12">
                                    <label class="form-label">Jumlah Dibayar <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" wire:model="proofAmount" class="form-control @error('proofAmount') is-invalid @enderror" min="1" step="100">
                                    </div>
                                    @error('proofAmount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Proof Photo -->
                                <div class="col-12">
                                    <label class="form-label">Foto Bukti Transfer <span class="text-danger">*</span></label>
                                    <input type="file" wire:model="proofPhoto" class="form-control @error('proofPhoto') is-invalid @enderror" accept="image/*">
                                    @error('proofPhoto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($proofPhoto)
                                        <div class="mt-2">
                                            <img src="{{ $proofPhoto->temporaryUrl() }}" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                                        </div>
                                    @endif
                                    <small class="text-muted">Upload screenshot atau foto bukti transfer</small>
                                </div>

                                <!-- Notes -->
                                <div class="col-12">
                                    <label class="form-label">Catatan</label>
                                    <textarea wire:model="proofNotes" class="form-control @error('proofNotes') is-invalid @enderror" rows="2" placeholder="Catatan pembayaran..."></textarea>
                                    @error('proofNotes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeProofModal">Batal</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bx bx-upload me-1"></i> Upload Bukti
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
