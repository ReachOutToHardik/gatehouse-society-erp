<?php
// DELETE: remove a bill
require_once '../config/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db_execute($conn, "DELETE FROM maintenance_bills WHERE bill_id = ?", 'i', [(int)($_POST['id'] ?? 0)]);
    set_flash($stmt->affected_rows ? 'success' : 'error',
              $stmt->affected_rows ? 'Bill deleted successfully.' : 'Bill not found.');
}
redirect('bills.php');
