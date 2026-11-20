<?php
// READ + SEARCH: maintenance bills
require_once '../config/db.php';
require_admin();

$q      = trim($_GET['q'] ?? '');
$month  = $_GET['month'] ?? '';          // YYYY-MM
$status = $_GET['status'] ?? '';

$where = []; $types = ''; $params = [];
if ($q !== '')     { $where[] = "f.flat_no LIKE ?"; $types .= 's'; $params[] = "%$q%"; }
if (preg_match('/^\d{4}-\d{2}$/', $month)) {
    $where[] = "b.bill_month = ?"; $types .= 's'; $params[] = $month . '-01';
}
if ($status !== '') { $where[] = "b.status = ?"; $types .= 's'; $params[] = $status; }

$sql = "SELECT b.*, f.flat_no, f.flat_type
        FROM maintenance_bills b JOIN flats f ON f.flat_id = b.flat_id"
     . ($where ? " WHERE " . implode(' AND ', $where) : '')
     . " ORDER BY b.bill_month DESC, f.flat_no";
$bills = db_select($conn, $sql, $types, $params);

$base = '../'; $role = 'admin'; $page = 'bills'; $title = 'Maintenance Bills';
include '../includes/header.php';
?>
<div class="page-head">
    <div><h1>Maintenance Bills</h1><p>Monthly maintenance charges for each flat</p></div>
    <div class="actions">
        <a href="bill_generate.php" class="btn btn-light">Generate Monthly Bills</a>
        <a href="bill_add.php" class="btn">+ Add Bill</a>
    </div>
</div>

<div class="card">
    <form method="get" class="filter-bar">
        <div class="form-group">
            <label for="q">Flat No</label>
            <input type="text" id="q" name="q" value="<?= e($q) ?>" placeholder="e.g. A-101">
        </div>
        <div class="form-group">
            <label for="month">Month</label>
            <input type="month" id="month" name="month" value="<?= e($month) ?>">
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status"><option value="">All</option><?= options($BILL_STATUS, $status) ?></select>
        </div>
        <div class="btns">
            <button class="btn" type="submit">Search</button>
            <a class="btn btn-light" href="bills.php">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <p class="count"><?= $bills->num_rows ?> bill(s) found</p>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Month</th><th>Flat</th><th>Type</th><th class="num">Amount</th><th>Due Date</th>
                    <th>Status</th><th>Paid On</th><th>Mode</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if ($bills->num_rows === 0): ?>
                <tr><td colspan="9" class="empty">No bills found.</td></tr>
            <?php endif; ?>
            <?php $total = 0; $unpaid = 0;
            while ($b = $bills->fetch_assoc()):
                $total += $b['amount'];
                if ($b['status'] === 'Unpaid') $unpaid += $b['amount'];
                $overdue = $b['status'] === 'Unpaid' && $b['due_date'] < date('Y-m-d'); ?>
                <tr>
                    <td><?= fmt_month($b['bill_month']) ?></td>
                    <td><b><?= e($b['flat_no']) ?></b></td>
                    <td><?= e($b['flat_type']) ?></td>
                    <td class="num"><?= money($b['amount']) ?></td>
                    <td><?= fmt_date($b['due_date']) ?> <?= $overdue ? '<span class="overdue">OVERDUE</span>' : '' ?></td>
                    <td><?= badge($b['status']) ?></td>
                    <td><?= fmt_date($b['paid_on']) ?></td>
                    <td><?= e($b['payment_mode'] ?: '-') ?></td>
                    <td class="actions">
                        <?php if ($b['status'] === 'Unpaid'): ?>
                            <a href="bill_edit.php?id=<?= $b['bill_id'] ?>&pay=1" style="color:var(--green)">Mark Paid</a>
                        <?php endif; ?>
                        <a href="bill_edit.php?id=<?= $b['bill_id'] ?>">Edit</a>
                        <form method="post" action="bill_delete.php" data-confirm="Delete this bill?">
                            <input type="hidden" name="id" value="<?= $b['bill_id'] ?>">
                            <button type="submit" class="link-btn danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
            <?php if ($bills->num_rows): ?>
            <tfoot>
                <tr><td colspan="3">Total billed</td><td class="num"><?= money($total) ?></td>
                    <td colspan="5">Unpaid: <span style="color:var(--red)"><?= money($unpaid) ?></span></td></tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
