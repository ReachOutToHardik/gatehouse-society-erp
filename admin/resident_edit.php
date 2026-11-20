<?php
// UPDATE: edit a resident
require_once '../config/db.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$d = db_select($conn, "SELECT * FROM residents WHERE resident_id = ?", 'i', [$id])->fetch_assoc();
if (!$d) {
    set_flash('error', 'Resident not found.');
    redirect('residents.php');
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$d, $errors] = validate_resident($conn);

    if (!$errors) {
        db_execute($conn,
            "UPDATE residents SET flat_id = ?, name = ?, mobile = ?, email = ?, resident_type = ?, move_in_date = ?
             WHERE resident_id = ?",
            'isssssi',
            [$d['flat_id'], $d['name'], $d['mobile'], $d['email'] ?: null, $d['resident_type'], $d['move_in_date'], $id]
        );
        db_execute($conn, "UPDATE flats SET status = 'Occupied' WHERE flat_id = ?", 'i', [$d['flat_id']]);

        set_flash('success', "Resident '{$d['name']}' updated successfully.");
        redirect('residents.php');
    }
}

$base = '../'; $role = 'admin'; $page = 'residents'; $title = 'Edit Resident';
include '../includes/header.php';
?>
<div class="page-head"><div><h1>Edit Resident</h1><p>Update details of <?= e($d['name']) ?></p></div></div>
<div class="card">
    <?php show_errors($errors); $submitLabel = 'Update Resident'; include '_resident_form.php'; ?>
</div>
<?php include '../includes/footer.php'; ?>
