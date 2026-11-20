<?php
// ADMIN DASHBOARD: overview of the society
require_once '../config/db.php';
require_admin();

$totalFlats    = db_value($conn, "SELECT COUNT(*) FROM flats");
$occupied      = db_value($conn, "SELECT COUNT(*) FROM flats WHERE status = 'Occupied'");
$residents     = db_value($conn, "SELECT COUNT(*) FROM residents");
$unpaidCount   = db_value($conn, "SELECT COUNT(*) FROM maintenance_bills WHERE status = 'Unpaid'");
$unpaidAmount  = db_value($conn, "SELECT COALESCE(SUM(amount), 0) FROM maintenance_bills WHERE status = 'Unpaid'");
$collectedThisMonth = db_value($conn,
    "SELECT COALESCE(SUM(amount), 0) FROM maintenance_bills
     WHERE status = 'Paid' AND DATE_FORMAT(paid_on, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')");
$openComplaints = db_value($conn, "SELECT COUNT(*) FROM complaints WHERE status <> 'Resolved'");

$pending = $conn->query(
    "SELECT c.complaint_id, c.subject, c.category, c.status, c.created_at, f.flat_no
     FROM complaints c JOIN flats f ON f.flat_id = c.flat_id
     WHERE c.status <> 'Resolved'
     ORDER BY c.created_at DESC LIMIT 6");

$defaulters = $conn->query(
    "SELECT f.flat_no, COUNT(*) AS months, SUM(b.amount) AS due
     FROM maintenance_bills b JOIN flats f ON f.flat_id = b.flat_id
     WHERE b.status = 'Unpaid'
     GROUP BY f.flat_id
     ORDER BY due DESC LIMIT 6");

$base = '../'; $role = 'admin'; $page = 'dashboard'; $title = 'Admin Dashboard';
include '../includes/header.php';
?>
<div class="page-head">
    <div><h1>Dashboard</h1><p>Welcome, <?= e($_SESSION['admin_name']) ?>. Here is today's summary.</p></div>
    <div class="actions">
        <a href="flat_add.php" class="btn btn-light">+ Flat</a>
        <a href="resident_add.php" class="btn btn-light">+ Resident</a>
        <a href="bill_generate.php" class="btn">Generate Bills</a>
    </div>
</div>

<div class="stats">
    <div class="stat"><div class="label">Total Flats</div><div class="value"><?= $totalFlats ?></div></div>
    <div class="stat"><div class="label">Occupied / Vacant</div><div class="value"><?= $occupied ?> / <?= $totalFlats - $occupied ?></div></div>
    <div class="stat"><div class="label">Residents</div><div class="value"><?= $residents ?></div></div>
    <div class="stat bad"><div class="label">Pending Dues</div><div class="value"><?= money($unpaidAmount) ?></div><div class="sub"><?= $unpaidCount ?> unpaid bills</div></div>
    <div class="stat good"><div class="label">Collected This Month</div><div class="value"><?= money($collectedThisMonth) ?></div></div>
    <div class="stat bad"><div class="label">Open Complaints</div><div class="value"><?= $openComplaints ?></div></div>
</div>

<div class="grid-2">
    <div class="card">
        <h2>Pending Complaints</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Date</th><th>Flat</th><th>Complaint</th><th>Status</th></tr></thead>
                <tbody>
                <?php if ($pending->num_rows === 0): ?>
                    <tr><td colspan="4" class="empty">No pending complaints.</td></tr>
                <?php endif; ?>
                <?php while ($c = $pending->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d M', strtotime($c['created_at'])) ?></td>
                        <td><?= e($c['flat_no']) ?></td>
                        <td class="wrap" style="min-width:160px"><a href="complaint_edit.php?id=<?= $c['complaint_id'] ?>"><?= e($c['subject']) ?></a><br><small><?= e($c['category']) ?></small></td>
                        <td><?= badge($c['status']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>Maintenance Defaulters</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Flat</th><th class="num">Unpaid Months</th><th class="num">Amount Due</th><th></th></tr></thead>
                <tbody>
                <?php if ($defaulters->num_rows === 0): ?>
                    <tr><td colspan="4" class="empty">All bills are paid.</td></tr>
                <?php endif; ?>
                <?php while ($d = $defaulters->fetch_assoc()): ?>
                    <tr>
                        <td><b><?= e($d['flat_no']) ?></b></td>
                        <td class="num"><?= $d['months'] ?></td>
                        <td class="num" style="color:var(--red)"><?= money($d['due']) ?></td>
                        <td><a href="bills.php?q=<?= urlencode($d['flat_no']) ?>&status=Unpaid">View bills</a></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
