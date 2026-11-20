<?php
// ---------------------------------------------
// Database connection (MySQL via mysqli)
// Change these values if your XAMPP MySQL uses a password.
// ---------------------------------------------
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'society_db');

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
