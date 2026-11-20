<?php
// RESIDENT: READ + SEARCH my complaints
require_once '../config/db.php';
require_resident();

$fid    = (int)$_SESSION['flat_id'];
$q      = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';

// flat_id = ? makes sure a resident only ever sees their own complaints
$sql = "SELECT * FROM complaints WHERE flat_id = ?";
$types = 'i'; $params = [$fid];
if ($q !== '') { $sql .= " AND (subject LIKE ? OR description LIKE ?)"; $types .= 'ss'; array_push($params, "%$q%", "%$q%"); }
if (in_array($status, $COMPLAINT_STATUS)) { $sql .= " AND status = ?"; $types .= 's'; $params[] = $status; }
$complaints = db_select($conn, $sql . " ORDER BY created_at DESC", $types, $params);

$base = '../'; $role = 'resident'; $page = 'complaints'; $title = 'My Complaints';
include '../includes/header.php';
?>
<div class="page-head">
    <div><h1>My Complaints</h1><p>You can edit or delete a complaint while it is still Open.</p></div>
    <a href="complaint_add.php" class="btn">+ Raise a Complaint</a>
</div>

<div class="card">
    <form method="get" class="filter-bar">
        <div class="form-group">
            <label for="q">Search</label>
            <input type="text" id="q" name="q" value="<?= e($q) ?>" placeholder="Subject or details">
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
    <p class="count"><?= $complaints->num_rows ?> complaint(s)</p>
    <div class="table-wrap">
        <table>
            <thead><tr><th>#</th><th>Date</th><th>Category</th><th>Complaint</th><th>Status</th><th>Office Remarks</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if ($complaints->num_rows === 0): ?>
                <tr><td colspan="7" class="empty">No complaints found.</td></tr>
            <?php endif; ?>
            <?php while ($c = $complaints->fetch_assoc()): ?>
                <tr>
                    <td><?= $c['complaint_id'] ?></td>
                    <td><?= fmt_date($c['created_at']) ?></td>
                    <td><?= e($c['category']) ?></td>
                    <td class="wrap"><b><?= e($c['subject']) ?></b><br><small><?= e($c['description']) ?></small></td>
                    <td><?= badge($c['status']) ?></td>
                    <td class="wrap"><?= e($c['admin_remarks'] ?: '-') ?></td>
                    <td class="actions">
                        <?php if ($c['status'] === 'Open'): ?>
                            <a href="complaint_edit.php?id=<?= $c['complaint_id'] ?>">Edit</a>
                            <form method="post" action="complaint_delete.php" data-confirm="Withdraw this complaint?">
                                <input type="hidden" name="id" value="<?= $c['complaint_id'] ?>">
                                <button type="submit" class="link-btn danger">Delete</button>
                            </form>
                        <?php else: ?>
                            <span class="muted">Locked</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
