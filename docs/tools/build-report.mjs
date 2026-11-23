// Builds docs/Project_Report.html (printed to PDF afterwards with headless Edge)
import { writeFileSync } from 'node:fs';
const OUT = process.argv[2];

// ---------------------------------------------------------------- group members
// [name, roll / enrollment no. ('' = not known yet), contribution]
const MEMBERS = [
  ['Hardik Joshi', '243501062', 'Database design, SQL script with sample data, ER diagram'],
  ['Harsh Vaishnav', '243501069', 'Admin module: Flats, Residents and Maintenance Bills (CRUD pages)'],
  ['Mahak', '243501111', 'Complaints module, resident portal, search/filter and reports'],
  ['Nishita', '243501139','User interface (CSS, JavaScript validation), testing, report and DFDs'],
];

// ---------------------------------------------------------------- helpers
let fig = 0, tab = 0;
const esc = s => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
const figure = (src, caption, cls = '') => `<figure class="${cls}"><img src="screenshots/${src}.png" alt="${esc(caption)}"><figcaption>Fig. ${++fig}: ${esc(caption)}</figcaption></figure>`;
const svgFig = (svg, caption, cls = '') => `<figure class="diagram ${cls}">${svg}<figcaption>Fig. ${++fig}: ${esc(caption)}</figcaption></figure>`;
const table = (head, rows, caption, cls = '') => `<table class="${cls}">${caption ? `<caption>Table ${++tab}: ${esc(caption)}</caption>` : ''}<thead><tr>${head.map(h => `<th>${h}</th>`).join('')}</tr></thead><tbody>${rows.map(r => `<tr>${r.map(c => `<td>${c}</td>`).join('')}</tr>`).join('')}</tbody></table>`;
const code = s => `<pre><code>${esc(s.trim())}</code></pre>`;

// ---------------------------------------------------------------- diagram helpers
const INK = '#1b2733', LINE = '#33475b', SOFT = '#e9eef5', ACC = '#1f3a5f';
const defs = id => `<defs><marker id="${id}" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse"><path d="M0 0L10 5L0 10z" fill="${LINE}"/></marker></defs>`;
const T = (x, y, s, o = {}) => `<text x="${x}" y="${y}" text-anchor="${o.a || 'middle'}" font-size="${o.fs || 13}" font-weight="${o.fw || 400}" fill="${o.fill || INK}"${o.it ? ' font-style="italic"' : ''}>${esc(s)}</text>`;
const lines = (x, y, arr, o = {}) => arr.map((s, i) => T(x, y + i * (o.lh || 16), s, o)).join('');
const arrow = (d, id, both = false) => `<path d="${d}" fill="none" stroke="${LINE}" stroke-width="1.4" marker-end="url(#${id})"${both ? ` marker-start="url(#${id})"` : ''}/>`;
const rect = (x, y, w, h, o = {}) => `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${o.rx ?? 6}" fill="${o.fill || '#fff'}" stroke="${o.stroke || LINE}" stroke-width="${o.sw || 1.4}"/>`;

// ---- 1. System flowchart
function flowchart() {
  const id = 'fa', W = 230, cx = 380, ax = 190, rx = 570;
  const box = (x, y, t, h = 44) => rect(x - W / 2, y - h / 2, W, h) + lines(x, y + 5 - (t.length - 1) * 8, t);
  const dia = (x, y, t) => `<polygon points="${x - 80},${y} ${x},${y - 36} ${x + 80},${y} ${x},${y + 36}" fill="${SOFT}" stroke="${LINE}" stroke-width="1.4"/>` + T(x, y + 5, t, { fw: 600 });
  const term = (x, y, t) => `<rect x="${x - 70}" y="${y - 20}" width="140" height="40" rx="20" fill="${ACC}" stroke="${ACC}"/>` + T(x, y + 5, t, { fill: '#fff', fw: 600 });
  const para = (x, y, t) => `<polygon points="${x - W / 2 + 14},${y - 22} ${x + W / 2 + 14},${y - 22} ${x + W / 2 - 14},${y + 22} ${x - W / 2 - 14},${y + 22}" fill="#fff" stroke="${LINE}" stroke-width="1.4"/>` + T(x, y + 5, t);
  return `<svg viewBox="0 0 760 900" xmlns="http://www.w3.org/2000/svg" font-family="Arial, Helvetica, sans-serif">${defs(id)}
  ${arrow(`M${cx} 60V88`, id)}${arrow(`M${cx} 132V158`, id)}${arrow(`M${cx} 202V226`, id)}
  ${arrow(`M${cx + 80} 262H${640 - 70}`, id)}${arrow(`M640 240V180H${cx + W / 2 - 4}`, id)}
  ${arrow(`M${cx} 298V328`, id)}
  ${arrow(`M${cx - 80} 364H${ax}V412`, id)}${arrow(`M${cx + 80} 364H${rx}V412`, id)}
  ${arrow(`M${ax} 456V486`, id)}${arrow(`M${ax} 546V576`, id)}${arrow(`M${ax} 620V650`, id)}
  ${arrow(`M${rx} 456V486`, id)}${arrow(`M${rx} 546V576`, id)}${arrow(`M${rx} 620V650`, id)}
  ${arrow(`M${ax} 694V730H${cx - W / 2 + 40}V752`, id)}${arrow(`M${rx} 694V730H${cx + W / 2 - 40}V752`, id)}
  ${arrow(`M${cx} 796V826`, id)}
  ${term(cx, 40, 'Start')}
  ${box(cx, 110, ['Open login page'])}
  ${para(cx, 180, 'Enter Flat ID / username + password')}
  ${dia(cx, 262, 'Valid login?')}
  ${box(640, 262, ['Show error'], 40).replace(`x="${640 - W / 2}"`, `x="${640 - 70}"`).replace(`width="${W}"`, 'width="140"')}
  ${T(cx + 96, 254, 'No', { fs: 12, fill: LINE })}${T(cx + 12, 318, 'Yes', { fs: 12, fill: LINE, a: 'start' })}
  ${dia(cx, 364, 'Role?')}
  ${T(cx - 100, 356, 'Admin', { fs: 12, fill: LINE, a: 'end' })}${T(cx + 100, 356, 'Resident', { fs: 12, fill: LINE, a: 'start' })}
  ${box(ax, 434, ['Admin Dashboard'])}
  ${box(ax, 516, ['Choose module: Flats, Residents,', 'Maintenance, Complaints, Reports'], 60)}
  ${box(ax, 598, ['Add / View / Edit / Delete / Search'])}
  ${box(ax, 672, ['Validate and save to MySQL'])}
  ${box(rx, 434, ['Resident Dashboard'])}
  ${box(rx, 516, ['View bills and dues, raise or', 'track complaints, change password'], 60)}
  ${box(rx, 598, ['Only own flat records shown'])}
  ${box(rx, 672, ['Validate and save to MySQL'])}
  ${box(cx, 774, ['Logout (session destroyed)'])}
  ${term(cx, 848, 'End')}
  </svg>`;
}

