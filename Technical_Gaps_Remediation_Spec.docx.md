**TECHNICAL GAPS REMEDIATION SPECIFICATION**

Farm Workforce & Operations Management System

Version 1.0  |  March 2026  |  Full Implementation-Ready Detail

| Document type | Remediation specification for post-implementation gaps |
| :---- | :---- |
| **Audience** | Solo developer — pick up and implement without further analysis |
| **Scope** | 10 gap areas — all four priority modules covered in full |
| **Status** | System already implemented — this document covers what to add/fix next |

# **Table of Contents**

# **1\. Overview & Remediation Philosophy**

This document is a developer-facing specification covering every significant gap identified in the Farm Workforce & Operations Management System after initial implementation. It is not a requirements document — it assumes the base system (Phases 1–11 of the original plan) is already live and functioning.

Each gap is described with: the exact problem, the schema changes required, the service/model logic to add, the API endpoints to expose, and the UI changes needed. You should be able to implement each section independently without cross-referencing any other document.

| RULE | Do not refactor existing working code to accommodate these changes. Add new tables, new service methods, new endpoints. Existing functionality must remain untouched during gap remediation. |
| :---: | :---- |

## **Priority Order**

Work through gaps in this order. Each builds on the previous:

1. Multi-tenancy foundation — must be done before any other gap, as all other tables reference tenant\_id

2. Shift scheduling — attendance becomes meaningful only when anchored to shifts

3. Geofencing & attendance hardening — integrity of the attendance record

4. Payroll: statutory deductions & per-unit pay — legal correctness

5. Payroll: payslip generation & mobile money disbursement — operational completeness

6. Offline sync conflict resolution protocol — PWA correctness

7. WhatsApp / push notification system — operational alerting

8. Leave & absence management — payroll depends on this

9. Expense tracking — management visibility

10. Remaining quality-of-life gaps (weather, i18n, yield tracking)

## **How to Read This Document**

| CRITICAL | Legal requirement or data integrity issue — implement before going live with any new feature |
| :---- | :---- |
| **HIGH** | Missing feature that significantly limits operational usefulness of the system |
| **MEDIUM** | Quality-of-life improvement that meaningfully improves user experience or data quality |

| GAP 1: Multi-Tenancy Foundation | CRITICAL | \~3–4 days |
| :---- | :---: | :---: |

## **1.1  Problem Statement**

The current system assumes a single farm owner and a single farm. The moment a second farm owner registers, all their data is visible to the first owner. Every model query is written without a tenant scoping clause. This is a data leakage vulnerability, not just a missing feature.

| ROOT CAUSE | No tenant\_id column exists on any table. No base model enforces row-level scoping. The auth system issues sessions without farm context. |
| :---: | :---- |

## **1.2  Schema Changes**

Step 1: Add a tenants table. This is the root of your multi-tenant hierarchy.

**Table: tenants**

| Column | Type | Notes |
| :---- | :---- | :---- |
| id | BIGINT UNSIGNED PK AI |  |
| name | VARCHAR(120) | Farm business name |
| slug | VARCHAR(80) UNIQUE | Used in subdomain routing |
| plan | ENUM('trial','starter','pro') | Subscription tier |
| country\_code | CHAR(2) | Drives tax config lookup |
| timezone | VARCHAR(60) | e.g. Africa/Accra |
| trial\_ends\_at | DATETIME NULL |  |
| is\_active | TINYINT(1) DEFAULT 1 |  |
| created\_at | DATETIME |  |

Step 2: Add tenant\_id to every existing table. Run this migration script in order:

ALTER TABLE farms        ADD COLUMN tenant\_id BIGINT UNSIGNED NOT NULL AFTER id,

                         ADD INDEX idx\_tenant (tenant\_id);

ALTER TABLE users        ADD COLUMN tenant\_id BIGINT UNSIGNED NOT NULL AFTER id,

                         ADD INDEX idx\_tenant (tenant\_id);

ALTER TABLE attendance   ADD COLUMN tenant\_id BIGINT UNSIGNED NOT NULL AFTER id,

                         ADD INDEX idx\_tenant (tenant\_id);

ALTER TABLE reports      ADD COLUMN tenant\_id BIGINT UNSIGNED NOT NULL AFTER id,

                         ADD INDEX idx\_tenant (tenant\_id);

ALTER TABLE crops        ADD COLUMN tenant\_id BIGINT UNSIGNED NOT NULL AFTER id,

                         ADD INDEX idx\_tenant (tenant\_id);

ALTER TABLE animals      ADD COLUMN tenant\_id BIGINT UNSIGNED NOT NULL AFTER id,

                         ADD INDEX idx\_tenant (tenant\_id);

ALTER TABLE payroll\_records ADD COLUMN tenant\_id BIGINT UNSIGNED NOT NULL AFTER id,

                         ADD INDEX idx\_tenant (tenant\_id);

\-- Repeat for: equipment, inventory, payments, salary\_advances,

\-- payroll\_periods, payroll\_adjustments, worker\_payment\_profiles

Step 3: Populate tenant\_id on existing data. If you have existing rows, assign them all to a default tenant\_id \= 1 before adding the NOT NULL constraint.

## **1.3  Base Model Pattern**

Create a TenantAwareModel base class. Every model must extend this. It injects tenant\_id automatically on all reads, inserts, and updates.

\<?php

// /app/models/BaseModel.php

abstract class BaseModel {

    protected PDO $db;

    protected int $tenantId;

    protected string $table;

    public function \_\_construct(PDO $db, int $tenantId) {

        $this-\>db \= $db;

        $this-\>tenantId \= $tenantId;

    }

    // All reads: inject tenant scope

    protected function scopedQuery(string $sql, array $params \= \[\]): PDOStatement {

        // Append AND tenant\_id \= ? to WHERE clauses

        $sql \= $this-\>injectTenantScope($sql);

        $params\[\] \= $this-\>tenantId;

        $stmt \= $this-\>db-\>prepare($sql);

        $stmt-\>execute($params);

        return $stmt;

    }

    // All inserts: auto-add tenant\_id

    protected function insert(array $data): int {

        $data\['tenant\_id'\] \= $this-\>tenantId;

        // build INSERT from $data array...

        return (int)$this-\>db-\>lastInsertId();

    }

}

