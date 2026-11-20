<?php
// UPDATE: edit flat details / reset its password
require_once '../config/db.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$d = db_select($conn, "SELECT * FROM flats WHERE flat_id = ?", 'i', [$id])->fetch_assoc();
if (!$d) {
    set_flash('error', 'Flat not found.');
    redirect('flats.php');
}
$isNew = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$d, $errors] = validate_flat(false);

    if (!$errors) {
        try {
            db_execute($conn,
                "UPDATE flats SET flat_no = ?, block = ?, floor_no = ?, flat_type = ?, area_sqft = ?, status = ?
                 WHERE flat_id = ?",
                'ssisisi',
                [$d['flat_no'], $d['block'], $d['floor_no'], $d['flat_type'], $d['area_sqft'], $d['status'], $id]
            );
            // Change password only if admin typed a new one
            if ($d['password'] !== '') {
                db_execute($conn, "UPDATE flats SET password = ? WHERE flat_id = ?", 'si',
                    [password_hash($d['password'], PASSWORD_DEFAULT), $id]);
            }
            set_flash('success', "Flat {$d['flat_no']} updated" . ($d['password'] !== '' ? ' and password changed.' : '.'));
            redirect('flats.php');
        } catch (mysqli_sql_exception $ex) {
            $errors[] = $ex->getCode() == 1062 ? 'Another flat already has this number.' : 'Database error: ' . $ex->getMessage();
        }
    }
}

$base = '../'; $role = 'admin'; $page = 'flats'; $title = 'Edit Flat';
include '../includes/header.php';
?>
<div class="page-head"><div><h1>Edit Flat <?= e($d['flat_no']) ?></h1><p>Update flat details or reset its login password</p></div></div>
<div class="card">
    <?php show_errors($errors); include '_flat_form.php'; ?>
</div>
<?php include '../includes/footer.php'; ?>
