<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Manajemen Pre Order</h4>
            <p class="text-muted mb-0">Kelola pre order pelanggan</p>
        </div>
        <button wire:click="openCreateModal" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i>
            Buat Pre Order
        </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari Order</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Nomor order atau nama toko...">
                </div>
                <div class="col-md-4">
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
                <div class="col-md-4">
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

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>
                                    <div>
                                        <h6 class="mb-0">{{ $order->nomor_order }}</h6>
                                        <small class="text-muted">{{ $order->orderItems->count() }} item</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-medium">{{ $order->customer->nama_toko }}</div>
                                        <small class="text-muted">{{ $order->customer->phone }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-medium">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                                </td>
                                <td>
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
                                <td>
                                    <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#" onclick="showOrderDetail({{ $order->id }})">
                                                <i class="bx bx-show me-1"></i> Detail
                                            </a>
                                            @if($order->canBeEdited())
                                                <a class="dropdown-item" href="#" wire:click.prevent="openEditModal({{ $order->id }})">
                                                    <i class="bx bx-edit me-1"></i> Edit
                                                </a>
                                            @endif
                                            @if($order->canBeCancelled())
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-danger" href="#"
                                                   wire:click.prevent="cancelOrder({{ $order->id }})"
                                                   onclick="return confirm('Yakin ingin membatalkan order ini?')">
                                                    <i class="bx bx-x-circle me-1"></i> Batalkan
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bx bx-package text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">Belum ada order</p>
                                    <button wire:click="openCreateModal" class="btn btn-primary btn-sm">
                                        <i class="bx bx-plus me-1"></i> Buat Order Pertama
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $orders->links() }}
            </div>
        </div>
    </div>

    <!-- Order Modal -->
    @if($showModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editMode ? 'Edit Pre Order' : 'Buat Pre Order Baru' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="row g-3">
                                <!-- Customer Selection -->
                                <div class="col-md-6">
                                    <label class="form-label">Pilih Pelanggan <span class="text-danger">*</span></label>
                                    <select wire:model="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                        <option value="">-- Pilih Pelanggan --</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->nama_toko }} - {{ $customer->phone }}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Notes -->
                                <div class="col-md-6">
                                    <label class="form-label">Catatan</label>
                                    <textarea wire:model="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="2" placeholder="Catatan tambahan..."></textarea>
                                    @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Add Product Section -->
                                <div class="col-12">
                                    <hr>
                                    <h6>Tambah Produk</h6>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-6">
                                            <label class="form-label">Pilih Produk</label>
                                            <select wire:model="selectedProduct" class="form-select">
                                                <option value="">-- Pilih Produk --</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}">
                                                        {{ $product->nama_barang }} - Rp {{ number_format($product->harga_jual, 0, ',', '.') }}
                                                        (Stok: {{ $product->stok_tersedia }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Jumlah</label>
                                            <input type="number" wire:model="quantity" class="form-control" min="1" value="1">
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" wire:click="addProduct" class="btn btn-primary w-100">
                                                <i class="bx bx-plus me-1"></i> Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Items Table -->
                                <div class="col-12">
                                    <hr>
                                    <h6>Daftar Produk Order</h6>
                                    @if(count($orderItems) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Produk</th>
                                                        <th>Harga</th>
                                                        <th>Jumlah</th>
                                                        <th>Total</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($orderItems as $index => $item)
                                                        <tr>
                                                            <td>{{ $item['product_name'] }}</td>
                                                            <td>Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                                            <td>
                                                                <input type="number"
                                                                       wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                                                       value="{{ $item['quantity'] }}"
                                                                       class="form-control form-control-sm"
                                                                       style="width: 80px;"
                                                                       min="1">
                                                            </td>
                                                            <td>Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                                                            <td>
                                                                <button type="button"
                                                                        wire:click="removeProduct({{ $index }})"
                                                                        class="btn btn-sm btn-outline-danger">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr class="table-active">
                                                        <th colspan="3">Total Order</th>
                                                        <th>Rp {{ number_format(collect($orderItems)->sum('total'), 0, ',', '.') }}</th>
                                                        <th></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-3">
                                            <i class="bx bx-package text-muted" style="font-size: 2rem;"></i>
                                            <p class="text-muted mt-2">Belum ada produk ditambahkan</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn btn-primary" {{ count($orderItems) === 0 ? 'disabled' : '' }}>
                                {{ $editMode ? 'Update Order' : 'Buat Order' }}
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
