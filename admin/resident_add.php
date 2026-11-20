<?php
// CREATE: add a resident to a flat
require_once '../config/db.php';
require_admin();

$d = ['flat_id' => $_GET['flat_id'] ?? '', 'name' => '', 'mobile' => '', 'email' => '',
      'resident_type' => '', 'move_in_date' => date('Y-m-d')];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$d, $errors] = validate_resident($conn);

    if (!$errors) {
        db_execute($conn,
            "INSERT INTO residents (flat_id, name, mobile, email, resident_type, move_in_date)
             VALUES (?, ?, ?, ?, ?, ?)",
            'isssss',
            [$d['flat_id'], $d['name'], $d['mobile'], $d['email'] ?: null, $d['resident_type'], $d['move_in_date']]
        );
        // Business rule: a flat with a resident is no longer vacant
        db_execute($conn, "UPDATE flats SET status = 'Occupied' WHERE flat_id = ?", 'i', [$d['flat_id']]);

        set_flash('success', "Resident '{$d['name']}' added successfully.");
        redirect('residents.php');
    }
}

$base = '../'; $role = 'admin'; $page = 'residents'; $title = 'Add Resident';
include '../includes/header.php';
?>
<div class="page-head"><div><h1>Add Resident</h1><p>Add an owner, tenant or family member</p></div></div>
<div class="card">
    <?php show_errors($errors); $submitLabel = 'Save Resident'; include '_resident_form.php'; ?>
</div>
<?php include '../includes/footer.php'; ?>
