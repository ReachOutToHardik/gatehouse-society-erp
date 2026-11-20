<?php
// Variables set by each page before including this file:
// $base  = path back to project root ('' for root pages, '../' for admin/resident pages)
// $role  = 'admin', 'resident' or '' (login page)
// $page  = current menu item (to highlight it)
// $title = page title
$base  = $base  ?? '';
$role  = $role  ?? '';
$page  = $page  ?? '';
$title = $title ?? APP_NAME;

if ($role === 'admin') {
    $menu = [
        'dashboard'  => ['Dashboard',   'admin/dashboard.php'],
        'flats'      => ['Flats',       'admin/flats.php'],
        'residents'  => ['Residents',   'admin/residents.php'],
        'bills'      => ['Maintenance', 'admin/bills.php'],
        'complaints' => ['Complaints',  'admin/complaints.php'],
        'reports'    => ['Reports',     'admin/reports.php'],
    ];
    $who = 'Admin: ' . ($_SESSION['admin_name'] ?? '');
} elseif ($role === 'resident') {
    $menu = [
        'dashboard'  => ['My Flat',         'resident/dashboard.php'],
        'bills'      => ['My Bills',        'resident/bills.php'],
        'complaints' => ['My Complaints',   'resident/complaints.php'],
        'password'   => ['Change Password', 'resident/password.php'],
    ];
    $who = 'Flat ' . ($_SESSION['flat_no'] ?? '');
} else {
    $menu = [];
    $who = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> - <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body class="<?= $role ? 'role-' . $role : 'role-guest' ?>">
<header class="masthead">
    <div class="masthead-inner">
        <a class="wordmark" href="<?= $base ?>index.php"><?= e(strtoupper(APP_NAME)) ?></a>
        <div class="site-title">
            <div class="site-name"><?= e(SOCIETY_NAME) ?></div>
            <div class="site-sub">Housing Society Management System</div>
        </div>
        <?php if ($who): ?>
            <div class="userbox">
                <span><?= e($who) ?></span>
                <a href="<?= $base ?>logout.php">Logout</a>
            </div>
        <?php else: ?>
            <div class="userbox"><?= date('l, d F Y') ?></div>
        <?php endif; ?>
    </div>
</header>
<?php if ($menu): ?>
<nav class="navbar">
    <div class="navbar-inner">
        <span class="nav-role"><?= $role === 'admin' ? 'Admin Panel' : 'Resident Portal' ?></span>
        <button class="menu-toggle" type="button" onclick="document.querySelector('.nav').classList.toggle('open')">Menu</button>
        <div class="nav">
            <?php foreach ($menu as $key => [$label, $link]): ?>
                <a href="<?= $base . $link ?>" class="<?= $page === $key ? 'active' : '' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</nav>
<?php endif; ?>
<main class="container">
    <?php show_flash(); ?>
