<?php
/**
 * Worker - Payslips View
 */
$title = $title ?? 'My Payslips';
require_once __DIR__ . '/../layouts/app_header.php';
?>

<div class="px-4 py-8 md:p-8">
    <div class="mb-8">
        <h2 class="text-2xl font-black tracking-tight mb-2">My Payslips</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">View and download your payment history.</p>
    </div>

    <?php if (empty($payslips)): ?>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-12 text-center border border-primary/10 shadow-sm">
            <span class="material-symbols-outlined text-slate-200 text-6xl mb-4">payments</span>
            <p class="text-slate-500">You don't have any payslips yet.</p>
        </div>
    <?php else: ?>
        <div class="grid gap-4">
            <?php foreach ($payslips as $payslip): ?>
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-primary/10 shadow-sm p-5 hover:border-primary/30 transition-all">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Period</p>
                            <p class="font-bold text-slate-900 dark:text-slate-100 italic">
                                <?= date('M j', strtotime($payslip['period_start'])) ?> - <?= date('M j, Y', strtotime($payslip['period_end'])) ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Net Amount</p>
                            <p class="text-xl font-black text-primary">$<?= number_format($payslip['net_pay'], 2) ?></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pb-4 border-b border-slate-50 dark:border-slate-800 mb-4">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Hours</p>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300"><?= number_format($payslip['total_hours'], 1) ?>h</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Status</p>
                            <?php 
                                $badgeClass = match($payslip['status']) {
                                    'paid' => 'bg-green-100 text-green-700',
                                    'pending' => 'bg-blue-100 text-blue-700',
                                    default => 'bg-slate-100 text-slate-600'
                                };
                            ?>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?= $badgeClass ?>">
                                <?= htmlspecialchars($payslip['status']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <p class="text-[10px] text-slate-400">Processed on <?= date('M j, Y', strtotime($payslip['generated_at'])) ?></p>
                        <button class="flex items-center gap-1 text-xs font-bold text-primary hover:underline">
                            <span class="material-symbols-outlined text-sm">download</span>
                            Download PDF
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/app_footer.php'; ?>