// ---- 2. DFD level 0
function dfd0() {
  const id = 'd0';
  const ent = (x, y, t) => rect(x, y, 150, 56, { rx: 0, sw: 1.8 }) + T(x + 75, y + 33, t, { fw: 700, fs: 15 });
  return `<svg viewBox="0 0 900 330" xmlns="http://www.w3.org/2000/svg" font-family="Arial, Helvetica, sans-serif">${defs(id)}
  ${ent(20, 137, 'ADMIN')}${ent(730, 137, 'RESIDENT')}
  <circle cx="450" cy="165" r="110" fill="${SOFT}" stroke="${LINE}" stroke-width="1.8"/>
  ${T(450, 128, '0.0', { fw: 700, fs: 15 })}${lines(450, 156, ['Gatehouse', 'Society Management', 'System'], { fs: 14, lh: 19, fw: 600 })}
  ${arrow('M170 150H340', id)}${arrow('M343 182H173', id)}
  ${arrow('M730 150H560', id)}${arrow('M557 182H727', id)}
  ${lines(255, 98, ['Login, flat & resident details,', 'bills, payments, complaint status'], { fs: 12, lh: 15 })}
  ${lines(255, 210, ['Dashboard, flat / bill lists,', 'complaints, reports'], { fs: 12, lh: 15 })}
  ${lines(645, 112, ['Login, new complaints,', 'password change'], { fs: 12, lh: 15 })}
  ${lines(645, 210, ['Bills & dues, flat details,', 'complaint status & remarks'], { fs: 12, lh: 15 })}
  </svg>`;
}

// ---- 3. DFD level 1
function dfd1() {
  const id = 'd1', py = 300, r = 56, xs = [100, 260, 420, 580, 740, 900];
  const names = [['1.0', 'Login /', 'Authenticate'], ['2.0', 'Manage', 'Flats'], ['3.0', 'Manage', 'Residents'], ['4.0', 'Manage', 'Maintenance'], ['5.0', 'Manage', 'Complaints'], ['6.0', 'Generate', 'Reports']];
  const proc = (x, n) => `<circle cx="${x}" cy="${py}" r="${r}" fill="${SOFT}" stroke="${LINE}" stroke-width="1.5"/>` + T(x, py - 16, n[0], { fw: 700 }) + T(x, py + 4, n[1], { fs: 12.5 }) + T(x, py + 20, n[2], { fs: 12.5 });
  const store = (x, idd, name) => `<g><line x1="${x}" y1="455" x2="${x + 120}" y2="455" stroke="${LINE}" stroke-width="1.5"/><line x1="${x}" y1="495" x2="${x + 120}" y2="495" stroke="${LINE}" stroke-width="1.5"/><line x1="${x}" y1="455" x2="${x}" y2="495" stroke="${LINE}" stroke-width="1.5"/><line x1="${x + 32}" y1="455" x2="${x + 32}" y2="495" stroke="${LINE}" stroke-width="1.5"/>${T(x + 16, 480, idd, { fw: 700, fs: 12.5 })}${T(x + 38, 480, name, { a: 'start', fs: 12.5 })}</g>`;
  const ent = (x, y, t) => rect(x, y, 160, 50, { rx: 0, sw: 1.8 }) + T(x + 80, y + 31, t, { fw: 700, fs: 14 });
  const dy = dx => py + Math.sqrt(r * r - dx * dx);
  const adminLbl = ['login', 'flat details', 'resident details', 'bills, payments', 'status, remarks', 'report request'];
  // stores: under processes 1-4 on the left side (x-95), D5 to the right of 5.0
  const storeX = [xs[0] - 95, xs[1] - 95, xs[2] - 95, xs[3] - 95, xs[4] - 15];
  const storeNames = [['D1', 'Admins'], ['D2', 'Flats'], ['D3', 'Residents'], ['D4', 'Bills'], ['D5', 'Complaints']];
  let s = `<svg viewBox="0 0 1000 690" xmlns="http://www.w3.org/2000/svg" font-family="Arial, Helvetica, sans-serif">${defs(id)}`;
  s += ent(30, 30, 'ADMIN') + ent(30, 615, 'RESIDENT');
  // admin bus
  s += `<path d="M110 80V150M${xs[0]} 150H${xs[5]}" fill="none" stroke="${LINE}" stroke-width="1.4"/>`;
  xs.forEach((x, i) => { s += arrow(`M${x} 150V${py - r - 2}`, id, i === 5) + T(x + 8, 206, adminLbl[i], { a: 'start', fs: 11.5, fill: LINE }); });
  // processes
  xs.forEach((x, i) => { s += proc(x, names[i]); });
  // process <-> store
  [0, 1, 2, 3].forEach(i => { const x = xs[i] - 40; s += arrow(`M${x} ${dy(40) + 2}V453`, id, true); });
  s += arrow(`M${xs[4] + 40} ${dy(40) + 2}V453`, id, true);
  storeNames.forEach((n, i) => { s += store(storeX[i], n[0], n[1]); });
  // flats store also used for flat login
  s += arrow(`M${storeX[1]} 485H${xs[0] + 50}V${dy(50) + 3}`, id) + T(xs[0] + 56, 410, 'flat login', { a: 'start', fs: 11, fill: LINE, it: true });
  // reports read stores
  s += arrow(`M${storeX[4] + 120} 470L${xs[5] - 20} ${dy(20) + 3}`, id) + T(892, 425, 'reads D2-D5', { a: 'start', fs: 11, fill: LINE, it: true });
  // resident bus
  s += arrow(`M135 615V${dy(35) + 2}`, id, true) + T(143, 575, 'login', { a: 'start', fs: 11.5, fill: LINE });
  s += `<path d="M190 640H${xs[4] - 35}" fill="none" stroke="${LINE}" stroke-width="1.4"/>`;
  s += arrow(`M${xs[3] + 35} 640V${dy(35) + 2}`, id) + T(xs[3] + 43, 575, 'view bills', { a: 'start', fs: 11.5, fill: LINE });
  s += arrow(`M${xs[4] - 35} 640V${dy(35) + 2}`, id, true) + T(xs[4] - 27, 575, 'complaints', { a: 'start', fs: 11.5, fill: LINE });
  return s + '</svg>';
}

