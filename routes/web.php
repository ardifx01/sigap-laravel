<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    // Jika user sudah login, redirect ke dashboard sesuai role
    if (auth()->check()) {
        $user = auth()->user();
        return match($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'sales' => redirect()->route('sales.dashboard'),
            'gudang' => redirect()->route('gudang.dashboard'),
            'supir' => redirect()->route('supir.dashboard'),
            default => redirect()->route('login'),
        };
    }

    // Jika belum login, redirect ke halaman login
    return redirect()->route('login');
})->name('home');

// Role-based dashboard redirects
Route::get('dashboard', function () {
    $user = auth()->user();

    return match($user->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'sales' => redirect()->route('sales.dashboard'),
        'gudang' => redirect()->route('gudang.dashboard'),
        'supir' => redirect()->route('supir.dashboard'),
        default => redirect()->route('login'),
    };
})->middleware(['auth', 'verified', 'role'])->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('dashboard', 'admin.dashboard')->name('dashboard');
    Route::view('users', 'admin.users')->name('users');
    Route::view('reports', 'admin.reports')->name('reports');
    Route::view('activity-logs', 'admin.activity-logs')->name('activity-logs');
});

// Sales Routes
Route::middleware(['auth', 'verified', 'role:sales'])->prefix('sales')->name('sales.')->group(function () {
    Route::view('dashboard', 'sales.dashboard')->name('dashboard');
    Route::view('customers', 'sales.customers')->name('customers');
    Route::view('check-in', 'sales.check-in')->name('check-in');
    Route::view('orders', 'sales.orders')->name('orders');
    Route::view('payments', 'sales.payments')->name('payments');
});

// Gudang Routes
Route::middleware(['auth', 'verified', 'role:gudang'])->prefix('gudang')->name('gudang.')->group(function () {
    Route::view('dashboard', 'gudang.dashboard')->name('dashboard');
    Route::view('products', 'gudang.products')->name('products');
    Route::view('orders', 'gudang.orders')->name('orders');
    Route::view('deliveries', 'gudang.deliveries')->name('deliveries');
    Route::view('backorders', 'gudang.backorders')->name('backorders');
});

// Supir Routes
Route::middleware(['auth', 'verified', 'role:supir'])->prefix('supir')->name('supir.')->group(function () {
    Route::view('dashboard', 'supir.dashboard')->name('dashboard');
    Route::view('deliveries', 'supir.deliveries')->name('deliveries');
    Route::view('k3-checklist', 'supir.k3-checklist')->name('k3-checklist');
    Route::view('tracking', 'supir.tracking')->name('tracking');
});

Route::middleware(['auth'])->group(function () {
  Route::redirect('settings', 'settings/profile');

  Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
  Volt::route('settings/password', 'settings.password')->name('settings.password');
});

require __DIR__ . '/auth.php';
