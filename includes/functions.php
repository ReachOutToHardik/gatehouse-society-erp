<?php
// ---------------------------------------------
// Common helper functions used on every page
// ---------------------------------------------

// Fixed lists used in forms and filters
$FLAT_TYPES     = ['1BHK', '2BHK', '3BHK'];
$FLAT_STATUS    = ['Occupied', 'Vacant'];
$RESIDENT_TYPES = ['Owner', 'Tenant', 'Family Member'];
$BILL_STATUS    = ['Unpaid', 'Paid'];
$PAY_MODES      = ['Cash', 'UPI', 'Cheque', 'Bank Transfer'];
$CATEGORIES     = ['Plumbing', 'Electrical', 'Lift', 'Security', 'Cleaning', 'Parking', 'Other'];
$COMPLAINT_STATUS = ['Open', 'In Progress', 'Resolved'];
// Monthly maintenance charge by flat type (used when generating bills)
$RATES = ['1BHK' => 1500, '2BHK' => 2000, '3BHK' => 2500];

// Escape output to prevent XSS
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Redirect to another page and stop the script
function redirect($url)
{
    header("Location: $url");
    exit;
}

// ---------- Login checks ----------
// Every admin page calls require_admin(), every resident page calls require_resident().
// If the right person is not logged in, they are sent back to the login page.
function require_admin()
{
    if (empty($_SESSION['admin_id'])) {
        set_flash('error', 'Please log in as admin to continue.');
        redirect('../index.php');
    }
}

function require_resident()
{
    if (empty($_SESSION['flat_id'])) {
        set_flash('error', 'Please log in with your flat ID to continue.');
        redirect('../index.php');
    }
}

// ---------- Flash messages ----------
function set_flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash()
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        echo '<div class="alert alert-' . e($f['type']) . '">' . e($f['message']) . '</div>';
        unset($_SESSION['flash']);
    }
}

function show_errors($errors)
{
    if (!$errors) return;
    echo '<div class="alert alert-error"><b>Please fix the following:</b><ul>';
    foreach ($errors as $err) {
        echo '<li>' . e($err) . '</li>';
    }
    echo '</ul></div>';
}

// ---------- Database helpers (prepared statements) ----------
// $types = 's' string, 'i' integer, 'd' decimal  e.g. 'si' for (string, int)
function db_select($conn, $sql, $types = '', $params = [])
{
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}

function db_execute($conn, $sql, $types = '', $params = [])
{
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

// Run a query that returns one number (e.g. COUNT)
function db_value($conn, $sql, $types = '', $params = [])
{
    return db_select($conn, $sql, $types, $params)->fetch_row()[0] ?? 0;
}

// ---------- Small formatting helpers ----------
function post($key)
{
    return trim($_POST[$key] ?? '');
}

function badge($status)
{
    $cls = strtolower(str_replace(' ', '-', $status));
    return '<span class="badge badge-' . e($cls) . '">' . e($status) . '</span>';
}

function fmt_date($date)
{
    return $date ? date('d M Y', strtotime($date)) : '-';
}

function fmt_month($date)
{
    return $date ? date('M Y', strtotime($date)) : '-';
}

function money($amount)
{
    return '&#8377; ' . number_format((float)$amount, 0);
}

// Build "<option>" tags for a <select>
function options($list, $selected)
{
    $html = '';
    foreach ($list as $item) {
        $html .= '<option' . ((string)$selected === (string)$item ? ' selected' : '') . '>' . e($item) . '</option>';
    }
    return $html;
}
