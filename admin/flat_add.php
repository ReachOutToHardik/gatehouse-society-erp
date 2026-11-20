<?php
// CREATE: add a new flat and set its login password
require_once '../config/db.php';
require_admin();

$isNew = true;
$d = ['flat_no' => '', 'block' => '', 'floor_no' => '', 'flat_type' => '', 'area_sqft' => '', 'status' => 'Vacant'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$d, $errors] = validate_flat(true);

    if (!$errors) {
        try {
            // Never store plain passwords - store a hash
            $hash = password_hash($d['password'], PASSWORD_DEFAULT);
            db_execute($conn,
                "INSERT INTO flats (flat_no, password, block, floor_no, flat_type, area_sqft, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                'sssisis',
                [$d['flat_no'], $hash, $d['block'], $d['floor_no'], $d['flat_type'], $d['area_sqft'], $d['status']]
            );
            set_flash('success', "Flat {$d['flat_no']} added. Login ID: {$d['flat_no']}");
            redirect('flats.php');
        } catch (mysqli_sql_exception $ex) {
            $errors[] = $ex->getCode() == 1062 ? 'Flat ' . $d['flat_no'] . ' already exists.' : 'Database error: ' . $ex->getMessage();
        }
    }
}

$base = '../'; $role = 'admin'; $page = 'flats'; $title = 'Add Flat';
include '../includes/header.php';
?>
<div class="page-head"><div><h1>Add Flat</h1><p>Register a flat and create its login</p></div></div>
<div class="card">
    <?php show_errors($errors); include '_flat_form.php'; ?>
</div>
<?php include '../includes/footer.php'; ?>
