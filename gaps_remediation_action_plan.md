# Cultiv — Gaps Remediation Action Plan
## Post-Implementation Technical Remediation (March 2026)

This document outlines the step-by-step action plan to address the 10 critical and high-priority technical gaps identified in the Farm Workforce & Operations Management System. 

> [!IMPORTANT]
> **Priority Rule**: Multi-tenancy (Phase 1) is the absolute foundation. No other feature should be implemented before tenant isolation is in place to prevent data leakage.

---

## Phase 1: Foundation & Security (Critical)
**Goal:** Establish multi-tenant isolation and harden system integrity.

### 1.1 Multi-Tenancy Implementation
- [ ] **Database**: Create `tenants` table.
- [ ] **Database**: Add `tenant_id` to all existing tables (12+ tables).
- [ ] **Database**: Populate `tenant_id=1` for existing data and enforce `NOT NULL`.
- [ ] **Auth**: Update `AuthController` and session handling to store and enforce `tenant_id`.
- [ ] **Controller**: Add `assertTenantOwns()` to `BaseController` for resource-level authorization.

### 1.2 Audit Log Integrity & Rate Limiting
- [ ] **Audit**: Implement Hash-Chain in `audit_logs` (SHA-256 of row + previous hash).
- [ ] **Audit**: Add a cron job to verify audit log integrity daily.
- [ ] **Security**: Install `predis/predis` and implement rate-limiting middleware.
- [ ] **Security**: Apply strict rate limits to `clock-in` (10/min) and `login` (5/5min) endpoints.

---

## Phase 2: Operational Integrity (Critical)
**Goal:** Enable precise attendance tracking and location verification.

### 2.1 Shift Scheduling
- [ ] **Schema**: Create `shift_templates` and `shift_assignments` tables.
- [ ] **Logic**: Create `ShiftService.php` to handle rosters, recurring templates, and attendance deviation.
- [ ] **Integration**: Link `attendance` records to `shift_assignment_id`.
- [ ] **UI**: Build the Supervisor 'Roster' grid view.
- [ ] **UI**: Update Worker Home screen to show today's scheduled shift.

### 2.2 Geofencing & Hardening
- [ ] **Schema**: Add `geofence_polygon` and `geofence_radius_metres` to `farms`.
- [ ] **Backend**: Implement server-side Haversine validation in `AttendanceService`.
- [ ] **Security**: Implement client-side device fingerprinting to prevent 'buddy punching'.

---

## Phase 3: Payroll & Statutory Compliance (Critical/High)
**Goal:** Ensure legal correctness for wages and deductions in Ghana.

### 3.1 Statutory Deductions (PAYE/SSNIT)
- [ ] **Schema**: Create `tax_configurations` table.
- [ ] **Data**: Seed Ghana 2025 PAYE and SSNIT (Employee/Employer) bands.
- [ ] **Logic**: Implement `calculateStatutoryDeductions()` in `PayrollService`.
- [ ] **Integration**: Update `payroll_records` with gross, net, and deduction columns.

### 3.2 Piece-Rate (Per-Unit) Pay
- [ ] **Schema**: Create `production_records` table to track crates, kg, bunches, etc.
- [ ] **Logic**: Update `PayrollService` to branch calculation for `payment_type = 'per_unit'`.
- [ ] **UI**: Add supervisor interface for recording production units.

---

## Phase 4: Financial Automation & Leave (High)
**Goal:** Automate payments and manage worker absences.

### 4.1 Payslips & Mobile Money
- [x] **Integration**: Install `mpdf/mpdf` for PDF generation.
- [x] **Logic**: Create `PayslipService.php` to generate and secure PDF payslips.
- [x] **Payments**: Integrate Paystack Transfer API for MTN MoMo / Vodafone Cash.
- [x] **Automation**: Implement webhooks for payment status updates.

### 4.2 Leave & Absence Management
- [x] **Schema**: Create `leave_requests` and `leave_balances` tables.
- [x] **Logic**: Implement `LeaveService.php` for request approval workflow.
- [x] **Integration**: Auto-deduct unpaid leave days during payroll generation.

---

## Phase 5: PWA Robustness & Expenses (High)
**Goal:** Improve offline reliability and track farm expenditure.

### 5.1 Offline Sync Protocol (Conflict Resolution)
- [ ] **PWA**: Define explicit IndexedDB stores for `outbox` and `cache`.
- [ ] **PWA**: Implement client-side photo compression before storage.
- [ ] **Backend**: Create `/api/sync/batch` with entity-specific conflict rules (e.g., Client-wins for timestamps).

### 5.2 Expense Tracking
- [ ] **Schema**: Create `farm_expenses` table with category and `crop_id` links.
- [ ] **UI**: Build owner dashboard widgets for monthly expense breakdown.
- [ ] **Analytics**: Implement 'Cost-per-Harvest' calculation using expense data.

---

## Phase 6: Engagement & Quality of Life (Medium)
**Goal:** Improve communication and user experience.

### 6.1 WhatsApp & Push Notifications
- [ ] **Integration**: Connect Meta WhatsApp Business Cloud API.
- [ ] **Logic**: Create `NotificationService.php` for multi-channel alerts.
- [ ] **Triggers**: Implement alerts for:
    - Clock-in outside geofence (Supervisor)
    - Shift reminders (Worker)
    - Payslip ready (Worker)
    - Crop health alerts (Owner)

---

## Implementation Checklist (Summary)
1. **Foundation**: Multi-tenancy & Security (Gaps 1, 10)
2. **Operations**: Shifts & Geofencing (Gaps 2, 3)
3. **Legal**: Statutory Payroll & Piece-rate (Gap 4)
4. **Disbursement**: MoMo & Payslips (Gap 5)
5. **Absence**: Leave Management (Gap 8)
6. **Data**: Offline Sync & Expenses (Gaps 6, 9)
7. **Communication**: Notifications (Gap 7)
