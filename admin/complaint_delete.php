<?php
// DELETE: remove a complaint
require_once '../config/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db_execute($conn, "DELETE FROM complaints WHERE complaint_id = ?", 'i', [(int)($_POST['id'] ?? 0)]);
    set_flash($stmt->affected_rows ? 'success' : 'error',
              $stmt->affected_rows ? 'Complaint deleted successfully.' : 'Complaint not found.');
}
redirect('complaints.php');
