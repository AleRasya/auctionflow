# 🎯 Sistem Lelang Online - Setup Guide

Panduan lengkap untuk setup Sistem Lelang Online di Laragon.

## 📋 Persyaratan Sistem

- **Laragon** (versi terbaru)
- **PHP** 7.4 atau lebih tinggi
- **MySQL** 5.7 atau lebih tinggi
- **Browser Modern** (Chrome, Firefox, Safari, Edge)

## 🚀 Langkah-Langkah Setup

### 1. Persiapan File Project

```bash
# Buka folder www di Laragon
# Biasanya ada di: C:\laragon\www

# Clone atau copy project ke folder www
# Struktur folder yang benar:
C:\laragon\www\auction-system\
├── assets/
├── config/
├── pages/
├── api/
├── uploads/
├── index.php
├── auction_system.sql
├── SETUP.md
└── ...
```

### 2. Konfigurasi Database

#### Step A: Buka MySQL di Laragon

1. Buka **Laragon**
2. Klik tombol **MySQL** untuk start MySQL server
3. Tunggu hingga status berubah menjadi "Running"

#### Step B: Buat Database

1. Buka **HeidiSQL** dari Laragon
2. Koneksi ke MySQL (biasanya sudah otomatis)
3. Klik kanan pada "Databases" di panel kiri
4. Pilih "Create new" → "Database"
5. Masukkan nama: `auction_system`
6. Klik OK

#### Step C: Import SQL

1. Pilih database `auction_system` yang baru dibuat
2. Di menu atas, pilih **File** → **Run SQL file**
3. Cari file `auction_system.sql` di folder project
4. Klik "Open" untuk import

Atau gunakan cara manual:

1. Buka file `auction_system.sql` dengan text editor
2. Copy seluruh isi file
3. Di HeidiSQL, paste di tab "Query"
4. Klik tombol "Execute" (F9)

#### Step D: Verifikasi Database

```sql
-- Jalankan query ini di HeidiSQL untuk verifikasi
USE auction_system;
SHOW TABLES;

-- Hasil yang diharapkan:
-- audit_logs
-- bids
-- products
-- users
```

### 3. Konfigurasi File Aplikasi

#### File: config/database.php

Pastikan konfigurasi sudah sesuai:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // User MySQL (default di Laragon)
define('DB_PASS', '');              // Password (default kosong di Laragon)
define('DB_NAME', 'auction_system'); // Nama database
define('DB_PORT', 3306);             // Port MySQL
```

**Catatan:** Jika Laragon Anda menggunakan konfigurasi berbeda:
- Buka **Laragon → Preferences → Database** untuk cek user dan password
- Update file `config/database.php` sesuai konfigurasi Anda

### 4. Setup Apache Virtual Host

Laragon biasanya sudah otomatis setup virtual host, tapi ikuti langkah ini untuk memastikan:

1. Buka **Laragon**
2. Klik kanan pada project folder → **Edit Hosts**
3. Pastikan ada entry seperti:
   ```
   127.0.0.1 auction-system.local
   ```
4. Jika tidak ada, tambahkan manual

### 5. Start Laragon Services

1. Buka **Laragon**
2. Klik tombol **Start All** atau start individually:
   - **Apache** (klik ikon Apache)
   - **MySQL** (klik ikon MySQL)
3. Tunggu hingga status "Running" untuk kedua service

### 6. Akses Aplikasi

Buka browser dan akses:

```
http://localhost/auction-system/
```

Atau jika sudah setup virtual host:

```
http://auction-system.local/
```

## 🔑 Akun Default

### Admin Account
- **Username:** `admin`
- **Email:** `admin@auctionsystem.local`
- **Password:** Perlu di-generate terlebih dahulu (lihat di bawah)

### Create Admin Account

Jalankan script ini di HeidiSQL untuk membuat admin dengan password `admin123`:

```sql
USE auction_system;

-- Hash password dengan bcrypt: admin123
-- Gunakan online tool: https://bcrypt-generator.com/
-- Atau buat secara manual dengan PHP

