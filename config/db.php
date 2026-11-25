<?php
// ---------------------------------------------
// Database connection (MySQL via mysqli)
// On a web host: copy db.local.example.php to db.local.php and put the
// host's database details there (db.local.php is not uploaded to GitHub).
// Without that file the XAMPP defaults below are used.
// ---------------------------------------------
if (file_exists(__DIR__ . '/db.local.php')) {
    require __DIR__ . '/db.local.php';
}
defined('DB_HOST') || define('DB_HOST', 'localhost');
defined('DB_USER') || define('DB_USER', 'root');
defined('DB_PASS') || define('DB_PASS', '');
defined('DB_NAME') || define('DB_NAME', 'society_db');

// Names shown in the app (change these to rebrand)
define('APP_NAME', 'Gatehouse');
define('SOCIETY_NAME', 'Palm Grove Residency');

// Throw exceptions on SQL errors so we can catch them
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    die('<div style="font-family:sans-serif;padding:30px;color:#b91c1c">
        <h2>Database connection failed</h2>
        <p>' . htmlspecialchars($ex->getMessage()) . '</p>
        <p>Make sure MySQL is running in XAMPP and you have imported
        <b>database/society_db.sql</b> in phpMyAdmin.</p></div>');
}

session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/validation.php';
