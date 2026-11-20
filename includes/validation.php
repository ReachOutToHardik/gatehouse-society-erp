<?php
// ---------------------------------------------
// Server-side validation for every form.
// Each function returns [cleanData, errors].
// (JavaScript checks the same rules in the browser first.)
// ---------------------------------------------

function valid_date($value)
{
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return $d && $d->format('Y-m-d') === $value;
}

// Flat form (admin). Password is required for a new flat, optional when editing.
function validate_flat($isNew)
{
    global $FLAT_TYPES, $FLAT_STATUS;
    $d = [
        'flat_no'   => strtoupper(post('flat_no')),
        'block'     => strtoupper(post('block')),
        'floor_no'  => post('floor_no'),
        'flat_type' => post('flat_type'),
        'area_sqft' => post('area_sqft'),
        'status'    => post('status'),
        'password'  => $_POST['password'] ?? '',
    ];
    $errors = [];

    if (!preg_match('/^[A-Z]-[0-9]{3}$/', $d['flat_no'])) {
        $errors[] = 'Flat number must look like A-101 (block letter, dash, 3 digits).';
    }
    if (!preg_match('/^[A-Z]$/', $d['block'])) $errors[] = 'Block must be a single letter (A-Z).';
    if ($d['flat_no'] && $d['block'] && $d['flat_no'][0] !== $d['block']) {
        $errors[] = 'Flat number must start with its block letter.';
    }
    if (!ctype_digit($d['floor_no']) || $d['floor_no'] > 50) $errors[] = 'Floor must be a number from 0 to 50.';
    if (!in_array($d['flat_type'], $FLAT_TYPES)) $errors[] = 'Please select a flat type.';
    if (!ctype_digit($d['area_sqft']) || $d['area_sqft'] < 200 || $d['area_sqft'] > 10000) {
        $errors[] = 'Area must be between 200 and 10000 sq.ft.';
    }
    if (!in_array($d['status'], $FLAT_STATUS)) $errors[] = 'Please select a status.';
    if (($isNew || $d['password'] !== '') && strlen($d['password']) < 6) {
        $errors[] = 'Login password must be at least 6 characters.';
    }
    return [$d, $errors];
}

// Resident form (admin)
function validate_resident($conn)
{
    global $RESIDENT_TYPES;
    $d = [
        'flat_id'       => post('flat_id'),
        'name'          => post('name'),
        'mobile'        => post('mobile'),
        'email'         => strtolower(post('email')),
        'resident_type' => post('resident_type'),
        'move_in_date'  => post('move_in_date'),
    ];
    $errors = [];

    if (db_select($conn, "SELECT 1 FROM flats WHERE flat_id = ?", 'i', [(int)$d['flat_id']])->num_rows === 0) {
        $errors[] = 'Please select a valid flat.';
    }
    if (!preg_match('/^[A-Za-z .]{3,100}$/', $d['name'])) $errors[] = 'Name is required (letters only, min 3).';
    if (!preg_match('/^[6-9][0-9]{9}$/', $d['mobile'])) $errors[] = 'Mobile must be a valid 10-digit number.';
    if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email address is not valid.';
    if (!in_array($d['resident_type'], $RESIDENT_TYPES)) $errors[] = 'Please select resident type.';
    if (!valid_date($d['move_in_date']) || $d['move_in_date'] > date('Y-m-d')) {
        $errors[] = 'Move-in date must be a valid date, not in the future.';
    }
    return [$d, $errors];
}

// Maintenance bill form (admin)
function validate_bill($conn)
{
    global $BILL_STATUS, $PAY_MODES;
    $d = [
        'flat_id'      => post('flat_id'),
        'bill_month'   => post('bill_month'),      // from <input type="month">: 2026-11
        'amount'       => post('amount'),
        'due_date'     => post('due_date'),
        'status'       => post('status'),
        'paid_on'      => post('paid_on'),
        'payment_mode' => post('payment_mode'),
    ];
    $errors = [];

    if (db_select($conn, "SELECT 1 FROM flats WHERE flat_id = ?", 'i', [(int)$d['flat_id']])->num_rows === 0) {
        $errors[] = 'Please select a valid flat.';
    }
    if (!preg_match('/^\d{4}-\d{2}$/', $d['bill_month'])) $errors[] = 'Please select the bill month.';
    if (!is_numeric($d['amount']) || $d['amount'] <= 0 || $d['amount'] > 100000) {
        $errors[] = 'Amount must be a number greater than 0.';
    }
    if (!valid_date($d['due_date'])) $errors[] = 'A valid due date is required.';
    if (!in_array($d['status'], $BILL_STATUS)) $errors[] = 'Please select bill status.';

    // Rule: a paid bill must have a payment date and mode; an unpaid bill has neither
    if ($d['status'] === 'Paid') {
        if (!valid_date($d['paid_on'])) $errors[] = 'Payment date is required for a paid bill.';
        if (!in_array($d['payment_mode'], $PAY_MODES)) $errors[] = 'Payment mode is required for a paid bill.';
    } else {
        $d['paid_on'] = null;
        $d['payment_mode'] = null;
    }
    $d['bill_month_date'] = $d['bill_month'] . '-01';   // stored as 1st of month
    return [$d, $errors];
}

// Complaint form (resident creates / edits)
function validate_complaint()
{
    global $CATEGORIES;
    $d = [
        'category'    => post('category'),
        'subject'     => post('subject'),
        'description' => post('description'),
    ];
    $errors = [];
    if (!in_array($d['category'], $CATEGORIES)) $errors[] = 'Please select a category.';
    if (strlen($d['subject']) < 5 || strlen($d['subject']) > 150) $errors[] = 'Subject must be 5 to 150 characters.';
    if (strlen($d['description']) < 10) $errors[] = 'Please describe the problem (min 10 characters).';
    return [$d, $errors];
}