| CRITICAL RULE | Never call $this-\>db-\>prepare() directly in any model. Always use $this-\>scopedQuery(). Code review for this. One un-scoped query \= cross-tenant data leak. |
| :---: | :---- |

## **1.4  Session & Middleware Changes**

The session must store tenant\_id alongside user\_id after login. Update AuthController:

// On successful login:

$\_SESSION\['user\_id'\]   \= $user\['id'\];

$\_SESSION\['tenant\_id'\] \= $user\['tenant\_id'\];

$\_SESSION\['role'\]      \= $user\['role'\];

$\_SESSION\['farm\_id'\]   \= $user\['farm\_id'\];

Update RoleMiddleware to also validate that the resource being accessed belongs to the session tenant. Add this check to every controller action that accepts a resource ID in the URL:

// In any controller action:

$this-\>assertTenantOwns('attendance', $attendanceId);

// In BaseController:

protected function assertTenantOwns(string $table, int $id): void {

    $stmt \= $this-\>db-\>prepare(

        "SELECT id FROM {$table} WHERE id \= ? AND tenant\_id \= ? LIMIT 1"

    );

    $stmt-\>execute(\[$id, $\_SESSION\['tenant\_id'\]\]);

    if (\!$stmt-\>fetch()) { http\_response\_code(403); exit; }

}

## **1.5  Registration Flow**

When a new farm owner registers, the flow must be:

11. Create tenant record — generate slug from business name

12. Create owner user record with tenant\_id

13. Create default farm record with tenant\_id

14. Seed default roles for that tenant

15. Send welcome email / WhatsApp message

Wrap all five steps in a database transaction. If any step fails, roll back all.

| GAP 2: Shift Scheduling Module | CRITICAL | \~5–6 days |
| :---- | :---: | :---: |

## **2.1  Problem Statement**

Attendance records have no reference to what shift a worker was supposed to be on. This means the system cannot calculate lateness, early departure, or no-shows. Payroll cannot determine whether overtime was authorized. Supervisors have no way to plan the workday before it begins.

## **2.2  Schema**

**Table: shift\_templates**

| Column | Type | Notes |
| :---- | :---- | :---- |
| id | BIGINT UNSIGNED PK AI |  |
| tenant\_id | BIGINT UNSIGNED NOT NULL |  |
| farm\_id | BIGINT UNSIGNED NOT NULL |  |
| name | VARCHAR(80) | e.g. 'Morning Harvest' |
| start\_time | TIME | e.g. 06:00:00 |
| end\_time | TIME | e.g. 14:00:00 |
| break\_minutes | SMALLINT DEFAULT 30 | Unpaid break |
| recurrence | ENUM('none','daily','weekdays','custom') |  |
| recurrence\_days | VARCHAR(20) NULL | JSON: \[1,2,3,4,5\] \= Mon–Fri |
| is\_active | TINYINT(1) DEFAULT 1 |  |
| created\_at | DATETIME |  |

**Table: shift\_assignments**

| Column | Type | Notes |
| :---- | :---- | :---- |
| id | BIGINT UNSIGNED PK AI |  |
| tenant\_id | BIGINT UNSIGNED NOT NULL |  |
| shift\_template\_id | BIGINT UNSIGNED NOT NULL | FK → shift\_templates |
| worker\_id | BIGINT UNSIGNED NOT NULL | FK → users |
| farm\_id | BIGINT UNSIGNED NOT NULL | FK → farms |
| date | DATE NOT NULL | Specific calendar date |
| status | ENUM('scheduled','confirmed','no\_show','cancelled') |  |
| assigned\_by | BIGINT UNSIGNED | FK → users (supervisor/owner) |
| notes | TEXT NULL |  |
| created\_at | DATETIME |  |
|  |  | UNIQUE KEY uq\_worker\_date (tenant\_id, worker\_id, date) |

Add a foreign key from attendance to shift\_assignments so every clock-in is anchored to a scheduled shift:

ALTER TABLE attendance

    ADD COLUMN shift\_assignment\_id BIGINT UNSIGNED NULL,

    ADD COLUMN lateness\_minutes   SMALLINT DEFAULT 0,

    ADD COLUMN early\_leave\_minutes SMALLINT DEFAULT 0,

    ADD COLUMN is\_unauthorized\_overtime TINYINT(1) DEFAULT 0;

## **2.3  ShiftService.php — Core Logic**

Create /app/services/ShiftService.php with the following methods:

getAssignmentsForDate(int $farmId, string $date): array

  // Returns all shift\_assignments for a farm on a given date

  // JOIN shift\_templates to get start\_time, end\_time

  // JOIN users to get worker name, role

assignWorker(int $shiftTemplateId, int $workerId, string $date): int

  // INSERT into shift\_assignments

  // Check for existing assignment on same date (UNIQUE constraint handles DB-level)

  // Return new shift\_assignment\_id

generateWeekFromTemplate(int $templateId, string $weekStartDate): void

  // For recurring templates: generate 7 days of assignments

  // Only insert for recurrence\_days that match

  // Skip dates that already have an assignment

calculateAttendanceDeviation(int $shiftAssignmentId, string $clockIn, ?string $clockOut): array

  // Compare actual clock\_in vs shift start\_time

  // Returns: \['lateness\_minutes' \=\> int, 'early\_leave\_minutes' \=\> int,

  //           'is\_unauthorized\_overtime' \=\> bool\]

## **2.4  Updated Clock-In Logic**

When a worker clocks in, AttendanceService must now look up their shift\_assignment for today and link it:

public function clockIn(int $workerId, float $lat, float $lng): array {

    // 1\. Find today's shift\_assignment for this worker

    $assignment \= $this-\>shiftModel-\>getTodayAssignment($workerId);

    // 2\. Validate geofence (see Gap 3\)

    $this-\>assertWithinGeofence($lat, $lng, $assignment\['farm\_id'\]);

    // 3\. Calculate lateness

    $deviation \= $this-\>shiftService-\>calculateAttendanceDeviation(

        $assignment\['id'\], date('H:i:s')

    );

    // 4\. Insert attendance record with shift link

    return $this-\>attendanceModel-\>insert(\[

        'worker\_id'           \=\> $workerId,

        'shift\_assignment\_id' \=\> $assignment\['id'\],

        'clock\_in'            \=\> date('Y-m-d H:i:s'),

        'clock\_in\_lat'        \=\> $lat,

        'clock\_in\_lng'        \=\> $lng,

        'lateness\_minutes'    \=\> $deviation\['lateness\_minutes'\],

    \]);

}