// ---- 4. ER diagram
function erd() {
  const W = 210, RH = 19, HH = 30;
  const ent = (x, y, name, rows) => {
    let s = `<g>${rect(x, y, W, HH + rows.length * RH + 8, { rx: 4, sw: 1.6 })}<rect x="${x}" y="${y}" width="${W}" height="${HH}" rx="4" fill="${ACC}"/><rect x="${x}" y="${y + HH - 6}" width="${W}" height="6" fill="${ACC}"/>${T(x + W / 2, y + 20, name, { fill: '#fff', fw: 700, fs: 13.5 })}`;
    rows.forEach(([k, n, t], i) => {
      const yy = y + HH + 18 + i * RH;
      if (k) s += T(x + 10, yy, k, { a: 'start', fs: 10.5, fw: 700, fill: k === 'PK' ? '#a86500' : '#2e6b34' });
      s += T(x + 38, yy, n, { a: 'start', fs: 12, fw: k === 'PK' ? 700 : 400, ...(k === 'PK' ? { } : {}) });
      s += T(x + W - 10, yy, t, { a: 'end', fs: 10.5, fill: '#6a6f76' });
    });
    return s + '</g>';
  };
  const dia = (x, y, t) => `<polygon points="${x - 46},${y} ${x},${y - 26} ${x + 46},${y} ${x},${y + 26}" fill="${SOFT}" stroke="${LINE}" stroke-width="1.5"/>` + T(x, y + 5, t, { fs: 12.5, fw: 600 });
  const ln = d => `<path d="${d}" fill="none" stroke="${LINE}" stroke-width="1.5"/>`;
  const card = (x, y, t) => T(x, y, t, { fs: 13, fw: 700, fill: '#b3261e' });
  const flats = [['PK', 'flat_id', 'INT'], ['', 'flat_no (UQ)', 'VARCHAR'], ['', 'password', 'VARCHAR'], ['', 'block', 'VARCHAR'], ['', 'floor_no', 'INT'], ['', 'flat_type', 'ENUM'], ['', 'area_sqft', 'INT'], ['', 'status', 'ENUM'], ['', 'created_at', 'TIMESTAMP']];
  const res = [['PK', 'resident_id', 'INT'], ['FK', 'flat_id', 'INT'], ['', 'name', 'VARCHAR'], ['', 'mobile', 'VARCHAR'], ['', 'email', 'VARCHAR'], ['', 'resident_type', 'ENUM'], ['', 'move_in_date', 'DATE'], ['', 'created_at', 'TIMESTAMP']];
  const bills = [['PK', 'bill_id', 'INT'], ['FK', 'flat_id', 'INT'], ['', 'bill_month', 'DATE'], ['', 'amount', 'DECIMAL'], ['', 'due_date', 'DATE'], ['', 'status', 'ENUM'], ['', 'paid_on', 'DATE'], ['', 'payment_mode', 'ENUM'], ['', 'created_at', 'TIMESTAMP']];
  const comp = [['PK', 'complaint_id', 'INT'], ['FK', 'flat_id', 'INT'], ['', 'category', 'ENUM'], ['', 'subject', 'VARCHAR'], ['', 'description', 'TEXT'], ['', 'status', 'ENUM'], ['', 'admin_remarks', 'VARCHAR'], ['', 'created_at', 'TIMESTAMP'], ['', 'resolved_on', 'DATE']];
  const adm = [['PK', 'admin_id', 'INT'], ['', 'username (UQ)', 'VARCHAR'], ['', 'password', 'VARCHAR'], ['', 'full_name', 'VARCHAR'], ['', 'created_at', 'TIMESTAMP']];
  // flats centre top, residents left, complaints right, bills below
  const fx = 395, fy = 30, fh = HH + 9 * RH + 8;
  return `<svg viewBox="0 0 1000 640" xmlns="http://www.w3.org/2000/svg" font-family="Arial, Helvetica, sans-serif">
  ${ln(`M215 120H${310 - 46}`)}${ln(`M${310 + 46} 120H${fx}`)}
  ${ln(`M${fx + W} 120H${690 - 46}`)}${ln(`M${690 + 46} 120H785`)}
  ${ln(`M${fx + W / 2} ${fy + fh}V${318 - 26}`)}${ln(`M${fx + W / 2} ${318 + 26}V392`)}
  ${ent(fx, fy, 'FLATS', flats)}
  ${ent(5, 30, 'RESIDENTS', res)}
  ${ent(785, 30, 'COMPLAINTS', comp)}
  ${ent(fx, 392, 'MAINTENANCE_BILLS', bills)}
  ${ent(5, 400, 'ADMINS', adm)}
  ${T(110, 590, 'Stand-alone table for office logins', { fs: 11.5, fill: '#6a6f76', it: true })}
  ${dia(310, 120, 'has')}${dia(690, 120, 'raises')}${dia(fx + W / 2, 318, 'billed')}
  ${card(fx - 12, 112, '1')}${card(228, 112, 'N')}
  ${card(fx + W + 12, 112, '1')}${card(772, 112, 'N')}
  ${card(fx + W / 2 + 14, fy + fh + 18, '1')}${card(fx + W / 2 + 14, 386, 'N')}
  </svg>`;
}

