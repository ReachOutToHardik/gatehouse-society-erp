<?php
// Shared form for resident_add.php and resident_edit.php. Uses $d and $submitLabel.
$flatList = $conn->query("SELECT flat_id, flat_no, status FROM flats ORDER BY flat_no");
?>
<form method="post" class="validate" novalidate>
    <div class="form-grid">
        <div class="form-group">
            <label for="flat_id">Flat <span class="req">*</span></label>
            <select id="flat_id" name="flat_id" required>
                <option value="">-- Select flat --</option>
                <?php while ($f = $flatList->fetch_assoc()): ?>
                    <option value="<?= $f['flat_id'] ?>" <?= $d['flat_id'] == $f['flat_id'] ? 'selected' : '' ?>>
                        <?= e($f['flat_no']) ?> (<?= e($f['status']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>
            <div class="hint">Adding a resident to a vacant flat marks it as Occupied.</div>
        </div>
        <div class="form-group">
            <label for="name">Full Name <span class="req">*</span></label>
            <input type="text" id="name" name="name" value="<?= e($d['name']) ?>" required data-minlength="3" maxlength="100">
        </div>
        <div class="form-group">
            <label for="mobile">Mobile <span class="req">*</span></label>
            <input type="text" id="mobile" name="mobile" value="<?= e($d['mobile']) ?>" required data-rule="mobile" maxlength="10">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($d['email']) ?>" data-rule="email" maxlength="100">
        </div>
        <div class="form-group">
            <label for="resident_type">Resident Type <span class="req">*</span></label>
            <select id="resident_type" name="resident_type" required>
                <option value="">-- Select --</option><?= options($RESIDENT_TYPES, $d['resident_type']) ?>
            </select>
        </div>
        <div class="form-group">
            <label for="move_in_date">Move-in Date <span class="req">*</span></label>
            <input type="date" id="move_in_date" name="move_in_date" value="<?= e($d['move_in_date']) ?>" required max="<?= date('Y-m-d') ?>">
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn"><?= e($submitLabel) ?></button>
        <a href="residents.php" class="btn btn-light">Cancel</a>
    </div>
</form>
