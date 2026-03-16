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
    <!-- Quick Actions -->
    <section>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="/worker/reports/create" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm hover:border-primary/50 hover:shadow-md transition-all group">
                <span class="material-symbols-outlined text-3xl text-primary mb-2 group-hover:scale-110 transition-transform">add_circle</span>
                <span class="text-xs font-bold uppercase tracking-wider">Submit Report</span>
            </a>
            <a href="/worker/leave/request" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm hover:border-primary/50 hover:shadow-md transition-all group">
                <span class="material-symbols-outlined text-3xl text-primary mb-2 group-hover:scale-110 transition-transform">event_busy</span>
                <span class="text-xs font-bold uppercase tracking-wider">Request Leave</span>
            </a>
            <a href="/worker/reports" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm hover:border-primary/50 hover:shadow-md transition-all group">
                <span class="material-symbols-outlined text-3xl text-primary mb-2 group-hover:scale-110 transition-transform">history</span>
                <span class="text-xs font-bold uppercase tracking-wider">Report History</span>
            </a>
            <a href="/worker/payslips" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm hover:border-primary/50 hover:shadow-md transition-all group">
                <span class="material-symbols-outlined text-3xl text-primary mb-2 group-hover:scale-110 transition-transform">payments</span>
                <span class="text-xs font-bold uppercase tracking-wider">My Payslips</span>
            </a>
        </div>
    </section>
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
            <?php if (!empty($stats['activeTasks'])): ?>
                <span class="bg-primary/20 text-primary text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">
                    <?= count($stats['activeTasks']) ?> Active
                </span>
            <?php endif; ?>
        </div>
        
        <?php if (empty($stats['activeTasks'])): ?>
            <div class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 p-8 rounded-xl text-center border border-dashed border-primary/20">
                <span class="material-symbols-outlined text-3xl opacity-30 mb-2">assignment_turned_in</span>
                <p class="text-sm">No tasks assigned for today. Take a break!</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <?php foreach ($stats['activeTasks'] as $task): ?>
                    <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm flex flex-col">
                        <div class="flex justify-between items-start mb-2">
                            <?php
                            $priority_class = [
                                'high' => 'bg-red-500/10 text-red-500',
                                'medium' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                'low' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                            ][$task['priority']] ?? 'bg-slate-100 text-slate-600';
                            ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $priority_class ?>">
                                <?= e($task['priority']) ?>
                            </span>
                            <span class="text-[10px] text-slate-400"><?= date('M j', strtotime($task['due_date'])) ?></span>
                        </div>
                        <h3 class="font-bold text-sm mb-1"><?= e($task['title']) ?></h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mb-4">
                            <?= e($task['description']) ?>
                        </p>
                        
                        <div class="mt-auto flex gap-2">
                            <?php if ($task['status'] === 'pending'): ?>
                                <button onclick="updateTaskStatus(<?= $task['id'] ?>, 'in_progress')" class="flex-1 bg-primary/10 text-primary py-1.5 rounded-lg text-[10px] font-bold hover:bg-primary/20 transition-colors">START TASK</button>
                            <?php elseif ($task['status'] === 'in_progress'): ?>
                                <button onclick="updateTaskStatus(<?= $task['id'] ?>, 'completed')" class="flex-1 bg-green-500 text-white py-1.5 rounded-lg text-[10px] font-bold hover:brightness-95 transition-colors">COMPLETE</button>
                            <?php endif; ?>
                            <button onclick="viewTaskDetails(<?= $task['id'] ?>)" class="p-1.5 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-500 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[16px]">visibility</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Task Detail Modal -->
    <div id="taskModal" class="fixed inset-0 z-50 hidden">
        <div class="min-h-screen px-4 flex items-center justify-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('taskModal')"></div>
            <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-sm w-full p-6 border border-primary/10">
                <div id="taskDetailContent"></div>
                <button onclick="closeModal('taskModal')" class="w-full mt-6 py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold rounded-lg">Close</button>
            </div>
        </div>
    </div>

    <script>
    async function updateTaskStatus(id, status) {
        try {
            const response = await fetch('/api/tasks', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
                },
                body: JSON.stringify({ id, status })
            });
            const result = await response.json();
            if (result.success) window.location.reload();
            else alert(result.message || 'Error updating status');
        } catch (err) {
            console.error(err);
            alert('Network error');
        }
    }

    function viewTaskDetails(id) {
        // Find task data from stats (available in JS via PHP)
        const tasks = <?= json_encode($stats['activeTasks']) ?>;
        const task = tasks.find(t => t.id == id);
        if (!task) return;

        const content = `
            <div class="mb-4">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-primary/10 text-primary mb-2 inline-block">Task Detail</span>
                <h3 class="text-lg font-bold">${task.title}</h3>
                <p class="text-[10px] text-slate-400 uppercase font-bold mt-1">Due: ${task.due_date}</p>
            </div>
            <div class="space-y-4">
                <div>
                    <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider mb-1">Description</p>
                    <p class="text-sm text-slate-700 dark:text-slate-300">${task.description || 'No description provided.'}</p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider mb-1">Assigned By</p>
                    <p class="text-sm font-medium">${task.created_by_name}</p>
                </div>
            </div>
        `;
        document.getElementById('taskDetailContent').innerHTML = content;
        document.getElementById('taskModal').classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
    </script>

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
