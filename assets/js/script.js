// =========================================
// Society ERP - Client-side scripts
// 1. Form validation for <form class="validate">
//    Rules come from HTML attributes:
//      required             -> field must not be empty
//      data-rule="email"    -> valid email
//      data-rule="mobile"   -> 10 digit mobile starting 6-9
//      data-rule="flatno"   -> like A-101
//      data-rule="money"    -> number greater than 0
//      data-minlength="6"   -> minimum length
//      data-match="id"      -> must equal another field (confirm password)
//    (PHP checks the same rules again on the server.)
// 2. Delete confirmation
// 3. Login page tabs
// 4. Bill form: show payment fields only when status = Paid
// =========================================

function validateField(field) {
    if (field.offsetParent === null && field.type !== 'hidden') {
        // hidden by CSS (e.g. payment fields when Unpaid) - skip
        return true;
    }
    const value = field.value.trim();
    const rule = field.dataset.rule;
    let msg = '';

    if (field.hasAttribute('required') && value === '') {
        msg = 'This field is required.';
    } else if (value !== '') {
        if (rule === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            msg = 'Enter a valid email address.';
        } else if (rule === 'mobile' && !/^[6-9][0-9]{9}$/.test(value)) {
            msg = 'Enter a valid 10-digit mobile number.';
        } else if (rule === 'flatno' && !/^[A-Za-z]-[0-9]{3}$/.test(value)) {
            msg = 'Flat number must look like A-101.';
        } else if (rule === 'money' && (isNaN(value) || Number(value) <= 0)) {
            msg = 'Enter an amount greater than 0.';
        } else if (field.dataset.minlength && value.length < Number(field.dataset.minlength)) {
            msg = 'Must be at least ' + field.dataset.minlength + ' characters.';
        } else if (field.dataset.match && value !== document.getElementById(field.dataset.match).value) {
            msg = 'Passwords do not match.';
        }
    }

    let errorBox = field.parentElement.querySelector('.error');
    if (!errorBox) {
        errorBox = document.createElement('div');
        errorBox.className = 'error';
        field.parentElement.appendChild(errorBox);
    }
    errorBox.textContent = msg;
    field.classList.toggle('invalid', msg !== '');
    return msg === '';
}

document.querySelectorAll('form.validate').forEach(function (form) {
    const fields = form.querySelectorAll('input:not([type=hidden]), select, textarea');
    fields.forEach(function (f) {
        f.addEventListener('blur', function () { validateField(f); });
    });
    form.addEventListener('submit', function (event) {
        let ok = true;
        fields.forEach(function (f) { if (!validateField(f)) ok = false; });
        if (!ok) {
            event.preventDefault();
            const firstBad = form.querySelector('.invalid');
            if (firstBad) firstBad.focus();
        }
    });
});

// Delete / action confirmation
document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        if (!confirm(form.dataset.confirm)) event.preventDefault();
    });
});

// Login page tabs (Admin / Flat Resident)
document.querySelectorAll('.tabs button').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.tabs button').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.login-form').forEach(f => f.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.target).classList.add('active');
    });
});

// Bill form: payment date + mode only needed when status is Paid
const billStatus = document.getElementById('status');
const payFields = document.querySelectorAll('.pay-field');
if (billStatus && payFields.length) {
    const togglePay = function () {
        payFields.forEach(f => f.style.display = billStatus.value === 'Paid' ? '' : 'none');
    };
    billStatus.addEventListener('change', togglePay);
    togglePay();
}
