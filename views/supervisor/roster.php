<?php
/**
 * Supervisor Roster View
 * Grid view showing shift assignments for a specific date.
 */
$active_nav = 'roster';
include VIEWS_PATH . '/layouts/app_header.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">calendar_month</span>
            Shift Roster
        </h1>
        <div class="flex items-center gap-3">
            <input type="date" id="roster-date" value="<?= e($current_date) ?>" class="px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-sm">
            <button onclick="loadRoster()" class="px-4 py-2 bg-primary text-slate-900 font-bold rounded-lg hover:bg-primary/90 transition">
                Load Roster
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-primary/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-400">Worker</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-400">Shift</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-400">Template</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-400">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-400">Notes</th>
                    </tr>
                </thead>
                <tbody id="roster-body" class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($roster)): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                <span class="material-symbols-outlined text-4xl mb-2 opacity-50 block">event_busy</span>
                                <p>No shifts scheduled for this date.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($roster as $assignment): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3">
                                    <div class="font-semibold"><?= e($assignment['worker_name']) ?></div>
                                    <div class="text-xs text-slate-500"><?= e($assignment['role']) ?></div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">
                                        <?= date('g:i A', strtotime($assignment['start_time'])) ?> - 
                                        <?= date('g:i A', strtotime($assignment['end_time'])) ?>
                                    </div>
                                    <?php if ($assignment['break_duration_minutes'] > 0): ?>
                                        <div class="text-xs text-slate-500">
                                            Break: <?= $assignment['break_duration_minutes'] ?> min
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if ($assignment['template_name']): ?>
                                        <span class="text-sm text-slate-600 dark:text-slate-400"><?= e($assignment['template_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400 italic">One-off</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php
                                    $statusColors = [
                                        'scheduled' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                        'confirmed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                        'no_show' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        'completed' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                        'cancelled' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                                    ];
                                    $statusClass = $statusColors[$assignment['status']] ?? 'bg-slate-100 text-slate-700';
                                    ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase <?= $statusClass ?>">
                                        <?= e($assignment['status']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if ($assignment['notes']): ?>
                                        <span class="text-xs text-slate-600 dark:text-slate-400"><?= e($assignment['notes']) ?></span>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function loadRoster() {
    const date = document.getElementById('roster-date').value;
    window.location.href = `/supervisor/roster?date=${date}`;
}
</script>

<?php include VIEWS_PATH . '/layouts/app_footer.php'; ?>