// ---------------------------------------------------------------- table structures
const fields = {
  admins: [['admin_id', 'INT', 'PK, AUTO_INCREMENT', 'Unique admin number'], ['username', 'VARCHAR(50)', 'NOT NULL, UNIQUE', 'Login username'], ['password', 'VARCHAR(255)', 'NOT NULL', 'Password hash (password_hash)'], ['full_name', 'VARCHAR(100)', 'NOT NULL', 'Name shown after login'], ['created_at', 'TIMESTAMP', 'DEFAULT CURRENT_TIMESTAMP', 'Record creation time']],
  flats: [['flat_id', 'INT', 'PK, AUTO_INCREMENT', 'Unique flat number (internal)'], ['flat_no', 'VARCHAR(10)', 'NOT NULL, UNIQUE', 'Flat ID used to log in, e.g. A-101'], ['password', 'VARCHAR(255)', 'NOT NULL', 'Flat login password hash'], ['block', 'VARCHAR(5)', 'NOT NULL', 'Building block (A, B, C)'], ['floor_no', 'INT', 'NOT NULL', 'Floor number'], ['flat_type', "ENUM('1BHK','2BHK','3BHK')", 'NOT NULL', 'Decides the maintenance rate'], ['area_sqft', 'INT', 'NOT NULL', 'Carpet area in sq.ft'], ['status', "ENUM('Occupied','Vacant')", "DEFAULT 'Vacant'", 'Occupancy status'], ['created_at', 'TIMESTAMP', 'DEFAULT CURRENT_TIMESTAMP', 'Record creation time']],
  residents: [['resident_id', 'INT', 'PK, AUTO_INCREMENT', 'Unique resident number'], ['flat_id', 'INT', 'FK → flats, ON DELETE CASCADE', 'Flat the person lives in'], ['name', 'VARCHAR(100)', 'NOT NULL', 'Full name'], ['mobile', 'VARCHAR(10)', 'NOT NULL', '10-digit mobile number'], ['email', 'VARCHAR(100)', 'NULL', 'Optional email'], ['resident_type', "ENUM('Owner','Tenant','Family Member')", 'NOT NULL', 'Relation to the flat'], ['move_in_date', 'DATE', 'NOT NULL', 'Date of moving in'], ['created_at', 'TIMESTAMP', 'DEFAULT CURRENT_TIMESTAMP', 'Record creation time']],
  maintenance_bills: [['bill_id', 'INT', 'PK, AUTO_INCREMENT', 'Unique bill number'], ['flat_id', 'INT', 'FK → flats, ON DELETE CASCADE', 'Flat being billed'], ['bill_month', 'DATE', 'NOT NULL, UNIQUE (flat_id, bill_month)', 'Month of the bill (stored as 1st day)'], ['amount', 'DECIMAL(8,2)', 'NOT NULL', 'Bill amount in ₹'], ['due_date', 'DATE', 'NOT NULL', 'Last date to pay'], ['status', "ENUM('Unpaid','Paid')", "DEFAULT 'Unpaid'", 'Payment status'], ['paid_on', 'DATE', 'NULL', 'Date of payment'], ['payment_mode', "ENUM('Cash','UPI','Cheque','Bank Transfer')", 'NULL', 'How it was paid'], ['created_at', 'TIMESTAMP', 'DEFAULT CURRENT_TIMESTAMP', 'Record creation time']],
  complaints: [['complaint_id', 'INT', 'PK, AUTO_INCREMENT', 'Unique complaint number'], ['flat_id', 'INT', 'FK → flats, ON DELETE CASCADE', 'Flat that raised it'], ['category', "ENUM('Plumbing','Electrical','Lift', 'Security','Cleaning','Parking','Other')", 'NOT NULL', 'Type of problem'], ['subject', 'VARCHAR(150)', 'NOT NULL', 'Short title'], ['description', 'TEXT', 'NOT NULL', 'Details of the problem'], ['status', "ENUM('Open','In Progress','Resolved')", "DEFAULT 'Open'", 'Current status'], ['admin_remarks', 'VARCHAR(255)', 'NULL', 'Action taken by the office'], ['created_at', 'TIMESTAMP', 'DEFAULT CURRENT_TIMESTAMP', 'Date raised'], ['resolved_on', 'DATE', 'NULL', 'Date resolved']],
};
const structures = () => Object.entries(fields).map(([t, rows]) => table(['Field', 'Data Type', 'Constraint', 'Description'], rows.map(r => r.map((c, i) => i === 0 ? `<b>${esc(c)}</b>` : esc(c))), `Structure of table ${t}`, 'struct')).join('');

// ---------------------------------------------------------------- test cases
const tests = [
  ['TC01', 'Admin login with valid details', 'admin / admin123', 'Admin dashboard opens', 'Admin dashboard opened'],
  ['TC02', 'Admin login with wrong password', 'admin / wrong', 'Error message, stay on login', '"Invalid admin username or password."'],
  ['TC03', 'Flat login (case-insensitive ID)', 'a-102 / flat123', 'Resident dashboard of A-102', 'Resident dashboard opened'],
  ['TC04', 'Open admin page without login', 'admin/flats.php', 'Redirect to login page', 'Redirected to index.php'],
  ['TC05', 'Resident opens an admin page', 'admin/dashboard.php as A-102', 'Access denied', 'Sent back to resident dashboard'],
  ['TC06', 'Add flat with all fields empty', 'Empty form', 'Required-field errors in browser', '"This field is required." under each field'],
  ['TC07', 'Add flat with invalid values', 'Z1, block AA, area 5, password 12', 'Server lists every error', '7 validation errors shown'],
  ['TC08', 'Add a new flat (Create)', 'D-101, D, 1, 2BHK, 1100 sq.ft', 'Flat saved and listed', '"Flat D-101 added", list shows 25 flats'],
  ['TC09', 'Add a duplicate flat', 'D-101 again', 'Duplicate rejected', '"Flat D-101 already exists."'],
  ['TC10', 'Edit a flat (Update)', 'D-101 → 3BHK, 1450 sq.ft', 'Changes saved', '"Flat D-101 updated."'],
  ['TC11', 'Delete a flat (Delete)', 'Delete D-101, confirm', 'Flat removed', '"Flat deleted successfully", 24 flats'],
  ['TC12', 'Search / filter flats', 'Block C + Occupied', 'Only occupied C-block flats', '7 flats listed, all C-block'],
  ['TC13', 'Add resident to a vacant flat', 'New owner in vacant flat', 'Flat status becomes Occupied', 'Status changed to Occupied'],
  ['TC14', 'Generate monthly bills twice', 'Dec 2026, due 10 Dec', '2nd run creates no duplicates', '2nd run: "0 created, all already existed"'],
  ['TC15', 'Mark bill paid without payment info', 'Status Paid, no date/mode', 'Error shown', 'Date and mode required errors'],
  ['TC16', 'Second bill for same flat & month', 'Same flat, Dec 2026', 'Rejected', '"This flat already has a bill for that month."'],
  ['TC17', 'Resolve complaint without remarks', 'Status Resolved, remarks empty', 'Error shown', '"Please write what was done…"'],
  ['TC18', 'Raise complaint with invalid input', 'No category, subject "hi"', 'Validation errors', '3 errors shown'],
  ['TC19', 'Raise a valid complaint', 'Plumbing, "Tap leaking in bathroom"', 'Saved as Open', '"Complaint registered."'],
  ['TC20', 'Edit another flat\'s complaint', 'complaint_edit.php?id=2 as A-102', 'Not allowed', '"This complaint cannot be edited"'],
  ['TC21', 'Delete another flat\'s complaint', 'POST id=2 as A-102', 'Not allowed', '"This complaint cannot be deleted."'],
  ['TC22', 'Change password with wrong data', 'Wrong current, short, mismatch', 'Errors shown', '3 errors shown'],
  ['TC23', 'Logout', 'Click Logout', 'Session ends', '"You have been logged out."'],
];

