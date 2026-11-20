<?php
// UPDATE: edit a bill or record its payment (?pay=1 pre-selects "Paid")
require_once '../config/db.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$d = db_select($conn, "SELECT * FROM maintenance_bills WHERE bill_id = ?", 'i', [$id])->fetch_assoc();
if (!$d) {
    set_flash('error', 'Bill not found.');
    redirect('bills.php');
}
$d['bill_month'] = substr($d['bill_month'], 0, 7);   // 2026-11-01 -> 2026-11 for <input type="month">

if (isset($_GET['pay']) && $d['status'] === 'Unpaid') {
    $d['status'] = 'Paid';
    $d['paid_on'] = date('Y-m-d');
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$d, $errors] = validate_bill($conn);

    if (!$errors) {
        try {
            db_execute($conn,
                "UPDATE maintenance_bills
                 SET flat_id = ?, bill_month = ?, amount = ?, due_date = ?, status = ?, paid_on = ?, payment_mode = ?
                 WHERE bill_id = ?",
                'isdssssi',
                [$d['flat_id'], $d['bill_month_date'], $d['amount'], $d['due_date'], $d['status'], $d['paid_on'], $d['payment_mode'], $id]
            );
            set_flash('success', 'Bill updated successfully.');
            redirect('bills.php');
        } catch (mysqli_sql_exception $ex) {
            $errors[] = $ex->getCode() == 1062 ? 'This flat already has a bill for that month.' : 'Database error: ' . $ex->getMessage();
        }
    }
}

$base = '../'; $role = 'admin'; $page = 'bills'; $title = 'Edit Bill';
include '../includes/header.php';
?>
<div class="page-head"><div><h1><?= isset($_GET['pay']) ? 'Record Payment' : 'Edit Bill' ?></h1><p>Bill #<?= $id ?></p></div></div>
<div class="card">
    <?php show_errors($errors); $submitLabel = isset($_GET['pay']) ? 'Save Payment' : 'Update Bill'; include '_bill_form.php'; ?>
</div>
<?php include '../includes/footer.php'; ?>
