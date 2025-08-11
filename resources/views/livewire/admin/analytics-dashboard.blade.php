<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Advanced Analytics Dashboard</h4>
            <p class="text-muted mb-0">Real-time business intelligence dan insights</p>
        </div>
        <div class="d-flex gap-2">
            <select wire:model.live="dateRange" class="form-select" style="width: auto;">
                <option value="7">7 Hari</option>
                <option value="30">30 Hari</option>
                <option value="90">90 Hari</option>
                <option value="365">1 Tahun</option>
            </select>
            <button wire:click="refreshData" class="btn btn-outline-primary">
                <i class="bx bx-refresh"></i>
            </button>
            <button wire:click="toggleAutoRefresh" class="btn btn-outline-{{ $autoRefresh ? 'success' : 'secondary' }}">
                <i class="bx bx-{{ $autoRefresh ? 'pause' : 'play' }}"></i>
                {{ $autoRefresh ? 'Auto' : 'Manual' }}
            </button>
        </div>
    </div>

    <!-- Last Updated -->
    <div class="text-end mb-3">
        <small class="text-muted">Last updated: {{ $lastUpdated }}</small>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="bx bx-trending-up"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Sales</small>
                            <h6 class="mb-0">Rp {{ number_format($salesData->sum('total'), 0, ',', '.') }}</h6>
                            <small class="text-success">{{ $salesData->sum('count') }} orders</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="bx bx-package"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Active Deliveries</small>
                            <h6 class="mb-0">{{ $deliveryMetrics['in_progress'] }}</h6>
                            <small class="text-info">{{ $deliveryMetrics['completed_deliveries'] }} completed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-warning">
                                <i class="bx bx-money"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Outstanding</small>
                            <h6 class="mb-0">Rp {{ number_format($paymentMetrics['total_outstanding'], 0, ',', '.') }}</h6>
                            <small class="text-warning">{{ $paymentMetrics['overdue_invoices'] }} overdue</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-danger">
                                <i class="bx bx-error-circle"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Low Stock Items</small>
                            <h6 class="mb-0">{{ $inventoryAlerts['low_stock'] }}</h6>
                            <small class="text-danger">{{ $inventoryAlerts['out_of_stock'] }} out of stock</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row g-4 mb-4">
        <!-- Sales Trend Chart -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Sales Trend</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" wire:click.prevent="$set('selectedMetric', 'sales')">Sales Amount</a>
                            <a class="dropdown-item" href="#" wire:click.prevent="$set('selectedMetric', 'orders')">Order Count</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="salesTrendChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Order Status Pie Chart -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Order Status Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="orderStatusChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row g-4 mb-4">
        <!-- Top Products -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Top Selling Products</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topProducts as $product)
                                    <tr>
                                        <td>{{ Str::limit($product->nama_barang, 20) }}</td>
                                        <td>{{ $product->total_sold }}</td>
                                        <td>Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Customers -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Top Customers</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Orders</th>
                                    <th>Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCustomers as $customer)
                                    <tr>
                                        <td>{{ Str::limit($customer->nama_toko, 20) }}</td>
                                        <td>{{ $customer->total_orders }}</td>
                                        <td>Rp {{ number_format($customer->total_spent, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics Row -->
    <div class="row g-4 mb-4">
        <!-- Sales Performance -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Sales Team Performance</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Sales</th>
                                    <th>Orders</th>
                                    <th>Total Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesPerformance as $sales)
                                    <tr>
                                        <td>{{ $sales->name }}</td>
                                        <td>{{ $sales->total_orders }}</td>
                                        <td>Rp {{ number_format($sales->total_sales, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Metrics -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Activity Overview</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        <i class="bx bx-map-pin"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Check-ins Today</small>
                                    <h6 class="mb-0">{{ $activityMetrics['today_checkins'] }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-success">
                                        <i class="bx bx-check-square"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">K3 Completed</small>
                                    <h6 class="mb-0">{{ $activityMetrics['k3_completed'] }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-warning">
                                        <i class="bx bx-shield"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">K3 Today</small>
                                    <h6 class="mb-0">{{ $activityMetrics['k3_today'] }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-info">
                                        <i class="bx bx-user"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Active Users</small>
                                    <h6 class="mb-0">{{ $activityMetrics['active_users'] }}</h6>
                                </div>
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

    <!-- Chart.js Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let salesChart, statusChart;
        let autoRefreshInterval;

        document.addEventListener('livewire:init', () => {
            initializeCharts();

            // Listen for chart updates
            Livewire.on('updateCharts', () => {
                updateCharts();
            });

            // Auto refresh functionality
            Livewire.on('startAutoRefresh', () => {
                startAutoRefresh();
            });

            Livewire.on('stopAutoRefresh', () => {
                stopAutoRefresh();
            });

            // Start auto refresh if enabled
            @if($autoRefresh)
                startAutoRefresh();
            @endif
        });

        function initializeCharts() {
            // Sales Trend Chart
            const salesCtx = document.getElementById('salesTrendChart').getContext('2d');
            salesChart = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: @json($salesData->pluck('date')),
                    datasets: [{
                        label: 'Sales Amount',
                        data: @json($salesData->pluck('total')),
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });

            // Order Status Chart
            const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
            statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($orderStatusData->pluck('status')),
                    datasets: [{
                        data: @json($orderStatusData->pluck('count')),
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF',
                            '#FF9F40'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function updateCharts() {
            // This will be called when data updates
            // Charts will be updated with new data from Livewire
            setTimeout(() => {
                if (salesChart) {
                    salesChart.destroy();
                }
                if (statusChart) {
                    statusChart.destroy();
                }
                initializeCharts();
            }, 100);
        }

        function startAutoRefresh() {
            autoRefreshInterval = setInterval(() => {
                @this.call('refreshData');
            }, 30000); // Refresh every 30 seconds
        }

        function stopAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            stopAutoRefresh();
        });
    </script>
</div>
