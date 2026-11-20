<?php // Shared form for flat_add.php and flat_edit.php. Uses $d and $isNew. ?>
<form method="post" class="validate" novalidate>
    <div class="form-grid">
        <div class="form-group">
            <label for="flat_no">Flat ID (login ID) <span class="req">*</span></label>
            <input type="text" id="flat_no" name="flat_no" value="<?= e($d['flat_no']) ?>" required data-rule="flatno" maxlength="5" placeholder="A-101">
            <div class="hint">Format: block letter, dash, 3 digits (floor + flat).</div>
        </div>
        <div class="form-group">
            <label for="block">Block <span class="req">*</span></label>
            <input type="text" id="block" name="block" value="<?= e($d['block']) ?>" required maxlength="1" placeholder="A">
        </div>
        <div class="form-group">
            <label for="floor_no">Floor <span class="req">*</span></label>
            <input type="number" id="floor_no" name="floor_no" value="<?= e($d['floor_no']) ?>" required min="0" max="50">
        </div>
        <div class="form-group">
            <label for="flat_type">Flat Type <span class="req">*</span></label>
            <select id="flat_type" name="flat_type" required>
                <option value="">-- Select --</option><?= options($FLAT_TYPES, $d['flat_type']) ?>
            </select>
        </div>
        <div class="form-group">
            <label for="area_sqft">Area (sq.ft) <span class="req">*</span></label>
            <input type="number" id="area_sqft" name="area_sqft" value="<?= e($d['area_sqft']) ?>" required min="200" max="10000">
        </div>
        <div class="form-group">
            <label for="status">Status <span class="req">*</span></label>
            <select id="status" name="status" required><?= options($FLAT_STATUS, $d['status']) ?></select>
        </div>
        <div class="form-group">
            <label for="password"><?= $isNew ? 'Login Password <span class="req">*</span>' : 'New Password' ?></label>
            <input type="password" id="password" name="password" <?= $isNew ? 'required' : '' ?> data-minlength="6" autocomplete="new-password">
            <div class="hint"><?= $isNew ? 'Give this password to the flat owner. Min 6 characters.' : 'Leave blank to keep the current password.' ?></div>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn"><?= $isNew ? 'Save Flat' : 'Update Flat' ?></button>
        <a href="flats.php" class="btn btn-light">Cancel</a>
    </div>
</form>
