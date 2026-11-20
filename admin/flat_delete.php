<?php
// DELETE: remove a flat (its residents, bills and complaints go too - ON DELETE CASCADE)
require_once '../config/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db_execute($conn, "DELETE FROM flats WHERE flat_id = ?", 'i', [(int)($_POST['id'] ?? 0)]);
    set_flash($stmt->affected_rows ? 'success' : 'error',
              $stmt->affected_rows ? 'Flat deleted successfully.' : 'Flat not found.');
}
redirect('flats.php');
