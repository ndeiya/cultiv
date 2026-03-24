<?php
/**
 * Shift Templates View
 */
$active_nav = 'roster';
include VIEWS_PATH . '/layouts/app_header.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">schedule</span>
            Shift Templates
        </h1>
        <button onclick="document.getElementById('template-modal').classList.remove('hidden')" class="px-4 py-2 bg-primary text-slate-900 font-bold rounded-lg hover:bg-primary/90 transition flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">add</span>
            New Template
        </button>
    </div>

    <!-- Templates Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-primary/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-bold text-slate-600 dark:text-slate-400">Template Name</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-600 dark:text-slate-400">Time Range</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-600 dark:text-slate-400">Break</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-600 dark:text-slate-400">Days</th>
                        <th class="px-4 py-3 text-right font-bold text-slate-600 dark:text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($templates)): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500 italic">No templates defined yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($templates as $t): ?>
                            <tr>
                                <td class="px-4 py-3 font-semibold"><?= e($t['name']) ?></td>
                                <td class="px-4 py-3">
                                    <?= date('g:i A', strtotime($t['start_time'])) ?> - <?= date('g:i A', strtotime($t['end_time'])) ?>
                                </td>
                                <td class="px-4 py-3 text-slate-500"><?= $t['break_duration_minutes'] ?> min</td>
                                <td class="px-4 py-3">
                                    <?php
                                    $days = explode(',', $t['days_of_week']);
                                    $dayNames = [1=>'Mon', 2=>'Tue', 3=>'Wed', 4=>'Thu', 5=>'Fri', 6=>'Sat', 7=>'Sun'];
                                    $labels = array_map(fn($d) => $dayNames[$d] ?? $d, $days);
                                    echo implode(', ', $labels);
                                    ?>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="/supervisor/shifts/schedule?template_id=<?= $t['id'] ?>" class="text-primary hover:underline font-bold text-xs uppercase tracking-wider">Schedule</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Template Modal -->
<div id="template-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-md shadow-2xl border border-primary/10">
        <div class="p-6 border-b border-primary/5">
            <h3 class="text-xl font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">add_task</span>
                New Shift Template
            </h3>
        </div>
        <form action="/supervisor/shifts/templates" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Template Name</label>
                <input type="text" name="name" required placeholder="e.g. Standard Morning Shift" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg px-4 py-2.5">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Start Time</label>
                    <input type="time" name="start_time" required value="08:00" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">End Time</label>
                    <input type="time" name="end_time" required value="17:00" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg px-4 py-2.5">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Break Duration (mins)</label>
                <input type="number" name="break_duration" value="0" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg px-4 py-2.5">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Recursive Days</label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ([1=>'M', 2=>'T', 3=>'W', 4=>'T', 5=>'F', 6=>'S', 7=>'S'] as $val => $label): ?>
                        <label class="cursor-pointer">
                            <input type="checkbox" name="days_of_week[]" value="<?= $val ?>" <?= $val <= 5 ? 'checked' : '' ?> class="peer hidden">
                            <span class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 peer-checked:bg-primary peer-checked:text-slate-900 font-bold transition-all border border-transparent peer-checked:border-primary-light">
                                <?= $label ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="document.getElementById('template-modal').classList.add('hidden')" class="px-4 py-2 font-bold text-slate-500 hover:text-slate-700 transition">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-primary text-slate-900 font-bold rounded-lg hover:shadow-lg hover:shadow-primary/20 transition-all">Save Template</button>
            </div>
        </form>
    </div>
</div>

<?php include VIEWS_PATH . '/layouts/app_footer.php'; ?>
