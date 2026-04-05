# 🎯 Web Lelang Online (Auction System)

Aplikasi web lelang online yang **fully functional** dan **production-ready** dibangun dengan PHP Native, MySQL, dan Bootstrap offline.

## 📋 Daftar Fitur

### ✅ Fitur Utama yang Sudah Diimplementasikan

- **Authentication System**
  - Register user baru
  - Login dengan validasi
  - Logout & session management
  - Password hashing dengan bcrypt
  - Proteksi halaman dengan session check

- **User Management**
  - Update profil (nama, telepon, alamat)
  - Dashboard personal dengan statistik
  - Admin dapat manage user (aktif/nonaktif)

- **Product Management (CRUD)**
  - Tambah, edit, hapus produk lelang
  - Upload gambar produk (JPG, PNG, GIF, max 5MB)
  - Kategorisasi produk
  - Set harga awal dan waktu lelang

- **Auction System (Core)**
  - Ajukan penawaran (bid) dengan validasi
  - Bid harus lebih tinggi dari harga sekarang (+ 5% minimum)
  - Auto-update harga tertinggi
  - Tampilkan pembid tertinggi
  - Tidak bisa bid produk milik sendiri

- **Timer & Auto-Close**
  - Countdown real-time dengan JavaScript
  - Update status otomatis saat waktu habis
  - Indikator visual (aktif/expiring/expired)
  - Auto-close lelang yang sudah expired

- **Search & Filter**
  - Cari produk by nama/deskripsi
  - Filter by status (active/finished)
  - Pagination

- **Riwayat & History**
  - Riwayat penawaran user
  - Riwayat lelang selesai
  - Informasi pemenang lelang
  - Detail setiap transaksi

- **Security**
  - Input sanitasi
  - Prepared statement (mysqli)
  - Password hashing
  - File upload validation
  - Role-based access control (admin/user)

- **Admin Panel**
  - Dashboard dengan statistik sistem
  - Manage user (aktif/nonaktif)
  - Manage produk (view/filter)
  - Tutup lelang expired

