<?php
/**
 * Owner - Payroll Records View
 */
$title = $title ?? 'Payroll Records';
require_once __DIR__ . '/../layouts/app_header.php';
?>

<div class="p-8">
    <div class="flex justify-between items-end mb-8">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="/owner/payroll" class="text-primary hover:underline text-sm font-bold flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Back to Periods
                </a>
            </div>
            <h2 class="text-3xl font-black tracking-tight">Period Records</h2>
            <p class="text-slate-500">Review and pay staff for <strong><?= date('M j', strtotime($period['period_start'])) ?> - <?= date('M j, Y', strtotime($period['period_end'])) ?></strong></p>
        </div>
        <div class="flex gap-3">
            <button class="px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-lg font-bold flex items-center gap-2 hover:brightness-95 transition-all text-sm">
                <span class="material-symbols-outlined text-sm">download</span>
                Export Report
            </button>
            <a href="/owner/payroll/generate?period_id=<?= $period['id'] ?>" class="px-4 py-2 border border-primary text-primary font-bold rounded-lg hover:bg-primary/5 transition-all flex items-center gap-2 text-sm">
                <span class="material-symbols-outlined text-sm">refresh</span>
                Recalculate All
            </a>
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
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Worker</th>
                        <th class="px-6 py-4">Hours</th>
                        <th class="px-6 py-4">Regular Pay</th>
                        <th class="px-6 py-4">Overtime</th>
                        <th class="px-6 py-4">Adjustments</th>
                        <th class="px-6 py-4 font-bold text-slate-900 dark:text-slate-100">Net Pay</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-slate-300 text-5xl block mb-4">folder_off</span>
                                <p class="text-slate-500">No records generated yet for this period.</p>
                                <a href="/owner/payroll/generate?period_id=<?= $period['id'] ?>" class="text-primary font-bold hover:underline mt-2 inline-block">Generate now</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $record): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900 dark:text-slate-100"><?= htmlspecialchars($record['worker_name']) ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?= number_format($record['total_hours'], 1) ?>h
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    $<?= number_format($record['regular_pay'], 2) ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if ($record['overtime_pay'] > 0): ?>
                                        <span class="text-orange-500 font-bold">$<?= number_format($record['overtime_pay'], 2) ?></span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php 
                                        $adj = $record['bonus_amount'] - $record['deduction_amount'];
                                        if ($adj > 0) echo '<span class="text-green-600">+$' . number_format($adj, 2) . '</span>';
                                        elseif ($adj < 0) echo '<span class="text-red-500">-$' . number_format(abs($adj), 2) . '</span>';
                                        else echo '-';
                                    ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-black text-slate-900 dark:text-slate-100">$<?= number_format($record['net_pay'], 2) ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php 
                                        $badgeClass = match($record['status']) {
                                            'pending' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                            'approved' => 'bg-orange-500/10 text-orange-500',
                                            'paid' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                            default => 'bg-slate-100 text-slate-600'
                                        };
                                    ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase <?= $badgeClass ?>">
                                        <?= htmlspecialchars($record['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <?php if ($record['status'] !== 'paid'): ?>
                                        <button onclick="openPaymentModal(<?= $record['id'] ?>, '<?= htmlspecialchars($record['worker_name']) ?>', <?= $record['net_pay'] ?>)" class="px-3 py-1 bg-primary text-slate-900 text-[10px] font-bold uppercase rounded-lg hover:brightness-95">
                                            Record Payment
                                        </button>
                                    <?php else: ?>
                                        <span class="text-green-600">
                                            <span class="material-symbols-outlined text-sm">check_circle</span>
                                        </span>
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

<!-- Payment Modal -->
<div id="paymentModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md overflow-hidden border border-primary/20">
        <div class="p-6 border-b border-primary/10 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
            <h3 class="text-xl font-bold">Record Payment</h3>
            <button onclick="document.getElementById('paymentModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="/owner/payroll/pay" method="POST" class="p-6">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="payroll_record_id" id="modal_record_id">
            
            <div class="mb-6 p-4 bg-primary/10 rounded-xl border border-primary/20">
                <p class="text-xs font-bold text-slate-500 uppercase mb-1">Worker</p>
                <p class="text-lg font-black text-slate-900 dark:text-slate-100" id="modal_worker_name">-</p>
                <div class="mt-2 flex justify-between items-end">
                    <p class="text-xs font-bold text-slate-500 uppercase">Amount Due</p>
                    <p class="text-2xl font-black text-primary" id="modal_amount_due">$0.00</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Payment Method</label>
                    <select name="payment_method" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary py-3">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="mobile_money">Mobile Money (M-Pesa/Airtel)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Transaction Reference / Note</label>
                    <input type="text" name="transaction_reference" placeholder="e.g. EBK-12345678" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary py-3">
                </div>
            </div>

            <div class="mt-8">
                <button type="submit" class="w-full py-4 bg-primary text-slate-900 rounded-xl font-black hover:brightness-95 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2">
                    CONFIRM PAYMENT
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openPaymentModal(recordId, name, amount) {
    document.getElementById('modal_record_id').value = recordId;
    document.getElementById('modal_worker_name').textContent = name;
    document.getElementById('modal_amount_due').textContent = '$' + amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('paymentModal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/../layouts/app_footer.php'; ?>
