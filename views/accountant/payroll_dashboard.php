<?php
/**
 * Accountant - Payroll Dashboard View
 */
$title = $title ?? 'Payroll Dashboard';
require_once __DIR__ . '/../layouts/app_header.php';
?>

<div class="p-8">
    <div class="flex justify-between items-end mb-8">
        <div>
            <h2 class="text-3xl font-black tracking-tight mb-2">Payroll Dashboard</h2>
            <p class="text-slate-500 dark:text-slate-400">Financial oversight and payment tracking.</p>
        </div>
        <div class="flex gap-3">
            <a href="/accountant/reports" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-lg font-bold flex items-center gap-2 hover:brightness-95 transition-all">
                <span class="material-symbols-outlined text-sm text-primary">description</span>
                Financial Reports
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 bg-primary/20 rounded-xl flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined">pending_actions</span>
                </div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pending Approval</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-slate-100">$0.00</span>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 bg-orange-100 rounded-xl flex items-center justify-center text-orange-500">
                    <span class="material-symbols-outlined">payments</span>
                </div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Awaiting Payment</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-slate-100">$0.00</span>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 bg-green-100 rounded-xl flex items-center justify-center text-green-600">
                    <span class="material-symbols-outlined">check_circle</span>
                </div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Paid This Month</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-slate-100">$0.00</span>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                    <span class="material-symbols-outlined">account_balance</span>
                </div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Budget</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-slate-100">$0.00</span>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800/50 rounded-xl border border-primary/10 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-primary/10">
            <h3 class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">history</span>
                Payroll History
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Period</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Total Cost</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($periods)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                No payroll periods found.
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
                                            'draft' => 'bg-blue-100 text-blue-700',
                                            'finalized' => 'bg-orange-500/10 text-orange-500',
                                            'paid' => 'bg-green-100 text-green-700',
                                            default => 'bg-slate-100 text-slate-600'
                                        };
                                    ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase <?= $badgeClass ?>">
                                        <?= htmlspecialchars($period['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-bold text-slate-900 dark:text-slate-100">-</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="/accountant/payroll/records?period_id=<?= $period['id'] ?>" class="text-xs font-bold text-primary hover:underline flex items-center gap-1 justify-end">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                        Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/app_footer.php'; ?>
