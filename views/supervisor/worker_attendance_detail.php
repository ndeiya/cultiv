<?php
/**
 * Worker Attendance Details
 */
$active_nav = 'attendance';
include VIEWS_PATH . '/layouts/app_header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="/supervisor/attendance" class="hover:text-primary transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">arrow_back</span> Back to Team
                </a>
            </div>
            <h2 class="text-xl font-bold"><?= e($worker['name']) ?>'s Attendance</h2>
            <p class="text-sm text-slate-500">Role: <?= e(ucfirst($worker['role'])) ?></p>
        </div>
        
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="id" value="<?= $worker['id'] ?>">
            <input type="date" name="date_from" value="<?= e($_GET['date_from'] ?? '') ?>" class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg text-sm px-3 py-2">
            <span class="text-slate-500 text-sm">to</span>
            <input type="date" name="date_to" value="<?= e($_GET['date_to'] ?? '') ?>" class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg text-sm px-3 py-2">
            <button type="submit" class="bg-primary text-slate-900 font-bold px-4 py-2 rounded-lg text-sm hover:brightness-95 transition-all">Filter</button>
        </form>
    </div>

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
                            <p class="text-sm">No attendance records found for this period.</p>
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
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Active</span>
                                <?php elseif ($record['status'] === 'normal'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Completed</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400"><?= e($record['status']) ?></span>
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
