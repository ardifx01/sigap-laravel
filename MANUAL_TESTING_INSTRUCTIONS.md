# 🧪 MANUAL TESTING INSTRUCTIONS - SIGAP LARAVEL
## Testing K3 Checklist & Delivery Flow Integration

---

## 🎯 **OVERVIEW TESTING FLOW**
```
Admin → Sales → Gudang → Supir → Validation Flow
```

**Tujuan:** Test full integration K3 Checklist dengan Delivery Flow sesuai perbaikan flowchart.

---

## 🔐 **AKUN TESTING**

| Role    | Email                     | Password | Fungsi                    |
|---------|---------------------------|----------|---------------------------|
| Admin   | budi.admin@sigap.com      | password | System management         |
| Sales   | ahmad.sales@sigap.com     | password | Order & customer          |
| Gudang  | hendra.gudang@sigap.com   | password | Assign delivery           |
| Supir   | krisna.supir@sigap.com    | password | K3 checklist & delivery   |

---

## 📋 **PRE-TESTING SETUP**

### **1. Pastikan Database Ready**
```bash
cd C:\laragon\www\sigap-laravel
php artisan migrate:fresh --seed
```

### **2. Start Laravel Server**
```bash
php artisan serve
# Akses: http://localhost:8000
```

### **3. Clear Cache (Optional)**
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 👤 **ROLE 1: ADMIN TESTING**

### **Login Admin**
- **URL:** `http://localhost:8000/login`
- **Email:** `budi.admin@sigap.com`
- **Password:** `password`

### **A. Dashboard Verification**
1. **Navigate:** Admin Dashboard
   - ✅ Verifikasi stats cards muncul
   - ✅ Check recent activities
   - ✅ Verifikasi grafik/chart (jika ada)

### **B. User Management**
1. **Navigate:** Admin → Users
   - ✅ Verifikasi semua role user terlihat
   - ✅ Test create user baru (role sales)
   - ✅ Test edit user existing
   - ✅ Test activate/deactivate user

2. **Test Create Sales User:**
   ```
   Nama: Test Sales Baru
   Email: testsales@sigap.com  
   Phone: 081234567999
   Role: Sales
   Password: password123
   ```

### **C. Product Management**
1. **Navigate:** Admin → Products
   - ✅ Verifikasi list produk
   - ✅ Test create produk baru
   - ✅ Test edit produk
   - ✅ Test stock management

2. **Test Create Product:**
   ```
   Nama: Produk Test K3
   SKU: TST-K3-001
   Kategori: Test Category
   Harga: 50000
   Stock: 100
   Unit: PCS
   ```

### **D. Order & Delivery Monitoring**
1. **Navigate:** Admin → Orders
   - ✅ Verifikasi all orders dari semua sales
   - ✅ Check order status progression

2. **Navigate:** Admin → Deliveries
   - ✅ Monitor delivery status
   - ✅ Verifikasi K3 status integration
   - ✅ Check delivery tracking

### **E. Reports & Analytics**
1. **Navigate:** Admin → Reports
   - ✅ Generate sales report
   - ✅ Generate delivery report
   - ✅ Check K3 compliance report

**🔓 Logout Admin**

---

## 💼 **ROLE 2: SALES TESTING**

### **Login Sales**
- **URL:** `http://localhost:8000/login`
- **Email:** `ahmad.sales@sigap.com`
- **Password:** `password`

### **A. Dashboard Verification**
1. **Navigate:** Sales Dashboard
   - ✅ Verifikasi personal stats
   - ✅ Check target vs achievement
   - ✅ Recent orders

### **B. Customer Management**
1. **Navigate:** Sales → Customers
   - ✅ View existing customers
   - ✅ Test create customer baru

2. **Test Create Customer:**
   ```
   Nama Toko: Toko Test K3
   Nama Pemilik: Budi Tester
   Phone: 081234999888
   Email: tokotestk3@email.com
   Alamat: Jl. Test K3 No. 123, Jakarta
   Koordinat: -6.200000, 106.816666 (Jakarta)
   ```

### **C. Check-In System**
1. **Navigate:** Sales → Check-In
   - ✅ Test check-in ke customer
   - ✅ Upload foto lokasi
   - ✅ Add visit notes

