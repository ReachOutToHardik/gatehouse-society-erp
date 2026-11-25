-- =====================================================
-- Gatehouse - database script for WEB HOSTING (InfinityFree etc.)
-- Same as society_db.sql but without CREATE DATABASE / USE:
-- create the database in the hosting panel, open it in phpMyAdmin,
-- then import this file.
-- =====================================================

-- =====================================================
-- Housing Society Management ERP - Database Script
-- Database : society_db
-- Tables   : admins, flats, residents, maintenance_bills, complaints
-- Import this file in phpMyAdmin (Import tab)
-- =====================================================


-- -----------------------------------------------------
-- admins : society office staff who manage the system
-- -----------------------------------------------------
CREATE TABLE admins (
    admin_id    INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,          -- stored as hash (password_hash)
    full_name   VARCHAR(100) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- flats (Master) : every flat has its own login (flat_no + password)
-- -----------------------------------------------------
CREATE TABLE flats (
    flat_id     INT AUTO_INCREMENT PRIMARY KEY,
    flat_no     VARCHAR(10)  NOT NULL UNIQUE,   -- login ID, e.g. A-101
    password    VARCHAR(255) NOT NULL,          -- stored as hash
    block       VARCHAR(5)   NOT NULL,
    floor_no    INT          NOT NULL,
    flat_type   ENUM('1BHK','2BHK','3BHK') NOT NULL,
    area_sqft   INT          NOT NULL,
    status      ENUM('Occupied','Vacant') NOT NULL DEFAULT 'Vacant',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- residents (Master) : people living in a flat
-- -----------------------------------------------------
CREATE TABLE residents (
    resident_id   INT AUTO_INCREMENT PRIMARY KEY,
    flat_id       INT          NOT NULL,
    name          VARCHAR(100) NOT NULL,
    mobile        VARCHAR(10)  NOT NULL,
    email         VARCHAR(100),
    resident_type ENUM('Owner','Tenant','Family Member') NOT NULL,
    move_in_date  DATE         NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (flat_id) REFERENCES flats(flat_id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- maintenance_bills (Transaction) : one bill per flat per month
-- -----------------------------------------------------
CREATE TABLE maintenance_bills (
    bill_id       INT AUTO_INCREMENT PRIMARY KEY,
    flat_id       INT           NOT NULL,
    bill_month    DATE          NOT NULL,       -- always the 1st of the month
    amount        DECIMAL(8,2)  NOT NULL,
    due_date      DATE          NOT NULL,
    status        ENUM('Unpaid','Paid') NOT NULL DEFAULT 'Unpaid',
    paid_on       DATE          NULL,
    payment_mode  ENUM('Cash','UPI','Cheque','Bank Transfer') NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_flat_month (flat_id, bill_month),
    FOREIGN KEY (flat_id) REFERENCES flats(flat_id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- complaints (Transaction) : raised by a flat, handled by admin
-- -----------------------------------------------------
CREATE TABLE complaints (
    complaint_id   INT AUTO_INCREMENT PRIMARY KEY,
    flat_id        INT          NOT NULL,
    category       ENUM('Plumbing','Electrical','Lift','Security','Cleaning','Parking','Other') NOT NULL,
    subject        VARCHAR(150) NOT NULL,
    description    TEXT         NOT NULL,
    status         ENUM('Open','In Progress','Resolved') NOT NULL DEFAULT 'Open',
    admin_remarks  VARCHAR(255),
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_on    DATE NULL,
    FOREIGN KEY (flat_id) REFERENCES flats(flat_id) ON DELETE CASCADE
);

-- =====================================================
-- Sample Data
-- Demo logins:
--   Admin : username admin  / password admin123
--   Flat  : flat no (e.g. A-101) / password flat123
-- =====================================================

INSERT INTO admins (username, password, full_name) VALUES
('admin', '$2y$10$BBZCrGa5DHxVxXM6OFmYk.HWevyn6ImSDH6uZ8yNSvm/.vFrTv4Mm', 'Society Secretary');

SET @pw = '$2y$10$FOofd0EYHZtqdvTcgAXQKOZYStyV.rX/XcFAlXqPNse2cV4fwqOe6';

INSERT INTO flats (flat_no, password, block, floor_no, flat_type, area_sqft, status) VALUES
('A-101', @pw, 'A', 1, '2BHK', 1050, 'Occupied'),
('A-102', @pw, 'A', 1, '1BHK',  650, 'Occupied'),
('A-201', @pw, 'A', 2, '2BHK', 1050, 'Occupied'),
('A-202', @pw, 'A', 2, '3BHK', 1450, 'Occupied'),
('A-301', @pw, 'A', 3, '2BHK', 1050, 'Vacant'),
('A-302', @pw, 'A', 3, '3BHK', 1450, 'Occupied'),
('B-101', @pw, 'B', 1, '1BHK',  650, 'Occupied'),
('B-102', @pw, 'B', 1, '2BHK', 1050, 'Occupied'),
('B-201', @pw, 'B', 2, '3BHK', 1450, 'Occupied'),
('B-202', @pw, 'B', 2, '2BHK', 1050, 'Vacant'),
('B-301', @pw, 'B', 3, '2BHK', 1050, 'Occupied'),
('B-302', @pw, 'B', 3, '1BHK',  650, 'Occupied'),
('A-401', @pw, 'A', 4, '3BHK', 1450, 'Occupied'),
('A-402', @pw, 'A', 4, '2BHK', 1050, 'Vacant'),
('B-401', @pw, 'B', 4, '2BHK', 1050, 'Occupied'),
('B-402', @pw, 'B', 4, '3BHK', 1450, 'Occupied'),
('C-101', @pw, 'C', 1, '1BHK',  650, 'Occupied'),
('C-102', @pw, 'C', 1, '2BHK', 1050, 'Occupied'),
('C-201', @pw, 'C', 2, '2BHK', 1050, 'Occupied'),
('C-202', @pw, 'C', 2, '3BHK', 1450, 'Occupied'),
('C-301', @pw, 'C', 3, '1BHK',  650, 'Occupied'),
('C-302', @pw, 'C', 3, '2BHK', 1050, 'Occupied'),
('C-401', @pw, 'C', 4, '3BHK', 1450, 'Occupied'),
('C-402', @pw, 'C', 4, '2BHK', 1050, 'Vacant');

INSERT INTO residents (flat_id, name, mobile, email, resident_type, move_in_date) VALUES
(1,  'Rajesh Bhandari', '9829000001', 'rajesh.b@example.com',  'Owner',         '2019-04-10'),
(1,  'Sunita Bhandari', '9829000002', NULL,                    'Family Member', '2019-04-10'),
(2,  'Amit Choudhary',  '9829000003', 'amit.c@example.com',    'Tenant',        '2024-06-01'),
(3,  'Pooja Mehta',     '9829000004', 'pooja.m@example.com',   'Owner',         '2020-01-15'),
(4,  'Vikram Rathore',  '9829000005', 'vikram.r@example.com',  'Owner',         '2018-11-20'),
(4,  'Kiran Rathore',   '9829000006', NULL,                    'Family Member', '2018-11-20'),
(6,  'Neha Joshi',      '9829000007', 'neha.j@example.com',    'Tenant',        '2025-02-01'),
(7,  'Suresh Purohit',  '9829000008', 'suresh.p@example.com',  'Owner',         '2021-07-05'),
(8,  'Anjali Sharma',   '9829000009', 'anjali.s@example.com',  'Owner',         '2017-03-12'),
(8,  'Rohit Sharma',    '9829000010', NULL,                    'Family Member', '2017-03-12'),
(9,  'Manish Agarwal',  '9829000011', 'manish.a@example.com',  'Owner',         '2022-09-18'),
(11, 'Deepika Soni',    '9829000012', 'deepika.s@example.com', 'Tenant',        '2025-08-01'),
(12, 'Harish Vyas',     '9829000013', 'harish.v@example.com',  'Owner',         '2016-05-25'),
(13, 'Prakash Jain',    '9829000014', 'prakash.j@example.com', 'Owner',         '2020-08-14'),
(13, 'Meena Jain',      '9829000015', NULL,                    'Family Member', '2020-08-14'),
(15, 'Arjun Singh',     '9829000016', 'arjun.s@example.com',   'Tenant',        '2025-04-01'),
(16, 'Kavita Mathur',   '9829000017', 'kavita.m@example.com',  'Owner',         '2019-12-02'),
(16, 'Nitin Mathur',    '9829000018', NULL,                    'Family Member', '2019-12-02'),
(17, 'Ramesh Gehlot',   '9829000019', 'ramesh.g@example.com',  'Owner',         '2021-01-20'),
(18, 'Sneha Kapoor',    '9829000020', 'sneha.k@example.com',   'Tenant',        '2026-03-01'),
(19, 'Dinesh Bohra',    '9829000021', 'dinesh.b@example.com',  'Owner',         '2018-06-30'),
(20, 'Priya Lodha',     '9829000022', 'priya.l@example.com',   'Owner',         '2023-02-11'),
(20, 'Aarav Lodha',     '9829000023', NULL,                    'Family Member', '2023-02-11'),
(21, 'Mohit Saini',     '9829000024', 'mohit.s@example.com',   'Tenant',        '2025-10-01'),
(22, 'Lata Parihar',    '9829000025', 'lata.p@example.com',    'Owner',         '2017-09-09'),
(23, 'Sanjay Bhati',    '9829000026', 'sanjay.b@example.com',  'Owner',         '2022-04-17'),
(23, 'Ritu Bhati',      '9829000027', NULL,                    'Family Member', '2022-04-17');

-- Bills for Sep, Oct, Nov 2026 for every occupied flat
-- Rate: 1BHK = 1500, 2BHK = 2000, 3BHK = 2500
INSERT INTO maintenance_bills (flat_id, bill_month, amount, due_date, status, paid_on, payment_mode)
SELECT flat_id, '2026-09-01',
       CASE flat_type WHEN '1BHK' THEN 1500 WHEN '2BHK' THEN 2000 ELSE 2500 END,
       '2026-09-10', 'Paid', '2026-09-06', 'UPI'
FROM flats WHERE status = 'Occupied';

INSERT INTO maintenance_bills (flat_id, bill_month, amount, due_date, status, paid_on, payment_mode)
SELECT flat_id, '2026-10-01',
       CASE flat_type WHEN '1BHK' THEN 1500 WHEN '2BHK' THEN 2000 ELSE 2500 END,
       '2026-10-10', 'Paid', '2026-10-08', 'Cash'
FROM flats WHERE status = 'Occupied';

INSERT INTO maintenance_bills (flat_id, bill_month, amount, due_date, status)
SELECT flat_id, '2026-11-01',
       CASE flat_type WHEN '1BHK' THEN 1500 WHEN '2BHK' THEN 2000 ELSE 2500 END,
       '2026-11-10', 'Unpaid'
FROM flats WHERE status = 'Occupied';

-- Some defaulters for October, and some flats that already paid November
UPDATE maintenance_bills SET status = 'Unpaid', paid_on = NULL, payment_mode = NULL
WHERE bill_month = '2026-10-01' AND flat_id IN (2, 9, 17, 21);
UPDATE maintenance_bills SET status = 'Paid', paid_on = '2026-11-07', payment_mode = 'UPI'
WHERE bill_month = '2026-11-01' AND flat_id IN (1, 3, 4, 8, 12, 15, 19, 20, 23);
UPDATE maintenance_bills SET status = 'Paid', paid_on = '2026-11-09', payment_mode = 'Cheque'
WHERE bill_month = '2026-11-01' AND flat_id IN (13, 16, 22);

INSERT INTO complaints (flat_id, category, subject, description, status, admin_remarks, created_at, resolved_on) VALUES
(1,  'Plumbing',   'Kitchen sink leaking',        'Water leaking from the pipe under the kitchen sink since yesterday.', 'Resolved',    'Plumber replaced the pipe joint.', '2026-10-05 09:30:00', '2026-10-06'),
(3,  'Lift',       'Block A lift stuck at 2nd floor', 'Lift stops between floors and the door does not open properly.', 'In Progress', 'Technician visit scheduled.',     '2026-11-12 18:10:00', NULL),
(7,  'Security',   'Gate left open at night',     'Main gate was found open around 1 AM on two nights.',               'Open',        NULL,                                '2026-11-15 07:45:00', NULL),
(8,  'Electrical', 'Corridor light not working',  'Tube light outside B-102 has not been working for a week.',          'Resolved',    'Light replaced.',                   '2026-11-02 20:00:00', '2026-11-03'),
(9,  'Parking',    'Unknown car in my parking',   'A white car without society sticker is parked in slot B-201.',       'Open',        NULL,                                '2026-11-18 10:20:00', NULL),
(12, 'Cleaning',   'Garbage not collected',       'Garbage has not been collected from the 3rd floor for 2 days.',     'Open',        NULL,                                '2026-11-19 08:05:00', NULL),
(4,  'Plumbing',   'Low water pressure',          'Very low water pressure in bathroom in the mornings.',               'In Progress', 'Checking overhead tank valve.',     '2026-11-10 07:00:00', NULL),
(19, 'Electrical', 'Frequent power trips in C block', 'Main switch in the C block meter room trips almost every evening.', 'Resolved',  'Electrician replaced the faulty MCB.', '2026-10-22 19:15:00', '2026-10-24'),
(21, 'Lift',       'C block lift making noise',   'Loud grinding noise from the lift between 3rd and 4th floor.',      'Open',        NULL,                                '2026-11-17 09:40:00', NULL),
(16, 'Cleaning',   'Staircase not cleaned',       'B block staircase from 3rd to 4th floor has not been swept this week.', 'Resolved', 'Housekeeping schedule updated.',   '2026-11-04 11:00:00', '2026-11-05'),
(17, 'Other',      'Stray dogs near C block gate', 'A group of stray dogs gathers near the C block gate at night.',      'In Progress', 'Contacted municipal animal control.', '2026-11-14 21:30:00', NULL);
