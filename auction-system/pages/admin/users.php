<?php
/**
 * Halaman Kelola User (Admin)
 */

$page_title = 'Kelola User';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../functions/helpers.php';

requireLogin();

$user = getUserSession();

// Check if admin
if ($user['role'] !== 'admin') {
    setFlashMessage('error', 'Anda tidak memiliki akses ke halaman ini');
    header('Location: ' . APP_URL . '/pages/dashboard/index.php');
    exit;
}

$page = intval($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;
$search = sanitize($_GET['search'] ?? '');

// Get users
$query = "SELECT id, username, email, full_name, phone, role, is_active, created_at FROM users";
$params = [];

if (!empty($search)) {
    $query .= " WHERE username LIKE ? OR email LIKE ? OR full_name LIKE ?";
    $search_term = '%' . $search . '%';
    $params = [$search_term, $search_term, $search_term];
}

$query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$users = getRows($conn, $query, $params);

// Count
$count_query = "SELECT COUNT(*) as total FROM users";
if (!empty($search)) {
    $count_query .= " WHERE username LIKE ? OR email LIKE ? OR full_name LIKE ?";
    $count_params = [$search_term, $search_term, $search_term];
} else {
    $count_params = [];
}

$count_result = getRow($conn, $count_query, $count_params);
$total = $count_result['total'] ?? 0;
$total_pages = ceil($total / $limit);

// Handle toggle status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $user_id = intval($_POST['user_id'] ?? 0);
    
    if ($user_id && $user_id !== $user['id']) {
        try {
            $target_user = getUserById($conn, $user_id);
            $new_status = $target_user['is_active'] ? 0 : 1;
            
            executeQuery(
                $conn,
                "UPDATE users SET is_active = ? WHERE id = ?",
                [$new_status, $user_id]
            );
            
            $status_text = $new_status ? 'diaktifkan' : 'dinonaktifkan';
            setFlashMessage('success', 'User berhasil ' . $status_text);
        } catch (Exception $e) {
            setFlashMessage('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}
?>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
            👥 Kelola User
        </h1>
        <p style="color: #999;">Total user: <strong><?php echo $total; ?></strong></p>
    </div>
</div>

<!-- Search -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-body">
        <form method="GET" action="">
            <div style="display: flex; gap: 1rem;">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Cari username, email, atau nama..."
                    value="<?php echo htmlspecialchars($search); ?>"
                    style="flex: 1;"
                >
                <button type="submit" class="btn btn-primary">
                    🔍 Cari
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Nama Lengkap</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Terdaftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span class="badge badge-primary">Admin</span>
                            <?php else: ?>
                                <span class="badge badge-info">User</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['is_active']): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatDate($u['created_at']); ?></td>
                        <td>
                            <?php if ($u['id'] !== $user['id']): ?>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <button type="submit" class="btn btn-sm <?php echo $u['is_active'] ? 'btn-danger' : 'btn-success'; ?>" onclick="return confirm('Apakah Anda yakin?');">
                                        <?php echo $u['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span style="color: #999; font-size: 0.85rem;">Akun Anda</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">« Pertama</a>
            <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">‹ Sebelumnya</a>
        <?php endif; ?>

        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <?php if ($i === $page): ?>
                <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Selanjutnya ›</a>
            <a href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Terakhir »</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>