<?php
// REPORTS: unpaid maintenance, monthly collection, complaint summary
require_once '../config/db.php';
require_admin();

// ---- 1. Unpaid maintenance (defaulters) ----
$unpaid = $conn->query(
    "SELECT f.flat_no,
            (SELECT r.name FROM residents r WHERE r.flat_id = f.flat_id
               ORDER BY FIELD(r.resident_type, 'Owner', 'Tenant', 'Family Member') LIMIT 1) AS head,
            (SELECT r.mobile FROM residents r WHERE r.flat_id = f.flat_id
               ORDER BY FIELD(r.resident_type, 'Owner', 'Tenant', 'Family Member') LIMIT 1) AS mobile,
            GROUP_CONCAT(DATE_FORMAT(b.bill_month, '%b %Y') ORDER BY b.bill_month SEPARATOR ', ') AS months,
            COUNT(*) AS bill_count,
            SUM(b.amount) AS total_due
     FROM maintenance_bills b JOIN flats f ON f.flat_id = b.flat_id
     WHERE b.status = 'Unpaid'
     GROUP BY f.flat_id
     ORDER BY total_due DESC, f.flat_no")->fetch_all(MYSQLI_ASSOC);
$unpaidTotal = array_sum(array_column($unpaid, 'total_due'));

// ---- 2. Month-wise collection ----
$monthly = $conn->query(
    "SELECT bill_month,
            SUM(amount) AS billed,
            SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END) AS collected,
            SUM(CASE WHEN status = 'Unpaid' THEN amount ELSE 0 END) AS pending
     FROM maintenance_bills
     GROUP BY bill_month
     ORDER BY bill_month DESC");

// ---- 3. Complaint summary by category ----
$complaintSummary = $conn->query(
    "SELECT category,
            SUM(status = 'Open') AS open_count,
            SUM(status = 'In Progress') AS progress_count,
            SUM(status = 'Resolved') AS resolved_count,
            COUNT(*) AS total
     FROM complaints
     GROUP BY category
     ORDER BY total DESC")->fetch_all(MYSQLI_ASSOC);

// ---- 4. Open complaints list ----
$openList = $conn->query(
    "SELECT c.complaint_id, c.subject, c.category, c.status, c.created_at, f.flat_no,
            DATEDIFF(CURDATE(), DATE(c.created_at)) AS days_open
     FROM complaints c JOIN flats f ON f.flat_id = c.flat_id
     WHERE c.status <> 'Resolved'
     ORDER BY c.created_at");

$base = '../'; $role = 'admin'; $page = 'reports'; $title = 'Reports';
include '../includes/header.php';
?>
<div class="page-head">
    <div><h1>Society Reports</h1><p><?= e(SOCIETY_NAME) ?> &middot; Generated on <?= date('d M Y') ?></p></div>
    <button class="btn no-print" onclick="window.print()">Print Report</button>
</div>

<div class="card">
    <h2>1. Unpaid Maintenance Report</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Flat</th><th>Head of Flat</th><th>Mobile</th><th>Unpaid Months</th><th class="num">Bills</th><th class="num">Total Due</th></tr></thead>
            <tbody>
            <?php if (!$unpaid): ?>
                <tr><td colspan="6" class="empty">No unpaid maintenance. All dues are clear.</td></tr>
            <?php endif; ?>
            <?php foreach ($unpaid as $u): ?>
                <tr>
                    <td><b><?= e($u['flat_no']) ?></b></td>
                    <td><?= e($u['head'] ?: '-') ?></td>
                    <td><?= e($u['mobile'] ?: '-') ?></td>
                    <td><?= e($u['months']) ?></td>
                    <td class="num"><?= $u['bill_count'] ?></td>
                    <td class="num" style="color:var(--red)"><?= money($u['total_due']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <?php if ($unpaid): ?>
            <tfoot><tr><td colspan="5">Total pending (<?= count($unpaid) ?> flats)</td><td class="num"><?= money($unpaidTotal) ?></td></tr></tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <h2>2. Month-wise Collection</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Month</th><th class="num">Billed</th><th class="num">Collected</th><th class="num">Pending</th><th class="num">Collected %</th></tr></thead>
                <tbody>
                <?php while ($m = $monthly->fetch_assoc()): ?>
                    <tr>
                        <td><?= fmt_month($m['bill_month']) ?></td>
                        <td class="num"><?= money($m['billed']) ?></td>
                        <td class="num"><?= money($m['collected']) ?></td>
                        <td class="num"><?= money($m['pending']) ?></td>
                        <td class="num"><?= $m['billed'] > 0 ? round($m['collected'] * 100 / $m['billed']) : 0 ?>%</td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>3. Complaints by Category</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Category</th><th class="num">Open</th><th class="num">In Progress</th><th class="num">Resolved</th><th class="num">Total</th></tr></thead>
                <tbody>
                <?php if (!$complaintSummary): ?>
                    <tr><td colspan="5" class="empty">No complaints yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($complaintSummary as $c): ?>
                    <tr>
                        <td><?= e($c['category']) ?></td>
                        <td class="num"><?= $c['open_count'] ?></td>
                        <td class="num"><?= $c['progress_count'] ?></td>
                        <td class="num"><?= $c['resolved_count'] ?></td>
                        <td class="num"><b><?= $c['total'] ?></b></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <h2>4. Open Complaints</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>#</th><th>Raised On</th><th>Flat</th><th>Category</th><th>Subject</th><th>Status</th><th class="num">Days Open</th></tr></thead>
            <tbody>
            <?php if ($openList->num_rows === 0): ?>
                <tr><td colspan="7" class="empty">No open complaints.</td></tr>
            <?php endif; ?>
            <?php while ($o = $openList->fetch_assoc()): ?>
                <tr>
                    <td><?= $o['complaint_id'] ?></td>
                    <td><?= fmt_date($o['created_at']) ?></td>
                    <td><b><?= e($o['flat_no']) ?></b></td>
                    <td><?= e($o['category']) ?></td>
                    <td><?= e($o['subject']) ?></td>
                    <td><?= badge($o['status']) ?></td>
                    <td class="num"><?= $o['days_open'] ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