### **D. Order Management**
1. **Navigate:** Sales → Orders
   - ✅ Test create order untuk customer test

2. **Test Create Order:**
   ```
   Customer: Toko Test K3 (yang baru dibuat)
   Products: 
   - Produk Test K3 (Qty: 5)
   - Pilih produk lain (Qty: 3)
   
   Payment Method: Transfer
   Notes: Order untuk testing K3 flow
   ```

3. **Verifikasi Order Status:**
   - ✅ Order status = 'pending'
   - ✅ Order muncul di list

### **E. Payment Processing**
1. **Navigate:** Sales → Payments
   - ✅ Process payment untuk order test
   - ✅ Upload bukti payment
   - ✅ Confirm payment

**Order Status harus berubah:** `pending → confirmed`

**🔓 Logout Sales**

---

## 📦 **ROLE 3: GUDANG TESTING**

### **Login Gudang**
- **URL:** `http://localhost:8000/login`
- **Email:** `hendra.gudang@sigap.com`
- **Password:** `password`

### **A. Dashboard Verification**
1. **Navigate:** Gudang Dashboard
   - ✅ Stats inventory
   - ✅ Pending deliveries count
   - ✅ Low stock alerts

### **B. Product & Stock Management**
1. **Navigate:** Gudang → Products
   - ✅ Monitor stock levels
   - ✅ Test stock adjustment
   - ✅ Check low stock items

### **C. Order Processing**
1. **Navigate:** Gudang → Orders
   - ✅ Verifikasi order dari sales (status: confirmed)
   - ✅ Check order details & items
   - ✅ Verify customer info

### **D. Delivery Assignment (KUNCI TESTING K3!)**
1. **Navigate:** Gudang → Deliveries
   
2. **Test Assign Delivery:**
   - ✅ Cari order dengan status 'confirmed'
   - ✅ Klik "Assign Supir" pada order test
   
   **Form Assignment:**
   ```
   Supir: Krisna Wijaya (krisna.supir@sigap.com)
   Rute: Jakarta Selatan  
   ETA: [Hari ini + 3 jam]
   ```
   
   - ✅ Submit assignment
   - ✅ **VERIFIKASI:** Order status → 'assigned'
   - ✅ **VERIFIKASI:** Delivery status → 'assigned' (BUKAN k3_checked!)

3. **Monitor Delivery Status:**
   - ✅ Refresh halaman deliveries
   - ✅ Verifikasi delivery muncul dengan status 'Assigned'
   - ✅ **PENTING:** Status harus 'Menunggu K3' (kuning)

### **E. Backorder Management**
1. **Navigate:** Gudang → Backorders
   - ✅ Check backorder items
   - ✅ Test fulfill backorder

**🔓 Logout Gudang**

---

## 🚛 **ROLE 4: SUPIR TESTING (CRITICAL!)**

### **Login Supir**
- **URL:** `http://localhost:8000/login`
- **Email:** `krisna.supir@sigap.com`
- **Password:** `password`

### **A. Dashboard Verification**
1. **Navigate:** Supir Dashboard
   - ✅ Verifikasi assigned deliveries count
   - ✅ Check today's activities
   - ✅ GPS status info

### **B. Delivery Management (Pre-K3)**
1. **Navigate:** Supir → Deliveries (GPS Tracking & Delivery)

2. **CRITICAL TEST - Verifikasi Block tanpa K3:**
   - ✅ **VERIFIKASI:** Delivery status = 'Menunggu K3' (badge kuning)
   - ✅ **VERIFIKASI:** K3 Checklist = 'Belum ada' (badge abu-abu)
   - ✅ **VERIFIKASI:** Tombol aksi = 'K3 Checklist' (kuning, bukan 'Mulai')
   - ❌ **TIDAK BOLEH ADA** tombol 'Mulai Perjalanan'

### **C. K3 Checklist System (TESTING UTAMA!)**
1. **Navigate:** Supir → K3 Checklist

2. **Test Create K3 Checklist:**
   - ✅ Verifikasi alert 'Pengiriman Menunggu' muncul
   - ✅ Klik tombol 'Checklist' pada delivery yang assigned
   