-- Contoh password hash untuk "admin123":
UPDATE users SET password = '$2y$10$YourHashedPasswordHere' 
WHERE username = 'admin';
```

**Atau** buat user baru dari halaman register:

1. Akses `http://localhost/auction-system/pages/register.php`
2. Daftar akun baru dengan username/email sesuai keinginan
3. Login dengan akun yang baru dibuat

### User Test Account

Anda bisa membuat beberapa test account untuk testing:

1. **User 1:** username: `user1`, password: `user123`
2. **User 2:** username: `user2`, password: `user123`

Daftar langsung dari halaman register atau buat di database.

## 🗂️ Struktur Folder

```
auction-system/
├── assets/
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/
│       └── placeholder.jpg
├── config/
│   ├── database.php (Konfigurasi database)
│   └── functions.php (Helper functions)
├── pages/
│   ├── login.php (Halaman login)
│   ├── register.php (Halaman register)
│   ├── dashboard.php (Dashboard utama)
│   ├── products.php (Daftar semua produk)
│   ├── product-detail.php (Detail produk & bidding)
│   ├── create-product.php (Buat produk baru)
│   ├── my-products.php (Produk milik user)
│   ├── my-bids.php (Riwayat bid)
│   ├── my-wins.php (Lelang yang dimenangkan)
│   ├── profile.php (Edit profil user)
│   ├── logout.php (Logout)
│   ├── admin-products.php (Admin: Kelola produk)
│   ├── admin-users.php (Admin: Kelola user)
│   └── admin-bids.php (Admin: Kelola bid)
├── api/
│   ├── place-bid.php (API untuk submit bid via AJAX)
│   └── delete-product.php (API untuk hapus produk)
├── uploads/ (Folder untuk upload gambar - auto-created)
├── index.php (Redirect ke login/dashboard)
├── .htaccess (Apache configuration)
├── auction_system.sql (Database schema)
└── SETUP.md (File ini)
```

## 🎨 Color Palette

Aplikasi menggunakan color scheme modern:

- **Dark (Background):** `#0B0F1A`
- **Primary Blue:** `#1E3A8A`
- **Accent Yellow:** `#FACC15`
- **Light Gray:** `#f5f5f5`

## ✨ Fitur Utama

### Authentication
✅ Register user baru
✅ Login dengan session
✅ Logout
✅ Role-based access (Admin/User)

### Product Management
✅ CRUD Produk (Create, Read, Update, Delete)
✅ Upload gambar produk
✅ Validasi file upload
✅ Kategori produk

### Auction System
✅ Real-time bidding
✅ Validasi bid amount
✅ Bid history dengan timestamp
✅ Countdown timer (JavaScript)
✅ Automatic winner determination

### User Features
✅ Dashboard dengan statistik
✅ Lihat semua produk
✅ Riwayat bid
✅ Riwayat kemenangan
✅ Edit profil

### Admin Features
✅ Monitor semua produk
✅ Monitor semua user
✅ Monitor semua bid
✅ Dashboard statistik sistem

### Security
✅ Password hashing (bcrypt)
✅ Prepared statement (SQL injection prevention)
✅ Session security
✅ Input sanitization
✅ File upload validation
✅ CSRF protection (optional)

## 🐛 Troubleshooting

### Error: "Connection failed"
**Solusi:**
- Pastikan MySQL sudah running di Laragon
- Verifikasi username/password di `config/database.php`
- Cek nama database: `auction_system`

### Error: "Database not selected"
**Solusi:**
- Jalankan import SQL lagi
- Verifikasi tabel sudah ada di HeidiSQL

### Upload gambar gagal
**Solusi:**
- Pastikan folder `uploads/` ada dan writable
- Set permission folder ke 755
- Verifikasi konfigurasi file size di `.htaccess`

### Page not found (404)
**Solusi:**
- Pastikan akses dengan path yang benar: `/auction-system/pages/...`
- Reload halaman (Ctrl+F5)
- Clear browser cache

