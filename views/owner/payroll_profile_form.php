<?php
/**
 * Owner - Payroll Profile Form View
 */
$title = $title ?? 'Configure Payment Profile';
require_once __DIR__ . '/../layouts/app_header.php';

$p = $profile ?? [
    'payment_type' => 'hourly',
    'hourly_rate' => 0,
    'daily_rate' => 0,
    'monthly_salary' => 0,
    'unit_rate' => 0,
    'overtime_rate' => 0,
    'overtime_threshold' => 40
];
?>

<div class="p-8 max-w-2xl mx-auto">
    <div class="flex items-center gap-4 mb-8">
        <a href="/owner/payroll/profiles" class="h-10 w-10 border border-primary/20 rounded-xl flex items-center justify-center text-slate-400 hover:text-primary transition-all">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h2 class="text-3xl font-black tracking-tight">Configure Payment</h2>
            <p class="text-slate-500">Setting up rates for <strong><?= htmlspecialchars($worker['name']) ?></strong></p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-primary/10 shadow-sm overflow-hidden p-8">
        <form action="/owner/payroll/profiles/save" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="user_id" value="<?= $worker['id'] ?>">

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Payment Type</label>
                <select name="payment_type" id="payment_type" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary py-3" onchange="updateFields()">
                    <option value="hourly" <?= $p['payment_type'] === 'hourly' ? 'selected' : '' ?>>Hourly (Based on clocked hours)</option>
                    <option value="daily" <?= $p['payment_type'] === 'daily' ? 'selected' : '' ?>>Daily (Based on days attended)</option>
                    <option value="monthly" <?= $p['payment_type'] === 'monthly' ? 'selected' : '' ?>>Monthly (Fixed salary)</option>
                    <option value="unit" <?= $p['payment_type'] === 'unit' ? 'selected' : '' ?>>Unit (Based on production quantity)</option>
                </select>
            </div>

            <div id="rate_fields" class="grid grid-cols-2 gap-4">
                <div id="hourly_field" class="payment-field">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Hourly Rate ($)</label>
                    <input type="number" step="0.01" name="hourly_rate" value="<?= $p['hourly_rate'] ?>" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary py-3">
                </div>
                <div id="daily_field" class="payment-field hidden">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Daily Rate ($)</label>
                    <input type="number" step="0.01" name="daily_rate" value="<?= $p['daily_rate'] ?>" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary py-3">
                </div>
                <div id="monthly_field" class="payment-field hidden">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Monthly Salary ($)</label>
                    <input type="number" step="0.01" name="monthly_salary" value="<?= $p['monthly_salary'] ?>" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary py-3">
                </div>
                <div id="unit_field" class="payment-field hidden">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Unit Rate ($)</label>
                    <input type="number" step="0.01" name="unit_rate" value="<?= $p['unit_rate'] ?>" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary py-3">
                </div>
            </div>

            <div class="pt-6 border-t border-primary/5 grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Overtime Threshold (Hrs/Period)</label>
                    <input type="number" name="overtime_threshold" value="<?= $p['overtime_threshold'] ?>" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary py-3">
                    <p class="text-[10px] text-slate-400 mt-1">Set to 0 to disable overtime.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Overtime Hourly Rate ($)</label>
                    <input type="number" step="0.01" name="overtime_rate" value="<?= $p['overtime_rate'] ?>" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary py-3">
                </div>
            </div>

            <div class="mt-8">
                <button type="submit" class="w-full py-4 bg-primary text-slate-900 rounded-xl font-bold hover:brightness-95 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">save</span>
                    Save Profile
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateFields() {
    const type = document.getElementById('payment_type').value;
    document.querySelectorAll('.payment-field').forEach(el => el.classList.add('hidden'));
    document.getElementById(type + '_field').classList.remove('hidden');
}
updateFields();
</script>

<?php require_once __DIR__ . '/../layouts/app_footer.php'; ?>
