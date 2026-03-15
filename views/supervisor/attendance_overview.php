<?php
/**
 * Supervisor Team Attendance Overview
 */
$active_nav = 'attendance';
include VIEWS_PATH . '/layouts/app_header.php';
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold">Team Attendance Overview</h2>
            <p class="text-sm text-slate-500">Date: <?= e($current_date) ?></p>
        </div>
        
        <form method="GET" class="flex items-center gap-2">
            <input type="date" name="date" value="<?= e($current_date) ?>" class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg text-sm px-3 py-2 focus:ring-primary focus:border-primary">
            <button type="submit" class="bg-primary text-slate-900 font-bold px-4 py-2 rounded-lg text-sm hover:brightness-95 transition-all">Filter</button>
        </form>
    </div>

    <div class="bg-white dark:bg-slate-800/50 rounded-xl border border-primary/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary/5 text-slate-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Staff Member</th>
                        <th class="px-6 py-4 font-semibold">Role</th>
                        <th class="px-6 py-4 font-semibold">Check-in</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary/5">
                    <?php if (empty($summary)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                <span class="material-symbols-outlined text-4xl mb-2 opacity-50">group_off</span>
                                <p class="text-sm">No attendance records for this date.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($summary as $record): 
                            $clockIn = new DateTime($record['clock_in']);
                            $nameParts = explode(' ', $record['name'] ?? 'Worker');
                            $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));

                            
                            $statusBadge = '';
                            if (!$record['clock_out']) {
                                $statusBadge = '<span class="px-2 py-1 bg-green-500/10 text-green-500 rounded text-[10px] font-bold uppercase">On-Site</span>';
                            } else {
                                $statusBadge = '<span class="px-2 py-1 bg-blue-500/10 text-blue-500 rounded text-[10px] font-bold uppercase">Completed</span>';
                            }
                        ?>
                            <tr class="hover:bg-primary/5 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="size-8 rounded-full bg-primary/20 flex items-center justify-center font-bold text-xs text-primary">
                                            <?= e($initials) ?>
                                        </div>
                                        <span class="font-medium"><?= e($record['name'] ?? 'Worker') ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                    <?= e(ucfirst($record['role'])) ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?= $clockIn->format('h:i A') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?= $statusBadge ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="/supervisor/attendance/detail?id=<?= $record['user_id'] ?>" class="text-primary hover:text-primary/80 font-semibold text-sm inline-flex items-center gap-1">
                                        Details <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php require __DIR__ . '/../shared/pagination.php'; ?>
    </div>
</div>

<?php include VIEWS_PATH . '/layouts/app_footer.php'; ?>
