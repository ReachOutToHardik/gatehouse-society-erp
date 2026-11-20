<?php
// RESIDENT CREATE: raise a new complaint for my flat
require_once '../config/db.php';
require_resident();

$d = ['category' => '', 'subject' => '', 'description' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$d, $errors] = validate_complaint();

    if (!$errors) {
        // flat_id comes from the session, not from the form, so a resident can't post for another flat
        db_execute($conn,
            "INSERT INTO complaints (flat_id, category, subject, description) VALUES (?, ?, ?, ?)",
            'isss', [$_SESSION['flat_id'], $d['category'], $d['subject'], $d['description']]);
        set_flash('success', 'Complaint registered. The society office will look into it.');
        redirect('complaints.php');
    }
}

$base = '../'; $role = 'resident'; $page = 'complaints'; $title = 'Raise Complaint';
include '../includes/header.php';
?>
<div class="page-head"><div><h1>Raise a Complaint</h1><p>Flat <?= e($_SESSION['flat_no']) ?></p></div></div>
<div class="card">
    <?php show_errors($errors); $submitLabel = 'Submit Complaint'; include '_complaint_form.php'; ?>
</div>
<?php include '../includes/footer.php'; ?>
