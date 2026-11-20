<?php
// LOGIN PAGE - two kinds of users:
//   1. Admin (society office)  -> username + password -> admin/dashboard.php
//   2. Flat resident           -> flat no + password  -> resident/dashboard.php
require_once 'config/db.php';

// Already logged in? Go straight to the right dashboard.
if (!empty($_SESSION['admin_id'])) redirect('admin/dashboard.php');
if (!empty($_SESSION['flat_id']))  redirect('resident/dashboard.php');

$type  = $_POST['login_type'] ?? 'flat';
$error = '';
$username = '';
$flatNo   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if ($type === 'admin') {
        $username = post('username');
        $admin = db_select($conn, "SELECT * FROM admins WHERE username = ?", 's', [$username])->fetch_assoc();

        // password_verify compares the typed password with the stored hash
        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            redirect('admin/dashboard.php');
        }
        $error = 'Invalid admin username or password.';
    } else {
        $flatNo = strtoupper(post('flat_no'));
        $flat = db_select($conn, "SELECT * FROM flats WHERE flat_no = ?", 's', [$flatNo])->fetch_assoc();

        if ($flat && password_verify($password, $flat['password'])) {
            session_regenerate_id(true);
            $_SESSION['flat_id'] = $flat['flat_id'];
            $_SESSION['flat_no'] = $flat['flat_no'];
            redirect('resident/dashboard.php');
        }
        $error = 'Invalid flat number or password.';
    }
}

$title = 'Login';
include 'includes/header.php';
?>
<div class="login-wrap">
    <div class="login-info">
        <h1>Welcome</h1>
        <p>Online management system for <?= e(SOCIETY_NAME) ?>.</p>
        <ul>
            <li>Residents can view maintenance bills and dues</li>
            <li>Raise and track complaints online</li>
            <li>Society office manages flats, residents, bills and complaints</li>
            <li>Reports on unpaid maintenance and open complaints</li>
        </ul>
    </div>

    <div class="login-box">
        <div class="tabs">
            <button type="button" data-target="flat-form" class="<?= $type !== 'admin' ? 'active' : '' ?>">Flat Resident</button>
            <button type="button" data-target="admin-form" class="<?= $type === 'admin' ? 'active' : '' ?>">Admin</button>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

        <form method="post" id="flat-form" class="login-form validate <?= $type !== 'admin' ? 'active' : '' ?>" novalidate>
            <input type="hidden" name="login_type" value="flat">
            <div class="form-group">
                <label for="flat_no">Flat ID</label>
                <input type="text" id="flat_no" name="flat_no" value="<?= e($flatNo) ?>" required data-rule="flatno" placeholder="e.g. A-101">
            </div>
            <div class="form-group">
                <label for="flat_password">Password</label>
                <input type="password" id="flat_password" name="password" required>
            </div>
            <button type="submit" class="btn">Login to My Flat</button>
            <p class="hint" style="margin-top:10px">Your Flat ID and password are given by the society office.</p>
        </form>

        <form method="post" id="admin-form" class="login-form validate <?= $type === 'admin' ? 'active' : '' ?>" novalidate>
            <input type="hidden" name="login_type" value="admin">
            <div class="form-group">
                <label for="username">Admin Username</label>
                <input type="text" id="username" name="username" value="<?= e($username) ?>" required>
            </div>
            <div class="form-group">
                <label for="admin_password">Password</label>
                <input type="password" id="admin_password" name="password" required>
            </div>
            <button type="submit" class="btn">Login as Admin</button>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
