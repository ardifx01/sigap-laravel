<!-- Menu -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="{{ url('/') }}" class="app-brand-link"><x-app-logo /></a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    <!-- Dashboard -->
    <li class="menu-item {{ request()->is('dashboard') || request()->is('*/dashboard') ? 'active' : '' }}">
      <a class="menu-link" href="{{ route('dashboard') }}" wire:navigate>
        <i class="menu-icon tf-icons bx bx-home-circle"></i>
        <div class="text-truncate">Dashboard</div>
      </a>
    </li>

    @if(auth()->user()->isAdmin())
      <!-- Admin Menu -->
      <li class="menu-header small text-uppercase"><span class="menu-header-text">Admin</span></li>

      <!-- Analytics -->
      <li class="menu-item {{ request()->is('admin/analytics*') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('admin.analytics') }}" wire:navigate>
          <i class="menu-icon tf-icons bx bx-line-chart"></i>
          <div class="text-truncate">Analytics</div>
        </a>
      </li>

      <!-- Master Data -->
      <li class="menu-item {{ request()->is('admin/users*') || request()->is('admin/customers*') || request()->is('admin/products*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons bx bx-collection"></i>
          <div class="text-truncate">Master Data</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ request()->is('admin/users*') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('admin.users') }}" wire:navigate>
              <div class="text-truncate">Manajemen User</div>
            </a>
          </li>
          <li class="menu-item {{ request()->is('admin/customers*') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('admin.customers') }}" wire:navigate>
              <div class="text-truncate">Data Pelanggan</div>
            </a>
          </li>
          <li class="menu-item {{ request()->is('admin/products*') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('admin.products') }}" wire:navigate>
              <div class="text-truncate">Manajemen Produk</div>
            </a>
          </li>
        </ul>
      </li>

      <!-- Operations -->
      <li class="menu-item {{ request()->is('admin/orders*') || request()->is('admin/deliveries*') || request()->is('admin/payments*') || request()->is('admin/backorders*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons bx bx-cog"></i>
          <div class="text-truncate">Operasional</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ request()->is('admin/orders*') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('admin.orders') }}" wire:navigate>
              <div class="text-truncate">Manajemen Order</div>
            </a>
          </li>
          <li class="menu-item {{ request()->is('admin/deliveries*') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('admin.deliveries') }}" wire:navigate>
              <div class="text-truncate">Manajemen Pengiriman</div>
            </a>
          </li>
          <li class="menu-item {{ request()->is('admin/payments*') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('admin.payments') }}" wire:navigate>
              <div class="text-truncate">Manajemen Pembayaran</div>
            </a>
          </li>
          <li class="menu-item {{ request()->is('admin/backorders*') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('admin.backorders') }}" wire:navigate>
              <div class="text-truncate">Manajemen Backorder</div>
            </a>
          </li>
        </ul>
      </li>

      <!-- Reports & Monitoring -->
      <li class="menu-item {{ request()->is('admin/reports*') || request()->is('admin/activity-logs*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
          <div class="text-truncate">Laporan & Monitor</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ request()->is('admin/reports*') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('admin.reports') }}" wire:navigate>
              <div class="text-truncate">Laporan</div>
            </a>
          </li>
          <li class="menu-item {{ request()->is('admin/activity-logs*') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('admin.activity-logs') }}" wire:navigate>
              <div class="text-truncate">Activity Log</div>
            </a>
          </li>
        </ul>
      </li>
    @endif

    @if(auth()->user()->isSales())
      <!-- Sales Menu -->
      <li class="menu-header small text-uppercase"><span class="menu-header-text">Sales</span></li>

      <li class="menu-item {{ request()->is('sales/customers*') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('sales.customers') }}" wire:navigate>
          <i class="menu-icon tf-icons bx bx-store"></i>
          <div class="text-truncate">Data Pelanggan</div>
        </a>
      </li>

      <li class="menu-item {{ request()->is('sales/check-in*') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('sales.check-in') }}" wire:navigate>
          <i class="menu-icon tf-icons bx bx-map-pin"></i>
          <div class="text-truncate">Check-in Toko</div>
        </a>
      </li>

      <li class="menu-item {{ request()->is('sales/orders*') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('sales.orders') }}" wire:navigate>
          <i class="menu-icon tf-icons bx bx-package"></i>
          <div class="text-truncate">Pre Order</div>
        </a>
      </li>

      <li class="menu-item {{ request()->is('sales/payments*') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('sales.payments') }}" wire:navigate>
          <i class="menu-icon tf-icons bx bx-credit-card"></i>
          <div class="text-truncate">Penagihan</div>
        </a>
      </li>
    @endif

    @if(auth()->user()->isGudang())
      <!-- Gudang Menu -->
      <li class="menu-header small text-uppercase"><span class="menu-header-text">Gudang</span></li>

      <li class="menu-item {{ request()->is('gudang/products*') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('gudang.products') }}" wire:navigate>
          <i class="menu-icon tf-icons bx bx-box"></i>
          <div class="text-truncate">Data Barang</div>
        </a>
      </li>

      <li class="menu-item {{ request()->is('gudang/orders*') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('gudang.orders') }}" wire:navigate>
          <i class="menu-icon tf-icons bx bx-check-circle"></i>
          <div class="text-truncate">Konfirmasi Order</div>
        </a>
      </li>

      <li class="menu-item {{ request()->is('gudang/deliveries*') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('gudang.deliveries') }}" wire:navigate>
          <i class="menu-icon tf-icons bx bx-car"></i>
          <div class="text-truncate">Pengiriman</div>
        </a>
      </li>

      <li class="menu-item {{ request()->is('gudang/backorders*') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('gudang.backorders') }}" wire:navigate>
          <i class="menu-icon tf-icons bx bx-error-circle"></i>
          <div class="text-truncate">Stok Kosong</div>
        </a>
      </li>
    @endif

    @if(auth()->user()->isSupir())
      <!-- Supir Menu -->
      <li class="menu-header small text-uppercase"><span class="menu-header-text">Supir</span></li>

      <li class="menu-item {{ request()->is('supir/deliveries*') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('supir.deliveries') }}" wire:navigate>
          <i class="menu-icon tf-icons bx bx-package"></i>
          <div class="text-truncate">Tugas Pengiriman</div>
        </a>
      </li>

      <li class="menu-item {{ request()->is('supir/k3-checklist*') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('supir.k3-checklist') }}" wire:navigate>
          <i class="menu-icon tf-icons bx bx-check-square"></i>
          <div class="text-truncate">K3 Checklist</div>
        </a>
      </li>

      <li class="menu-item {{ request()->is('supir/tracking*') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('supir.tracking') }}" wire:navigate>
          <i class="menu-icon tf-icons bx bx-map"></i>
          <div class="text-truncate">GPS Tracking</div>
        </a>
      </li>
    @endif

    <!-- Settings -->
    <li class="menu-header small text-uppercase"><span class="menu-header-text">Pengaturan</span></li>
    <li class="menu-item {{ request()->is('settings/*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-cog"></i>
        <div class="text-truncate">Pengaturan</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->routeIs('settings.profile') ? 'active' : '' }}">
          <a class="menu-link" href="{{ route('settings.profile') }}" wire:navigate>Profil</a>
        </li>
        <li class="menu-item {{ request()->routeIs('settings.password') ? 'active' : '' }}">
          <a class="menu-link" href="{{ route('settings.password') }}" wire:navigate>Password</a>
        </li>
      </ul>
    </li>
  </ul>
</aside>
<!-- / Menu -->

<script>
  // Toggle the 'open' class when the menu-toggle is clicked
  document.querySelectorAll('.menu-toggle').forEach(function(menuToggle) {
    menuToggle.addEventListener('click', function() {
      const menuItem = menuToggle.closest('.menu-item');
      // Toggle the 'open' class on the clicked menu-item
      menuItem.classList.toggle('open');
    });
  });
</script>
