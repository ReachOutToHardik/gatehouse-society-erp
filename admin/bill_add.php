<?php
// CREATE: add a single maintenance bill
require_once '../config/db.php';
require_admin();

$d = ['flat_id' => '', 'bill_month' => date('Y-m'), 'amount' => '', 'due_date' => date('Y-m-10'),
      'status' => 'Unpaid', 'paid_on' => '', 'payment_mode' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$d, $errors] = validate_bill($conn);

    if (!$errors) {
        try {
            db_execute($conn,
                "INSERT INTO maintenance_bills (flat_id, bill_month, amount, due_date, status, paid_on, payment_mode)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                'isdssss',
                [$d['flat_id'], $d['bill_month_date'], $d['amount'], $d['due_date'], $d['status'], $d['paid_on'], $d['payment_mode']]
            );
            set_flash('success', 'Bill added successfully.');
            redirect('bills.php');
        } catch (mysqli_sql_exception $ex) {
            // UNIQUE(flat_id, bill_month): only one bill per flat per month
            $errors[] = $ex->getCode() == 1062 ? 'This flat already has a bill for that month.' : 'Database error: ' . $ex->getMessage();
        }
    }
}

$base = '../'; $role = 'admin'; $page = 'bills'; $title = 'Add Bill';
include '../includes/header.php';
?>
<div class="page-head"><div><h1>Add Maintenance Bill</h1><p>Create a bill for one flat</p></div></div>
<div class="card">
    <?php show_errors($errors); $submitLabel = 'Save Bill'; include '_bill_form.php'; ?>
</div>
<?php include '../includes/footer.php'; ?>
