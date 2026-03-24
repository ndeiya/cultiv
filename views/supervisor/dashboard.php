<?php
/**
 * Supervisor Dashboard
 */
$active_nav = 'dashboard';
include VIEWS_PATH . '/layouts/app_header.php';
?>

<div class="space-y-6">
    
        </div>

        <?php include VIEWS_PATH . '/shared/widgets/pending_submissions.php'; ?>
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

    <!-- Recent Activity -->
    <?php 
        $activities = $stats['recentActivity'] ?? [];
        include VIEWS_PATH . '/shared/widgets/recent_activity.php'; 
    ?>
</div>

<?php include VIEWS_PATH . '/layouts/app_footer.php'; ?>
