<?php
/**
 * Accountant Dashboard
 */
$active_nav = 'dashboard';
include VIEWS_PATH . '/layouts/app_header.php';
?>

<div class="space-y-6">
    
    <!-- Stat Cards Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-primary">payments</span>
                <span class="text-xs font-medium text-slate-500">Total Payroll</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">$<?= number_format($stats['totalPayroll'], 2) ?></span>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-primary">pending</span>
                <span class="text-xs font-medium text-slate-500">Pending Payments</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">$<?= number_format($stats['pendingPayments'], 2) ?></span>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-primary">check_circle</span>
                <span class="text-xs font-medium text-slate-500">Paid This Period</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">0</span>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-primary">savings</span>
                <span class="text-xs font-medium text-slate-500">Advances</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">$0</span>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-2">
            <!-- Recent Transactions Table -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm p-4 h-full">
                <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">receipt_long</span>
                    Recent Transactions
                </h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50">
                        <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Date</th>
                        <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Worker</th>
                        <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Amount</th>
                        <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($stats['recentTransactions'])): ?>
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-400">
                            <span class="material-symbols-outlined text-3xl mb-2 block">account_balance</span>
                            No transactions yet
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($stats['recentTransactions'] as $transaction): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-4 py-3 text-sm text-slate-500"><?= date('M j, Y h:i A', strtotime($transaction['paid_at'])) ?></td>
                            <td class="px-4 py-3 text-sm font-medium"><?= htmlspecialchars($transaction['worker_name']) ?></td>
                            <td class="px-4 py-3 text-sm font-bold text-green-600 dark:text-green-400">
                                $<?= number_format($transaction['amount'], 2) ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Paid - <?= htmlspecialchars($transaction['payment_method']) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
            </div>
        </div>

        <div>
            <!-- Recent Activity -->
            <?php 
                $activities = $stats['recentActivity'] ?? [];
                include VIEWS_PATH . '/shared/widgets/recent_activity.php'; 
            ?>
        </div>
    </div>
</div>

<?php include VIEWS_PATH . '/layouts/app_footer.php'; ?>
