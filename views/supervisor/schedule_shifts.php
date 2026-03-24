<?php
/**
 * Schedule Shifts View
 */
$active_nav = 'roster';
include VIEWS_PATH . '/layouts/app_header.php';
$selected_template = (int)($_GET['template_id'] ?? 0);
?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="/supervisor/shifts/templates" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-2xl font-bold">Schedule Base Shifts</h1>
    </div>

    <form action="/supervisor/shifts/generate" method="POST" class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-primary/5 overflow-hidden">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <div class="p-6 space-y-6">
            <!-- Template Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Select Template</label>
                    <select name="template_id" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg px-4 py-2.5">
                        <option value="">-- Choose Template --</option>
                        <?php foreach ($templates as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= $selected_template === $t['id'] ? 'selected' : '' ?>>
                                <?= e($t['name']) ?> (<?= date('H:i', strtotime($t['start_time'])) ?> - <?= date('H:i', strtotime($t['end_time'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Start Date</label>
                    <input type="date" name="start_date" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">End Date</label>
                    <input type="date" name="end_date" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+6 days')) ?>" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg px-4 py-2.5">
                </div>
            </div>

            <!-- Worker Selection -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold uppercase text-slate-500">Assign Workers</label>
                    <button type="button" onclick="toggleAllWorkers()" class="text-xs text-primary font-bold hover:underline">Select All</button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-64 overflow-y-auto p-4 bg-slate-50 dark:bg-slate-800 rounded-xl border border-primary/5">
                    <?php if (empty($workers)): ?>
                        <p class="col-span-full text-center text-slate-500 py-4 italic">No workers found in this farm.</p>
                    <?php else: ?>
                        <?php foreach ($workers as $w): ?>
                            <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-white dark:hover:bg-slate-700 cursor-pointer transition-colors border border-transparent hover:border-primary/20">
                                <input type="checkbox" name="user_ids[]" value="<?= $w['id'] ?>" class="worker-checkbox w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary">
                                <span class="text-sm font-medium"><?= e($w['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="bg-slate-50 dark:bg-slate-800/50 p-6 flex justify-end">
            <button type="submit" class="px-8 py-3 bg-primary text-slate-900 font-black rounded-lg hover:shadow-lg hover:shadow-primary/30 transition-all flex items-center gap-2">
                <span>Generate Shifts</span>
                <span class="material-symbols-outlined text-lg">calendar_add_on</span>
            </button>
        </div>
    </form>
</div>

<script>
function toggleAllWorkers() {
    const checkboxes = document.querySelectorAll('.worker-checkbox');
    const allChecked = Array.from(checkboxes).every(c => c.checked);
    checkboxes.forEach(c => c.checked = !allChecked);
}
</script>

<?php include VIEWS_PATH . '/layouts/app_footer.php'; ?>
