<?php
/**
 * Worker Attendance History
 */
$active_nav = 'attendance';
include VIEWS_PATH . '/layouts/app_header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col gap-1">
        <h3 class="text-2xl font-bold tracking-tight">Attendance History</h3>
        <p class="text-slate-500 text-sm">Review your past clock-ins and clock-outs</p>
    </div>

    <!-- History Table -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date & Time</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Duration</th>
                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                <?php if (empty($history)): ?>
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-slate-500">
                            <span class="material-symbols-outlined text-4xl mb-2 opacity-50">history</span>
                            <p class="text-sm">No attendance records found.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($history as $record): 
                        $clockIn = new DateTime($record['clock_in']);
                        $clockOut = $record['clock_out'] ? new DateTime($record['clock_out']) : null;
                    ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold"><?= $clockIn->format('M j, Y') ?></span>
                                    <span class="text-xs text-slate-500">In: <?= $clockIn->format('h:i A') ?></span>
                                    <?php if ($clockOut): ?>
                                        <span class="text-xs text-slate-500">Out: <?= $clockOut->format('h:i A') ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($record['total_minutes'] > 0): ?>
                                    <span class="text-sm"><?= floor($record['total_minutes'] / 60) ?>h <?= $record['total_minutes'] % 60 ?>m</span>
                                <?php else: ?>
                                    <span class="text-sm text-slate-400">Ongoing</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if (!$record['clock_out']): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800">Active</span>
                                <?php elseif ($record['status'] === 'normal'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800">Completed</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800"><?= e($record['status']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php require __DIR__ . '/../shared/pagination.php'; ?>
    </div>

</div>

<?php include VIEWS_PATH . '/layouts/app_footer.php'; ?>
