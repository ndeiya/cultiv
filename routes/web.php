<?php
/**
 * Web Routes
 * Page routes for the Cultiv application.
 */

// ── Public Routes ─────────────────────────────────────
$router->get('/login', 'AuthController', 'showLoginForm');
$router->post('/login', 'AuthController', 'login');
$router->get('/logout', 'AuthController', 'logout');

// Home — redirect to role-specific dashboard
$router->get('/', 'AuthController', 'home');

// PWA Assets
$router->get('/manifest.json', function() {
    header('Content-Type: application/json');
    readfile(dirname(__DIR__) . '/pwa/manifest.json');
});
$router->get('/service-worker.js', function() {
    header('Content-Type: application/javascript');
    readfile(dirname(__DIR__) . '/pwa/service-worker.js');
});

// ── Worker Routes ─────────────────────────────────────
$router->get('/worker/dashboard', 'DashboardController', 'workerDashboard');
$router->get('/worker/attendance', 'AttendanceController', 'workerHistory');
$router->get('/worker/reports/create', 'ReportController', 'create');
$router->post('/worker/reports', 'ReportController', 'store');
$router->get('/worker/reports', 'ReportController', 'index');
$router->get('/worker/payslips', 'PayrollController', 'workerPayslips');
$router->get('/worker/leave/request', 'LeaveController', 'create');
$router->post('/worker/leave/request', 'LeaveController', 'store');
$router->get('/worker/leave/history', 'LeaveController', 'history');
$router->get('/worker/history', 'AttendanceController', 'workerHistory');

// ── Supervisor Routes ─────────────────────────────────
$router->get('/supervisor/dashboard', 'DashboardController', 'supervisorDashboard');
$router->get('/supervisor/attendance', 'AttendanceController', 'supervisorOverview');
$router->get('/supervisor/attendance/detail', 'AttendanceController', 'supervisorWorkerDetail');
$router->get('/supervisor/roster', 'ShiftController', 'roster');
$router->get('/supervisor/production', 'ProductionController', 'index');
$router->get('/supervisor/production/create', 'ProductionController', 'create');
$router->post('/supervisor/production', 'ProductionController', 'store');
$router->get('/supervisor/reports', 'ReportController', 'index');
$router->post('/supervisor/reports/resolve', 'ReportController', 'resolve');
$router->get('/supervisor/tasks', 'TaskController', 'index');

// ── Owner Routes ──────────────────────────────────────
$router->get('/owner/dashboard', 'DashboardController', 'ownerDashboard');
$router->get('/owner/attendance', 'AttendanceController', 'ownerManagement');
$router->get('/owner/roster', 'ShiftController', 'roster');
$router->get('/owner/reports', 'ReportController', 'index');
$router->post('/owner/reports/resolve', 'ReportController', 'resolve');
$router->get('/owner/tasks', 'TaskController', 'index');
$router->get('/owner/workers', 'UserController', 'index');
$router->get('/owner/workers/create', 'UserController', 'create');
$router->post('/owner/workers', 'UserController', 'store');
$router->get('/owner/workers/edit', 'UserController', 'edit');
$router->post('/owner/workers/update', 'UserController', 'update');
$router->post('/owner/workers/toggle-status', 'UserController', 'toggleStatus');
$router->get('/owner/leave/approvals', 'LeaveController', 'approvals');
$router->post('/owner/leave/update-status', 'LeaveController', 'updateStatus');
$router->get('/owner/approvals', 'ApprovalController', 'index');
$router->post('/owner/approvals/approve', 'ApprovalController', 'approve');
$router->post('/owner/approvals/reject', 'ApprovalController', 'reject');

// ── Supervisor Approval Routes ─────────────────────────
$router->get('/supervisor/approvals', 'ApprovalController', 'index');
$router->post('/supervisor/approvals/approve', 'ApprovalController', 'approve');
$router->post('/supervisor/approvals/reject', 'ApprovalController', 'reject');

// ── Owner Expense Routes ──────────────────────────────
$router->get('/owner/expenses', 'ExpenseController', 'index');
$router->post('/owner/expenses', 'ExpenseController', 'store');
$router->post('/owner/expenses/delete', 'ExpenseController', 'delete');

// ── Owner Settings Routes ─────────────────────────────
$router->get('/owner/settings', 'SettingsController', 'index');
$router->post('/owner/settings/update', 'SettingsController', 'update');

// ── Owner Payroll Routes ──────────────────────────────
$router->get('/owner/payroll', 'PayrollController', 'index');
$router->post('/owner/payroll/create-period', 'PayrollController', 'createPeriod');
$router->get('/owner/payroll/generate', 'PayrollController', 'showGenerate');
$router->post('/owner/payroll/generate', 'PayrollController', 'generate');
$router->get('/owner/payroll/records', 'PayrollController', 'viewRecords');
$router->get('/owner/payroll/profiles', 'PayrollController', 'profiles');
$router->get('/owner/payroll/profiles/edit', 'PayrollController', 'editProfile');
$router->post('/owner/payroll/profiles/save', 'PayrollController', 'saveProfile');
$router->post('/owner/payroll/pay', 'PayrollController', 'payRecord');

// ── Accountant Routes ─────────────────────────────────
$router->get('/accountant/dashboard', 'DashboardController', 'accountantDashboard');
$router->get('/accountant/payroll', 'PayrollController', 'index');
$router->get('/accountant/payroll/records', 'PayrollController', 'viewRecords');
$router->post('/accountant/payroll/pay', 'PayrollController', 'payRecord');

// ── Farm Operations (Phase 5) ─────────────────────────
// Support both prefixed and non-prefixed for flexibility
foreach (['/owner', '/supervisor', '/worker', ''] as $prefix) {
    $router->get($prefix . '/crops', 'CropController', 'index');
    $router->post($prefix . '/crops', 'CropController', 'store');
    $router->post($prefix . '/crops/update', 'CropController', 'update');
    $router->post($prefix . '/crops/delete', 'CropController', 'delete');

    $router->get($prefix . '/animals', 'AnimalController', 'index');
    $router->post($prefix . '/animals', 'AnimalController', 'store');
    $router->post($prefix . '/animals/update', 'AnimalController', 'update');
    $router->post($prefix . '/animals/delete', 'AnimalController', 'delete');

    $router->get($prefix . '/equipment', 'EquipmentController', 'index');
    $router->post($prefix . '/equipment', 'EquipmentController', 'store');
    $router->post($prefix . '/equipment/update', 'EquipmentController', 'update');
    $router->post($prefix . '/equipment/delete', 'EquipmentController', 'delete');

    $router->get($prefix . '/inventory', 'InventoryController', 'index');
    $router->post($prefix . '/inventory', 'InventoryController', 'store');
    $router->post($prefix . '/inventory/update', 'InventoryController', 'update');
    $router->post($prefix . '/inventory/update-quantity', 'InventoryController', 'updateQuantity');
    $router->post($prefix . '/inventory/delete', 'InventoryController', 'delete');
}

// ── Profile, Password & Notifications (all roles) ────
foreach (['worker', 'supervisor', 'owner', 'accountant'] as $profileRole) {
    $router->get("/$profileRole/profile", 'ProfileController', 'show');
    $router->post("/$profileRole/profile/update", 'ProfileController', 'update');
    $router->get("/$profileRole/change-password", 'ProfileController', 'showChangePassword');
    $router->post("/$profileRole/change-password", 'ProfileController', 'changePassword');
    $router->get("/$profileRole/notifications", 'ProfileController', 'notifications');
    $router->get("/$profileRole/payslip/download", 'PayrollController', 'downloadPayslip');
}

