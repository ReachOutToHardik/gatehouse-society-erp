<?php
// DELETE: remove a resident
require_once '../config/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db_execute($conn, "DELETE FROM residents WHERE resident_id = ?", 'i', [(int)($_POST['id'] ?? 0)]);
    set_flash($stmt->affected_rows ? 'success' : 'error',
              $stmt->affected_rows ? 'Resident deleted successfully.' : 'Resident not found.');
}
redirect('residents.php');
