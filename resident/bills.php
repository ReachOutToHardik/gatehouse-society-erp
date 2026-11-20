<?php
// RESIDENT: my maintenance bills (read only - payments are recorded by the office)
require_once '../config/db.php';
require_resident();

$fid    = (int)$_SESSION['flat_id'];
$status = $_GET['status'] ?? '';

$sql = "SELECT * FROM maintenance_bills WHERE flat_id = ?";
$types = 'i'; $params = [$fid];
if (in_array($status, $BILL_STATUS)) {
    $sql .= " AND status = ?"; $types .= 's'; $params[] = $status;
}
$bills = db_select($conn, $sql . " ORDER BY bill_month DESC", $types, $params);

$base = '../'; $role = 'resident'; $page = 'bills'; $title = 'My Bills';
include '../includes/header.php';
?>
<div class="page-head">
    <div><h1>My Maintenance Bills</h1><p>Flat <?= e($_SESSION['flat_no']) ?> &middot; Pay at the society office (Cash / UPI / Cheque)</p></div>
</div>

<div class="card">
    <form method="get" class="filter-bar">
        <div class="form-group" style="max-width:240px">
            <label for="status">Show</label>
            <select id="status" name="status" onchange="this.form.submit()">
                <option value="">All bills</option><?= options($BILL_STATUS, $status) ?>
            </select>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Month</th><th class="num">Amount</th><th>Due Date</th><th>Status</th><th>Paid On</th><th>Mode</th></tr></thead>
            <tbody>
            <?php if ($bills->num_rows === 0): ?>
                <tr><td colspan="6" class="empty">No bills found.</td></tr>
            <?php endif; ?>
            <?php $paid = 0; $due = 0;
            while ($b = $bills->fetch_assoc()):
                if ($b['status'] === 'Paid') $paid += $b['amount']; else $due += $b['amount'];
                $overdue = $b['status'] === 'Unpaid' && $b['due_date'] < date('Y-m-d'); ?>
                <tr>
                    <td><?= fmt_month($b['bill_month']) ?></td>
                    <td class="num"><?= money($b['amount']) ?></td>
                    <td><?= fmt_date($b['due_date']) ?> <?= $overdue ? '<span class="overdue">OVERDUE</span>' : '' ?></td>
                    <td><?= badge($b['status']) ?></td>
                    <td><?= fmt_date($b['paid_on']) ?></td>
                    <td><?= e($b['payment_mode'] ?: '-') ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
            <?php if ($bills->num_rows): ?>
            <tfoot><tr><td colspan="6">Paid: <?= money($paid) ?> &nbsp;&nbsp; | &nbsp;&nbsp; Pending: <span style="color:var(--red)"><?= money($due) ?></span></td></tr></tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