## **2.5  API Endpoints**

**New endpoints — ShiftController.php**

| Method | Endpoint | Action |
| :---- | :---- | :---- |
| GET | /api/shifts/templates | List all shift templates for farm |
| POST | /api/shifts/templates | Create new template |
| PUT | /api/shifts/templates/{id} | Update template |
| GET | /api/shifts/assignments?date=YYYY-MM-DD | Get day's roster |
| POST | /api/shifts/assignments | Assign worker to shift |
| DELETE | /api/shifts/assignments/{id} | Remove assignment |
| POST | /api/shifts/generate-week | Generate week from template |
| GET | /api/shifts/worker/{id}?week=YYYY-MM-DD | Worker's week schedule |

## **2.6  UI Changes**

Supervisor interface — add a 'Roster' view under the Attendance submenu. It shows a calendar grid (day view) with workers as rows and time as columns. Each shift block is coloured by status: scheduled (blue), confirmed (green), no-show (red), absent (grey).

Worker interface — the worker Home screen must show their shift for today: start time, end time, location. If no shift is assigned, show a message instead of the clock-in button (or allow clock-in with a flag for owner review, depending on your policy).

| GAP 3: Geofencing & Attendance Hardening | CRITICAL | \~2–3 days |
| :---- | :---: | :---: |

## **3.1  Schema Changes**

Add geofence boundary to farms table. Store as a GeoJSON polygon string:

ALTER TABLE farms

    ADD COLUMN geofence\_polygon JSON NULL,

    ADD COLUMN geofence\_radius\_metres SMALLINT DEFAULT 200,

    ADD COLUMN geofence\_enabled TINYINT(1) DEFAULT 1;

Add device fingerprint and deduplication to attendance:

ALTER TABLE attendance

    ADD COLUMN device\_fingerprint VARCHAR(64) NULL,

    ADD COLUMN clock\_out\_lat DECIMAL(10,8) NULL,

    ADD COLUMN clock\_out\_lng DECIMAL(11,8) NULL,

    ADD UNIQUE KEY uq\_worker\_open (worker\_id, clock\_out)

    \-- Prevents double-open sessions at the DB level

## **3.2  Server-Side Geofence Validation**

Do not rely on the client to validate geofence. Always validate on the server. Use the Haversine formula in PHP — do not add a GIS extension dependency:

// In AttendanceService.php

private function assertWithinGeofence(float $lat, float $lng, int $farmId): void {

    $farm \= $this-\>farmModel-\>find($farmId);

    if (\!$farm\['geofence\_enabled'\]) return;

    $centerLat \= $farm\['latitude'\];

    $centerLng \= $farm\['longitude'\];

    $radius    \= $farm\['geofence\_radius\_metres'\];

    $earthR \= 6371000; // metres

    $dLat \= deg2rad($lat \- $centerLat);

    $dLng \= deg2rad($lng \- $centerLng);

    $a \= sin($dLat/2)\*\*2

       \+ cos(deg2rad($centerLat)) \* cos(deg2rad($lat)) \* sin($dLng/2)\*\*2;

    $distance \= $earthR \* 2 \* atan2(sqrt($a), sqrt(1-$a));

    if ($distance \> $radius) {

        throw new GeofenceException(

            "Clock-in rejected: {$distance}m from farm centre (max {$radius}m)"

        );

    }

}

## **3.3  Buddy Punching Prevention**

On every clock-in, check if the device\_fingerprint already has an open session for a different worker today. Generate the fingerprint client-side (User-Agent \+ screen dimensions \+ canvas fingerprint hash) and send it with the clock-in request.

// In AttendanceService.clockIn():

$existing \= $this-\>attendanceModel-\>findOpenSessionByDevice($fingerprint);

if ($existing && $existing\['worker\_id'\] \!== $workerId) {

    // Log the suspicious attempt to audit\_log

    $this-\>auditLog-\>flag('buddy\_punch\_attempt', \[

        'fingerprint' \=\> $fingerprint,

        'existing\_worker\_id' \=\> $existing\['worker\_id'\],

        'attempting\_worker\_id' \=\> $workerId,

    \]);

    throw new SecurityException('Device already in use by another worker');

}

## **3.4  Overtime & Break Rules**

Add a configurable rules table. This prevents the payroll service from having hardcoded thresholds:

**Table: overtime\_rules**

| Column | Type | Notes |
| :---- | :---- | :---- |
| id | BIGINT UNSIGNED PK AI |  |
| tenant\_id | BIGINT UNSIGNED NOT NULL |  |
| daily\_threshold\_minutes | SMALLINT DEFAULT 480 | 8 hours \= 480 min |
| weekly\_threshold\_minutes | SMALLINT DEFAULT 2400 | 40 hours \= 2400 min |
| overtime\_multiplier | DECIMAL(4,2) DEFAULT 1.50 | e.g. 1.5x |
| break\_minutes\_per\_shift | SMALLINT DEFAULT 30 | Unpaid break |
| effective\_from | DATE |  |
| effective\_to | DATE NULL |  |

| GAP 4: Payroll: Statutory Deductions & Per-Unit Pay | CRITICAL | \~4–5 days |
| :---- | :---: | :---: |

## **4.1  Problem Statement**

The current payroll system calculates gross wages only. It has no concept of PAYE income tax, SSNIT contributions, or pension. Disbursing gross wages to workers violates Ghana Revenue Authority requirements. Additionally, per-unit output pay (piece-rate) is listed in the plan but has no supporting data model.

## **4.2  Tax Configuration Schema**

Store tax bands as data, not code. This makes annual updates a data change, not a deployment:

**Table: tax\_configurations**