// ---------------------------------------------------------------- document
const html = `<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Gatehouse - Project Report</title>
<style>
@page { size: A4; margin: 22mm 20mm 22mm 22mm;
  @bottom-center { content: counter(page); font-family: "Times New Roman", serif; font-size: 10pt; color: #444; }
  @top-right { content: "Gatehouse - Housing Society Management ERP"; font-family: "Times New Roman", serif; font-size: 9pt; color: #777; } }
@page cover { @bottom-center { content: none; } @top-right { content: none; } }
* { box-sizing: border-box; }
body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.5; color: #111; margin: 0; }
h1 { font-size: 18pt; color: #1f3a5f; margin: 0 0 10pt; padding-bottom: 5pt; border-bottom: 2px solid #1f3a5f; break-before: page; break-after: avoid; }
h2 { font-size: 14pt; color: #1f3a5f; margin: 16pt 0 6pt; break-after: avoid; }
h3 { font-size: 12.5pt; margin: 12pt 0 4pt; break-after: avoid; }
p { margin: 0 0 8pt; text-align: justify; }
ul, ol { margin: 0 0 8pt; padding-left: 22pt; }
li { margin-bottom: 3pt; }
table { width: 100%; border-collapse: collapse; margin: 6pt 0 12pt; font-size: 10.5pt; line-height: 1.35; break-inside: auto; }
caption { caption-side: top; text-align: left; font-weight: bold; font-size: 10.5pt; margin-bottom: 4pt; }
th, td { border: 1px solid #8a8f96; padding: 4pt 6pt; text-align: left; vertical-align: top; }
th { background: #e9eef5; }
tr { break-inside: avoid; }
table.struct td:nth-child(2), table.struct td:nth-child(3) { font-family: Consolas, "Courier New", monospace; font-size: 9pt; }
table.tests { font-size: 9.5pt; }
figure { margin: 8pt 0 14pt; text-align: center; break-inside: avoid; }
figure img { max-width: 100%; max-height: 228mm; border: 1px solid #b9bdc4; }
figure.diagram svg { width: 100%; height: auto; max-height: 225mm; }
figure.flow svg { width: 72%; }
table:not(.tests) { break-inside: avoid; }
figcaption { font-size: 10.5pt; font-style: italic; margin-top: 4pt; color: #333; }
pre { background: #f4f5f7; border: 1px solid #d5d8dd; padding: 7pt 9pt; font-size: 9pt; line-height: 1.35; white-space: pre-wrap; break-inside: avoid; margin: 4pt 0 10pt; }
code { font-family: Consolas, "Courier New", monospace; }
p code, li code, td code { font-size: 10pt; background: #f1f2f4; padding: 0 2pt; }
.cover { page: cover; height: 250mm; display: flex; flex-direction: column; align-items: center; text-align: center; padding-top: 14mm; }
.cover .kicker { font-size: 13pt; letter-spacing: 2pt; color: #444; }
.cover .title { font-size: 32pt; font-weight: bold; color: #1f3a5f; margin: 8mm 0 2mm; letter-spacing: 1pt; }
.cover .subtitle { font-size: 16pt; }
.cover .rule { width: 40%; border-top: 2px solid #1f3a5f; margin: 16mm 0 12mm; }
.cover .lbl { font-size: 12pt; color: #555; margin-bottom: 3mm; }
.cover .names { font-size: 14pt; line-height: 1.8; margin-bottom: 12mm; }
.cover .names span { color: #555; }
.cover .val { font-size: 14pt; font-weight: bold; }
.cover .college { margin-top: auto; font-size: 15pt; font-weight: bold; color: #1f3a5f; }
.toc { list-style: none; padding: 0; }
.toc li { display: flex; border-bottom: 1px dotted #999; padding: 3pt 0; margin: 0; }
.toc li span:first-child { width: 34pt; font-weight: bold; }
.toc li.sub { padding-left: 34pt; font-size: 11pt; }
.note { font-size: 10.5pt; color: #444; font-style: italic; }
.two { display: grid; grid-template-columns: 1fr 1fr; gap: 12pt; }
</style></head><body>

<section class="cover">
  <div class="kicker">PROJECT REPORT</div>
  <div class="title">GATEHOUSE</div>
  <div class="subtitle">Housing Society Management ERP</div>
  <div class="rule"></div>
  <div class="lbl">Submitted by</div>
  <div class="names">${MEMBERS.map(([n, r]) => `${esc(n)}${r ? ` <span>(${esc(r)})</span>` : ''}`).join('<br>')}</div>
  <div class="lbl">Submitted to</div>
  <div class="val" style="margin-bottom:12mm">Mr. Ashish Rai</div>
  <div class="val">BCA &ndash; 5th Semester</div>
  <div class="val" style="font-weight:normal;margin-top:1mm">2026</div>
  <div class="college">Lachoo Memorial College of Science &amp; Technology<br><span style="font-size:12pt;font-weight:normal;color:#333">Jodhpur, Rajasthan</span></div>
</section>

<h1>Table of Contents</h1>
<ul class="toc">
${[['1', 'Group Member Details'], ['2', 'Introduction'], ['3', 'Problem Definition'], ['4', 'Objectives'], ['5', 'Scope'], ['6', 'Technology Used'], ['7', 'System Requirements'], ['8', 'System Analysis & Design'], ['', '8.1 System Flowchart', 1], ['', '8.2 Data Flow Diagram – Level 0', 1], ['', '8.3 Data Flow Diagram – Level 1', 1], ['', '8.4 Entity Relationship Diagram', 1], ['', '8.5 Database / Table Structure', 1], ['9', 'Module Description'], ['10', 'CRUD Operations'], ['11', 'Screenshots'], ['12', 'Testing'], ['13', 'Conclusion'], ['14', 'Future Scope'], ['15', 'References']].map(([n, t, sub]) => `<li class="${sub ? 'sub' : ''}"><span>${n}</span><span>${esc(t)}</span></li>`).join('')}
</ul>
<p class="note" style="margin-top:14pt">Source code and database script: <b>github.com/ReachOutToHardik/gatehouse-society-erp</b></p>

<h1>1. Group Member Details</h1>
<p>The project was developed by a group of four students. The work was divided as shown below.</p>
${table(['#', 'Name', 'Roll / Enrollment No.', 'Contribution'], MEMBERS.map(([n, r, c], i) => [String(i + 1), esc(n), r ? esc(r) : '', esc(c)]), 'Group members and their contribution')}

<h1>2. Introduction</h1>
<p><b>Gatehouse</b> is a web-based ERP (Enterprise Resource Planning) system for a housing society. It brings the daily work of a society office – keeping flat and member records, raising monthly maintenance bills, recording payments and handling residents' complaints – into one MySQL database that is used through a web browser.</p>
<p>The system has two kinds of users. The <b>admin</b> (society office) manages all records and sees reports. Every <b>flat</b> gets its own login ID (the flat number, for example <code>A-101</code>) and password, so residents can check their bills and dues and raise complaints online without visiting the office.</p>
<p>The application is built with HTML, CSS and JavaScript on the front end, PHP on the server and MySQL as the database, and runs on the XAMPP server. The sample data describes a society called <i>Palm Grove Residency</i> with 24 flats in three blocks (A, B and C), 27 residents, 60 maintenance bills for September–November 2026 and 11 complaints.</p>

<h1>3. Problem Definition</h1>
<p>Most small and medium housing societies still run on paper registers, receipt books, spreadsheets and WhatsApp groups. This causes the following problems:</p>
<ul>
<li><b>Scattered records</b> – flat details, member lists and payment receipts are kept in different registers, so finding one flat's full history takes time.</li>
<li><b>Untracked dues</b> – the office has no quick way to see which flats have not paid maintenance, for how many months, and the total amount pending.</li>
<li><b>Manual billing</b> – the bill for every flat has to be written or typed again every month, which is slow and error-prone.</li>
<li><b>Lost complaints</b> – complaints given verbally or on WhatsApp are forgotten; residents cannot see whether anyone is working on them.</li>
<li><b>No self-service</b> – residents must visit or call the office to know their dues or the status of their complaint.</li>
<li><b>No reports</b> – preparing a defaulter list or a month-wise collection summary for the committee meeting is done by hand.</li>
</ul>
<p>Gatehouse solves these problems with a single database-driven system that the office and the residents both use.</p>

<h1>4. Objectives</h1>
<ol>
<li>To store flats, residents, maintenance bills and complaints in a relational MySQL database with proper primary and foreign keys.</li>
<li>To provide complete Create, Read, Update and Delete (CRUD) operations for every main module.</li>
<li>To provide search and filter options on every list (by flat number, name, block, type, status, month and category).</li>
<li>To generate the monthly maintenance bill for all occupied flats with a single click, using a fixed rate per flat type.</li>
<li>To give every flat its own secure login so residents can view their bills and dues and raise and track complaints.</li>
<li>To validate every form both in the browser (JavaScript) and on the server (PHP).</li>
<li>To produce reports on unpaid maintenance, month-wise collection and complaints.</li>
<li>To keep the system secure using password hashing, prepared statements and role-based access.</li>
</ol>

<h1>5. Scope</h1>
<h2>5.1 Features covered</h2>
${table(['Area', 'What the system does'], [
  ['Login', 'Separate login for admin (username) and flats (flat ID); session-based access control; logout'],
  ['Flats', 'Add, view, edit, delete flats; set or reset each flat\'s login password; search by flat number or resident; filter by block, type, status'],
  ['Residents', 'Add, view, edit, delete owners, tenants and family members; search by name, mobile, email, flat'],
  ['Maintenance', 'Add, view, edit, delete bills; record payments; one-click monthly bill generation; overdue marking; totals'],
  ['Complaints', 'Residents raise, edit and withdraw complaints; admin updates status with remarks; search and filters'],
  ['Reports', 'Unpaid maintenance (defaulters), month-wise collection, complaints by category, open complaints; printable'],
  ['Resident portal', 'Flat details, household members, dues alert, bill history, complaint tracking, change password'],
], 'Features and areas covered')}
<h2>5.2 Out of scope</h2>
<p>Online payment gateways, SMS/e-mail notifications, visitor management and accounting (expenses, ledgers) are not part of this version. They are listed under Future Scope.</p>

<h1>6. Technology Used</h1>
${table(['Layer', 'Technology', 'Purpose in the project'], [
  ['Front end', 'HTML5', 'Structure of all pages and forms'],
  ['', 'CSS3', 'Layout, colours, tables and responsive design (assets/css/style.css)'],
  ['', 'JavaScript', 'Client-side form validation, delete confirmation, login tabs (assets/js/script.js)'],
  ['Back end', 'PHP 8.2', 'Server-side logic, sessions, validation, database access with mysqli prepared statements'],
  ['Database', 'MySQL (MariaDB 10.4 bundled with XAMPP)', 'Stores all records in 5 related tables'],
  ['Server', 'XAMPP (Apache + MySQL)', 'Runs the application locally'],
  ['Tools', 'Visual Studio Code, phpMyAdmin, Git and GitHub', 'Coding, database management, version control'],
  ['Browser', 'Google Chrome / Microsoft Edge', 'Running and testing the application'],
], 'Technologies used')}

<h1>7. System Requirements</h1>
<div class="two"><div>
${table(['Hardware', 'Minimum'], [['Processor', 'Intel Core i3 / AMD equivalent'], ['RAM', '4 GB'], ['Storage', '500 MB free space'], ['Display', '1366 × 768'], ['Input', 'Keyboard and mouse']], 'Hardware requirements')}
</div><div>
${table(['Software', 'Version'], [['Operating System', 'Windows 10/11, Linux or macOS'], ['Server package', 'XAMPP 8.x (Apache, PHP 8, MySQL/MariaDB)'], ['Web browser', 'Chrome, Edge or Firefox (latest)'], ['Code editor', 'Visual Studio Code'], ['Database tool', 'phpMyAdmin']], 'Software requirements')}
</div></div>
<h2>7.1 Installation steps</h2>
<ol>
<li>Copy the <code>society-erp</code> folder to <code>C:\\xampp\\htdocs\\</code>.</li>
<li>Start <b>Apache</b> and <b>MySQL</b> from the XAMPP Control Panel.</li>
<li>Open <code>http://localhost/phpmyadmin</code>, choose <b>Import</b> and import <code>database/society_db.sql</code>.</li>
<li>Open <code>http://localhost/society-erp/</code> and log in (admin: <code>admin / admin123</code>; any flat, e.g. <code>A-101 / flat123</code>).</li>
</ol>

<h1>8. System Analysis &amp; Design</h1>
<h2>8.1 System Flowchart</h2>
<p>The flowchart shows how a user moves through the system: after a successful login the session decides whether the admin panel or the resident portal opens; every change is validated before it is saved to the database.</p>
${svgFig(flowchart(), 'System flowchart of Gatehouse', 'flow')}

<h2>8.2 Data Flow Diagram – Level 0 (Context Diagram)</h2>
<p>The context diagram shows the whole system as one process and the two external entities that exchange data with it.</p>
${svgFig(dfd0(), 'DFD Level 0 – context diagram')}

<h2>8.3 Data Flow Diagram – Level 1</h2>
<p>Level 1 breaks the system into six processes and five data stores. The admin works with every process; a resident only logs in, views bills and manages own complaints. Reports read the flats, residents, bills and complaints stores.</p>
${svgFig(dfd1(), 'DFD Level 1 – processes and data stores')}

<h2>8.4 Entity Relationship Diagram</h2>
<p><b>FLATS</b> is the central entity. One flat <i>has</i> many residents, is <i>billed</i> many maintenance bills and <i>raises</i> many complaints (all one-to-many, linked by <code>flat_id</code>). <b>ADMINS</b> is a stand-alone table used only for office logins.</p>
${svgFig(erd(), 'ER diagram (PK = primary key, FK = foreign key, UQ = unique)')}

<h2>8.5 Database / Table Structure</h2>
<p>Database name: <code>society_db</code>. All foreign keys use <code>ON DELETE CASCADE</code>, so deleting a flat also removes its residents, bills and complaints. Passwords are never stored in plain text.</p>
${structures()}

<h1>9. Module Description</h1>
<h2>9.1 Login Module</h2>
<p>The login page (<code>index.php</code>) has two tabs. Admins log in with a username; residents log in with their flat ID. The typed password is checked with <code>password_verify()</code> against the stored hash. On success the user's ID is stored in the PHP session and the right dashboard opens. Every admin page calls <code>require_admin()</code> and every resident page calls <code>require_resident()</code>, which send an unauthorised user back to the login page.</p>
<h2>9.2 Admin Panel</h2>
${table(['Module', 'Files', 'Description'], [
  ['Dashboard', 'admin/dashboard.php', 'Total, occupied and vacant flats; residents; pending dues; amount collected this month; open complaints; latest pending complaints and top defaulters'],
  ['Flats (Master)', 'flats.php, flat_add.php, flat_edit.php, flat_delete.php', 'Full CRUD on flats. The admin sets each flat\'s login password when adding it and can reset it while editing. Shows head of flat, member count and pending dues for each flat'],
  ['Residents (Master)', 'residents.php, resident_add.php, resident_edit.php, resident_delete.php', 'Full CRUD on residents. Adding a resident to a vacant flat automatically marks the flat as Occupied'],
  ['Maintenance (Transaction)', 'bills.php, bill_add.php, bill_edit.php, bill_delete.php, bill_generate.php', 'Full CRUD on bills, "Mark Paid" to record a payment, overdue marking and totals. "Generate Monthly Bills" creates the month\'s bill for all occupied flats (1BHK ₹1,500, 2BHK ₹2,000, 3BHK ₹2,500) and skips flats that already have one'],
  ['Complaints (Transaction)', 'complaints.php, complaint_edit.php, complaint_delete.php', 'Lists complaints with open ones first; admin changes status to In Progress or Resolved with remarks (remarks required to resolve); resolved date is stored automatically'],
  ['Reports', 'reports.php', 'Unpaid maintenance with months and total due per flat, month-wise collection with collection %, complaints by category, open complaints with days open; printable'],
], 'Admin panel modules')}
<h2>9.3 Resident Portal</h2>
${table(['Module', 'Files', 'Description'], [
  ['My Flat', 'resident/dashboard.php', 'Flat details, monthly maintenance, household members, pending dues alert, last payment and recent complaints'],
  ['My Bills', 'resident/bills.php', 'All bills of the flat with status, overdue marking and paid / pending totals (read only)'],
  ['My Complaints', 'complaints.php, complaint_add.php, complaint_edit.php, complaint_delete.php', 'Residents create, view, search, edit and withdraw their own complaints. Edit and delete are allowed only while the complaint is still Open'],
  ['Change Password', 'resident/password.php', 'Change the flat\'s login password after confirming the current one'],
], 'Resident portal modules')}
<h2>9.4 Common files</h2>
<ul>
<li><code>config/db.php</code> – database connection and application name.</li>
<li><code>includes/functions.php</code> – helpers: escaping, flash messages, login checks, prepared-statement wrappers.</li>
<li><code>includes/validation.php</code> – server-side validation of every form.</li>
<li><code>includes/header.php</code>, <code>footer.php</code> – page layout and role-based menu.</li>
</ul>

<h1>10. CRUD Operations</h1>
<p>All four operations are implemented for Flats, Residents, Maintenance Bills and Complaints. Every query uses a <b>prepared statement</b> (<code>?</code> placeholders) so user input can never change the SQL (protection against SQL injection). The Flats module is shown below as the example.</p>
${table(['Operation', 'Page', 'Flow', 'SQL'], [
  ['Create', 'admin/flat_add.php', 'Form → validate → INSERT', '<code>INSERT INTO flats (flat_no, password, block, …) VALUES (?, ?, ?, …)</code>'],
  ['Read', 'admin/flats.php', 'SELECT → HTML table', '<code>SELECT f.*, … FROM flats f ORDER BY f.flat_no</code>'],
  ['Update', 'admin/flat_edit.php', 'Pre-filled form → UPDATE', '<code>UPDATE flats SET flat_no = ?, … WHERE flat_id = ?</code>'],
  ['Delete', 'admin/flat_delete.php', 'Confirm → POST → DELETE', '<code>DELETE FROM flats WHERE flat_id = ?</code>'],
  ['Search', 'admin/flats.php?q=…', 'Search field / filters → WHERE', '<code>… WHERE f.flat_no LIKE ? AND f.block = ? …</code>'],
], 'CRUD operations in the Flats module')}

<h2>10.1 Create</h2>
${code(`
// admin/flat_add.php
[$d, $errors] = validate_flat(true);              // server-side validation
if (!$errors) {
    $hash = password_hash($d['password'], PASSWORD_DEFAULT);
    db_execute($conn,
        "INSERT INTO flats (flat_no, password, block, floor_no,
                            flat_type, area_sqft, status)
         VALUES (?, ?, ?, ?, ?, ?, ?)",
        'sssisis',
        [$d['flat_no'], $hash, $d['block'], $d['floor_no'],
         $d['flat_type'], $d['area_sqft'], $d['status']]);
    set_flash('success', "Flat {$d['flat_no']} added. Login ID: {$d['flat_no']}");
    redirect('flats.php');
}`)}
${figure('05-create-form', 'Create – add flat form filled with flat D-101')}
${figure('06-create-result', 'Create – success message and the new flat D-101 at the end of the list (25 flats)')}

<h2>10.2 Read</h2>
${code(`
// admin/flats.php
$sql = "SELECT f.*,
          (SELECT COUNT(*) FROM residents r WHERE r.flat_id = f.flat_id) AS members,
          (SELECT COALESCE(SUM(b.amount), 0) FROM maintenance_bills b
             WHERE b.flat_id = f.flat_id AND b.status = 'Unpaid') AS dues
        FROM flats f"
     . ($where ? " WHERE " . implode(' AND ', $where) : '')
     . " ORDER BY f.flat_no";
