<?php // Shared form for complaint_add.php and complaint_edit.php. Uses $d and $submitLabel. ?>
<form method="post" class="validate" novalidate>
    <div class="form-grid">
        <div class="form-group">
            <label for="category">Category <span class="req">*</span></label>
            <select id="category" name="category" required>
                <option value="">-- Select --</option><?= options($CATEGORIES, $d['category']) ?>
            </select>
        </div>
        <div class="form-group">
            <label for="subject">Subject <span class="req">*</span></label>
            <input type="text" id="subject" name="subject" value="<?= e($d['subject']) ?>" required data-minlength="5" maxlength="150" placeholder="e.g. Bathroom tap leaking">
        </div>
        <div class="form-group full">
            <label for="description">Details <span class="req">*</span></label>
            <textarea id="description" name="description" rows="5" required data-minlength="10" placeholder="Describe the problem, where it is and since when"><?= e($d['description']) ?></textarea>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn"><?= e($submitLabel) ?></button>
        <a href="complaints.php" class="btn btn-light">Cancel</a>
    </div>
</form>
