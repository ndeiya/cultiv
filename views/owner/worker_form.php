<?php
/**
 * Owner - Worker Form View
 * Used for both Add and Edit actions
 */

$isEdit = isset($worker) && $worker !== null;
$actionPath = $isEdit ? '/owner/workers/update' : '/owner/workers';
require_once __DIR__ . '/../layouts/app_header.php';
?>

<div class="px-8 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-end mb-8">
            <div>
                <a href="/owner/workers" class="text-sm font-bold text-slate-500 hover:text-primary transition-colors flex items-center gap-1 mb-2">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Back to Workforce
                </a>
                <h2 class="text-3xl font-black tracking-tight mb-2"><?= $isEdit ? 'Edit Worker' : 'Add New Worker' ?></h2>
                <p class="text-slate-500 dark:text-slate-400">
                    <?= $isEdit ? 'Update staff member details and roles.' : 'Add a new member to your farm\'s workforce.' ?>
                </p>
            </div>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">error</span>
                <?php echo htmlspecialchars($_SESSION['error']); ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="<?= $actionPath ?>" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $worker['id'] ?>">
            <?php endif; ?>

            <!-- Worker Details -->
            <section class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-primary/10 shadow-sm">
                <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">person</span>
                    Worker Details
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Full Name *</label>
                        <input type="text" name="name" required class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary" 
                               value="<?= $isEdit ? htmlspecialchars($worker['name']) : '' ?>" placeholder="John Doe">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Email Address</label>
                        <input type="email" name="email" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary" 
                               value="<?= $isEdit ? htmlspecialchars($worker['email'] ?? '') : '' ?>" placeholder="john@example.com">
                        <p class="text-[10px] text-slate-400 mt-1 mt-1">Required for login if they use the app.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Phone Number</label>
                        <input type="tel" name="phone" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary" 
                               value="<?= $isEdit ? htmlspecialchars($worker['phone'] ?? '') : '' ?>" placeholder="+1234567890">
                    </div>
                    
                    <?php if (!$isEdit): ?>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Initial Password *</label>
                            <input type="password" name="password" required class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary" placeholder="••••••••">
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Role Selection -->
            <section class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-primary/10 shadow-sm">
                <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">badge</span>
                    System Role
                </h3>
                
                <?php $currentRole = $isEdit ? $worker['role'] : 'worker'; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Worker Role -->
                    <label class="relative cursor-pointer">
                        <input type="radio" name="role" value="worker" class="peer sr-only" <?= $currentRole === 'worker' ? 'checked' : '' ?>>
                        <div class="h-full p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 text-slate-500 hover:border-primary/50 peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-slate-900 dark:peer-checked:text-white transition-all">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="material-symbols-outlined peer-checked:text-primary">agriculture</span>
                                <span class="font-bold">Worker</span>
                            </div>
                            <p class="text-xs opacity-80">Can view assigned tasks, submit simple reports, and clock in/out.</p>
                        </div>
                    </label>

                    <!-- Supervisor Role -->
                    <label class="relative cursor-pointer">
                        <input type="radio" name="role" value="supervisor" class="peer sr-only" <?= $currentRole === 'supervisor' ? 'checked' : '' ?>>
                        <div class="h-full p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 text-slate-500 hover:border-primary/50 peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-slate-900 dark:peer-checked:text-white transition-all">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="material-symbols-outlined peer-checked:text-primary">engineering</span>
                                <span class="font-bold">Supervisor</span>
                            </div>
                            <p class="text-xs opacity-80">Can manage daily operations, approve reports, and view team attendance.</p>
                        </div>
                    </label>

                    <!-- Accountant Role -->
                    <label class="relative cursor-pointer">
                        <input type="radio" name="role" value="accountant" class="peer sr-only" <?= $currentRole === 'accountant' ? 'checked' : '' ?>>
                        <div class="h-full p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 text-slate-500 hover:border-primary/50 peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-slate-900 dark:peer-checked:text-white transition-all">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="material-symbols-outlined peer-checked:text-primary">account_balance</span>
                                <span class="font-bold">Accountant</span>
                            </div>
                            <p class="text-xs opacity-80">Access to payroll, financial reports, and worker payment profiles.</p>
                        </div>
                    </label>
                </div>
            </section>

            <!-- Payment Profile Settings -->
            <section id="payment_profile_section" class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-primary/10 shadow-sm">
                <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">payments</span>
                    Payment Profile
                </h3>
                
                <?php 
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
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-2">Payment Type</label>
                        <select name="payment_type" id="payment_type" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary py-3" onchange="updateRateFields()">
                            <option value="hourly" <?= $p['payment_type'] === 'hourly' ? 'selected' : '' ?>>Hourly (Based on clocked hours)</option>
                            <option value="daily" <?= $p['payment_type'] === 'daily' ? 'selected' : '' ?>>Daily (Based on days attended)</option>
                            <option value="monthly" <?= $p['payment_type'] === 'monthly' ? 'selected' : '' ?>>Monthly (Fixed salary)</option>
                            <option value="unit" <?= $p['payment_type'] === 'unit' ? 'selected' : '' ?>>Unit (Based on production quantity)</option>
                        </select>
                    </div>

                    <div id="rate_fields" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div id="hourly_field" class="payment-field">
                            <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Hourly Rate ($)</label>
                            <input type="number" step="0.01" name="hourly_rate" value="<?= $p['hourly_rate'] ?>" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div id="daily_field" class="payment-field hidden">
                            <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Daily Rate ($)</label>
                            <input type="number" step="0.01" name="daily_rate" value="<?= $p['daily_rate'] ?>" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div id="monthly_field" class="payment-field hidden">
                            <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Monthly Salary ($)</label>
                            <input type="number" step="0.01" name="monthly_salary" value="<?= $p['monthly_salary'] ?>" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div id="unit_field" class="payment-field hidden">
                            <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Unit Rate ($)</label>
                            <input type="number" step="0.01" name="unit_rate" value="<?= $p['unit_rate'] ?>" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                    </div>

                    <div class="pt-6 border-t border-primary/5 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Overtime Threshold (Hrs/Period)</label>
                            <input type="number" name="overtime_threshold" value="<?= $p['overtime_threshold'] ?>" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary">
                            <p class="text-[10px] text-slate-400 mt-1">Set to 0 to disable overtime.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Overtime Hourly Rate ($)</label>
                            <input type="number" step="0.01" name="overtime_rate" value="<?= $p['overtime_rate'] ?>" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                </div>
            </section>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-4 pt-4">
                <a href="/owner/workers" class="px-6 py-3 font-bold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-10 py-3 bg-primary text-slate-900 font-black rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined">save</span>
                    <span><?= $isEdit ? 'Save Changes' : 'Create Worker' ?></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateRateFields() {
    const type = document.getElementById('payment_type').value;
    document.querySelectorAll('.payment-field').forEach(el => el.classList.add('hidden'));
    const targetField = document.getElementById(type + '_field');
    if (targetField) {
        targetField.classList.remove('hidden');
    }
}

// Initialize on load
document.addEventListener('DOMContentLoaded', updateRateFields);
</script>

<?php require_once __DIR__ . '/../layouts/app_footer.php'; ?>
