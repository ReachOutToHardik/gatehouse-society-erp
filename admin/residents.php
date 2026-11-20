<?php
// READ + SEARCH: residents with their flat (JOIN)
require_once '../config/db.php';
require_admin();

$q      = trim($_GET['q'] ?? '');
$rtype  = $_GET['resident_type'] ?? '';
$flatId = (int)($_GET['flat_id'] ?? 0);

$where = []; $types = ''; $params = [];
if ($q !== '') {
    $where[] = "(r.name LIKE ? OR r.mobile LIKE ? OR r.email LIKE ? OR f.flat_no LIKE ?)";
    $types .= 'ssss'; array_push($params, "%$q%", "%$q%", "%$q%", "%$q%");
}
if ($rtype !== '') { $where[] = "r.resident_type = ?"; $types .= 's'; $params[] = $rtype; }
if ($flatId > 0)   { $where[] = "r.flat_id = ?";       $types .= 'i'; $params[] = $flatId; }

$sql = "SELECT r.*, f.flat_no
        FROM residents r JOIN flats f ON f.flat_id = r.flat_id"
     . ($where ? " WHERE " . implode(' AND ', $where) : '')
     . " ORDER BY f.flat_no, FIELD(r.resident_type, 'Owner', 'Tenant', 'Family Member'), r.name";
$residents = db_select($conn, $sql, $types, $params);

$base = '../'; $role = 'admin'; $page = 'residents'; $title = 'Residents';
include '../includes/header.php';
?>
<div class="page-head">
    <div><h1>Residents</h1><p>Owners, tenants and family members living in the society</p></div>
    <a href="resident_add.php<?= $flatId ? '?flat_id=' . $flatId : '' ?>" class="btn">+ Add Resident</a>
</div>

<div class="card">
    <form method="get" class="filter-bar">
        <?php if ($flatId): ?><input type="hidden" name="flat_id" value="<?= $flatId ?>"><?php endif; ?>
        <div class="form-group">
            <label for="q">Search</label>
            <input type="text" id="q" name="q" value="<?= e($q) ?>" placeholder="Name, mobile, email or flat no">
        </div>
        <div class="form-group">
            <label for="resident_type">Type</label>
            <select id="resident_type" name="resident_type"><option value="">All</option><?= options($RESIDENT_TYPES, $rtype) ?></select>
        </div>
        <div class="btns">
            <button class="btn" type="submit">Search</button>
            <a class="btn btn-light" href="residents.php">Reset</a>
        </div>
    </form>
    <?php if ($flatId): ?><p class="hint" style="margin-top:8px">Showing residents of one flat only. Click Reset to see everyone.</p><?php endif; ?>
</div>

<div class="card">
    <p class="count"><?= $residents->num_rows ?> resident(s) found</p>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Flat</th><th>Name</th><th>Type</th><th>Mobile</th><th>Email</th><th>Moved In</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if ($residents->num_rows === 0): ?>
                <tr><td colspan="7" class="empty">No residents found.</td></tr>
            <?php endif; ?>
            <?php while ($r = $residents->fetch_assoc()): ?>
                <tr>
                    <td><b><?= e($r['flat_no']) ?></b></td>
                    <td><?= e($r['name']) ?></td>
                    <td><?= e($r['resident_type']) ?></td>
                    <td><?= e($r['mobile']) ?></td>
                    <td><?= e($r['email'] ?: '-') ?></td>
                    <td><?= fmt_date($r['move_in_date']) ?></td>
                    <td class="actions">
                        <a href="resident_edit.php?id=<?= $r['resident_id'] ?>">Edit</a>
                        <form method="post" action="resident_delete.php" data-confirm="Remove <?= e($r['name']) ?> from the records?">
                            <input type="hidden" name="id" value="<?= $r['resident_id'] ?>">
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
