# Aplikasi Sales Gudang

Sistem digital komprehensif untuk mengelola proses operasional bisnis mulai dari pemesanan oleh Sales, manajemen stok Gudang, pengiriman Supir, hingga pengawasan menyeluruh oleh Admin.

## Tech Stack

- **Backend**: Laravel 12
- **Frontend**: Livewire & Volt
- **Database**: MySQL
- **Styling**: Bootstrap 5

## Fitur Utama

### 🔐 Role-Based Access Control
- **Admin**: Dashboard monitoring, user management, reporting
- **Sales**: Pre-order, check-in toko, penagihan
- **Gudang**: Manajemen stok, konfirmasi order, kontrol pengiriman
- **Supir**: Tracking pengiriman, GPS coordination

### 📊 Dashboard & Monitoring
- Real-time sales tracking
- Live GPS tracking supir
- Stok alert & inventory management
- Comprehensive activity logs

### 🚚 Order Management
- End-to-end order processing
- Automated backorder handling
- Status tracking real-time
- GPS-based delivery confirmation

## Instalasi

### Prerequisites
- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js & NPM

### Setup

```bash
# Clone repository
git clone https://github.com/prassaaa/sigap-laravel.git
cd sigap-laravel

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Build assets
npm run build

# Start server
php artisan serve
```

### Konfigurasi Database

```sql
CREATE DATABASE sigap_laravel;
```

Update `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sigap_laravel
DB_USERNAME=root
DB_PASSWORD=
```

## Workflow System

### 1. Order Creation Flow
```
Sales Check-in → Input Pre Order → Gudang Confirmation → Ready for Delivery
```

### 2. Delivery Flow
```
Gudang Assign Driver → K3 Checklist → Process Delivery → GPS Confirmation → Delivered
```

### 3. Payment Flow
```
Delivery Confirmed → Sales Create Invoice → Payment Processing → Status Update
```

## Deployment

### Production Setup

```bash
# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build production assets
npm run production

# Set permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```
