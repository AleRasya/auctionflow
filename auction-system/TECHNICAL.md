# 🔧 Dokumentasi Teknis - Auction System

Penjelasan detail tentang implementasi teknis setiap komponen sistem.

---

## 📋 Daftar Konten

1. [Architecture](#architecture)
2. [Config & Database](#config--database)
3. [Helper Functions](#helper-functions)
4. [Authentication](#authentication)
5. [Product Management](#product-management)
6. [Auction & Bidding](#auction--bidding)
7. [Frontend & JavaScript](#frontend--javascript)
8. [API Endpoints](#api-endpoints)
9. [Security Implementation](#security-implementation)

---

## Architecture

### Pola Desain: Procedural PHP dengan Helper Functions

```
Request → Router (index.php) → Controller (pages/*.php) 
        → Model (functions/*.php) → Database (config/database.php)
        → View (pages/*.php) → Response
```

### Flow Diagram:

```
User Request
    ↓
config/database.php (Connect to DB)
    ↓
functions/helpers.php (Sanitasi, Validasi)
    ↓
functions/auth.php / functions/product.php / functions/auction.php
    ↓
pages/*.php (Business Logic & View)
    ↓
includes/header.php + includes/footer.php (Render HTML)
    ↓
assets/css/style.css + assets/js/main.js (Styling & Interactivity)
    ↓
HTTP Response (HTML, JSON, Redirect)
```

---

## Config & Database

### File: `config/database.php`

```php
// Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Constants
define('DB_HOST', 'localhost');
define('UPLOAD_DIR', __DIR__ . '/../uploads/products/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);
```

**Fitur:**
- Charset UTF-8 untuk support Indonesia
- Constants untuk konfigurasi global
- Error handling untuk koneksi

---

## Helper Functions

### File: `functions/helpers.php`

Fungsi-fungsi umum yang digunakan di seluruh aplikasi:

#### 1. Input Sanitasi
```php
sanitize($input) // Remove tags, trim, htmlspecialchars
isValidEmail($email) // Filter validate
isValidImage($filename) // Check extension
```

#### 2. File Upload
```php
uploadImage($file) // Validate & move file
deleteImage($filename) // Delete file
```

**Validasi:**
- Size check (max 5MB)
- Extension check (JPG, PNG, GIF)
- MIME type check dengan finfo

#### 3. Session Management
```php
startSecureSession() // Start with timeout
isLoggedIn() // Check session
requireLogin() // Redirect if not logged in
isAdmin() // Check role
setUserSession($user) // Set session data
logout() // Destroy session
```

**Security:**
- Session timeout 1 jam
- HttpOnly cookies
- SameSite=Lax

#### 4. Database Helpers
```php
getRow($conn, $query, $params) // Get 1 row
getRows($conn, $query, $params) // Get multiple rows
executeQuery($conn, $query, $params) // INSERT, UPDATE, DELETE
```

**Security:**
- Prepared statement untuk prevent SQL injection
- Type binding (i=integer, d=double, s=string)

#### 5. Flash Messages
```php
setFlashMessage($type, $message) // Set alert
getFlashMessage($type) // Get & unset
getAllFlashMessages() // Get all messages
```

**Tipe:** success, error, warning, info

#### 6. Utility Functions
```php
formatCurrency($amount) // Rp format
formatDateTime($datetime) // Date format
getTimeRemaining($endTime) // Countdown seconds
isAuctionActive($endTime) // Check if still running
formatTimeRemaining($seconds) // Human readable time
```

---

## Authentication

### File: `config/auth.php`

#### Function: `registerUser()`
```php
registerUser($conn, $username, $email, $password, $confirm_password, $full_name, $phone)
```

**Validasi:**
- Username min 3 karakter
- Email format valid
- Password min 6 karakter & match
- Cek duplicate username/email

**Security:**
- Password di-hash dengan `password_hash(..., PASSWORD_BCRYPT)`
- Return array dengan success status

#### Function: `loginUser()`
```php
loginUser($conn, $username, $password)
```

**Validasi:**
- User exists
- User is active (is_active = 1)
- Password verify dengan `password_verify()`

**Session:**
- Set $_SESSION setelah login successful
- Return success/error array

#### Function: `getUserById()`
```php
getUserById($conn, $id)
```

**Return:**
- User data (all columns except password)

#### Function: `updateUserProfile()`
```php
updateUserProfile($conn, $user_id, $full_name, $phone, $address)
```

**Validasi:**
- Full name tidak kosong
- Update $_SESSION untuk current user

---

## Product Management

### File: `functions/product.php`

#### Function: `addProduct()`
```php
addProduct($conn, $user_id, $name, $description, $category, $initial_price, $start_time, $end_time, $image)
```

**Validasi:**
- Name, description, category required
- Initial price > 0
- Start time tidak boleh di masa lalu
- End time > start time
- Image required

**Process:**
1. Validasi semua input
2. INSERT ke tabel products
3. INSERT ke tabel auction_history (status=ongoing)
4. Return insert_id

#### Function: `updateProduct()`
```php
updateProduct($conn, $product_id, $user_id, $name, $description, ...)
```

**Security:**
- Verify ownership (product.user_id == user_id)
- Check if no bids yet
- Delete old image jika ada yang baru

#### Function: `deleteProduct()`
```php
deleteProduct($conn, $product_id, $user_id)
```

**Security:**
- Verify ownership
- Check if no bids
- Delete image file

#### Function: `getProducts()`
```php
getProducts($conn, $status='all', $search='', $limit=12, $offset=0)
```

**Features:**
- Filter by status (active/finished)
- Search by name/description
- Pagination dengan LIMIT OFFSET
- Count bids dengan GROUP BY

#### Function: `getProduct()`
```php
getProduct($conn, $product_id)
```

**Return:**
- Product data + seller info + highest bidder

---

## Auction & Bidding

### File: `functions/auction.php`

#### Function: `addBid()`
```php
addBid($conn, $product_id, $user_id, $bid_amount)
```

**Validasi:**
- Bid amount > 0
- Product exists & status = active
- Auction still running (end_time > now)
- User ≠ product seller
- Bid amount > current_price
- Bid amount ≥ (current_price + 5%)

**Process:**
1. Validasi semua kondisi
2. INSERT ke tabel bids
3. UPDATE products (current_price, highest_bidder_id)
4. Return success/error

**Security:**
- Prevent self-bidding
- Prevent bidding on finished auction
- Min increment prevent bid spam

#### Function: `getLatestBid()`
```php
getLatestBid($conn, $product_id)
```

**Query:**
- Select terbaru dari bids
- Join dengan users untuk info pembid
- ORDER BY bid_time DESC LIMIT 1

#### Function: `getProductBids()`
```php
getProductBids($conn, $product_id, $limit=10)
```

**Features:**
- Get all bids untuk product
- Sort by amount DESC (highest first)
- Limited results untuk performance

#### Function: `updateProductStatus()`
```php
updateProductStatus($conn, $product_id, $status)
```

**Features:**
- Update status ke finished
- Update auction_history dengan winner_id, final_price, completed_at

#### Function: `autoCloseExpiredAuctions()`
```php
autoCloseExpiredAuctions($conn)
```

**Process:**
1. UPDATE products: status='finished' WHERE end_time <= NOW()
2. UPDATE auction_history untuk setiap finished product
3. Set winner_id, final_price, completed_at

**Trigger:**
- Called saat page load (preventive)
- Called saat bid (reactive)
- Called manual dari admin panel

#### Function: `getDashboardStats()`
```php
getDashboardStats($conn, $user_id=null)
```

**Return:**
- total_products
- active_products
- total_bids
- winning_auctions (jika user_id provided)
- total_users (jika user_id null = admin)

---

## Frontend & JavaScript

### File: `assets/js/main.js`

#### Timer Functions

```javascript
formatTime(seconds) // Convert seconds to readable format
updateCountdown(productId, endTime) // Start countdown interval
```

**Implementation:**
```javascript
// Every 1 second:
1. Calculate remaining time
2. Update DOM dengan formatted time
3. Change color (green/orange/red)
4. Disable form jika habis
```

**Features:**
- Real-time update setiap detik
- Format: "Nh Nm" atau "Nm Nd"
- Auto disable bid form saat expired

#### Bid Functions

```javascript
placeBid(productId, bidAmount) // AJAX submit bid
validateBidForm(productId) // Validate sebelum submit
```

**Flow:**
1. Validate input (client-side)
2. Send AJAX POST ke /api/place-bid.php
3. Parse JSON response
4. Show alert
5. Reload page jika success

#### Alert/Message Functions

```javascript
showAlert(type, message) // Show notification
hideLoading(element) // Hide spinner
```

**Features:**
- Auto-hide setelah 5 detik (kecuali error)
- Animated slide-in
- Remove saat user klik close

---

## API Endpoints

### `api/place-bid.php`

**Method:** POST
**Content-Type:** application/json

**Request:**
```json
{
  "product_id": 1,
  "bid_amount": 1500000
}
```

**Response Success (200):**
```json
{
  "success": true,
  "message": "Penawaran berhasil diajukan!",
  "bid_id": 123
}
```

**Response Error (400/401/500):**
```json
{
  "success": false,
  "message": "Error message"
}
```

**Security:**
- Check isLoggedIn()
- Check HTTP method is POST
- Validate JSON
- Use addBid() function

### `api/close-expired.php`

**Method:** GET
**Access:** Admin only

**Process:**
1. Check authentication
2. Check role = admin
3. Call autoCloseExpiredAuctions()
4. Redirect ke admin panel

---

## Security Implementation

### 1. Password Security

```php
// Register
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Login
if (password_verify($input_password, $stored_hash)) {
    // Login successful
}
```

**Strength:** BCRYPT dengan cost=10 (default)

### 2. SQL Injection Prevention

```php
// ❌ UNSAFE
$query = "SELECT * FROM users WHERE username = '$username'";

// ✅ SAFE
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
```

**Implementation:**
- Selalu gunakan prepared statement
- Bind parameter dengan type (i, d, s, b)

### 3. XSS Prevention

```php
// Output
<?php echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8'); ?>

// Input
$input = sanitize($_POST['input']); // htmlspecialchars + trim
```

### 4. File Upload Security

```php
// Validasi type
$allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
$mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tmp_file);
if (!in_array($mime, $allowed_mimes)) {
    throw new Exception("Invalid file type");
}

// Validasi size
if ($file['size'] > MAX_FILE_SIZE) {
    throw new Exception("File too large");
}

// Generate safe filename
$filename = 'product_' . time() . '_' . generateRandomString(8) . '.' . $ext;
```

### 5. Session Security

```php
// Start secure
session_set_cookie_params([
    'lifetime' => 3600,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Timeout check
if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
    session_destroy();
    // Redirect to login
}
```

### 6. Access Control

```php
// Page protection
requireLogin(); // Require login untuk protected pages

// Role check
if ($user['role'] !== 'admin') {
    // Unauthorized
}

// Ownership check
if ($product['user_id'] != $current_user['id']) {
    // Not owner - cannot edit
}
```

---

## Performance Optimization

### Database
- Index pada frequently queried columns (id, user_id, status)
- LIMIT untuk pagination
- GROUP BY untuk aggregate queries

### Frontend
- Inline CSS untuk critical styling
- Defer JavaScript loading
- Image optimization (resize, compress)
- Browser caching dengan .htaccess

### Queries
```php
// ✅ GOOD - Get only needed columns
SELECT p.id, p.name, p.current_price FROM products p LIMIT 10;

// ❌ BAD - Get all columns even if not needed
SELECT * FROM products LIMIT 10;

// ✅ GOOD - Use WHERE untuk filter early
SELECT * FROM products WHERE status='active' AND user_id=1;

// ❌ BAD - Get all then filter in PHP
SELECT * FROM products;
foreach ($products as $p) if ($p['status']=='active') ...
```

---

## Error Handling

### PHP Level
```php
try {
    // Database operation
    $result = executeQuery($conn, $sql, $params);
} catch (Exception $e) {
    // Log error
    error_log($e->getMessage());
    // Show user-friendly message
    $errors[] = "Terjadi kesalahan: " . $e->getMessage();
}
```

### JavaScript Level
```javascript
try {
    const response = await fetch(url, options);
    const data = await response.json();
    if (!response.ok) {
        throw new Error(data.message);
    }
} catch (error) {
    showAlert('error', error.message);
}
```

---

## Testing Checklist

- [ ] Register dengan valid/invalid data
- [ ] Login dengan correct/wrong password
- [ ] Update profile
- [ ] Create product dengan valid data
- [ ] Edit product (hanya jika no bids)
- [ ] Delete product (hanya jika no bids)
- [ ] Browse products dengan search/filter
- [ ] Place bid dengan berbagai amounts
- [ ] Verify bid validation (min amount, harus > current)
- [ ] Check timer countdown works
- [ ] Verify auto-close saat time up
- [ ] Check admin panel access (auth)
- [ ] Verify file upload (image only, max size)
- [ ] Test session timeout
- [ ] Check SQL injection prevention
- [ ] Test XSS prevention
- [ ] Verify CSRF protection (via session)

---

**Dokumentasi Teknis Selesai!** 🎯

Untuk pertanyaan lebih lanjut, lihat README.md dan SETUP.md