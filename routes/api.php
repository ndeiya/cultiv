<?php
/**
 * API Routes
 * RESTful API endpoints under /api/ prefix.
 */

$router->group('/api', function (Router $router) {
    // ── Authentication ──────────────────────────────────
    $router->post('/login', 'AuthController', 'apiLogin');
    $router->post('/logout', 'AuthController', 'apiLogout');

    // ── Attendance ──────────────────────────────────────
    $router->post('/attendance/clock-in', 'AttendanceController', 'apiClockIn');
    $router->post('/attendance/clock-out', 'AttendanceController', 'apiClockOut');
    $router->get('/attendance/history', 'AttendanceController', 'apiHistory');

    // ── Shift Scheduling ─────────────────────────────────
    $router->get('/shifts/roster', 'ShiftController', 'apiGetRoster');
    $router->post('/shifts/templates', 'ShiftController', 'apiCreateTemplate');
    $router->post('/shifts/generate', 'ShiftController', 'apiGenerateFromTemplate');
    $router->get('/shifts/worker-schedule', 'ShiftController', 'apiGetWorkerSchedule');

    // ── Reports ─────────────────────────────────────────
    $router->post('/reports/create', 'ReportController', 'store');
    $router->get('/reports', 'ReportController', 'apiIndex');

    // ── Farm Operations (Phase 5) ───────────────────────
    $router->get('/crops', 'CropController', 'apiIndex');
    $router->post('/crops', 'CropController', 'apiStore');
    $router->get('/animals', 'AnimalController', 'apiIndex');
    $router->get('/equipment', 'EquipmentController', 'apiIndex');

    // ── Payroll (Phase 6) ───────────────────────────────
    $router->post('/payroll/create-period', 'PayrollController', 'createPeriod');
    $router->post('/payroll/generate', 'PayrollController', 'generate');
    $router->get('/payroll/records', 'PayrollController', 'viewRecords');
    $router->post('/payroll/pay', 'PayrollController', 'payRecord');

    // ── Production Records (Phase 3) ─────────────────────
    $router->get('/production/worker', 'ProductionController', 'apiGetWorkerProduction');
    $router->post('/production', 'ProductionController', 'apiStore');

    // ── Synchronization (Phase 5.1) ──────────────────────
    $router->post('/sync/batch', 'SyncController', 'batchSync');
});
