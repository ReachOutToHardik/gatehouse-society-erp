<?php
// CREATE (bulk): generate this month's bill for every occupied flat in one click.
// Amount comes from $RATES (by flat type). Flats that already have a bill for the month are skipped.
require_once '../config/db.php';
require_admin();

$month   = date('Y-m');
$dueDate = date('Y-m-10');
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $month   = post('bill_month');
    $dueDate = post('due_date');

    if (!preg_match('/^\d{4}-\d{2}$/', $month)) $errors[] = 'Please select the bill month.';
    if (!valid_date($dueDate)) $errors[] = 'Please select a valid due date.';

    if (!$errors) {
        $flats = $conn->query("SELECT flat_id, flat_type FROM flats WHERE status = 'Occupied'");
        $created = 0; $skipped = 0;

        while ($f = $flats->fetch_assoc()) {
            $exists = db_value($conn, "SELECT COUNT(*) FROM maintenance_bills WHERE flat_id = ? AND bill_month = ?",
                               'is', [$f['flat_id'], $month . '-01']);
            if ($exists) { $skipped++; continue; }

            db_execute($conn,
                "INSERT INTO maintenance_bills (flat_id, bill_month, amount, due_date, status) VALUES (?, ?, ?, ?, 'Unpaid')",
                'isds', [$f['flat_id'], $month . '-01', $RATES[$f['flat_type']], $dueDate]);
            $created++;
        }
        set_flash('success', "Bills for " . fmt_month($month . '-01') . ": $created created, $skipped already existed.");
        redirect('bills.php?month=' . $month);
    }
}

$base = '../'; $role = 'admin'; $page = 'bills'; $title = 'Generate Bills';
include '../includes/header.php';
?>
<div class="page-head"><div><h1>Generate Monthly Bills</h1><p>Create maintenance bills for all occupied flats at once</p></div></div>
<div class="card narrow">
    <?php show_errors($errors); ?>
    <div class="alert alert-info">
        Rates: 1BHK &#8377;<?= $RATES['1BHK'] ?>, 2BHK &#8377;<?= $RATES['2BHK'] ?>, 3BHK &#8377;<?= $RATES['3BHK'] ?> per month.
        Flats that already have a bill for the month are skipped.
    </div>
    <form method="post" class="validate" novalidate>
        <div class="form-group" style="margin-bottom:14px">
            <label for="bill_month">Bill Month <span class="req">*</span></label>
            <input type="month" id="bill_month" name="bill_month" value="<?= e($month) ?>" required>
        </div>
        <div class="form-group">
            <label for="due_date">Due Date <span class="req">*</span></label>
            <input type="date" id="due_date" name="due_date" value="<?= e($dueDate) ?>" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn">Generate Bills</button>
            <a href="bills.php" class="btn btn-light">Cancel</a>
        </div>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
