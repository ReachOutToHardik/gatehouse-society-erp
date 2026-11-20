<?php
// RESIDENT DELETE: withdraw my complaint (only my own and only while Open)
require_once '../config/db.php';
require_resident();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db_execute($conn,
        "DELETE FROM complaints WHERE complaint_id = ? AND flat_id = ? AND status = 'Open'",
        'ii', [(int)($_POST['id'] ?? 0), (int)$_SESSION['flat_id']]);
    set_flash($stmt->affected_rows ? 'success' : 'error',
              $stmt->affected_rows ? 'Complaint withdrawn.' : 'This complaint cannot be deleted.');
}
redirect('complaints.php');
