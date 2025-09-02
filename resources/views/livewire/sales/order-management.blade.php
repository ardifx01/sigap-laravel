<div>
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1">Manajemen Pre-Order</h4>
            <p class="text-muted mb-0">Buat, lihat, dan kelola semua pre-order Anda.</p>
        </div>
        <button wire:click="openCreateModal" class="btn btn-primary align-self-md-auto align-self-stretch">
            <i class="bx bx-plus me-1"></i>
            Buat Pre-Order
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
                <div class="col-12 col-md-5">
                    <label class="form-label">Cari Order</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Nomor order atau nama toko...">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Filter Pelanggan</label>
                     <div wire:ignore>
                        <select id="customer-filter-select" class="form-select">
                            <option value="">Semua Pelanggan</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->nama_toko }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Filter Status</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Dikonfirmasi</option>
                        <option value="ready">Siap Kirim</option>
                        <option value="assigned">Ditugaskan</option>
                        <option value="shipped">Dikirim</option>
                        <option value="delivered">Terkirim</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Pre-Order</h5>
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
                    .mobile-cards .order-info-cell {
                        display: block; /* Override the flex for the main user info */
                        padding-bottom: 1rem;
                        margin-bottom: 1rem;
                        border-bottom: 1px solid #eee;
                    }
                    .mobile-cards .order-info-cell:before {
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
                            <th>Order</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td data-label="Order" class="order-info-cell">
                                    <div>
                                        <span class="fw-medium">{{ $order->nomor_order }}</span>
                                        <br>
                                        <small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</small>
                                    </div>
                                </td>
                                <td data-label="Pelanggan">
                                    <div>
                                        <span class="fw-medium">{{ $order->customer->nama_toko }}</span>
                                        <br>
                                        <small class="text-muted">{{ $order->orderItems->count() }} item</small>
                                    </div>
                                </td>
                                <td data-label="Total">
                                    <span class="fw-medium">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                </td>
                                <td data-label="Status">
                                    <span class="badge bg-label-{{
                                        $order->status === 'pending' ? 'warning' :
                                        ($order->status === 'confirmed' ? 'info' :
                                        ($order->status === 'ready' ? 'primary' :
                                        ($order->status === 'delivered' ? 'success' :
                                        ($order->status === 'cancelled' ? 'danger' : 'secondary'))))
                                    }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td data-label="Aksi" class="actions-cell">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" wire:click="viewOrder({{ $order->id }})">
                                                    <i class="bx bx-show me-1"></i> Detail
                                                </a>
                                            </li>
                                            @if($order->canBeEdited())
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click="openEditModal({{ $order->id }})">
                                                        <i class="bx bx-edit me-1"></i> Edit
                                                    </a>
                                                </li>
                                            @endif
                                            @if($order->canBeCancelled())
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#"
                                                       wire:click="cancelOrder({{ $order->id }})"
                                                       onclick="return confirm('Anda yakin ingin membatalkan order ini?')">
                                                        <i class="bx bx-x-circle me-1"></i> Batalkan
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-package text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-1">Belum ada data pre-order</p>
                                        <small class="text-muted">Buat pre-order pertama Anda.</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
            <div class="card-footer">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Order Modal -->
    @if($showModal)
        <div class="modal fade show" style="display: block;" tabindex="-1" wire:ignore.self>
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editMode ? 'Edit Pre-Order' : 'Buat Pre-Order Baru' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <h6>Informasi Order</h6>
                                    <div class="row g-3">
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
                                                                <div class="text-muted small">{{ $customer->alamat }}</div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            @error('customer_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Catatan</label>
                                            <textarea wire:model="catatan" class="form-control" rows="1" placeholder="Catatan untuk gudang..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <hr class="my-2">
                                    <h6>Tambah Produk</h6>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-7">
                                            <label class="form-label">Produk</label>
                                            <div class="position-relative">
                                                <input type="text" 
                                                       wire:model.live.debounce.300ms="productSearch"
                                                       class="form-control" 
                                                       placeholder="Ketik nama produk..."
                                                       autocomplete="off">
                                                
                                                @if($this->productSuggestions->count() > 0 && $showProductSuggestions)
                                                    <div class="dropdown-suggestions">
                                                        @foreach($this->productSuggestions as $product)
                                                            <div class="suggestion-item" wire:click="selectProduct({{ $product->id }})">
                                                                <div class="fw-medium">{{ $product->nama_barang }}</div>
                                                                <div class="text-muted small">Stok: {{ $product->stok_tersedia }}</div>
                                                                <div class="text-success small">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Jumlah</label>
                                            <input type="number" wire:model="quantity" class="form-control" min="1">
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" wire:click="addProduct" class="btn btn-primary w-100">
                                                <i class="bx bx-plus"></i> Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <hr class="my-2">
                                    <h6>Daftar Produk</h6>
                                    <div class="table-responsive border rounded">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Produk</th>
                                                    <th style="width: 120px;">Jumlah</th>
                                                    <th class="text-end">Subtotal</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($orderItems as $index => $item)
                                                    <tr>
                                                        <td>
                                                            <span class="fw-medium">{{ $item['product_name'] }}</span><br>
                                                            <small class="text-muted">@ Rp {{ number_format($item['price'], 0, ',', '.') }}</small>
                                                        </td>
                                                        <td>
                                                            <input type="number" wire:change="updateQuantity({{ $index }}, $event.target.value)" value="{{ $item['quantity'] }}" class="form-control form-control-sm" min="1">
                                                        </td>
                                                        <td class="text-end">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                                                        <td class="text-center">
                                                            <button type="button" wire:click="removeProduct({{ $index }})" class="btn btn-sm btn-icon btn-outline-danger">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center py-4">
                                                            <p class="mb-0 text-muted">Belum ada produk ditambahkan.</p>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                            @if(count($orderItems) > 0)
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <th colspan="2" class="text-end">Total:</th>
                                                        <th class="text-end">Rp {{ number_format(collect($orderItems)->sum('total'), 0, ',', '.') }}</th>
                                                        <th></th>
                                                    </tr>
                                                </tfoot>
                                            @endif
                                        </table>
                                    </div>
                                    @error('orderItems') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" {{ count($orderItems) === 0 ? 'disabled' : '' }}>
                                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                {{ $editMode ? 'Update Order' : 'Simpan Order' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" wire:ignore.self></div>
    @endif

    <!-- View Order Modal -->
    @if($selectedOrder)
        <div class="modal fade show" style="display: block;" tabindex="-1" wire:ignore.self>
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Order: {{ $selectedOrder->nomor_order }}</h5>
                        <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6 class="mb-2">Informasi Pelanggan</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-1"><span class="fw-medium">Toko:</span> {{ $selectedOrder->customer->nama_toko }}</li>
                                    <li class="mb-1"><span class="fw-medium">Telepon:</span> {{ $selectedOrder->customer->phone }}</li>
                                    <li><span class="fw-medium">Alamat:</span> {{ $selectedOrder->customer->alamat }}</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-2">Informasi Order</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-1"><span class="fw-medium">Tanggal:</span> {{ $selectedOrder->created_at->format('d M Y, H:i') }}</li>
                                    <li class="mb-1"><span class="fw-medium">Status:</span>
                                        <span class="badge bg-label-primary">{{ ucfirst($selectedOrder->status) }}</span>
                                    </li>
                                    <li><span class="fw-medium">Total:</span> Rp {{ number_format($selectedOrder->total_amount, 0, ',', '.') }}</li>
                                </ul>
                            </div>
                            @if($selectedOrder->catatan)
                            <div class="col-12">
                                <h6 class="mb-2">Catatan</h6>
                                <p class="text-muted">{{ $selectedOrder->catatan }}</p>
                            </div>
                            @endif
                            <div class="col-12">
                                <h6 class="mb-2">Daftar Item</h6>
                                <div class="table-responsive border rounded">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                                <th>Jumlah</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($selectedOrder->orderItems as $item)
                                                <tr>
                                                    <td>
                                                        <span class="fw-medium">{{ $item->product->nama_barang }}</span><br>
                                                        <small class="text-muted">@ Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</small>
                                                    </td>
                                                    <td>{{ $item->jumlah_pesan }}</td>
                                                    <td class="text-end">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="2" class="text-end">Total Akhir:</th>
                                                <th class="text-end">Rp {{ number_format($selectedOrder->total_amount, 0, ',', '.') }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeViewModal">Tutup</button>
                        <button type="button" class="btn btn-primary"><i class="bx bx-printer me-1"></i> Cetak</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" wire:ignore.self></div>
    @endif

    <!-- Scripts -->
    <script>
        // Global variables to store TomSelect instances
        let customerFilterSelectInstance = null;
        let modalCustomerSelectInstance = null;
        let modalProductSelectInstance = null;

        // Use global TomSelect helper function
        const initTomSelect = (elementId, model) => {
            return window.initTomSelectWithNavigation(elementId, model, '{{ $this->getId() }}');
        };

        // Function to initialize page TomSelects
        const initializePageTomSelects = () => {
            // Destroy existing instances
            if (customerFilterSelectInstance) {
                try {
                    customerFilterSelectInstance.destroy();
                } catch (e) {
                    console.log('Error destroying TomSelect instance:', e);
                }
                customerFilterSelectInstance = null;
            }

            // Initialize customer filter select
            setTimeout(() => {
                try {
                    customerFilterSelectInstance = initTomSelect('customer-filter-select', 'customerFilter');
                } catch (e) {
                    console.log('Error initializing TomSelect:', e);
                }
            }, 100);
        };

        // Initialize on page load and navigation
        document.addEventListener('livewire:init', initializePageTomSelects);
        document.addEventListener('livewire:navigated', initializePageTomSelects);

        // Handle modal TomSelects
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-modal', () => {
                setTimeout(() => {
                    // Destroy existing modal instances
                    if (modalCustomerSelectInstance) {
                        try {
                            modalCustomerSelectInstance.destroy();
                        } catch (e) {
                            console.log('Error destroying modal customer select:', e);
                        }
                        modalCustomerSelectInstance = null;
                    }
                    if (modalProductSelectInstance) {
                        try {
                            modalProductSelectInstance.destroy();
                        } catch (e) {
                            console.log('Error destroying modal product select:', e);
                        }
                        modalProductSelectInstance = null;
                    }

                    // Initialize modal selects
                    try {
                        modalCustomerSelectInstance = initTomSelect('customer-select', 'customer_id');
                        modalProductSelectInstance = initTomSelect('product-select', 'selectedProduct');

                        // Set initial value if exists
                        if (modalCustomerSelectInstance && @this.get('customer_id')) {
                            modalCustomerSelectInstance.setValue(@this.get('customer_id'), true);
                        }
                    } catch (e) {
                        console.log('Error initializing modal TomSelects:', e);
                    }
                }, 100);
            });

            Livewire.on('close-order-modal', () => {
                if (modalCustomerSelectInstance) {
                    try {
                        modalCustomerSelectInstance.destroy();
                    } catch (e) {
                        console.log('Error destroying modal customer select on close:', e);
                    }
                    modalCustomerSelectInstance = null;
                }
                if (modalProductSelectInstance) {
                    try {
                        modalProductSelectInstance.destroy();
                    } catch (e) {
                        console.log('Error destroying modal product select on close:', e);
                    }
                    modalProductSelectInstance = null;
                }
            });
        });
    </script>
</div>
