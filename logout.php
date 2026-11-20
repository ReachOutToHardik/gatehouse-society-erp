<?php
// LOGOUT - clear the session and go back to the login page
require_once 'config/db.php';

$_SESSION = [];
session_destroy();

session_start();
set_flash('success', 'You have been logged out.');
redirect('index.php');
