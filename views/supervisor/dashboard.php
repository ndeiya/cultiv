<?php
/**
 * Supervisor Dashboard
 */
$active_nav = 'dashboard';
include VIEWS_PATH . '/layouts/app_header.php';
?>

<div class="space-y-6">
    
    <!-- Stat Cards Row -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-primary">assignment</span>
                <span class="text-xs font-medium text-slate-500">Pending Tasks</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold"><?= htmlspecialchars($stats['pendingTasks']) ?></span>
            </div>
        </div>
    </div>

    <!-- Team Attendance Overview -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm p-4">
        <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">schedule</span>
            Team Attendance
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50">
                        <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Worker</th>
                        <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Status</th>
                        <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Clock In</th>
                        <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Hours</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($stats['teamAttendance'])): ?>
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-400">
                            <span class="material-symbols-outlined text-3xl mb-2 block">event_busy</span>
                            No attendance records yet today
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($stats['teamAttendance'] as $record): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-4 py-3 text-sm font-medium"><?= htmlspecialchars($record['name']) ?></td>
                            <td class="px-4 py-3">
                                <?php if ($record['status'] === 'normal'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">On-Site</span>
                                <?php elseif ($record['status'] === 'late'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Late</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400"><?= htmlspecialchars($record['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-500"><?= date('h:i A', strtotime($record['clock_in'])) ?></td>
                            <td class="px-4 py-3 text-sm font-bold"><?= round($record['total_minutes'] / 60, 1) ?>h</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($stats['teamAttendance'])): ?>
        <div class="mt-4 text-center">
            <a href="/supervisor/attendance" class="text-sm font-bold text-primary hover:underline">View All Attendance</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Recent Task Activity -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm p-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">history</span>
                Recent Task Activity
            </h3>
            <a href="/<?= $user['role'] ?>/tasks" class="text-xs font-bold text-primary hover:underline">Manage All Tasks</a>
        </div>
        <div class="space-y-4">
            <?php if (empty($stats['recentTasks'])): ?>
                <p class="text-center text-sm text-slate-400 py-4">No tasks assigned yet.</p>
            <?php else: ?>
                <?php foreach ($stats['recentTasks'] as $task): ?>
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                        <div class="size-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-primary text-sm">assignment</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <p class="text-sm font-bold truncate"><?= e($task['title']) ?></p>
                                <?php
                                $status_class = [
                                    'completed' => 'text-green-500',
                                    'in_progress' => 'text-orange-500',
                                    'pending' => 'text-blue-500',
                                    'cancelled' => 'text-slate-400'
                                ][$task['status']] ?? 'text-slate-500';
                                ?>
                                <span class="text-[10px] font-bold uppercase <?= $status_class ?>"><?= str_replace('_', ' ', $task['status']) ?></span>
                            </div>
                            <p class="text-xs text-slate-500">Assigned to <span class="font-medium text-slate-700 dark:text-slate-300"><?= e($task['assigned_to_name']) ?></span></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include VIEWS_PATH . '/layouts/app_footer.php'; ?>
