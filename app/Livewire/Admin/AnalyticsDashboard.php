<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\Payment;
use App\Models\Delivery;
use App\Models\CheckIn;
use App\Models\K3Checklist;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsDashboard extends Component
{
    public $dateRange = '30'; // Default 30 days
    public $selectedMetric = 'sales';

    // Real-time refresh
    public $autoRefresh = true;
    public $lastUpdated;

    public function mount()
    {
        $this->lastUpdated = now()->format('H:i:s');
    }

    public function updatedDateRange()
    {
        $this->dispatch('updateCharts');
    }

    public function updatedSelectedMetric()
    {
        $this->dispatch('updateCharts');
    }

    public function refreshData()
    {
        $this->lastUpdated = now()->format('H:i:s');
        $this->dispatch('updateCharts');
        session()->flash('success', 'Data berhasil direfresh!');
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
        if ($this->autoRefresh) {
            $this->dispatch('startAutoRefresh');
        } else {
            $this->dispatch('stopAutoRefresh');
        }
    }

    public function getSalesData()
    {
        $days = (int) $this->dateRange;
        $startDate = now()->subDays($days);

        return Order::where('created_at', '>=', $startDate)
                   ->where('status', 'delivered')
                   ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as count')
                   ->groupBy('date')
                   ->orderBy('date')
                   ->get()
                   ->map(function ($item) {
                       return [
                           'date' => Carbon::parse($item->date)->format('d/m'),
                           'total' => (float) $item->total,
                           'count' => $item->count
                       ];
                   });
    }

    public function getOrderStatusData()
    {
        return Order::selectRaw('status, COUNT(*) as count')
                   ->groupBy('status')
                   ->get()
                   ->map(function ($item) {
                       return [
                           'status' => ucfirst(str_replace('_', ' ', $item->status)),
                           'count' => $item->count
                       ];
                   });
    }

    public function getTopProducts()
    {
        $days = (int) $this->dateRange;
        $startDate = now()->subDays($days);

        return DB::table('order_items')
                 ->join('orders', 'order_items.order_id', '=', 'orders.id')
                 ->join('products', 'order_items.product_id', '=', 'products.id')
                 ->where('orders.created_at', '>=', $startDate)
                 ->where('orders.status', 'delivered')
                 ->selectRaw('products.nama_barang, SUM(order_items.jumlah_pesan) as total_sold, SUM(order_items.total_harga) as total_revenue')
                 ->groupBy('products.id', 'products.nama_barang')
                 ->orderBy('total_sold', 'desc')
                 ->limit(10)
                 ->get();
    }

    public function getTopCustomers()
    {
        $days = (int) $this->dateRange;
        $startDate = now()->subDays($days);

        return DB::table('orders')
                 ->join('customers', 'orders.customer_id', '=', 'customers.id')
                 ->where('orders.created_at', '>=', $startDate)
                 ->where('orders.status', 'delivered')
                 ->selectRaw('customers.nama_toko, COUNT(orders.id) as total_orders, SUM(orders.total_amount) as total_spent')
                 ->groupBy('customers.id', 'customers.nama_toko')
                 ->orderBy('total_spent', 'desc')
                 ->limit(10)
                 ->get();
    }

    public function getSalesPerformance()
    {
        $days = (int) $this->dateRange;
        $startDate = now()->subDays($days);

        return DB::table('orders')
                 ->join('users', 'orders.sales_id', '=', 'users.id')
                 ->where('orders.created_at', '>=', $startDate)
                 ->where('orders.status', 'delivered')
                 ->selectRaw('users.name, COUNT(orders.id) as total_orders, SUM(orders.total_amount) as total_sales')
                 ->groupBy('users.id', 'users.name')
                 ->orderBy('total_sales', 'desc')
                 ->get();
    }

    public function getDeliveryMetrics()
    {
        $days = (int) $this->dateRange;
        $startDate = now()->subDays($days);

        return [
            'total_deliveries' => Delivery::where('created_at', '>=', $startDate)->count(),
            'completed_deliveries' => Delivery::where('created_at', '>=', $startDate)->where('status', 'delivered')->count(),
            'in_progress' => Delivery::where('status', 'in_progress')->count(),
            'average_delivery_time' => Delivery::where('created_at', '>=', $startDate)
                                             ->where('status', 'delivered')
                                             ->whereNotNull('started_at')
                                             ->whereNotNull('delivered_at')
                                             ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, delivered_at)) as avg_time')
                                             ->value('avg_time') ?? 0
        ];
    }

    public function getInventoryAlerts()
    {
        return [
            'low_stock' => Product::whereColumn('stok_tersedia', '<=', 'stok_minimum')->count(),
            'out_of_stock' => Product::where('stok_tersedia', 0)->count(),
            'total_products' => Product::where('is_active', true)->count(),
            'total_stock_value' => Product::selectRaw('SUM(stok_tersedia * harga_jual) as total')->value('total') ?? 0
        ];
    }

    public function getPaymentMetrics()
    {
        $days = (int) $this->dateRange;
        $startDate = now()->subDays($days);

        return [
            'total_invoices' => Payment::where('created_at', '>=', $startDate)->count(),
            'paid_invoices' => Payment::where('created_at', '>=', $startDate)->where('status', 'lunas')->count(),
            'overdue_invoices' => Payment::where('status', 'belum_lunas')->where('tanggal_jatuh_tempo', '<', now())->count(),
            'total_outstanding' => Payment::where('status', '!=', 'lunas')->sum('sisa_tagihan'),
            'total_collected' => Payment::where('created_at', '>=', $startDate)->sum('jumlah_dibayar')
        ];
    }

    public function getActivityMetrics()
    {
        $days = (int) $this->dateRange;
        $startDate = now()->subDays($days);

        return [
            'total_checkins' => CheckIn::where('checked_in_at', '>=', $startDate)->count(),
            'today_checkins' => CheckIn::whereDate('checked_in_at', today())->count(),
            'k3_completed' => K3Checklist::where('created_at', '>=', $startDate)->where('status', 'approved')->count(),
            'k3_pending' => K3Checklist::where('status', 'pending')->count(),
            'active_users' => User::where('last_login_at', '>=', now()->subDays(7))->count()
        ];
    }

    public function render()
    {
        $salesData = $this->getSalesData();
        $orderStatusData = $this->getOrderStatusData();
        $topProducts = $this->getTopProducts();
        $topCustomers = $this->getTopCustomers();
        $salesPerformance = $this->getSalesPerformance();
        $deliveryMetrics = $this->getDeliveryMetrics();
        $inventoryAlerts = $this->getInventoryAlerts();
        $paymentMetrics = $this->getPaymentMetrics();
        $activityMetrics = $this->getActivityMetrics();

        return view('livewire.admin.analytics-dashboard', [
            'salesData' => $salesData,
            'orderStatusData' => $orderStatusData,
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'salesPerformance' => $salesPerformance,
            'deliveryMetrics' => $deliveryMetrics,
            'inventoryAlerts' => $inventoryAlerts,
            'paymentMetrics' => $paymentMetrics,
            'activityMetrics' => $activityMetrics,
        ]);
    }
}