| Column | Type | Notes |
| :---- | :---- | :---- |
| id | BIGINT UNSIGNED PK AI |  |
| country\_code | CHAR(2) NOT NULL | GH, NG, KE, etc. |
| tax\_type | ENUM('PAYE','SSNIT\_EMPLOYEE','SSNIT\_EMPLOYER','PENSION') |  |
| tax\_year | YEAR NOT NULL |  |
| band\_from | DECIMAL(12,2) | Monthly income lower bound |
| band\_to | DECIMAL(12,2) NULL | NULL \= no upper limit |
| rate | DECIMAL(6,4) | e.g. 0.1500 \= 15% |
| flat\_amount | DECIMAL(12,2) DEFAULT 0 | Additional flat deduction |
|  |  | INDEX idx\_country\_year (country\_code, tax\_year) |

Seed Ghana 2025 PAYE bands (monthly equivalent):

INSERT INTO tax\_configurations (country\_code, tax\_type, tax\_year, band\_from, band\_to, rate) VALUES

('GH', 'PAYE', 2025,    0.00,   319.00, 0.0000),

('GH', 'PAYE', 2025,  319.01,   479.00, 0.0500),

('GH', 'PAYE', 2025,  479.01,   959.00, 0.1000),

('GH', 'PAYE', 2025,  959.01,  1438.00, 0.1750),

('GH', 'PAYE', 2025, 1438.01,  4785.00, 0.2500),

('GH', 'PAYE', 2025, 4785.01,      NULL, 0.3500),

('GH', 'SSNIT\_EMPLOYEE', 2025, 0, NULL, 0.0550),

('GH', 'SSNIT\_EMPLOYER', 2025, 0, NULL, 0.1300);

## **4.3  Statutory Deduction Calculator**

Add calculateStatutoryDeductions() to PayrollService.php:

public function calculateStatutoryDeductions(float $grossMonthly, string $countryCode, int $year): array {

    $bands \= $this-\>taxModel-\>getBands($countryCode, 'PAYE', $year);

    $paye  \= 0;

    $remaining \= $grossMonthly;

    foreach ($bands as $band) {

        $upper \= $band\['band\_to'\] ?? PHP\_FLOAT\_MAX;

        $taxable \= min($remaining, $upper \- $band\['band\_from'\]);

        if ($taxable \<= 0\) break;

        $paye \+= $taxable \* $band\['rate'\];

        $remaining \-= $taxable;

    }

    $ssnitEmployee \= $grossMonthly \* $this-\>taxModel-\>getRate($countryCode, 'SSNIT\_EMPLOYEE', $year);

    $ssnitEmployer \= $grossMonthly \* $this-\>taxModel-\>getRate($countryCode, 'SSNIT\_EMPLOYER', $year);

    return \[

        'paye'            \=\> round($paye, 2),

        'ssnit\_employee'  \=\> round($ssnitEmployee, 2),

        'ssnit\_employer'  \=\> round($ssnitEmployer, 2),

        'total\_deductions'=\> round($paye \+ $ssnitEmployee, 2),

        'net\_pay'         \=\> round($grossMonthly \- $paye \- $ssnitEmployee, 2),

    \];

}

Add ssnit\_employee, ssnit\_employer, paye, net\_pay columns to payroll\_records:

ALTER TABLE payroll\_records

    ADD COLUMN gross\_pay         DECIMAL(12,2) NOT NULL DEFAULT 0,

    ADD COLUMN paye\_deduction    DECIMAL(12,2) NOT NULL DEFAULT 0,

    ADD COLUMN ssnit\_employee    DECIMAL(12,2) NOT NULL DEFAULT 0,

    ADD COLUMN ssnit\_employer    DECIMAL(12,2) NOT NULL DEFAULT 0,

    ADD COLUMN other\_deductions  DECIMAL(12,2) NOT NULL DEFAULT 0,

    ADD COLUMN net\_pay           DECIMAL(12,2) NOT NULL DEFAULT 0;

## **4.4  Per-Unit Output Pay**

Add a production\_records table to capture piece-rate work. The payroll service joins this table when the worker's payment profile is set to 'per\_unit':

**Table: production\_records**

| Column | Type | Notes |
| :---- | :---- | :---- |
| id | BIGINT UNSIGNED PK AI |  |
| tenant\_id | BIGINT UNSIGNED NOT NULL |  |
| worker\_id | BIGINT UNSIGNED NOT NULL | FK → users |
| farm\_id | BIGINT UNSIGNED NOT NULL |  |
| crop\_id | BIGINT UNSIGNED NULL | FK → crops (nullable) |
| date | DATE NOT NULL |  |
| unit\_type | VARCHAR(40) | e.g. 'crates', 'kg', 'bunches' |
| quantity | DECIMAL(10,2) NOT NULL |  |
| rate\_per\_unit | DECIMAL(10,4) NOT NULL | At time of recording |
| total\_amount | DECIMAL(12,2) GENERATED AS (quantity \* rate\_per\_unit) STORED |  |
| recorded\_by | BIGINT UNSIGNED NOT NULL | FK → users (supervisor) |
| notes | TEXT NULL |  |
| created\_at | DATETIME |  |

In PayrollService.calculateWages(), add a branch for per-unit workers:

case 'per\_unit':

    $productions \= $this-\>productionModel-\>sumForPeriod(

        $workerId, $period\['start\_date'\], $period\['end\_date'\]

    );

    $grossPay \= $productions\['total\_amount'\];

    break;

| GAP 5: Payslip Generation & Mobile Money Disbursement | HIGH | \~3–4 days |
| :---- | :---: | :---: |

## **5.1  Payslip PDF Generation**

Install mPDF via Composer (works without a browser binary, unlike Puppeteer):

composer require mpdf/mpdf

Create /app/services/PayslipService.php. The generate() method produces a PDF payslip and saves it to storage:

public function generate(int $payrollRecordId): string {

    $record \= $this-\>payrollModel-\>findWithWorker($payrollRecordId);

    $html   \= $this-\>renderTemplate($record); // load payslip.html template

    $mpdf   \= new \\Mpdf\\Mpdf(\['margin\_top' \=\> 15\]);

    $mpdf-\>WriteHTML($html);

    $filename \= "payslip\_{$record\['worker\_id'\]}\_{$record\['period\_end'\]}.pdf";

    $path     \= STORAGE\_PATH . '/payslips/' . $filename;

    $mpdf-\>Output($path, \\Mpdf\\Output\\Destination::FILE);

    // Store path in payroll\_records.payslip\_path

    $this-\>payrollModel-\>updatePayslipPath($payrollRecordId, $path);

    return $path;

}

