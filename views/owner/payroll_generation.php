<?php
/**
 * Owner - Payroll Generation View
 */
$title = $title ?? 'Generate Payroll';
require_once __DIR__ . '/../layouts/app_header.php';
?>

<div class="p-8 max-w-4xl mx-auto">
    <div class="flex items-center gap-4 mb-8">
        <a href="/owner/payroll" class="h-10 w-10 border border-primary/20 rounded-xl flex items-center justify-center text-slate-400 hover:text-primary transition-all">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h2 class="text-3xl font-black tracking-tight">Generate Payroll</h2>
            <p class="text-slate-500">Processing payroll for period <strong><?= date('M j', strtotime($period['period_start'])) ?> - <?= date('M j, Y', strtotime($period['period_end'])) ?></strong></p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-primary/10 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-primary/10 flex items-center gap-6">
            <div class="h-16 w-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1">analytics</span>
            </div>
            <div>
                <h3 class="text-xl font-bold">Calculation Summary</h3>
                <p class="text-sm text-slate-500">The system will automatically calculate wages based on attendance data and worker profiles.</p>
            </div>
        </div>

        <div class="p-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-primary/5">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 block">System Logic</span>
                    <ul class="text-sm space-y-2">
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-green-500 text-sm">check_circle</span>
                            Sum hours from attendance
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-green-500 text-sm">check_circle</span>
                            Apply configured rates
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-green-500 text-sm">check_circle</span>
                            Calculate overtime
                        </li>
                    </ul>
                </div>
                <div class="p-6 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-primary/5">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 block">Deductions</span>
                    <ul class="text-sm space-y-2">
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-sm">info</span>
                            Salary advances
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-sm">info</span>
                            Manual deductions
                        </li>
                    </ul>
                </div>
                <div class="p-6 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-primary/5">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 block">Post-Processing</span>
                    <ul class="text-sm space-y-2">
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-sm">info</span>
                            Review records
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-sm">info</span>
                            Finalize for payment
                        </li>
                    </ul>
                </div>
            </div>

            <div class="bg-primary/5 p-6 rounded-2xl border border-primary/20 flex items-start gap-4">
                <span class="material-symbols-outlined text-primary mt-0.5">warning</span>
                <div class="text-sm">
                    <p class="font-bold text-slate-900">Important Note</p>
                    <p class="text-slate-600">Generating payroll will overwrite any existing drafted records for this period. Ensure all attendance is finalized before proceeding.</p>
                </div>
            </div>

            <form action="/owner/payroll/generate" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="period_id" value="<?= $period['id'] ?>">
                
                <button type="submit" class="w-full py-5 bg-primary text-slate-900 rounded-2xl font-black text-lg hover:brightness-95 shadow-xl shadow-primary/30 transition-all flex items-center justify-center gap-3">
                    <span class="material-symbols-outlined text-2xl">rocket_launch</span>
                    START PAYROLL GENERATION
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/app_footer.php'; ?>