3. **Test Incomplete Checklist:**
   ```
   Checklist Items (SENGAJA INCOMPLETE):
   ✅ Kondisi Ban Baik
   ✅ Kondisi Oli Baik  
   ✅ Air Radiator Cukup
   ❌ Kondisi Rem Baik (JANGAN CENTANG)
   ✅ Level BBM Cukup
   ✅ Kondisi Terpal Baik
   
   Catatan: Testing incomplete checklist
   ```
   
   - ✅ Submit checklist
   - ✅ **VERIFIKASI:** Checklist tersimpan tapi completion < 100%
   - ✅ **VERIFIKASI:** Delivery status MASIH 'assigned' (TIDAK berubah ke k3_checked)

4. **Back to Deliveries:**
   - ✅ **VERIFIKASI:** Status masih 'Menunggu K3'
   - ✅ **VERIFIKASI:** K3 Checklist = 'Incomplete' (badge kuning/merah)
   - ✅ **VERIFIKASI:** Tombol masih 'K3 Checklist' (belum bisa mulai)

5. **Test Complete K3 Checklist:**
   - ✅ Back to K3 Checklist page
   - ✅ Edit checklist yang incomplete
   - ✅ **CENTANG SEMUA ITEMS** (termasuk 'Kondisi Rem Baik')
   - ✅ Update checklist

6. **CRITICAL VALIDATION - Post Complete K3:**
   - ✅ Back to Deliveries page
   - ✅ **VERIFIKASI:** Status berubah menjadi 'Siap Berangkat' (badge biru)
   - ✅ **VERIFIKASI:** K3 Checklist = 'Complete' (badge hijau, 100%)
   - ✅ **VERIFIKASI:** Tombol berubah menjadi 'Mulai Perjalanan' (biru)

### **D. Start Delivery Process**
1. **Test Start Delivery:**
   - ✅ Klik 'Mulai Perjalanan'
   - ✅ Modal 'Mulai Pengiriman' terbuka
   - ✅ Klik 'Ambil Lokasi GPS'
   - ✅ Allow location permission
   - ✅ **VERIFIKASI:** Koordinat GPS terisi
   - ✅ Klik 'Mulai Pengiriman'

2. **Verifikasi Tracking Active:**
   - ✅ **VERIFIKASI:** Status → 'Dalam Perjalanan' (badge biru primary)
   - ✅ **VERIFIKASI:** Alert 'Pengiriman Aktif' muncul di atas
   - ✅ **VERIFIKASI:** Badge 'GPS Tracking Aktif' muncul
   - ✅ **VERIFIKASI:** Tombol berubah → 'Selesaikan'

### **E. Complete Delivery**
1. **Test Complete Delivery:**
   - ✅ Klik 'Selesaikan'
   - ✅ Modal 'Selesaikan Pengiriman' terbuka
   - ✅ Ambil lokasi GPS delivery
   - ✅ Upload foto bukti delivery
   - ✅ Isi catatan delivery
   - ✅ (Optional) Buat tanda tangan customer
   - ✅ Submit completion

2. **Final Verification:**
   - ✅ **VERIFIKASI:** Status → 'Terkirim' (badge hijau)
   - ✅ **VERIFIKASI:** GPS Tracking stop
   - ✅ **VERIFIKASI:** Alert pengiriman aktif hilang
   - ✅ **VERIFIKASI:** Dropdown actions tersedia (bukti foto, lokasi)

**🔓 Logout Supir**

---

## 🔄 **SCENARIO TESTING TAMBAHAN**

### **SCENARIO A: Testing Edge Cases**

#### **A1. Multiple Deliveries Same Supir**
1. **Login Gudang → Assign 2 delivery ke supir yang sama**
2. **Login Supir → Test K3 checklist untuk masing-masing delivery**
3. **Verifikasi:** Tidak bisa start delivery kedua sebelum selesaikan yang pertama

#### **A2. Testing Validation Errors**
1. **Login Supir → Coba start delivery tanpa GPS location**
2. **Verifikasi:** Error message muncul
3. **Test:** Coba complete delivery tanpa foto bukti
4. **Verifikasi:** Validation error

#### **A3. Testing K3 Edit/Delete**
1. **Login Supir → Buat K3 checklist**
2. **Test:** Edit checklist (ubah beberapa items)
3. **Verifikasi:** Status delivery berubah sesuai completion
4. **Test:** Delete checklist
5. **Verifikasi:** Delivery status kembali ke 'assigned'

