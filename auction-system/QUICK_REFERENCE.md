# 🚀 QUICK REFERENCE - Web Lelang Online

Panduan cepat untuk menggunakan aplikasi.

---

## 🔐 Login Credentials

### Demo Account
```
Username: user1
Password: password123
Email: user1@auction.com
```

```
Username: admin
Password: password123
Email: admin@auction.com
```

---

## 🌐 URL Penting

### Authentication
- Login: `http://localhost/auction-system/pages/auth/login.php`
- Register: `http://localhost/auction-system/pages/auth/register.php`

### Main Pages
- Dashboard: `http://localhost/auction-system/pages/dashboard/index.php`
- Browse: `http://localhost/auction-system/pages/products/browse.php`
- Create: `http://localhost/auction-system/pages/products/create.php`

### Admin
- Admin Panel: `http://localhost/auction-system/pages/admin/index.php`
- Manage Users: `http://localhost/auction-system/pages/admin/users.php`
- Manage Products: `http://localhost/auction-system/pages/admin/products.php`

---

## 📂 File Struktur

```
auction-system/
├── config/              # Konfigurasi
├── functions/           # Business logic
├── pages/               # Halaman (29 files)
├── api/                 # AJAX endpoints
├── assets/              # CSS, JS
├── includes/            # Layouts
├── database/            # SQL
└── uploads/products/    # Gambar upload
```

---

## 🔧 Konfigurasi Database

### File: `config/database.php`

Jika menggunakan Laragon (default):
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'auction_system');
```

### Setup Database
```bash
# Import SQL
mysql -u root < database/auction_system.sql

# Atau via phpMyAdmin:
1. Buka phpMyAdmin
2. Buat database: auction_system
3. Import file: database/auction_system.sql
```

---

## 👤 User Roles

### Regular User
- ✓ Register & Login
- ✓ Create/Edit/Delete products
- ✓ Browse & filter products
- ✓ Place bids
- ✓ View history
- ✓ Update profile
- ✗ Cannot access admin panel

### Admin
- ✓ All user features
- ✓ Manage users (aktif/nonaktif)
- ✓ View all products
- ✓ View system statistics
- ✓ Close expired auctions
- ✓ Access admin panel

---

## 🎯 Main Features

### 1. Authentication
```
Login/Register → Set Session → Redirect to Dashboard
```

### 2. Product Management
```
Create → Upload Image → Set Time → Publish
Edit → (only if no bids)
Delete → (only if no bids)
```

### 3. Bidding
```
Browse → Select Product → Enter Amount → Confirm
↓
Validate: amount > current_price + 5%
↓
Save to database
↓
Update highest price & bidder
```

### 4. Timer
```
JavaScript Countdown → Every 1 second
↓
Format: "2j 30m" or "45m 30d"
↓
Color: Green (aktif) → Orange (< 1 hour) → Red (expired)
↓
Disable form when time = 0
```

### 5. Auto-Close
```
When product.end_time <= NOW():
1. Update status = 'finished'
2. Update auction_history
3. Set winner
4. Set final_price
```

---

## 📊 Database Tables Quick View

### Users Table
```sql
SELECT id, username, email, full_name, role, is_active 
FROM users;
```

### Products Table
```sql
SELECT id, name, initial_price, current_price, status, end_time
FROM products 
ORDER BY created_at DESC;
```

### Bids Table
```sql
SELECT b.id, b.product_id, b.user_id, b.bid_amount, b.bid_time
FROM bids 
ORDER BY bid_time DESC;
```

### Auction History Table
```sql
SELECT ah.id, ah.product_id, ah.winner_id, ah.final_price, ah.status
FROM auction_history;
```

---

## 🔒 Security Quick Tips

### Do's ✓
- Always use `sanitize()` untuk input user
- Always use prepared statement untuk database query
- Always check `isLoggedIn()` untuk protected pages
- Always verify user ownership sebelum edit/delete
- Always hash password dengan `password_hash()`

### Don'ts ✗
- Don't use string concatenation untuk SQL query
- Don't store password as plain text
- Don't allow access tanpa authentication
- Don't upload file tanpa validation
- Don't trust user input

---

## 🚀 Deployment Checklist

- [ ] Database sudah di-import
- [ ] `uploads/products/` folder writable
- [ ] `config/database.php` konfigurasi benar
- [ ] All functions files included correctly
- [ ] Error logs dicheck (check PHP error log)
- [ ] Test login dengan demo account
- [ ] Test create product
- [ ] Test place bid
- [ ] Test timer countdown
- [ ] Test search & filter
- [ ] Test admin panel access

---

## 🐛 Common Issues & Fix

| Issue | Fix |
|-------|-----|
| "Connection failed" | Check DB config di config/database.php |
| "File upload error" | Check uploads/products/ writable (chmod 755) |
| "Session not working" | Clear browser cookies, restart browser |
| "CSS/JS tidak load" | Refresh (Ctrl+F5), check APP_URL di config |
| "Lelang tidak auto-close" | Open browse page to trigger auto-close |
| "Page blank/error" | Check PHP error log, verify includes path |

---

## 🔄 Database Backup

```bash
# Backup
mysqldump -u root auction_system > backup.sql

