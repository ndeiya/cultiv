<?php
/**
 * Owner - Waiter Payroll Profiles View
 */
$title = $title ?? 'Worker Payment Profiles';
require_once __DIR__ . '/../layouts/app_header.php';
?>

<div class="p-8">
    <div class="flex justify-between items-end mb-8">
        <div>
            <h2 class="text-3xl font-black tracking-tight mb-2">Payment Profiles</h2>
            <p class="text-slate-500 dark:text-slate-400">Configure how each worker is paid (Hourly, Daily, Monthly, or Unit-based).</p>
        </div>
        <a href="/owner/payroll" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-lg font-bold flex items-center gap-2 hover:brightness-95 transition-all">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            Back to Payroll
        </a>
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
                <span class="material-symbols-outlined text-primary">account_balance_wallet</span>
                Staff Payment Config
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Worker</th>
                        <th class="px-6 py-4">Payment Type</th>
                        <th class="px-6 py-4">Rate</th>
                        <th class="px-6 py-4">Overtime</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($workers)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                No workers found in the system.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($workers as $worker): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold text-xs">
                                            <?= strtoupper(substr($worker['name'], 0, 1)) ?>
                                        </div>
                                        <div class="font-medium text-slate-900 dark:text-slate-100">
                                            <?= htmlspecialchars($worker['name']) ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($worker['payment_type']): ?>
                                        <span class="text-sm font-medium capitalize text-slate-900 dark:text-slate-100">
                                            <?= htmlspecialchars($worker['payment_type']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400 italic">Not set</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-900 dark:text-slate-100 font-bold">
                                        <?php 
                                            echo match($worker['payment_type']) {
                                                'hourly' => '$' . number_format($worker['hourly_rate'], 2) . '/hr',
                                                'daily' => '$' . number_format($worker['daily_rate'], 2) . '/day',
                                                'monthly' => '$' . number_format($worker['monthly_salary'], 2) . '/mo',
                                                'unit' => '$' . number_format($worker['unit_rate'], 2) . '/unit',
                                                default => '-'
                                            };
                                        ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs text-slate-500">
                                        <?= $worker['payment_type'] === 'hourly' ? 'Automatic' : 'Manual/N/A' ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="/owner/payroll/profiles/edit?user_id=<?= $worker['id'] ?>" class="px-3 py-1 bg-primary text-slate-900 text-[10px] font-bold uppercase rounded-lg hover:brightness-95 transition-all">
                                        <?= $worker['payment_type'] ? 'Edit' : 'Configure' ?>
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