### **SCENARIO B: Negative Testing**

#### **B1. Test Access Control**
- **Sales trying to access Gudang pages:** `http://localhost:8000/gudang/deliveries`
- **Expected:** 403 Forbidden atau redirect
- **Supir trying to access Admin pages:** `http://localhost:8000/admin/users`
- **Expected:** 403 Forbidden atau redirect

#### **B2. Test Data Integrity**
- **Login Supir A → Coba akses K3 checklist milik Supir B**
- **Expected:** Data tidak visible/error
- **Test:** Manipulasi URL delivery_id di K3 form
- **Expected:** Validation error

---

## 🔍 **CRITICAL CHECKPOINTS**

### **✅ MUST PASS VALIDATIONS:**

1. **K3 Enforcement:**
   - [ ] Delivery dengan status 'assigned' TIDAK bisa dimulai
   - [ ] Tombol 'Mulai Perjalanan' HANYA muncul saat status 'k3_checked'
   - [ ] K3 checklist incomplete TIDAK update delivery status

2. **Status Flow Integration:**
   - [ ] `assigned` → `k3_checked` (otomatis saat K3 complete)
   - [ ] `k3_checked` → `in_progress` (manual via 'Mulai Perjalanan')
   - [ ] `in_progress` → `delivered` (manual via 'Selesaikan')

3. **UI Consistency:**
   - [ ] Status badge colors match dengan kondisi
   - [ ] Tombol aksi sesuai dengan status
   - [ ] Progress indicators accurate

4. **Data Integrity:**
   - [ ] K3 checklist linked to specific delivery
   - [ ] Timestamps recorded correctly
   - [ ] GPS tracking data saved

---

## 🚨 **ERROR SCENARIOS TO TEST**

### **Expected Errors (These SHOULD fail):**

1. **Login Supir → Coba start delivery status 'assigned'**
   - **Expected:** Error "Delivery tidak dapat dimulai. K3 checklist belum complete!"

2. **Buat K3 checklist incomplete → Coba start delivery**
   - **Expected:** Tombol 'Mulai Perjalanan' tidak muncul

3. **Login Sales → Coba akses halaman Supir K3**
   - **Expected:** 403 atau redirect

4. **Start delivery tanpa GPS location**
   - **Expected:** Error "Ambil lokasi GPS terlebih dahulu!"

---

## 📱 **MOBILE TESTING**

### **Responsive Testing:**
1. **Open browser dev tools → Toggle mobile view**
2. **Test all flows di mobile screen**
3. **GPS functionality di mobile browser**
4. **Touch gestures untuk tanda tangan customer**

---

## 📊 **REPORTING RESULTS**

### **Create Testing Report:**
```
TESTING RESULTS - [Date]

✅ PASSED TESTS:
- Admin user management
- Sales order creation  
- Gudang delivery assignment
- Supir K3 checklist flow
- Status integration

❌ FAILED TESTS:
- [List any failures]

🐛 BUGS FOUND:
- [Detail any bugs]

🔧 RECOMMENDATIONS:
- [Any improvements needed]
```

---

## ⚡ **QUICK TEST SEQUENCE (15 menit)**

**Untuk testing cepat:**

1. **Login Admin** → Check dashboard → Create 1 product
2. **Login Sales** → Create 1 customer → Create 1 order → Process payment  
3. **Login Gudang** → Assign delivery ke supir
4. **Login Supir** → 
   - Verify status 'Menunggu K3'
   - Create complete K3 checklist
   - Verify status 'Siap Berangkat'
   - Start delivery
   - Complete delivery

**Total Flow Time:** ~15 menit untuk end-to-end test

---

## 🎯 **SUCCESS CRITERIA**

**Testing dianggap BERHASIL jika:**

- ✅ **K3 Checklist WAJIB** sebelum delivery bisa dimulai
- ✅ **Status progression** berjalan sesuai flowchart baru
- ✅ **UI informatif** dan tidak misleading  
- ✅ **Validation errors** muncul di scenario yang tepat
- ✅ **Data integrity** terjaga di semua role
- ✅ **Security** - role access control berfungsi

**🏆 GOAL:** Zero delivery tanpa K3 validation, full compliance dengan prosedur keselamatan kerja!
