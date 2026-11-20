<?php
// RESIDENT UPDATE: edit my complaint (only while it is still Open)
require_once '../config/db.php';
require_resident();

$id  = (int)($_GET['id'] ?? 0);
$fid = (int)$_SESSION['flat_id'];

// Load only if it belongs to this flat and is still Open
$d = db_select($conn,
    "SELECT * FROM complaints WHERE complaint_id = ? AND flat_id = ? AND status = 'Open'",
    'ii', [$id, $fid])->fetch_assoc();
if (!$d) {
    set_flash('error', 'This complaint cannot be edited (not found or already being handled).');
    redirect('complaints.php');
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$d, $errors] = validate_complaint();

    if (!$errors) {
        db_execute($conn,
            "UPDATE complaints SET category = ?, subject = ?, description = ?
             WHERE complaint_id = ? AND flat_id = ? AND status = 'Open'",
            'sssii', [$d['category'], $d['subject'], $d['description'], $id, $fid]);
        set_flash('success', 'Complaint updated.');
        redirect('complaints.php');
    }
}

$base = '../'; $role = 'resident'; $page = 'complaints'; $title = 'Edit Complaint';
include '../includes/header.php';
?>
<div class="page-head"><div><h1>Edit Complaint #<?= $id ?></h1><p>You can change it until the office starts working on it.</p></div></div>
<div class="card">
    <?php show_errors($errors); $submitLabel = 'Update Complaint'; include '_complaint_form.php'; ?>
</div>
<?php include '../includes/footer.php'; ?>