- **UI/UX**
  - Dark theme modern (#0B0F1A background)
  - Konsisten color: #1E3A8A (blue), #FACC15 (yellow)
  - Responsive design
  - Bootstrap offline
  - Smooth animations
  - Flash messages

---

## 🎨 Design & Color Scheme

```
Background Utama:   #0B0F1A (Hitam)
Primary Color:      #1E3A8A (Biru)
Accent Color:       #FACC15 (Kuning)
Text Light:         #f0f0f0
Border Color:       #333
```

Layout:
- Navbar (top)
- Sidebar (left) + Main Content (right)
- Responsive untuk mobile

---

## 📁 Struktur Folder

```
auction-system/
├── config/
│   ├── database.php          # Konfigurasi & koneksi database
│   └── auth.php             # Fungsi authentication
├── functions/
│   ├── helpers.php          # Helper functions (sanitasi, validasi, session, dll)
│   ├── product.php          # Fungsi CRUD produk
│   └── auction.php          # Fungsi lelang (bid, timer, history)
├── assets/
│   ├── css/style.css        # Custom styling (dark theme)
│   └── js/main.js           # JavaScript (timer, AJAX, validasi)
├── pages/
│   ├── auth/                # Login, register, logout
│   ├── dashboard/           # Dashboard utama
│   ├── products/            # Browse, create, edit, my-products
│   ├── auction/             # Detail lelang, my-bids, history
│   ├── profile/             # Profil user
│   └── admin/               # Admin panel, manage users/products
├── api/
│   ├── place-bid.php        # AJAX API untuk bid
│   └── close-expired.php    # API tutup lelang expired
├── includes/
│   ├── header.php           # Layout header (navbar+sidebar)
│   └── footer.php           # Layout footer
├── uploads/products/        # Folder gambar produk
├── database/
│   └── auction_system.sql   # Database schema
├── SETUP.md                 # Panduan instalasi lengkap
└── README.md               # File ini
```

---

## 🚀 Quick Start

### 1. Persiapan
- Pastikan Laragon (atau XAMPP/WAMP) sudah terinstall
- Pastikan PHP 7.4+ dan MySQL 5.7+

### 2. Download Project
```bash
# Copy folder ke
C:\laragon\www\auction-system
```

### 3. Import Database
```bash
mysql -u root < database/auction_system.sql
```

### 4. Buka di Browser
```
http://localhost/auction-system
```

### 5. Login dengan Demo Account
- Username: `user1`
- Password: `password123`

**Lihat SETUP.md untuk dokumentasi lengkap**

---

## 👤 Demo Accounts

| Role  | Username | Password   | Email              |
|-------|----------|------------|--------------------|
| Admin | admin    | password123| admin@auction.com  |
| User  | user1    | password123| user1@auction.com  |
| User  | user2    | password123| user2@auction.com  |

---

## 📊 Database Schema

### Tabel Users
```sql
- id (PK)
- username (UNIQUE)
- email (UNIQUE)
- password (hashed)
- full_name
- phone
- address
- role (admin/user)
- is_active
- created_at, updated_at
```

### Tabel Products
```sql
- id (PK)
- user_id (FK → users)
- name
- description
- category
- initial_price
- current_price
- image
- start_time, end_time
- status (active/finished/cancelled)
- highest_bidder_id (FK → users)
- created_at, updated_at
```

### Tabel Bids
```sql
- id (PK)
- product_id (FK → products)
- user_id (FK → users)
- bid_amount
- bid_time
- created_at
```

### Tabel Auction_History
```sql
- id (PK)
- product_id (FK → products)
- winner_id (FK → users)
- final_price
- total_bids
- status (ongoing/completed/cancelled)
- completed_at
- created_at
```

---

## 🔒 Security Features

- ✅ Password hashing dengan bcrypt
- ✅ Prepared statement untuk prevent SQL injection
- ✅ Input sanitasi & validation
- ✅ Session management dengan timeout
- ✅ File upload validation (type, size, MIME)
- ✅ Role-based access control
- ✅ CSRF protection via session
- ✅ Proteksi halaman dengan authentication

---

## ⚙️ Teknologi Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **UI Framework**: Bootstrap offline
- **Authentication**: Session-based
- **Database Access**: MySQLi Prepared Statement

---

## 📝 Validasi

### Client-Side (JavaScript)
- Form input validation
- Bid amount validation
- File upload preview & validation
- Confirm dialog untuk delete

### Server-Side (PHP)
- Email format validation
- Password strength (min 6 karakter)
- Bid amount validation (harus lebih tinggi + 5%)
- File upload validation (type, size)
- User authentication untuk protected pages
- Role checking untuk admin pages

---

## 🎯 Use Cases

### Untuk User Biasa:

1. **Register & Login**
   - Buat akun baru atau login dengan akun yang ada

2. **Browse Lelang**
   - Cari dan filter produk lelang yang tersedia
   - Lihat detail produk dan countdown timer

3. **Ajukan Penawaran**
   - Ajukan penawaran untuk produk yang diinginkan
   - Lihat riwayat penawaran

4. **Buat Lelang**
   - Upload produk untuk dilelang
   - Set harga awal dan durasi lelang

5. **Kelola Produk**
   - Edit atau hapus produk (sebelum ada bid)
   - Lihat statistik produk

### Untuk Admin:

1. **Monitor Sistem**
   - Lihat statistik keseluruhan sistem
   - Lihat semua produk dan user

2. **Manage User**
   - Aktifkan/nonaktifkan user
   - Lihat data user

3. **Manage Produk**
   - View semua produk
   - Filter dan cari
   - Monitor status lelang

---

## 🐛 Known Issues & Fixes

### Issue: Lelang tidak auto-close
**Fix**: Auto-close terjadi saat ada request ke server. Buka halaman untuk trigger auto-close.

### Issue: Timer tidak jalan
**Fix**: Pastikan JavaScript enabled di browser. Check console untuk error.

### Issue: File upload gagal
**Fix**: Pastikan folder `uploads/products/` exist dan writable (chmod 755).

---

## 📈 Future Enhancements (Optional)

- [ ] Email notification untuk bid
- [ ] Payment integration (Stripe/Midtrans)
- [ ] User rating & review system
- [ ] Real-time notification dengan WebSocket
- [ ] Mobile app
- [ ] Advanced analytics & reporting
- [ ] Multi-language support
- [ ] Better image optimization

---

## 📚 Dokumentasi Lengkap

Lihat **SETUP.md** untuk:
- Instalasi step-by-step
- Konfigurasi database
- Troubleshooting
- Tips & trik

---

## 📞 Support

Jika ada pertanyaan atau issue:

1. Cek dokumentasi (SETUP.md)
2. Lihat browser console untuk error messages
3. Check server logs di Laragon
4. Verify database connection di config/database.php

---

## 📄 License

Proyek ini dibuat untuk pembelajaran dan pengembangan sistem lelang online.

---

## 👨‍💻 Developer Notes

### Code Quality
- Clean code yang mudah dipahami
- Konsisten naming convention
- Proper error handling
- Security best practices
- Well-commented code

### Performance
- Optimized database queries dengan index
- Caching untuk static assets
- Efficient AJAX implementation
- Lazy loading untuk images

### Scalability
- Modular function structure
- Reusable components
- Prepared for database optimization
- Easy to add new features

---

**Happy Bidding! 🎯**

Web Lelang Online yang reliable dan terpercaya untuk transaksi lelang Anda.

Dibuat dengan ❤️ menggunakan PHP Native, MySQL, dan Bootstrap Offline.