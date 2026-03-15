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

<?php require_once __DIR__ . '/../layouts/app_footer.php'; ?>