The payslip HTML template must show: worker name, ID, period, pay breakdown table (gross, PAYE, SSNIT, deductions, net), payment method, and farm logo. Store the template at /storage/templates/payslip.html. Use simple token replacement (str\_replace) — no Twig dependency needed.

| FILE SECURITY | Payslips are stored outside /public. Serve them via a controller that verifies the requesting user is the worker on the payslip or has the owner/accountant role. Never expose the file path directly. |
| :---: | :---- |

## **5.2  Mobile Money Integration (MTN MoMo / Paystack)**

For Ghana, integrate Paystack as the payment rail — it supports MTN MoMo, Vodafone Cash, AirtelTigo Money, and bank transfers from a single API.

**Table: payment\_methods**

| Column | Type | Notes |
| :---- | :---- | :---- |
| id | BIGINT UNSIGNED PK AI |  |
| tenant\_id | BIGINT UNSIGNED NOT NULL |  |
| worker\_id | BIGINT UNSIGNED NOT NULL | FK → users |
| method\_type | ENUM('momo\_mtn','momo\_vodafone','bank','cash') |  |
| phone\_number | VARCHAR(15) NULL | For MoMo |
| bank\_code | VARCHAR(10) NULL | Paystack bank code |
| account\_number | VARCHAR(20) NULL |  |
| account\_name | VARCHAR(120) NULL | Must match bank records |
| is\_primary | TINYINT(1) DEFAULT 1 |  |
| is\_verified | TINYINT(1) DEFAULT 0 | Paystack account resolution |

Add disbursement tracking to the payments table:

ALTER TABLE payments

    ADD COLUMN payment\_method\_id    BIGINT UNSIGNED NULL,

    ADD COLUMN provider\_reference   VARCHAR(80)  NULL,

    ADD COLUMN provider\_status      VARCHAR(40)  NULL,

    ADD COLUMN disbursed\_at         DATETIME     NULL,

    ADD COLUMN failure\_reason        TEXT         NULL;

PaymentService.php — disbursement method:

public function disburse(int $payrollRecordId): array {

    $record \= $this-\>payrollModel-\>find($payrollRecordId);

    $method \= $this-\>paymentMethodModel-\>getPrimaryForWorker($record\['worker\_id'\]);

    if ($method\['method\_type'\] \=== 'cash') {

        return $this-\>markCashPaid($payrollRecordId);

    }

    // Paystack Transfer API

    $transferRecipient \= $this-\>paystackApi-\>createTransferRecipient(\[

        'type'           \=\> $method\['method\_type'\] \=== 'bank' ? 'nuban' : 'mobile\_money',

        'name'           \=\> $method\['account\_name'\],

        'account\_number' \=\> $method\['phone\_number'\] ?? $method\['account\_number'\],

        'bank\_code'      \=\> $method\['bank\_code'\],

        'currency'       \=\> 'GHS',

    \]);

    $transfer \= $this-\>paystackApi-\>initiateTransfer(\[

        'source'    \=\> 'balance',

        'amount'    \=\> (int)($record\['net\_pay'\] \* 100), // Kobo

        'recipient' \=\> $transferRecipient\['recipient\_code'\],

        'reason'    \=\> "Payroll {$record\['period\_end'\]}",

    \]);

    $this-\>paymentModel-\>recordDisbursement($payrollRecordId, $transfer);

    return $transfer;

}

Handle Paystack webhooks at /api/webhooks/paystack. Verify HMAC signature. Update payment status on transfer.success and transfer.failed events.

| GAP 6: Offline PWA Sync Protocol | CRITICAL | \~4–5 days |
| :---- | :---: | :---: |

## **6.1  Problem Statement**

The plan mentions IndexedDB and background sync but does not define the conflict resolution rules. Without explicit rules, two workers syncing from the same shift produce duplicate records, and offline edits silently overwrite server data.

## **6.2  Offline Queue Schema (IndexedDB)**

Define the IndexedDB stores in your service worker setup. Create exactly two stores:

// In service-worker.js or a db.js module

const DB\_NAME    \= 'farm\_offline\_v1';

const DB\_VERSION \= 1;

// Store 1: outbox — pending requests to sync

// keyPath: id (auto-increment)

// Indexes: status, created\_at

{

  name: 'outbox',

  keyPath: 'id',

  autoIncrement: true,

  indexes: \['status', 'endpoint', 'created\_at'\]

}

// Store 2: cache — local copies of server data for read-while-offline

// keyPath: key (string: 'attendance:2026-01-15:worker:42')

{

  name: 'cache',

  keyPath: 'key',

  indexes: \['entity\_type', 'expires\_at'\]

}

Every outbox record has this shape:

{

  id:           auto,

  endpoint:     '/api/attendance/clock-in',

  method:       'POST',

  payload:      { ...clockInData },

  client\_time:  '2026-01-15T06:14:22Z',  // ISO8601, always UTC

  status:       'pending',                // pending | syncing | done | failed

  attempts:     0,

  last\_attempt: null,

  error:        null,

}

## **6.3  Conflict Resolution Rules**

Define these rules explicitly. They are your contract between the client and server:

**Conflict rules by entity**

| Entity | Rule | Rationale |
| :---- | :---- | :---- |
| Clock-in timestamp | Client timestamp wins — server records it as-is | Worker's phone is the source of truth for when they arrived |
| Clock-out timestamp | Client timestamp wins | Same as above |
| Report text | Last-write wins — use client\_time to determine last write | Text is not merged; user's last version is correct |
| Report photos | Append-only — never delete on conflict | Photos can't be reconstructed if lost |
| Crop updates | Field-level merge — only overwrite fields included in the payload | Two supervisors may update different fields offline |
| Animal health records | Append-only — insert new record, never update | Health events are a log, not a state |
| Production records | Insert-only — reject duplicates by (worker\_id, date, crop\_id) | Prevent double-counting |

## **6.4  Server-Side Sync Endpoint**

Add a batch sync endpoint that the service worker POSTs to on reconnect:

POST /api/sync/batch

{

  "items": \[

    { "client\_id": "abc123", "endpoint": "/api/attendance/clock-in",

      "payload": {...}, "client\_time": "2026-01-15T06:14:22Z" },

    { "client\_id": "def456", "endpoint": "/api/reports",

      "payload": {...}, "client\_time": "2026-01-15T07:02:10Z" }

  \]

}

