<?php
// RESIDENT DASHBOARD: details of the logged-in flat only
require_once '../config/db.php';
require_resident();

$fid  = (int)$_SESSION['flat_id'];
$flat = db_select($conn, "SELECT * FROM flats WHERE flat_id = ?", 'i', [$fid])->fetch_assoc();

$members = db_select($conn,
    "SELECT name, resident_type, mobile, email, move_in_date FROM residents
     WHERE flat_id = ? ORDER BY FIELD(resident_type, 'Owner', 'Tenant', 'Family Member'), name", 'i', [$fid]);

$dueAmount  = db_value($conn, "SELECT COALESCE(SUM(amount), 0) FROM maintenance_bills WHERE flat_id = ? AND status = 'Unpaid'", 'i', [$fid]);
$dueCount   = db_value($conn, "SELECT COUNT(*) FROM maintenance_bills WHERE flat_id = ? AND status = 'Unpaid'", 'i', [$fid]);
$lastPaid   = db_select($conn, "SELECT amount, paid_on, bill_month FROM maintenance_bills WHERE flat_id = ? AND status = 'Paid' ORDER BY paid_on DESC LIMIT 1", 'i', [$fid])->fetch_assoc();
$openCount  = db_value($conn, "SELECT COUNT(*) FROM complaints WHERE flat_id = ? AND status <> 'Resolved'", 'i', [$fid]);

$recentComplaints = db_select($conn,
    "SELECT complaint_id, subject, status, admin_remarks, created_at FROM complaints
     WHERE flat_id = ? ORDER BY created_at DESC LIMIT 4", 'i', [$fid]);

$base = '../'; $role = 'resident'; $page = 'dashboard'; $title = 'My Flat';
include '../includes/header.php';
?>
<div class="page-head">
    <div><h1>Flat <?= e($flat['flat_no']) ?></h1><p>Welcome to your resident portal</p></div>
    <a href="complaint_add.php" class="btn">+ Raise a Complaint</a>
</div>

<?php if ($dueCount > 0): ?>
    <div class="alert alert-error">
        You have <?= $dueCount ?> unpaid maintenance bill(s) totalling <b><?= money($dueAmount) ?></b>.
        Please pay at the society office. <a href="bills.php">View bills</a>
    </div>
<?php endif; ?>

<div class="stats">
    <div class="stat <?= $dueAmount > 0 ? 'bad' : 'good' ?>"><div class="label">Pending Dues</div><div class="value"><?= money($dueAmount) ?></div></div>
    <div class="stat">
        <div class="label">Last Payment</div>
        <?php if ($lastPaid): ?>
            <div class="value"><?= money($lastPaid['amount']) ?></div>
            <div class="sub">Paid on <?= fmt_date($lastPaid['paid_on']) ?> &middot; <?= fmt_month($lastPaid['bill_month']) ?> bill</div>
        <?php else: ?>
            <div class="value">-</div>
            <div class="sub">No payments yet</div>
        <?php endif; ?>
    </div>
    <div class="stat"><div class="label">Open Complaints</div><div class="value"><?= $openCount ?></div></div>
    <div class="stat"><div class="label">Members</div><div class="value"><?= $members->num_rows ?></div></div>
</div>

<div class="grid-2">
    <div class="card">
        <h2>Flat Details</h2>
        <div class="details">
            <div>Flat ID</div><div><b><?= e($flat['flat_no']) ?></b></div>
            <div>Block / Floor</div><div>Block <?= e($flat['block']) ?>, Floor <?= e($flat['floor_no']) ?></div>
            <div>Type</div><div><?= e($flat['flat_type']) ?></div>
            <div>Area</div><div><?= e($flat['area_sqft']) ?> sq.ft</div>
            <div>Monthly Maintenance</div><div><?= money($RATES[$flat['flat_type']]) ?></div>
            <div>Status</div><div><?= badge($flat['status']) ?></div>
        </div>
    </div>

    <div class="card">
        <h2>Household Members</h2>
        <p class="hint" style="margin:-8px 0 12px">To add or change members, contact the society office.</p>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Type</th><th>Mobile</th></tr></thead>
                <tbody>
                <?php if ($members->num_rows === 0): ?>
                    <tr><td colspan="3" class="empty">No members registered. Contact the society office.</td></tr>
                <?php endif; ?>
                <?php while ($m = $members->fetch_assoc()): ?>
                    <tr><td><?= e($m['name']) ?></td><td><?= e($m['resident_type']) ?></td><td><?= e($m['mobile']) ?></td></tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <h2>My Recent Complaints</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Subject</th><th>Status</th><th>Office Remarks</th></tr></thead>
            <tbody>
            <?php if ($recentComplaints->num_rows === 0): ?>
                <tr><td colspan="4" class="empty">You have not raised any complaints.</td></tr>
            <?php endif; ?>
            <?php while ($c = $recentComplaints->fetch_assoc()): ?>
                <tr>
                    <td><?= fmt_date($c['created_at']) ?></td>
                    <td><?= e($c['subject']) ?></td>
                    <td><?= badge($c['status']) ?></td>
                    <td class="wrap"><?= e($c['admin_remarks'] ?: '-') ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
