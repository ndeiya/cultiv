<?php
/**
 * Owner - Payroll Periods View
 */
$title = $title ?? 'Payroll Management';
require_once __DIR__ . '/../layouts/app_header.php';
?>

<div class="p-8">
    <div class="flex justify-between items-end mb-8">
        <div>
            <h2 class="text-3xl font-black tracking-tight mb-2">Payroll Management</h2>
            <p class="text-slate-500 dark:text-slate-400">Manage payroll periods and worker payment profiles.</p>
        </div>
        <div class="flex gap-3">
            <a href="/owner/payroll/profiles" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-lg font-bold flex items-center gap-2 hover:brightness-95 transition-all">
                <span class="material-symbols-outlined text-sm text-primary">payments</span>
                Payment Profiles
            </a>
            <button onclick="document.getElementById('addPeriodModal').classList.remove('hidden')" class="px-4 py-2 bg-primary text-slate-900 font-bold rounded-lg hover:brightness-95 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">add</span>
                New Period
            </button>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">check_circle</span>
            <?php echo htmlspecialchars($_SESSION['success']); ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-slate-800/50 rounded-xl border border-primary/10 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-primary/10 flex justify-between items-center">
            <h3 class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">calendar_month</span>
                Payroll Periods
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Period</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Created At</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($periods)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                No payroll periods found. Create one to start processing payroll.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($periods as $period): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900 dark:text-slate-100">
                                        <?= date('M j, Y', strtotime($period['period_start'])) ?> - <?= date('M j, Y', strtotime($period['period_end'])) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php 
                                        $badgeClass = match($period['status']) {
                                            'draft' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                            'finalized' => 'bg-orange-500/10 text-orange-500',
                                            'paid' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                            default => 'bg-slate-100 text-slate-600'
                                        };
                                    ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase <?= $badgeClass ?>">
                                        <?= htmlspecialchars($period['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                    <?= date('M j, Y', strtotime($period['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="/owner/payroll/records?period_id=<?= $period['id'] ?>" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">visibility</span>
                                            View Records
                                        </a>
                                        <?php if ($period['status'] === 'draft'): ?>
                                            <a href="/owner/payroll/generate?period_id=<?= $period['id'] ?>" class="px-3 py-1 bg-primary/20 text-primary text-[10px] font-bold uppercase rounded-lg hover:bg-primary/30 transition-all">
                                                Generate
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Period Modal -->
<div id="addPeriodModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md overflow-hidden border border-primary/20">
        <div class="p-6 border-b border-primary/10 flex justify-between items-center">
            <h3 class="text-xl font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">calendar_add_on</span>
                New Payroll Period
            </h3>
            <button onclick="document.getElementById('addPeriodModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="/owner/payroll/create-period" method="POST" class="p-6">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Start Date</label>
                    <input type="date" name="period_start" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary py-3">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">End Date</label>
                    <input type="date" name="period_end" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary py-3">
                </div>
            </div>
            <div class="mt-8 flex gap-3">
                <button type="button" onclick="document.getElementById('addPeriodModal').classList.add('hidden')" class="flex-1 py-3 bg-slate-100 dark:bg-slate-800 rounded-lg font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-all text-slate-600 dark:text-slate-400">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-3 bg-primary text-slate-900 rounded-lg font-bold hover:brightness-95 shadow-lg shadow-primary/20 transition-all">
                    Create Period
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/app_footer.php'; ?>