$flats = db_select($conn, $sql, $types, $params);
while ($f = $flats->fetch_assoc()) { /* one <tr> per flat */ }`)}
${figure('03-flats-list', 'Read – all flats shown in an HTML table with members and pending dues')}

<h2>10.3 Update</h2>
${code(`
// admin/flat_edit.php
// 1. load the saved record to pre-fill the form
$d = db_select($conn, "SELECT * FROM flats WHERE flat_id = ?", 'i', [$id])->fetch_assoc();

// 2. after the form is submitted and validated
db_execute($conn,
    "UPDATE flats SET flat_no = ?, block = ?, floor_no = ?,
                      flat_type = ?, area_sqft = ?, status = ?
     WHERE flat_id = ?",
    'ssisisi',
    [$d['flat_no'], $d['block'], $d['floor_no'],
     $d['flat_type'], $d['area_sqft'], $d['status'], $id]);`)}
${figure('08-update-form', 'Update – edit form pre-filled with the saved values of D-101')}
${figure('09-update-result', 'Update – D-101 changed to 3BHK, 1450 sq.ft, Occupied')}

<h2>10.4 Delete</h2>
${code(`
<!-- admin/flats.php: Delete is a small POST form; script.js shows confirm() first -->
<form method="post" action="flat_delete.php"
      data-confirm="Delete flat D-101? Its residents, bills and complaints will also be deleted.">
    <input type="hidden" name="id" value="25">
    <button type="submit" class="link-btn danger">Delete</button>
