<?php
// UPDATE: admin changes complaint status and adds remarks
require_once '../config/db.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$c = db_select($conn,
    "SELECT c.*, f.flat_no FROM complaints c JOIN flats f ON f.flat_id = c.flat_id WHERE c.complaint_id = ?",
    'i', [$id])->fetch_assoc();
if (!$c) {
    set_flash('error', 'Complaint not found.');
    redirect('complaints.php');
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status  = post('status');
    $remarks = post('admin_remarks');

    if (!in_array($status, $COMPLAINT_STATUS)) $errors[] = 'Please select a valid status.';
    if (strlen($remarks) > 255) $errors[] = 'Remarks can be at most 255 characters.';
    if ($status === 'Resolved' && $remarks === '') $errors[] = 'Please write what was done before marking it Resolved.';

    if (!$errors) {
        // Record the resolved date when status becomes Resolved; clear it otherwise
        $resolvedOn = $status === 'Resolved' ? ($c['resolved_on'] ?: date('Y-m-d')) : null;
        db_execute($conn,
            "UPDATE complaints SET status = ?, admin_remarks = ?, resolved_on = ? WHERE complaint_id = ?",
            'sssi', [$status, $remarks ?: null, $resolvedOn, $id]);
        set_flash('success', "Complaint #$id updated to '$status'.");
        redirect('complaints.php');
    }
    $c['status'] = $status;
    $c['admin_remarks'] = $remarks;
}

$base = '../'; $role = 'admin'; $page = 'complaints'; $title = 'Update Complaint';
include '../includes/header.php';
?>
<div class="page-head"><div><h1>Update Complaint #<?= $id ?></h1><p>From flat <?= e($c['flat_no']) ?> on <?= fmt_date($c['created_at']) ?></p></div></div>

<div class="grid-2">
    <div class="card">
        <h2>Complaint Details</h2>
        <div class="details">
            <div>Flat</div><div><?= e($c['flat_no']) ?></div>
            <div>Category</div><div><?= e($c['category']) ?></div>
            <div>Subject</div><div><?= e($c['subject']) ?></div>
            <div>Description</div><div><?= nl2br(e($c['description'])) ?></div>
            <div>Raised On</div><div><?= fmt_date($c['created_at']) ?></div>
        </div>
    </div>
    <div class="card">
        <h2>Action Taken</h2>
        <?php show_errors($errors); ?>
        <form method="post" class="validate" novalidate>
            <div class="form-group" style="margin-bottom:14px">
                <label for="status">Status <span class="req">*</span></label>
                <select id="status" name="status" required><?= options($COMPLAINT_STATUS, $c['status']) ?></select>
            </div>
            <div class="form-group">
                <label for="admin_remarks">Office Remarks</label>
                <textarea id="admin_remarks" name="admin_remarks" rows="4" maxlength="255" placeholder="e.g. Plumber visited and fixed the leak"><?= e($c['admin_remarks']) ?></textarea>
                <div class="hint">The resident sees these remarks in their portal.</div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">Save</button>
                <a href="complaints.php" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
