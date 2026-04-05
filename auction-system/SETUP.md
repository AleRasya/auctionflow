# 🎯 AUCTION SYSTEM - Setup Guide

## Daftar Isi
1. [Persyaratan Sistem](#persyaratan-sistem)
2. [Instalasi di Laragon](#instalasi-di-laragon)
3. [Konfigurasi Database](#konfigurasi-database)
4. [Struktur Project](#struktur-project)
5. [Akun Demo](#akun-demo)
6. [Fitur Utama](#fitur-utama)
7. [Troubleshooting](#troubleshooting)

---

## Persyaratan Sistem

- **Laragon** (atau XAMPP/WAMP)
- **PHP 7.4+**
- **MySQL 5.7+**
- **Browser Modern** (Chrome, Firefox, Safari, Edge)

---

## Instalasi di Laragon

### Step 1: Download Project
```bash
# Clone atau download project ini ke folder:
C:\laragon\www\auction-system
```

### Step 2: Buat Database
1. Buka **Laragon** → Klik **Start All**
2. Buka **MySQL** (Klik database icon)
3. Buat database baru:
   - Username: `root`
   - Password: (kosong)
   - Database: `auction_system`

### Step 3: Import Database
1. Buka file SQL: `database/auction_system.sql`
2. Jalankan query untuk membuat tabel:
```sql
-- Copy semua isi dari database/auction_system.sql
-- Paste ke phpMyAdmin atau MySQL client
```

Atau via command line:
```bash
cd C:\laragon\www\auction-system
mysql -u root < database/auction_system.sql
```

### Step 4: Konfigurasi Koneksi Database
Edit file: `config/database.php`

```php
define('DB_HOST', 'localhost');      // Host database
define('DB_USER', 'root');           // Username MySQL
define('DB_PASS', '');               // Password MySQL (kosong untuk Laragon)
define('DB_NAME', 'auction_system'); // Nama database
define('DB_PORT', 3306);             // Port MySQL
```

### Step 5: Jalankan Project
1. Buka browser dan akses:
   ```
   http://localhost/auction-system
   ```

2. Atau jika menggunakan virtual host di Laragon:
   ```
   http://auction-system.local
   ```

---

## Konfigurasi Database

### Tabel yang Dibuat Otomatis

1. **users** - Data pengguna
   - Kolom: id, username, email, password, full_name, phone, address, role, is_active, created_at, updated_at
   - Role: admin, user

2. **products** - Data produk lelang
   - Kolom: id, user_id, name, description, category, initial_price, current_price, image, start_time, end_time, status, highest_bidder_id, created_at, updated_at
   - Status: active, finished, cancelled

3. **bids** - Data penawaran/lelang
   - Kolom: id, product_id, user_id, bid_amount, bid_time, created_at
   - Relasi: Foreign key ke products dan users

4. **auction_history** - Riwayat lelang
   - Kolom: id, product_id, winner_id, final_price, total_bids, status, completed_at, created_at
   - Status: ongoing, completed, cancelled

---

## Struktur Project

```
auction-system/
├── config/
│   ├── database.php           # Konfigurasi database
│   └── auth.php              # Fungsi authentication
├── functions/
│   ├── helpers.php           # Helper functions (validasi, sanitasi, session, dll)
│   ├── product.php           # Fungsi produk (CRUD)
│   └── auction.php           # Fungsi lelang (bid, timer, history)
├── assets/
│   ├── css/
│   │   └── style.css         # Custom styling (warna #0B0F1A, #1E3A8A, #FACC15)
│   ├── js/
│   │   └── main.js           # JavaScript utama (timer, AJAX, validasi)
│   └── bootstrap/            # Bootstrap offline (jika ada)
├── pages/
│   ├── auth/
│   │   ├── login.php         # Halaman login
│   │   ├── register.php      # Halaman register
│   │   └── logout.php        # Halaman logout
│   ├── dashboard/
│   │   └── index.php         # Dashboard utama
│   ├── products/
│   │   ├── browse.php        # Browse/jelajahi lelang
│   │   ├── create.php        # Buat lelang baru
│   │   ├── edit.php          # Edit lelang
│   │   └── my-products.php   # Produk saya
│   ├── auction/
│   │   ├── detail.php        # Detail lelang + form bid
│   │   ├── my-bids.php       # Penawaran saya
│   │   └── history.php       # Riwayat lelang
│   ├── profile/
│   │   └── index.php         # Profil user
│   └── admin/
│       ├── index.php         # Admin dashboard
│       ├── users.php         # Kelola user
│       └── products.php      # Kelola produk
├── api/
│   ├── place-bid.php         # API AJAX untuk bid
│   └── close-expired.php     # API untuk tutup lelang expired
├── includes/
│   ├── header.php            # Layout header (navbar + sidebar)
│   └── footer.php            # Layout footer
├── uploads/
│   └── products/             # Folder untuk gambar produk
├── database/
│   └── auction_system.sql    # File database
├── SETUP.md                  # Panduan setup ini
└── index.php                 # Home page (redirect)
```

---

## Akun Demo

### Admin Account
- **Username:** `admin`
- **Password:** `password123`
- **Email:** `admin@auction.com`

### User Account 1
- **Username:** `user1`
- **Password:** `password123`
- **Email:** `user1@auction.com`

### User Account 2
- **Username:** `user2`
- **Password:** `password123`
- **Email:** `user2@auction.com`

---

## Fitur Utama

### 1. Authentication System ✓
- Register user baru
- Login dengan username/email
- Logout
- Session management
- Proteksi halaman (harus login)
- Password hashing dengan bcrypt

### 2. User Management ✓
- Update profil (nama, telepon, alamat)
- Dashboard personal
- Admin dapat manage user (aktif/nonaktif)

### 3. Product Management (CRUD) ✓
- Tambah barang lelang baru
- Edit barang (hanya jika belum ada bid)
- Hapus barang (hanya jika belum ada bid)
- Upload gambar produk (JPG, PNG, GIF max 5MB)
- Kategorisasi produk

### 4. Auction System ✓
- Ajukan penawaran (bid)
- Validasi bid (harus lebih tinggi dari harga sekarang)
- Auto-update harga tertinggi
- Riwayat bid lengkap
- Tidak bisa bid produk milik sendiri

### 5. Timer Lelang ✓
- Countdown real-time dengan JavaScript
- Auto-close lelang ketika waktu habis
- Indikator warna (aktif, expiring, expired)
- Update status otomatis

### 6. Search & Filter ✓
- Cari produk by nama/deskripsi
- Filter by status (aktif/selesai)
- Pagination

### 7. Riwayat & Dashboard ✓
- Dashboard dengan statistik
- Riwayat penawaran user
- Riwayat lelang selesai
- Informasi pemenang lelang

### 8. Security Features ✓
- Input sanitasi
- Prepared statement (mysqli)
- Password hashing (bcrypt)
- CSRF protection via session
- File upload validation
- Role-based access control

### 9. Admin Panel ✓
- View statistik sistem
- Manage user (aktif/nonaktif)
- Manage produk
- Tutup lelang yang expired

### 10. UI/UX ✓
- Modern dark theme (#0B0F1A background)
- Konsisten color scheme (#1E3A8A blue, #FACC15 yellow)
- Responsive design
- Bootstrap offline
- Smooth animations & transitions
- Sidebar navigation
- Flash messages (alert)

---

## Cara Menggunakan

### Sebagai User Biasa:

1. **Register**
   - Klik "Daftar" di halaman login
   - Isi form dengan data valid
   - Klik "Daftar Akun"

2. **Login**
   - Masukkan username dan password
   - Klik "Login"

3. **Jelajahi Lelang**
   - Klik "Jelajahi Lelang" di sidebar
   - Cari dan filter produk
   - Klik produk untuk melihat detail

4. **Ajukan Penawaran**
   - Buka detail lelang
   - Masukkan jumlah penawaran (harus lebih tinggi dari harga sekarang)
   - Klik "Ajukan Penawaran"
   - Penawaran tidak bisa dibatalkan setelah diajukan

5. **Buat Lelang Baru**
   - Klik "Buat Lelang Baru" di sidebar
   - Isi informasi produk (nama, deskripsi, harga awal)
   - Upload gambar
   - Tentukan waktu mulai dan berakhir
   - Klik "Buat Lelang"

6. **Kelola Produk**
   - Klik "Produk Saya"
   - Edit (jika belum ada bid) atau lihat detail
   - Hapus (jika belum ada bid)

7. **Lihat Riwayat**
   - Klik "Penawaran Saya" untuk lihat bid yang sudah diajukan
   - Klik "Riwayat Lelang" untuk lihat lelang selesai

8. **Update Profil**
   - Klik "Profil Saya"
   - Edit nama, telepon, dan alamat
   - Klik "Simpan Perubahan"

### Sebagai Admin:

1. Login dengan akun admin
2. Klik "Admin Panel" di sidebar
3. Kelola user: aktifkan/nonaktifkan akun
4. Kelola produk: lihat semua produk dan status
5. Tutup lelang expired secara manual

---

## Validasi yang Diterapkan

### Validasi Client-Side (JavaScript)
- Validasi form input
- Validasi bid amount
- Konfirmasi delete dengan confirm()
- Preview gambar sebelum upload

### Validasi Server-Side (PHP)
- Email format validation
- Password strength (min 6 karakter)
- Bid amount (harus lebih tinggi dari current_price + 5%)
- File upload (tipe, ukuran)
- User authentication untuk setiap protected page
- Role checking untuk admin pages

---

## Security Best Practices

1. **Database**
   - Menggunakan prepared statement (mysqli)
   - Password di-hash dengan bcrypt
   - Kolom sensitive tidak ditampilkan di frontend

2. **Input**
   - Sanitasi semua input user
   - Strip tags dan special characters
   - Validasi tipe data

3. **File Upload**
   - Validasi MIME type
   - Validasi file extension
   - Validasi file size (max 5MB)
   - Generate random filename untuk keamanan

4. **Session**
   - Session timeout 1 jam
   - HttpOnly cookies
   - Secure session handling

5. **Access Control**
   - Role-based access (admin/user)
   - Proteksi halaman dengan session check
   - Verifikasi ownership saat edit/delete

---

## Troubleshooting

### Masalah: "Connection failed"
**Solusi:**
- Pastikan MySQL sudah running di Laragon
- Cek konfigurasi di `config/database.php`
- Username dan password sesuai dengan Laragon
- Database `auction_system` sudah dibuat

### Masalah: "File upload error"
**Solusi:**
- Pastikan folder `uploads/products/` sudah dibuat
- Folder harus readable dan writable (chmod 755)
- File size tidak melebihi 5MB
- Format: JPG, PNG, GIF saja

### Masalah: "Session/Login tidak bekerja"
**Solusi:**
- Pastikan cookies diizinkan di browser
- Clear browser cache
- Restart browser
- Cek php.ini session setting

### Masalah: "CSS/JS tidak load"
**Solusi:**
- Pastikan `APP_URL` di `config/database.php` benar
- Refresh browser (F5 atau Ctrl+F5)
- Check network tab di DevTools

### Masalah: "Lelang tidak auto-close"
**Solusi:**
- Sistem auto-close terjadi saat ada request ke server
- Buka halaman browse/detail untuk trigger auto-close
- Atau klik tombol "Tutup Lelang Expired" di admin panel

---

## Tips & Trik

1. **Testing Multiple Users**
   - Buka 2 tab browser
   - Login dengan user berbeda
   - Lakukan bid dari kedua tab untuk test bidding

2. **Testing Timer**
   - Buat lelang dengan durasi pendek (5 menit)
   - Lihat countdown timer berjalan real-time
   - Coba bid sebelum time up

3. **Testing Admin Panel**
   - Login sebagai admin
   - Cek semua statistik
   - Manage user dan produk

4. **Testing Responsive Design**
   - Resize browser window
   - Lihat layout menyesuaikan
   - Test di mobile device

---

## Dukungan & Kontak

Jika ada pertanyaan atau issue, silakan:
1. Cek documentation ini dulu
2. Lihat console browser untuk error message
3. Lihat server logs (Laragon > Logs)

---

## Changelog

### Version 1.0 (Initial Release)
- ✓ Complete authentication system
- ✓ Product management (CRUD)
- ✓ Auction & bidding system
- ✓ Real-time countdown timer
- ✓ Search & filter
- ✓ User dashboard
- ✓ Admin panel
- ✓ Modern dark UI
- ✓ Responsive design
- ✓ Security features

---

**Selamat menggunakan Web Lelang Online! 🎯**

Semoga aplikasi ini bermanfaat dan sukses!