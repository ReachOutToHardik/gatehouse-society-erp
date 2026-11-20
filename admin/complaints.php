<?php
// READ + SEARCH: all complaints raised by flats
require_once '../config/db.php';
require_admin();

$q        = trim($_GET['q'] ?? '');
$status   = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';

$where = []; $types = ''; $params = [];
if ($q !== '') {
    $where[] = "(c.subject LIKE ? OR c.description LIKE ? OR f.flat_no LIKE ?)";
    $types .= 'sss'; array_push($params, "%$q%", "%$q%", "%$q%");
}
if ($status !== '')   { $where[] = "c.status = ?";   $types .= 's'; $params[] = $status; }
if ($category !== '') { $where[] = "c.category = ?"; $types .= 's'; $params[] = $category; }

$sql = "SELECT c.*, f.flat_no
        FROM complaints c JOIN flats f ON f.flat_id = c.flat_id"
     . ($where ? " WHERE " . implode(' AND ', $where) : '')
     . " ORDER BY FIELD(c.status, 'Open', 'In Progress', 'Resolved'), c.created_at DESC";
$complaints = db_select($conn, $sql, $types, $params);

$base = '../'; $role = 'admin'; $page = 'complaints'; $title = 'Complaints';
include '../includes/header.php';
?>
<div class="page-head">
    <div><h1>Complaints</h1><p>Complaints raised by residents. Open ones are shown first.</p></div>
</div>

<div class="card">
    <form method="get" class="filter-bar">
        <div class="form-group">
            <label for="q">Search</label>
            <input type="text" id="q" name="q" value="<?= e($q) ?>" placeholder="Subject, details or flat no">
        </div>
        <div class="form-group">
            <label for="category">Category</label>
            <select id="category" name="category"><option value="">All</option><?= options($CATEGORIES, $category) ?></select>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status"><option value="">All</option><?= options($COMPLAINT_STATUS, $status) ?></select>
        </div>
        <div class="btns">
            <button class="btn" type="submit">Search</button>
            <a class="btn btn-light" href="complaints.php">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <p class="count"><?= $complaints->num_rows ?> complaint(s) found</p>
    <div class="table-wrap">
        <table>
            <thead><tr><th>#</th><th>Date</th><th>Flat</th><th>Category</th><th>Complaint</th><th>Status</th><th>Office Remarks</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if ($complaints->num_rows === 0): ?>
                <tr><td colspan="8" class="empty">No complaints found.</td></tr>
            <?php endif; ?>
            <?php while ($c = $complaints->fetch_assoc()): ?>
                <tr>
                    <td><?= $c['complaint_id'] ?></td>
                    <td><?= fmt_date($c['created_at']) ?></td>
                    <td><b><?= e($c['flat_no']) ?></b></td>
                    <td><?= e($c['category']) ?></td>
                    <td class="wrap"><b><?= e($c['subject']) ?></b><br><small><?= e($c['description']) ?></small></td>
                    <td><?= badge($c['status']) ?>
                        <?php if ($c['resolved_on']): ?><br><small><?= fmt_date($c['resolved_on']) ?></small><?php endif; ?></td>
                    <td class="wrap"><?= e($c['admin_remarks'] ?: '-') ?></td>
                    <td class="actions">
                        <a href="complaint_edit.php?id=<?= $c['complaint_id'] ?>">Update</a>
                        <form method="post" action="complaint_delete.php" data-confirm="Delete complaint #<?= $c['complaint_id'] ?>?">
                            <input type="hidden" name="id" value="<?= $c['complaint_id'] ?>">
                            <button type="submit" class="link-btn danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
