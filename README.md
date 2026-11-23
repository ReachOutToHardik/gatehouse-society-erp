# Gatehouse: Housing Society Management ERP

A web-based ERP for a housing society (the sample data uses a society called *Palm Grove Residency*). The society office (admin) manages flats, residents,
monthly maintenance bills and complaints. Each flat gets its own **Flat ID and password**,
so residents can log in to see their bills and dues and raise complaints.

## Team

BCA 5th Semester (2026), Lachoo Memorial College of Science & Technology, Jodhpur
Submitted to **Mr. Ashish Rai**

| Name | Roll No. |
|------|----------|
| Hardik Joshi | 243501062 |
| Harsh Vaishnav | 243501069 |
| Mahak | 243501111 |
| Nishita | 243501139 |

## Technology

| Layer    | Technology |
|----------|------------|
| Frontend | HTML, CSS, JavaScript |
| Backend  | PHP (mysqli + prepared statements) |
| Database | MySQL |
| Server   | XAMPP (Apache + MySQL) |

## Two Logins

| User | Login with | Goes to | Can do |
|------|-----------|---------|--------|
| **Admin** (society office) | username + password | `admin/` | Full CRUD on flats, residents, bills, complaints, plus reports |
| **Flat resident** | Flat ID (e.g. `A-101`) + password | `resident/` | See own flat, members and bills; raise, edit and delete own complaints; change password |

**Demo accounts** (from the sample data):
- Admin: `admin` / `admin123`
- Any flat, e.g. `A-101`, `B-201`: password `flat123`

## Features

### Admin Panel
- **Dashboard**: total, occupied and vacant flats, residents, unpaid bills, pending dues, collected this month, open complaints, pending complaints, defaulters
- **Flats**: CRUD; search by flat no or resident name; filter by block, type and status; set or reset each flat's login password
- **Residents**: CRUD; search by name, mobile, email or flat; filter by type
- **Maintenance Bills**: CRUD; filter by month, status and flat; *Mark Paid*; **Generate Monthly Bills** for all occupied flats in one click
- **Complaints**: search and filter; update status with remarks; delete
- **Reports**: unpaid maintenance (defaulters with months and total due), month-wise collection, complaints by category, open complaints with days open, print option

### Resident Portal
- **My Flat**: flat details, household members, pending dues alert, last payment, recent complaints
- **My Bills**: all bills with paid/pending totals and overdue marking
- **My Complaints**: raise (create), view and search (read), edit (update), withdraw (delete) while the complaint is still Open
- **Change Password**

### Business Rules
- Only one bill per flat per month (UNIQUE key)
- A paid bill must have a payment date and mode
- Adding a resident to a vacant flat marks it Occupied
- A complaint can't be marked Resolved without office remarks; the resolved date is recorded automatically
- Residents can only see or change their own flat's data (`flat_id` comes from the session, never from the form)
- Residents can't edit or delete a complaint once the office starts working on it

### Security
- Passwords stored as hashes (`password_hash` / `password_verify`)
- Prepared statements everywhere (prevent SQL injection)
- `htmlspecialchars` on all output (prevents XSS)
- Every admin page calls `require_admin()`; every resident page calls `require_resident()`
- Delete only via POST, with a confirmation box

### Validation
- **JavaScript (browser)**: required fields, email, 10-digit mobile, flat no format, amount > 0, min length, password match
- **PHP (server)**: the same rules again, in `includes/validation.php`

## Setup (XAMPP)

1. Copy the `society-erp` folder into `C:\xampp\htdocs\`
2. Start **Apache** and **MySQL** in the XAMPP Control Panel
3. Open http://localhost/phpmyadmin, go to **Import**, choose `database/society_db.sql` and click **Go**
4. Open http://localhost/society-erp/

If your MySQL root user has a password, set it in `config/db.php`. The app name and society name
are also set there (`APP_NAME`, `SOCIETY_NAME`).

## Folder Structure

```
society-erp/
├── index.php                 Login page (Admin / Flat Resident tabs)
├── logout.php
├── config/db.php             DB connection, app + society name
├── includes/
│   ├── functions.php         helpers, login checks, fixed lists, rates
│   ├── validation.php        server-side validation for all forms
│   ├── header.php            header + menu bar (changes with the role)
│   └── footer.php
├── assets/css/style.css
├── assets/js/script.js       validation, delete confirm, login tabs
├── database/society_db.sql   tables + sample data
├── docs/
│   ├── Project_Report.pdf    project report (diagrams, CRUD evidence, test cases)
│   ├── Project_Report.html   source of the report
│   ├── screenshots/          screenshots used in the report
│   └── tools/                scripts that build the report and print it to PDF
├── admin/
│   ├── dashboard.php, reports.php
│   ├── flats.php, flat_add.php, flat_edit.php, flat_delete.php, _flat_form.php
│   ├── residents.php, resident_add.php, resident_edit.php, resident_delete.php, _resident_form.php
│   ├── bills.php, bill_add.php, bill_edit.php, bill_delete.php, bill_generate.php, _bill_form.php
│   └── complaints.php, complaint_edit.php, complaint_delete.php
└── resident/
    ├── dashboard.php, bills.php, password.php
    └── complaints.php, complaint_add.php, complaint_edit.php, complaint_delete.php, _complaint_form.php
```

## Database Design

```
                 ┌──< residents
admins   flats ──┼──< maintenance_bills
                 └──< complaints
```

| Table | Primary Key | Foreign Key | Purpose |
|-------|-------------|-------------|---------|
| `admins` | `admin_id` | – | Society office logins |
| `flats` | `flat_id` | – | Flat details and the flat's login (`flat_no` + password) |
| `residents` | `resident_id` | `flat_id` → flats | Owners, tenants, family members |
| `maintenance_bills` | `bill_id` | `flat_id` → flats | Monthly bill, payment status |
| `complaints` | `complaint_id` | `flat_id` → flats | Complaints and their status |

`ON DELETE CASCADE`: deleting a flat removes its residents, bills and complaints.

## CRUD Mapping (Flats module)

| Operation | File | SQL |
|-----------|------|-----|
| Create | `admin/flat_add.php` | `INSERT INTO flats ...` |
| Read   | `admin/flats.php` | `SELECT ... FROM flats` |
| Update | `admin/flat_edit.php` | `UPDATE flats SET ... WHERE flat_id = ?` |
| Delete | `admin/flat_delete.php` | `DELETE FROM flats WHERE flat_id = ?` |
| Search | `admin/flats.php?q=` | `SELECT ... WHERE flat_no LIKE ?` |

Residents, Bills and Complaints follow the same pattern.

## Future Scope
- Online payment of maintenance (UPI gateway)
- SMS/email reminders for dues
- Notice board and event booking (clubhouse, hall)
- Visitor entry log at the gate
- Export reports to PDF/Excel

---
Built as a BCA 5th Semester ERP-Based CRUD Mini Project.
