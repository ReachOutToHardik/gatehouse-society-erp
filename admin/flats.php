<?php
// READ + SEARCH: list of all flats
require_once '../config/db.php';
require_admin();

$q      = trim($_GET['q'] ?? '');
$block  = $_GET['block'] ?? '';
$type   = $_GET['flat_type'] ?? '';
$status = $_GET['status'] ?? '';

// Build WHERE clause from the filters that were used
$where = []; $types = ''; $params = [];
if ($q !== '') {
    $where[] = "(f.flat_no LIKE ? OR EXISTS (SELECT 1 FROM residents r WHERE r.flat_id = f.flat_id AND r.name LIKE ?))";
    $types .= 'ss'; array_push($params, "%$q%", "%$q%");
}
if ($block !== '')  { $where[] = "f.block = ?";     $types .= 's'; $params[] = $block; }
if ($type !== '')   { $where[] = "f.flat_type = ?"; $types .= 's'; $params[] = $type; }
if ($status !== '') { $where[] = "f.status = ?";    $types .= 's'; $params[] = $status; }

$sql = "SELECT f.*,
          (SELECT COUNT(*) FROM residents r WHERE r.flat_id = f.flat_id) AS members,
          (SELECT r.name FROM residents r WHERE r.flat_id = f.flat_id
             ORDER BY FIELD(r.resident_type, 'Owner', 'Tenant', 'Family Member') LIMIT 1) AS head,
          (SELECT COALESCE(SUM(b.amount), 0) FROM maintenance_bills b
             WHERE b.flat_id = f.flat_id AND b.status = 'Unpaid') AS dues
        FROM flats f"
     . ($where ? " WHERE " . implode(' AND ', $where) : '')
     . " ORDER BY f.flat_no";
$flats  = db_select($conn, $sql, $types, $params);
$blocks = $conn->query("SELECT DISTINCT block FROM flats ORDER BY block");

$base = '../'; $role = 'admin'; $page = 'flats'; $title = 'Flats';
include '../includes/header.php';
?>
<div class="page-head">
    <div><h1>Flats</h1><p>All flats in the society with their login IDs</p></div>
    <a href="flat_add.php" class="btn">+ Add Flat</a>
</div>

<div class="card">
    <form method="get" class="filter-bar">
        <div class="form-group">
            <label for="q">Search</label>
            <input type="text" id="q" name="q" value="<?= e($q) ?>" placeholder="Flat no or resident name">
        </div>
        <div class="form-group">
            <label for="block">Block</label>
            <select id="block" name="block">
                <option value="">All</option>
                <?php while ($b = $blocks->fetch_assoc()): ?>
                    <option <?= $block === $b['block'] ? 'selected' : '' ?>><?= e($b['block']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="flat_type">Type</label>
            <select id="flat_type" name="flat_type"><option value="">All</option><?= options($FLAT_TYPES, $type) ?></select>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status"><option value="">All</option><?= options($FLAT_STATUS, $status) ?></select>
        </div>
        <div class="btns">
            <button class="btn" type="submit">Search</button>
            <a class="btn btn-light" href="flats.php">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <p class="count"><?= $flats->num_rows ?> flat(s) found</p>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Flat ID</th><th>Block</th><th>Floor</th><th>Type</th><th class="num">Area (sq.ft)</th>
                    <th>Status</th><th>Head of Flat</th><th class="num">Members</th><th class="num">Pending Dues</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if ($flats->num_rows === 0): ?>
                <tr><td colspan="10" class="empty">No flats found.</td></tr>
            <?php endif; ?>
            <?php while ($f = $flats->fetch_assoc()): ?>
                <tr>
                    <td><b><?= e($f['flat_no']) ?></b></td>
                    <td><?= e($f['block']) ?></td>
                    <td><?= e($f['floor_no']) ?></td>
                    <td><?= e($f['flat_type']) ?></td>
                    <td class="num"><?= e($f['area_sqft']) ?></td>
                    <td><?= badge($f['status']) ?></td>
                    <td><?= e($f['head'] ?: '-') ?></td>
                    <td class="num"><a href="residents.php?flat_id=<?= $f['flat_id'] ?>"><?= $f['members'] ?></a></td>
                    <td class="num"><?= $f['dues'] > 0 ? '<span style="color:var(--red)">' . money($f['dues']) . '</span>' : '-' ?></td>
                    <td class="actions">
                        <a href="flat_edit.php?id=<?= $f['flat_id'] ?>">Edit</a>
                        <form method="post" action="flat_delete.php"
                              data-confirm="Delete flat <?= e($f['flat_no']) ?>? Its residents, bills and complaints will also be deleted.">
                            <input type="hidden" name="id" value="<?= $f['flat_id'] ?>">
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
