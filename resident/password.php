<?php
// RESIDENT: change my flat's login password
require_once '../config/db.php';
require_resident();

$fid = (int)$_SESSION['flat_id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $hash = db_value($conn, "SELECT password FROM flats WHERE flat_id = ?", 'i', [$fid]);

    if (!password_verify($current, $hash)) $errors[] = 'Current password is wrong.';
    if (strlen($new) < 6) $errors[] = 'New password must be at least 6 characters.';
    if ($new !== $confirm) $errors[] = 'New password and confirm password do not match.';
    if ($new === $current) $errors[] = 'New password must be different from the current one.';

    if (!$errors) {
        db_execute($conn, "UPDATE flats SET password = ? WHERE flat_id = ?", 'si',
                   [password_hash($new, PASSWORD_DEFAULT), $fid]);
        set_flash('success', 'Password changed successfully.');
        redirect('dashboard.php');
    }
}

$base = '../'; $role = 'resident'; $page = 'password'; $title = 'Change Password';
include '../includes/header.php';
?>
<div class="page-head"><div><h1>Change Password</h1><p>Login ID: <?= e($_SESSION['flat_no']) ?></p></div></div>
<div class="card narrow">
    <?php show_errors($errors); ?>
    <form method="post" class="validate" novalidate>
        <div class="form-group" style="margin-bottom:14px">
            <label for="current_password">Current Password <span class="req">*</span></label>
            <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
        </div>
        <div class="form-group" style="margin-bottom:14px">
            <label for="new_password">New Password <span class="req">*</span></label>
            <input type="password" id="new_password" name="new_password" required data-minlength="6" autocomplete="new-password">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password <span class="req">*</span></label>
            <input type="password" id="confirm_password" name="confirm_password" required data-match="new_password" autocomplete="new-password">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn">Change Password</button>
            <a href="dashboard.php" class="btn btn-light">Cancel</a>
        </div>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
