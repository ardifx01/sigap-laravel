<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Report Management</h4>
            <p class="text-muted mb-0">Generate comprehensive business reports in Excel or PDF format</p>
        </div>
    </div>

    <!-- Report Generation Form -->
    <div class="card">
        <div class="card-header">
            <h6 class="card-title mb-0">Generate Report</h6>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="generateReport">
                <div class="row g-3">
                    <!-- Report Type -->
                    <div class="col-md-6">
                        <label class="form-label">Report Type <span class="text-danger">*</span></label>
                        <select wire:model.live="reportType" class="form-select @error('reportType') is-invalid @enderror">
                            <option value="sales">Sales Report</option>
                            <option value="inventory">Inventory Report</option>
                            <option value="payments">Payments Report</option>
                            <option value="customers">Customers Report</option>
                            <option value="deliveries">Deliveries Report</option>
                        </select>
                        @error('reportType') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Format -->
                    <div class="col-md-6">
                        <label class="form-label">Format <span class="text-danger">*</span></label>
                        <select wire:model="format" class="form-select @error('format') is-invalid @enderror">
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF (.pdf)</option>
                        </select>
                        @error('format') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Date Range -->
                    <div class="col-md-6">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" wire:model="startDate" class="form-control @error('startDate') is-invalid @enderror">
                        @error('startDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" wire:model="endDate" class="form-control @error('endDate') is-invalid @enderror">
                        @error('endDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Filters -->
                    @if(in_array($reportType, ['sales', 'payments']))
                        <div class="col-md-6">
                            <label class="form-label">Filter by Sales</label>
                            <select wire:model="salesFilter" class="form-select">
                                <option value="">All Sales</option>
                                @foreach($salesUsers as $sales)
                                    <option value="{{ $sales->id }}">{{ $sales->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if($reportType === 'customers')
                        <div class="col-md-6">
                            <label class="form-label">Filter by Customer</label>
                            <select wire:model="customerFilter" class="form-select">
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->nama_toko }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Generate Button -->
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary" {{ $isGenerating ? 'disabled' : '' }}>
                            @if($isGenerating)
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Generating... ({{ $generationProgress }}%)
                            @else
                                <i class="bx bx-download me-1"></i>
                                Generate Report
                            @endif
                        </button>
                    </div>

                    <!-- Progress Bar -->
                    @if($isGenerating)
                        <div class="col-12">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: {{ $generationProgress }}%" aria-valuenow="{{ $generationProgress }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $generationProgress }}%
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Report Types Information -->
    <div class="row g-4 mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Available Reports</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex align-items-center">
                            <i class="bx bx-trending-up text-success me-3"></i>
                            <div>
                                <h6 class="mb-1">Sales Report</h6>
                                <small class="text-muted">Order details, sales performance, revenue analysis</small>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-center">
                            <i class="bx bx-package text-info me-3"></i>
                            <div>
                                <h6 class="mb-1">Inventory Report</h6>
                                <small class="text-muted">Stock levels, low stock alerts, inventory value</small>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-center">
                            <i class="bx bx-money text-warning me-3"></i>
                            <div>
                                <h6 class="mb-1">Payments Report</h6>
                                <small class="text-muted">Invoice status, payment tracking, outstanding amounts</small>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-center">
                            <i class="bx bx-user text-primary me-3"></i>
                            <div>
                                <h6 class="mb-1">Customers Report</h6>
                                <small class="text-muted">Customer database, contact information, activity</small>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-center">
                            <i class="bx bx-car text-secondary me-3"></i>
                            <div>
                                <h6 class="mb-1">Deliveries Report</h6>
                                <small class="text-muted">Delivery status, GPS tracking, completion times</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Report Features</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-file-blank text-success me-2"></i>
                                <span>Excel format with auto-sizing columns</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-file text-danger me-2"></i>
                                <span>PDF format with professional styling</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-filter text-info me-2"></i>
                                <span>Advanced filtering options</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-calendar text-warning me-2"></i>
                                <span>Flexible date range selection</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-download text-primary me-2"></i>
                                <span>Instant download capability</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-data text-secondary me-2"></i>
                                <span>Comprehensive data analysis</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