The server processes each item, applies the conflict rules above, and returns a results array. The client uses client\_id to match results back to outbox records and update their status:

{

  "results": \[

    { "client\_id": "abc123", "status": "ok",     "server\_id": 892 },

    { "client\_id": "def456", "status": "duplicate", "server\_id": 445 }

  \]

}

## **6.5  Photo Upload Strategy**

Photos taken offline must be compressed before storing in IndexedDB to avoid hitting the 50MB quota. Add this to your report submission flow on the client:

async function compressPhoto(file) {

  const bitmap \= await createImageBitmap(file);

  const canvas \= document.createElement('canvas');

  const MAX\_DIM \= 1280;

  const scale   \= Math.min(1, MAX\_DIM / Math.max(bitmap.width, bitmap.height));

  canvas.width  \= bitmap.width  \* scale;

  canvas.height \= bitmap.height \* scale;

  canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);

  return new Promise(resolve \=\> canvas.toBlob(resolve, 'image/jpeg', 0.72));

}

Store the compressed blob as a base64 string in the outbox payload. During batch sync, the server receives it as a multipart field and writes it to /storage/uploads/ as normal.

## **6.6  Background Sync Registration**

Register a sync tag in the service worker. The browser triggers this when connectivity is restored, even if the tab is closed:

// In your PWA's main JS (when user submits offline):

await outboxDB.add('outbox', pendingRecord);

if ('serviceWorker' in navigator && 'SyncManager' in window) {

  const reg \= await navigator.serviceWorker.ready;

  await reg.sync.register('farm-sync');

}

// In service-worker.js:

self.addEventListener('sync', event \=\> {

  if (event.tag \=== 'farm-sync') {

    event.waitUntil(processSyncQueue());

  }

});

async function processSyncQueue() {

  const items \= await outboxDB.getAll('outbox', 'pending');

  if (\!items.length) return;

  const resp  \= await fetch('/api/sync/batch', {

    method: 'POST',

    headers: { 'Content-Type': 'application/json' },

    body: JSON.stringify({ items }),

  });

  const { results } \= await resp.json();

  for (const result of results) {

    await outboxDB.updateStatus('outbox', result.client\_id, result.status);

  }

}

| GAP 7: Notification System — WhatsApp & Push | HIGH | \~3 days |
| :---- | :---: | :---: |

## **7.1  Schema**

**Table: notifications**

| Column | Type | Notes |
| :---- | :---- | :---- |
| id | BIGINT UNSIGNED PK AI |  |
| tenant\_id | BIGINT UNSIGNED NOT NULL |  |
| user\_id | BIGINT UNSIGNED NOT NULL | Recipient |
| type | VARCHAR(60) | e.g. 'payslip\_ready', 'shift\_reminder' |
| title | VARCHAR(120) |  |
| body | TEXT |  |
| data\_json | JSON NULL | Deep-link data |
| channel | SET('in\_app','push','whatsapp','sms') |  |
| read\_at | DATETIME NULL |  |
| sent\_at | DATETIME NULL |  |
| created\_at | DATETIME |  |

## **7.2  NotificationService.php**

Centralise all notification dispatch here. Never call WhatsApp or FCM directly from a controller:

public function send(int $userId, string $type, array $data): void {

    $user  \= $this-\>userModel-\>find($userId);

    $prefs \= $this-\>getChannelPrefs($userId);

    $title \= $this-\>renderTitle($type, $data);

    $body  \= $this-\>renderBody($type, $data);

    // Always create in-app record

    $this-\>notificationModel-\>insert(\[...\]);

    if ($prefs\['push'\] && $user\['fcm\_token'\]) {

        $this-\>sendPush($user\['fcm\_token'\], $title, $body, $data);

    }

    if ($prefs\['whatsapp'\] && $user\['phone'\]) {

        $this-\>sendWhatsApp($user\['phone'\], $type, $data);

    }

}

## **7.3  WhatsApp Business API Integration**

Use the official Meta WhatsApp Business Cloud API (free for first 1,000 service conversations/month). You need a Meta Business account and a phone number registered on the platform.

private function sendWhatsApp(string $phone, string $type, array $data): void {

    $templateMap \= \[

        'payslip\_ready'   \=\> 'payslip\_notification',

        'shift\_reminder'  \=\> 'shift\_reminder\_template',

        'payroll\_paid'    \=\> 'payment\_confirmation',

    \];

    $templateName \= $templateMap\[$type\] ?? null;

    if (\!$templateName) return; // no WA template for this type

    $payload \= \[

        'messaging\_product' \=\> 'whatsapp',

        'to'                \=\> $phone,

        'type'              \=\> 'template',

        'template'          \=\> \[

            'name'     \=\> $templateName,

            'language' \=\> \['code' \=\> 'en'\],

            'components' \=\> $this-\>buildTemplateComponents($type, $data),

        \]

    \];

    $this-\>httpPost('https://graph.facebook.com/v19.0/' . WA\_PHONE\_NUMBER\_ID . '/messages', $payload, \[

        'Authorization: Bearer ' . WA\_ACCESS\_TOKEN

    \]);

}

## **7.4  Trigger Points**

Wire up NotificationService::send() at these points in your existing services:

**Notification triggers**

| Event | Recipient(s) | Channels |
| :---- | :---- | :---- |
| Clock-in outside geofence | Supervisor | in\_app, push |
| Shift starts in 60 minutes | Worker | push, whatsapp |
| No clock-in 30min after shift start | Supervisor | in\_app, push |
| Payslip generated | Worker | in\_app, whatsapp |
| Payment disbursed | Worker | push, whatsapp |
| Crop health alert (disease flag) | Owner, Supervisor | in\_app, push, whatsapp |
| Vaccination overdue | Owner | in\_app, push |
| Leave request submitted | Supervisor | in\_app, push |
| Leave request approved/rejected | Worker | in\_app, push, whatsapp |

Schedule the shift reminder and no-show check via a cron job that runs every 5 minutes on the server. Add a cron entry:

\*/5 \* \* \* \* php /var/www/farm/artisan schedule:run \>\> /var/log/farm-cron.log 2\>&1