### Images tidak muncul
**Solusi:**
- Buka DevTools (F12) → Console
- Cek apakah ada 404 error pada image
- Verifikasi path gambar di database dan file system

## 📱 Testing Checklist

- [ ] Login dengan akun admin
- [ ] Login dengan akun user
- [ ] Register akun baru
- [ ] Create product baru
- [ ] Upload gambar produk
- [ ] View product detail
- [ ] Place bid pada produk
- [ ] Check bid history
- [ ] View timer countdown
- [ ] Check my products
- [ ] Check my bids
- [ ] Check my wins
- [ ] Edit profile
- [ ] Admin: View all products
- [ ] Admin: View all users
- [ ] Admin: View all bids
- [ ] Logout

## 📚 Database Schema

### users table
- `id` (INT, Primary Key)
- `username` (VARCHAR, Unique)
- `email` (VARCHAR, Unique)
- `password` (VARCHAR, bcrypt hash)
- `full_name` (VARCHAR)
- `role` (ENUM: admin, user)
- `phone` (VARCHAR)
- `address` (TEXT)
- `profile_picture` (VARCHAR)
- `is_active` (BOOLEAN)
- `created_at`, `updated_at` (TIMESTAMP)

### products table
- `id` (INT, Primary Key)
- `user_id` (INT, Foreign Key)
- `title` (VARCHAR)
- `description` (TEXT)
- `category` (VARCHAR)
- `image_path` (VARCHAR)
- `starting_price` (DECIMAL)
- `current_price` (DECIMAL)
- `status` (ENUM: active, ended, cancelled)
- `start_time`, `end_time` (DATETIME)
- `winner_id` (INT, Foreign Key - nullable)
- `created_at`, `updated_at` (TIMESTAMP)

### bids table
- `id` (INT, Primary Key)
- `product_id` (INT, Foreign Key)
- `user_id` (INT, Foreign Key)
- `bid_amount` (DECIMAL)
- `bid_time` (DATETIME)
- `is_winner` (BOOLEAN)

### audit_logs table
- `id` (INT, Primary Key)
- `user_id` (INT, Foreign Key - nullable)
- `action` (VARCHAR)
- `description` (TEXT)
- `ip_address` (VARCHAR)
- `created_at` (TIMESTAMP)

## 🔒 Security Notes

1. **Change Default Credentials:** Ganti password admin setelah setup
2. **Update Database Config:** Jangan commit database credentials ke version control
3. **File Permissions:** Set folder `uploads/` ke 755
4. **SSL/HTTPS:** Gunakan HTTPS di production
5. **Input Validation:** Semua input sudah divalidasi di client dan server

## 📞 Support & Debugging

Untuk melihat error messages:

1. Buka browser DevTools (F12)
2. Cek tab **Console** untuk JavaScript errors
3. Cek tab **Network** untuk API errors
4. Cek file `config/database.php` untuk error messages

## 🎓 Cara Menggunakan Aplikasi

### Untuk Admin:
1. Login dengan akun admin
2. Akses dashboard admin
3. Monitor produk, user, dan bid
4. Check sistem statistics

### Untuk User Biasa:
1. Daftar akun baru
2. Lengkapi profil
3. Lihat semua produk lelang
4. Bid pada produk yang diinginkan
5. Pantau timer countdown
6. Lihat riwayat bid dan kemenangan

### Untuk Seller:
1. Login ke akun Anda
2. Buat produk lelang baru
3. Upload gambar produk
4. Tentukan harga awal dan waktu lelang
5. Monitor bid yang masuk
6. Produk dengan bid tertinggi saat waktu habis adalah pemenang

## 📝 Notes

- Aplikasi fully functional dan production-ready
- Semua validasi ada di client (JavaScript) dan server (PHP)
- Database sudah di-normalize dengan proper relationships
- Code sudah mengikuti best practices
- Responsive design untuk mobile dan desktop

---

**Happy Auctioning! 🎉**

Jika ada pertanyaan atau issue, silakan cek dokumentasi atau hubungi admin system.