</form>

// admin/flat_delete.php
$stmt = db_execute($conn, "DELETE FROM flats WHERE flat_id = ?", 'i', [(int)$_POST['id']]);`)}
${figure('10-delete-action', 'Delete – Delete action on flat D-101 (a confirmation box appears before deleting)')}
${figure('11-delete-result', 'Delete – "Flat deleted successfully", list back to 24 flats')}

<h2>10.5 Search</h2>
${code(`
// admin/flats.php – WHERE clause built only from the filters that were filled in
if ($q !== '') {
    $where[] = "(f.flat_no LIKE ? OR EXISTS (SELECT 1 FROM residents r
                 WHERE r.flat_id = f.flat_id AND r.name LIKE ?))";
    $types .= 'ss';
    array_push($params, "%$q%", "%$q%");
}
if ($block !== '')  { $where[] = "f.block = ?";  $types .= 's'; $params[] = $block; }
if ($status !== '') { $where[] = "f.status = ?"; $types .= 's'; $params[] = $status; }`)}
${figure('07-search-result', 'Search – filter Block = C and Status = Occupied returns the 7 occupied C-block flats')}

<h1>11. Screenshots</h1>
<h2>11.1 Login and validation</h2>
${figure('01-login', 'Login page with Flat Resident and Admin tabs')}
${figure('04-validation-errors', 'Client-side validation – empty Add Flat form submitted')}
<h2>11.2 Admin panel</h2>
${figure('02-admin-dashboard', 'Admin dashboard – summary figures, pending complaints, defaulters')}
${figure('12-residents', 'Residents list')}
${figure('13-resident-add', 'Add resident form')}
${figure('14-bills', 'Maintenance bills for November 2026 with totals')}
${figure('15-bill-generate', 'Generate monthly bills for all occupied flats')}
${figure('16-record-payment', 'Record payment ("Mark Paid") – payment date and mode')}
${figure('17-complaints-admin', 'Complaints list (open complaints first)')}
${figure('18-complaint-update', 'Admin updates complaint status with remarks')}
${figure('19-reports', 'Reports – unpaid maintenance, month-wise collection, complaints by category, open complaints')}
<h2>11.3 Resident portal</h2>
${figure('20-resident-dashboard', 'Resident dashboard of flat A-102 with dues alert')}
${figure('21-resident-bills', 'Resident – My Bills with paid and pending totals')}
${figure('22-resident-raise-complaint', 'Resident – raise a complaint')}
${figure('23-resident-complaints', 'Resident – My Complaints after the complaint is registered')}
${figure('24-resident-password', 'Resident – change password')}

<h1>12. Testing</h1>
<p>The system was tested manually (black-box testing) on XAMPP with PHP 8.2 and MariaDB 10.4. Each test below was run on the sample data; all 23 test cases passed.</p>
${table(['ID', 'Test case', 'Input', 'Expected result', 'Actual result', 'Status'], tests.map(t => [...t.map(esc), '<b style="color:#2e7d32">Pass</b>']), 'Test cases and results', 'tests')}
<h2>12.1 Security checks</h2>
<ul>
<li><b>SQL injection</b> – all queries use prepared statements; input like <code>' OR '1'='1</code> in the search box is treated as plain text.</li>
<li><b>XSS</b> – every value printed on a page passes through <code>htmlspecialchars()</code>.</li>
<li><b>Passwords</b> – stored as bcrypt hashes; the database never holds a readable password.</li>
<li><b>Access control</b> – a resident's queries always include <code>flat_id = ?</code> taken from the session, never from the form.</li>
</ul>

<h1>13. Conclusion</h1>
<p>Gatehouse meets all the requirements of the ERP-based CRUD mini project. It solves a real management problem – running a housing society – with a database-driven web application built on HTML, CSS, JavaScript, PHP and MySQL.</p>
<p>The system implements complete Create, Read, Update, Delete and Search operations in four modules (Flats, Residents, Maintenance Bills and Complaints), validates every form in the browser and on the server, and provides a dashboard and reports. Separate logins for the society office and for each flat make it useful for both the management and the residents. While building it, the group gained practical experience in database design with keys and relationships, server-side programming with PHP sessions and prepared statements, and testing a complete application.</p>

<h1>14. Future Scope</h1>
<ul>
<li><b>Online payments</b> – pay maintenance through a UPI / payment gateway and update the bill automatically.</li>
<li><b>Notifications</b> – SMS, e-mail or WhatsApp reminders for due bills and complaint updates.</li>
<li><b>Notice board and events</b> – circulars, meeting notices and booking of the clubhouse or community hall.</li>
<li><b>Visitor management</b> – gate entry log with visitor name, flat and time.</li>
<li><b>Accounts</b> – society expenses, vendor payments and an income–expenditure statement.</li>
<li><b>Export</b> – download reports as PDF or Excel.</li>
<li><b>Mobile app</b> – an Android app for residents using the same database.</li>
</ul>

<h1>15. References</h1>
<ol>
<li>PHP Manual – <i>mysqli prepared statements, password_hash()</i>, php.net/manual</li>
<li>MariaDB / MySQL Reference Manual – <i>CREATE TABLE, FOREIGN KEY, JOIN</i>, mariadb.com/kb and dev.mysql.com/doc</li>
<li>MDN Web Docs – <i>HTML forms, CSS grid, JavaScript events</i>, developer.mozilla.org</li>
<li>W3Schools – <i>PHP MySQL tutorial</i>, w3schools.com/php</li>
<li>Apache Friends – <i>XAMPP documentation</i>, apachefriends.org</li>
<li>Jon Duckett, <i>PHP &amp; MySQL: Server-side Web Development</i>, Wiley, 2022.</li>
<li>A. Silberschatz, H. F. Korth, S. Sudarshan, <i>Database System Concepts</i>, 7th ed., McGraw-Hill.</li>
<li>Project repository – github.com/ReachOutToHardik/gatehouse-society-erp</li>
</ol>
</body></html>`;

writeFileSync(OUT, html);
console.log('figures:', fig, 'tables:', tab);