| GAP 8: Leave & Absence Management | HIGH | \~2–3 days |
| :---- | :---: | :---: |

## **8.1  Schema**

**Table: leave\_requests**

| Column | Type | Notes |
| :---- | :---- | :---- |
| id | BIGINT UNSIGNED PK AI |  |
| tenant\_id | BIGINT UNSIGNED NOT NULL |  |
| worker\_id | BIGINT UNSIGNED NOT NULL | FK → users |
| type | ENUM('sick','annual','maternity','paternity','unpaid','compassionate') |  |
| start\_date | DATE NOT NULL |  |
| end\_date | DATE NOT NULL |  |
| days\_requested | TINYINT GENERATED AS (DATEDIFF(end\_date,start\_date)+1) STORED |  |
| reason | TEXT NULL |  |
| status | ENUM('pending','approved','rejected','cancelled') | DEFAULT pending |
| reviewed\_by | BIGINT UNSIGNED NULL | FK → users |
| reviewed\_at | DATETIME NULL |  |
| rejection\_reason | TEXT NULL |  |
| created\_at | DATETIME |  |

**Table: leave\_balances**

| Column | Type | Notes |
| :---- | :---- | :---- |
| id | BIGINT UNSIGNED PK AI |  |
| tenant\_id | BIGINT UNSIGNED NOT NULL |  |
| worker\_id | BIGINT UNSIGNED NOT NULL | FK → users |
| leave\_type | VARCHAR(30) | Matches leave\_requests.type |
| year | YEAR NOT NULL |  |
| entitled\_days | TINYINT DEFAULT 15 | Configurable per role |
| used\_days | TINYINT DEFAULT 0 | Updated on approval |
|  |  | UNIQUE KEY uq\_worker\_type\_year (tenant\_id, worker\_id, leave\_type, year) |

## **8.2  Payroll Integration**

When a payroll period is generated, LeaveService must flag unpaid leave absences as deductions. Add this to PayrollService.calculateWages():

$unpaidDays \= $this-\>leaveService-\>getUnpaidAbsenceDays($workerId, $periodStart, $periodEnd);

$dailyRate  \= $grossPay / $workingDaysInPeriod;

$deduction  \= $unpaidDays \* $dailyRate;

$grossPay  \-= $deduction;

// Record in payroll\_adjustments with type='unpaid\_leave\_deduction'

| GAP 9: Expense Tracking | HIGH | \~2 days |
| :---- | :---: | :---: |

## **9.1  Schema**

**Table: farm\_expenses**

| Column | Type | Notes |
| :---- | :---- | :---- |
| id | BIGINT UNSIGNED PK AI |  |
| tenant\_id | BIGINT UNSIGNED NOT NULL |  |
| farm\_id | BIGINT UNSIGNED NOT NULL |  |
| category | ENUM('labour','inputs','equipment','fuel','utilities','other') |  |
| description | VARCHAR(200) |  |
| amount | DECIMAL(12,2) NOT NULL |  |
| currency | CHAR(3) DEFAULT 'GHS' |  |
| vendor | VARCHAR(120) NULL |  |
| receipt\_path | VARCHAR(255) NULL | Stored outside /public |
| crop\_id | BIGINT UNSIGNED NULL | Assign cost to a crop |
| expense\_date | DATE NOT NULL |  |
| approved\_by | BIGINT UNSIGNED NULL | FK → users |
| approved\_at | DATETIME NULL |  |
| submitted\_by | BIGINT UNSIGNED NOT NULL | FK → users |
| created\_at | DATETIME |  |

The owner dashboard should show a monthly expense breakdown by category using Chart.js. The crop\_id foreign key enables cost-per-harvest calculation: sum all expenses where crop\_id \= X and compare against actual\_yield\_kg \* market\_price\_per\_kg.

| GAP 10: Audit Log Integrity & Security Hardening | CRITICAL | \~2 days |
| :---- | :---: | :---: |

## **10.1  Hash-Chain Audit Log**

The current audit\_log table is mutable — any database user with write access can alter it. Add a hash chain: each row stores the SHA-256 of its own content plus the previous row's hash, making tampering detectable.

ALTER TABLE audit\_logs

    ADD COLUMN row\_hash      CHAR(64) NULL,

    ADD COLUMN prev\_hash     CHAR(64) NULL;

// In AuditService.log():

$prevHash \= $this-\>auditModel-\>getLastHash($tenantId);

$content  \= json\_encode(\[$tenantId, $userId, $action, $entityType,

                         $entityId, $oldData, $newData, $createdAt\]);

$rowHash  \= hash('sha256', $prevHash . $content);

$this-\>auditModel-\>insert(\[..., 'row\_hash' \=\> $rowHash, 'prev\_hash' \=\> $prevHash\]);

Add a daily cron job that verifies the hash chain is intact. If any row's hash doesn't match, email the owner immediately.

## **10.2  API Rate Limiting with Redis**

Install Predis (pure PHP Redis client, no extension needed):

composer require predis/predis

Add rate limiting middleware. Apply stricter limits to the clock-in endpoint:

// In RateLimitMiddleware.php

public function handle(string $identifier, int $maxRequests, int $windowSeconds): void {

    $key   \= "rl:{$identifier}:" . floor(time() / $windowSeconds);

    $count \= $this-\>redis-\>incr($key);

    if ($count \=== 1\) {

        $this-\>redis-\>expire($key, $windowSeconds);

    }

    if ($count \> $maxRequests) {

        http\_response\_code(429);

        header('Retry-After: ' . ($windowSeconds \- (time() % $windowSeconds)));

        echo json\_encode(\['error' \=\> 'Too many requests'\]);

        exit;

    }

}

// Apply to clock-in endpoint: 10 requests per 60 seconds per user

// Apply to login endpoint:    5 attempts per 300 seconds per IP

# **Implementation Checklist**

Use this as your daily checklist. Tick each item before marking the gap done.

**Gap 1 — Multi-Tenancy**

