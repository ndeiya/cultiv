<?php
/**
 * Worker Dashboard
 */
$active_nav = 'dashboard';
include VIEWS_PATH . '/layouts/app_header.php';

// Fetch open session to show correct button state
require_once __DIR__ . '/../../app/services/AttendanceService.php';
require_once __DIR__ . '/../../app/services/ShiftService.php';
$attendanceService = new AttendanceService();
$shiftService = new ShiftService();
$openSession = $attendanceService->getOpenSession($user['id']);

$isCheckedIn = $openSession !== false && $openSession !== null;
$shiftDuration = '0h 0m';
if ($isCheckedIn) {
    $clockInTime = new DateTime($openSession['clock_in']);
    $now = new DateTime();
    $diff = $clockInTime->diff($now);
    $shiftDuration = "{$diff->h}h {$diff->i}m";
}

// Get today's shift assignment
$todayShift = $shiftService->getTodayAssignment($user['id']);
?>

<div class="space-y-6">
    <div class="grid md:grid-cols-3 gap-6">
        <!-- Today's Attendance -->
        <section class="md:col-span-2">
            <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">event_available</span>
                Today's Attendance
            </h2>
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 shadow-sm border border-primary/5 h-full">
                <?php if ($todayShift): ?>
                    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <p class="text-xs text-slate-600 dark:text-slate-400 uppercase font-bold mb-1">Scheduled Shift</p>
                        <p class="font-semibold text-blue-700 dark:text-blue-300">
                            <?= date('g:i A', strtotime($todayShift['start_time'])) ?> - <?= date('g:i A', strtotime($todayShift['end_time'])) ?>
                        </p>
                        <?php if ($todayShift['template_name']): ?>
                            <p class="text-xs text-slate-500 mt-1"><?= e($todayShift['template_name']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="mb-4 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                        <p class="text-xs text-amber-700 dark:text-amber-300">No shift scheduled for today</p>
                    </div>
                <?php endif; ?>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <?php if ($isCheckedIn): ?>
                            <div class="p-3 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-600">
                                <span class="material-symbols-outlined">timer</span>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider">Current Status</p>
                                <p class="font-semibold text-green-600" id="status-text">Checked In</p>
                            </div>
                        <?php else: ?>
                            <div class="p-3 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600">
                                <span class="material-symbols-outlined">timer</span>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider">Current Status</p>
                                <p class="font-semibold text-amber-600" id="status-text">Not Checked In</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-500 font-medium tracking-wider uppercase">Duration</p>
                        <p class="text-sm font-bold" id="duration-text"><?= $shiftDuration ?></p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-auto">
                    <button id="btn-clock-in" onclick="clockIn()" class="flex items-center justify-center gap-2 py-3 px-4 <?= $isCheckedIn ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 cursor-not-allowed' : 'bg-primary text-slate-900 shadow-sm' ?> font-bold rounded-lg transition-transform active:scale-95" <?= $isCheckedIn ? 'disabled' : '' ?>>
                        <span class="material-symbols-outlined text-xl">login</span> Check In
                    </button>
                    <button id="btn-clock-out" onclick="clockOut()" class="flex items-center justify-center gap-2 py-3 px-4 <?= !$isCheckedIn ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 cursor-not-allowed' : 'bg-white border-2 border-primary text-primary shadow-sm' ?> font-bold rounded-lg transition-transform active:scale-95" <?= !$isCheckedIn ? 'disabled' : '' ?>>
                        <span class="material-symbols-outlined text-xl">logout</span> Check Out
                    </button>
                </div>
                <div id="attendance-message" class="text-xs font-bold text-center mt-3 hidden"></div>
            </div>
        </section>

        <!-- Recent Payslip -->
        <section>
            <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">payments</span>
                Recent Payslip
            </h2>
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 shadow-sm border border-primary/10 h-full flex flex-col">
                <?php if (isset($stats['recentPayslip']) && $stats['recentPayslip']): ?>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs text-slate-500 uppercase font-bold tracking-wider">Period</p>
                            <p class="text-sm font-bold">
                                <?= date('M j', strtotime($stats['recentPayslip']['period_start'])) ?> - <?= date('M j, Y', strtotime($stats['recentPayslip']['period_end'])) ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="py-4 border-y border-slate-100 dark:border-slate-800 my-auto">
                        <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-1 text-center">Net Pay</p>
                        <p class="font-bold text-3xl text-center text-green-600 dark:text-green-400">
                            $<?= number_format($stats['recentPayslip']['net_pay'], 2) ?>
                        </p>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase <?= $stats['recentPayslip']['status'] === 'paid' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' ?>">
                            <?= e($stats['recentPayslip']['status']) ?>
                        </span>
                        <a href="/worker/payslips" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                            View Details <span class="material-symbols-outlined text-sm">chevron_right</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="flex flex-col items-center justify-center h-full text-center text-slate-400 py-6">
                        <span class="material-symbols-outlined text-4xl mb-2 opacity-50">receipt_long</span>
                        <p class="text-sm font-medium text-slate-500">No payslips available yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Tasks and Field Conditions (Placeholders for future phases) -->
    <section>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">assignment</span> Assigned Tasks
            </h2>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 p-4 rounded-xl text-sm border border-blue-100 dark:border-blue-800">
            Task management will be available in Phase 5.
        </div>
    </section>

    <section>
        <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">thermostat</span> Field Conditions
        </h2>
        <div class="bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 p-4 rounded-xl text-sm border border-blue-100 dark:border-blue-800">
            Field mapping and hardware sensors will be integrated in future phases.
        </div>
    </section>
</div>

<script src="/js/device-fingerprint.js"></script>
<script src="/js/attendance.js"></script>

<?php include VIEWS_PATH . '/layouts/app_footer.php'; ?>
