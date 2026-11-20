<?php
// Shared form for bill_add.php and bill_edit.php. Uses $d and $submitLabel.
$flatList = $conn->query("SELECT flat_id, flat_no, flat_type FROM flats ORDER BY flat_no");
?>
<form method="post" class="validate" novalidate>
    <div class="form-grid">
        <div class="form-group">
            <label for="flat_id">Flat <span class="req">*</span></label>
            <select id="flat_id" name="flat_id" required>
                <option value="">-- Select flat --</option>
                <?php while ($f = $flatList->fetch_assoc()): ?>
                    <option value="<?= $f['flat_id'] ?>" <?= $d['flat_id'] == $f['flat_id'] ? 'selected' : '' ?>>
                        <?= e($f['flat_no']) ?> (<?= e($f['flat_type']) ?> - &#8377;<?= $RATES[$f['flat_type']] ?>/month)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="bill_month">Bill Month <span class="req">*</span></label>
            <input type="month" id="bill_month" name="bill_month" value="<?= e($d['bill_month']) ?>" required>
        </div>
        <div class="form-group">
            <label for="amount">Amount (&#8377;) <span class="req">*</span></label>
            <input type="number" step="0.01" id="amount" name="amount" value="<?= e($d['amount']) ?>" required data-rule="money">
        </div>
        <div class="form-group">
            <label for="due_date">Due Date <span class="req">*</span></label>
            <input type="date" id="due_date" name="due_date" value="<?= e($d['due_date']) ?>" required>
        </div>
        <div class="form-group">
            <label for="status">Status <span class="req">*</span></label>
            <select id="status" name="status" required><?= options($BILL_STATUS, $d['status']) ?></select>
        </div>
        <div class="form-group pay-field">
            <label for="paid_on">Paid On <span class="req">*</span></label>
            <input type="date" id="paid_on" name="paid_on" value="<?= e($d['paid_on']) ?>" required max="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group pay-field">
            <label for="payment_mode">Payment Mode <span class="req">*</span></label>
            <select id="payment_mode" name="payment_mode" required>
                <option value="">-- Select --</option><?= options($PAY_MODES, $d['payment_mode']) ?>
            </select>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn"><?= e($submitLabel) ?></button>
        <a href="bills.php" class="btn btn-light">Cancel</a>
    </div>
</form>