| \# | Task | Done? |
| :---- | :---- | :---- |
| 1.1 | Create tenants table migration | \[ \] |
| 1.2 | Add tenant\_id to all 12 existing tables | \[ \] |
| 1.3 | Populate tenant\_id on all existing rows | \[ \] |
| 1.4 | Create BaseModel with scopedQuery() | \[ \] |
| 1.5 | Update all models to extend BaseModel | \[ \] |
| 1.6 | Update session to store tenant\_id | \[ \] |
| 1.7 | Add assertTenantOwns() to BaseController | \[ \] |
| 1.8 | Update registration flow with transaction | \[ \] |
| 1.9 | Test: owner A cannot see owner B data | \[ \] |

**Gap 2 — Shift Scheduling**

| \# | Task | Done? |
| :---- | :---- | :---- |
| 2.1 | Create shift\_templates table | \[ \] |
| 2.2 | Create shift\_assignments table | \[ \] |
| 2.3 | Add shift\_assignment\_id, lateness cols to attendance | \[ \] |
| 2.4 | Create ShiftService.php with all 4 methods | \[ \] |
| 2.5 | Update AttendanceService.clockIn() to link shifts | \[ \] |
| 2.6 | Create ShiftController with all 8 endpoints | \[ \] |
| 2.7 | Build supervisor roster UI | \[ \] |
| 2.8 | Update worker Home to show today's shift | \[ \] |

**Gap 4 — Payroll Statutory**

| \# | Task | Done? |
| :---- | :---- | :---- |
| 4.1 | Create tax\_configurations table | \[ \] |
| 4.2 | Seed Ghana 2025 PAYE \+ SSNIT bands | \[ \] |
| 4.3 | Add statutory columns to payroll\_records | \[ \] |
| 4.4 | Add calculateStatutoryDeductions() to PayrollService | \[ \] |
| 4.5 | Create production\_records table | \[ \] |
| 4.6 | Add per-unit branch to calculateWages() | \[ \] |
| 4.7 | Create ProductionController \+ endpoints | \[ \] |
| 4.8 | Add production entry UI for supervisors | \[ \] |

**Gap 6 — Offline Sync**

| \# | Task | Done? |
| :---- | :---- | :---- |
| 6.1 | Define IndexedDB stores (outbox \+ cache) | \[ \] |
| 6.2 | Implement compressPhoto() client-side | \[ \] |
| 6.3 | Build processSyncQueue() in service worker | \[ \] |
| 6.4 | Create POST /api/sync/batch endpoint | \[ \] |
| 6.5 | Implement conflict rules per entity in batch handler | \[ \] |
| 6.6 | Register background sync tag | \[ \] |
| 6.7 | Test: clock-in offline, reconnect, verify server record | \[ \] |
| 6.8 | Test: photo submitted offline syncs with correct timestamp | \[ \] |

# **Migration Execution Order**

Run migrations in exactly this order. Each step depends on the previous:

16. 001\_create\_tenants.sql

17. 002\_add\_tenant\_id\_to\_all\_tables.sql  ← run with SET FOREIGN\_KEY\_CHECKS=0

18. 003\_populate\_tenant\_id\_existing\_data.sql

19. 004\_create\_shift\_templates.sql

20. 005\_create\_shift\_assignments.sql

21. 006\_alter\_attendance\_shift\_cols.sql

22. 007\_create\_overtime\_rules.sql

23. 008\_create\_tax\_configurations.sql

24. 009\_seed\_ghana\_tax\_bands.sql

25. 010\_alter\_payroll\_records\_statutory.sql

26. 011\_create\_production\_records.sql

27. 012\_create\_payment\_methods.sql

28. 013\_alter\_payments\_disbursement.sql

29. 014\_create\_notifications.sql

30. 015\_create\_leave\_requests.sql

31. 016\_create\_leave\_balances.sql

32. 017\_create\_farm\_expenses.sql

33. 018\_alter\_audit\_logs\_hash\_chain.sql

| BACKUP FIRST | Take a full mysqldump of the production database before running migration 001\. Keep it for 30 days minimum. Migrations 002 and 003 alter every table in the database. |
| :---: | :---- |

# **Testing Reference**

## **Critical Test Cases**

These are the tests that must pass before any gap is considered done. Write them as PHPUnit tests in /tests/:

**Must-pass tests**

| Test | Expected result |
| :---- | :---- |
| Owner A logs in, requests GET /api/attendance | Returns only records with tenant\_id \= A |
| Owner B worker clocks in on Owner A's farm URL | 403 Forbidden |
| Worker clocks in 400m outside geofence | 422 with GeofenceException message |
| Two workers use same device fingerprint | Second clock-in flagged and blocked |
| calculateStatutoryDeductions(3000, 'GH', 2025\) | Returns PAYE ≈ 305.80, SSNIT ≈ 165.00 |
| Batch sync with duplicate clock-in | Server returns 'duplicate', no extra attendance row created |
| Leave request approved — payroll calculation | Deduction for unpaid days appears in payroll\_adjustments |
| Payslip generated — download by correct worker | 200 OK with PDF content-type |
| Payslip download by different worker | 403 Forbidden |
| Audit log hash chain — tamper one row | Integrity check detects mismatch on that row |

## **Load Testing**

Before going live with multiple farms on the same server, run a load test to confirm the tenant-scoped queries perform acceptably. Use Apache Bench:

ab \-n 1000 \-c 20 \-H 'Authorization: Bearer \<token\>' \\

   https://yourfarm.com/api/attendance?date=2026-01-15

Acceptable response time: under 200ms at p95 for attendance list. If slower, add a composite index on (tenant\_id, farm\_id, date) to the attendance table.

# **Final Notes for the Developer**

Work through one gap at a time, fully. Resist the temptation to scaffold all tables first and fill in logic later — the logic often reveals schema changes you didn't anticipate, and half-built schemas in production are dangerous.

The multi-tenancy gap (Gap 1\) is the only blocker — everything else can be done in any order after it. If you are under time pressure, ship Gap 1, Gap 3 (geofencing), and Gap 4 (statutory deductions) first. These three together make the system legally compliant and data-safe. Everything else is an improvement on top of a correct foundation.

Keep this document open while you work. Each section is written to be self-contained — you should not need to look anything up to implement what is described here.

| VERSION NOTE | This spec was written for the system as of its initial implementation (March 2026). If major refactoring has occurred since then, review the schema sections against your actual database before running any migration. |
| :---: | :---- |

*End of document — Farm Workforce & Operations Management System — Technical Gaps Remediation Specification v1.0*