# Restore
mysql -u root < backup.sql
```

---

## 📈 Performance Tips

1. **Clear Old Data**
   - Delete finished auctions setelah 30 hari

2. **Optimize Images**
   - Max 200px width untuk product cards
   - Compress before upload

3. **Database Maintenance**
   - Analyze & optimize tables
   - Check indexes

4. **Caching**
   - Browser cache di .htaccess
   - Use CDN untuk static files (optional)

---

## 🎨 Customization

### Change Theme Color
Edit `assets/css/style.css`:
```css
:root {
    --primary-dark: #0B0F1A;      /* Change background */
    --primary-blue: #1E3A8A;      /* Change primary */
    --accent-yellow: #FACC15;     /* Change accent */
}
```

### Change App Name
Edit `config/database.php`:
```php
define('APP_NAME', 'Your App Name');
```

### Change Upload Folder
Edit `config/database.php`:
```php
define('UPLOAD_DIR', __DIR__ . '/../your_folder/');
```

---

## 📚 File Functions Quick Lookup

### `functions/helpers.php`
```php
sanitize($input)                    # Clean input
isValidEmail($email)               # Validate email
isValidImage($filename)            # Check image type
uploadImage($file)                 # Upload & validate file
deleteImage($filename)             # Delete file
startSecureSession()               # Init session
isLoggedIn()                       # Check login
requireLogin()                     # Protect page
setFlashMessage($type, $msg)       # Set alert
getFlashMessage($type)             # Get alert
getRow($conn, $query, $params)     # Get 1 row
getRows($conn, $query, $params)    # Get multiple rows
executeQuery($conn, $query, $params) # Run query
formatCurrency($amount)            # Rp format
formatDateTime($datetime)          # Date format
isAuctionActive($endTime)         # Check if running
```

### `functions/product.php`
```php
addProduct()                       # Create product
updateProduct()                    # Edit product
deleteProduct()                    # Delete product
getProduct($id)                    # Get single product
getProducts()                      # Get all products (with filter)
getUserProducts()                  # Get user's products
countProducts()                    # Count products
```

### `functions/auction.php`
```php
addBid()                          # Place bid
getLatestBid()                    # Get highest bid
getProductBids()                  # Get all bids for product
updateProductStatus()             # Change status
autoCloseExpiredAuctions()        # Auto-close when time up
getDashboardStats()               # Get statistics
```

---

## 🎯 API Quick Reference

### Place Bid (AJAX)
```javascript
// POST to: /auction-system/api/place-bid.php
{
  "product_id": 1,
  "bid_amount": 1500000
}

// Response:
{
  "success": true,
  "message": "Penawaran berhasil!",
  "bid_id": 123
}
```

### Close Expired
```
GET: /auction-system/api/close-expired.php
(Admin only, redirects to admin panel)
```

---

## 🌍 Browser Compatibility

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ⚠️ IE11 (Partial - no support)

---

## 📱 Responsive Breakpoints

- Desktop: 1024px+
- Tablet: 768px - 1023px
- Mobile: < 768px

---

## ⏱️ Time Format

### Database
```sql
'2024-01-15 14:30:00'  -- DATETIME format
```

### PHP Display
```php
formatDateTime($datetime)          // 15 Jan 2024 14:30
formatDate($date)                 // 15 Jan 2024
formatTimeRemaining($seconds)     // 2 jam 30 menit
```

### JavaScript Timer
```javascript
'2j 30m'    // 2 jam 30 menit
'45m 30d'   // 45 menit 30 detik
'45d'       // 45 detik
```

---

## 🔐 Password Requirements

- Minimum 6 karakter
- Can contain: letters, numbers, special chars
- No length limit (for practical use: max 128)

---

## 📤 File Upload Requirements

- **Allowed Types:** JPG, PNG, GIF
- **Max Size:** 5MB
- **MIME Check:** Yes
- **Rename:** Auto-renamed untuk keamanan
- **Location:** `uploads/products/` folder

---

## 💾 Session Configuration

- **Timeout:** 1 jam (3600 seconds)
- **HttpOnly:** Yes (prevent JS access)
- **SameSite:** Lax (CSRF protection)
- **Secure:** No (set to Yes for HTTPS)

---

## 🎓 Code Example

### Contoh Query Aman
```php
// Get user dengan ID
$user = getRow($conn, 
    "SELECT id, username, email FROM users WHERE id = ?", 
    [$user_id]
);

// Get products dengan filter
$products = getRows($conn,
    "SELECT * FROM products WHERE status = ? AND user_id = ? ORDER BY created_at DESC",
    ['active', $user_id]
);

// Insert bid
$result = executeQuery($conn,
    "INSERT INTO bids (product_id, user_id, bid_amount) VALUES (?, ?, ?)",
    [$product_id, $user_id, $bid_amount]
);
```

### Contoh Form Submission
```html
<form method="POST" action="" enctype="multipart/form-data">
    <input type="text" name="name" required>
    <textarea name="description" required></textarea>
    <input type="number" name="initial_price" min="1000" required>
    <input type="file" name="image" accept="image/*" required>
    <button type="submit">Submit</button>
</form>
```

### Contoh Validasi
```php
if (empty($name)) {
    $errors[] = 'Nama tidak boleh kosong';
}

if (!isValidEmail($email)) {
    $errors[] = 'Format email tidak valid';
}

if ($password !== $confirm_password) {
    $errors[] = 'Password tidak sesuai';
}

if (!empty($errors)) {
    // Show errors
}
```

---

## 📞 Need Help?

1. **Installation Issues** → Check `SETUP.md`
2. **Technical Details** → Check `TECHNICAL.md`
3. **Feature Overview** → Check `README.md`
4. **Code Examples** → Check file comments
5. **Database** → Check schema di `database/auction_system.sql`

---

**Good luck with your Auction System! 🎯**

Semoga project ini bermanfaat dan sukses!