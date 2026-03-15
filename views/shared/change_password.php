<?php
/**
 * Shared Change Password View
 * Works for all roles.
 */
$active_nav = 'profile';
include VIEWS_PATH . '/layouts/app_header.php';
$csrf = generate_csrf_token();
$role = $user['role'] ?? 'worker';
?>

<div class="max-w-xl mx-auto space-y-6">

    <!-- Back Link -->
    <a href="/<?= htmlspecialchars($role) ?>/profile" 
       class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-sm mr-1">arrow_back</span>
        Back to Profile
    </a>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-500/10 text-red-500 px-4 py-3 rounded-xl font-medium flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">error</span>
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Change Password Form -->
    <section class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-primary/10 shadow-sm">
        <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">lock</span>
            Change Password
        </h3>

        <form action="/<?= htmlspecialchars($role) ?>/change-password" method="POST" class="space-y-5">
            <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">

            <div>
                <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Current Password *</label>
                <input type="password" name="current_password" required autocomplete="current-password"
                    class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary px-4 py-2.5"
                    placeholder="Enter your current password">
            </div>

            <div class="border-t border-slate-100 dark:border-slate-800 pt-5">
                <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">New Password *</label>
                <input type="password" name="new_password" required autocomplete="new-password" minlength="6"
                    class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary px-4 py-2.5"
                    placeholder="At least 6 characters">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Confirm New Password *</label>
                <input type="password" name="confirm_password" required autocomplete="new-password" minlength="6"
                    class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary px-4 py-2.5"
                    placeholder="Re-enter new password">
            </div>

            <div class="flex items-center justify-end gap-4 pt-2">
                <a href="/<?= htmlspecialchars($role) ?>/profile" 
                   class="px-6 py-3 font-bold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                    class="px-8 py-3 bg-primary text-slate-900 font-black rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all flex items-center gap-2">
                    <span>Update Password</span>
                    <span class="material-symbols-outlined text-lg">lock_reset</span>
                </button>
            </div>
        </form>
    </section>

    <!-- Security Tip -->
    <div class="bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 p-4 rounded-xl text-sm border border-blue-100 dark:border-blue-800 flex items-start gap-3">
        <span class="material-symbols-outlined text-lg mt-0.5 flex-shrink-0">info</span>
        <div>
            <p class="font-bold mb-1">Password Security Tips</p>
            <ul class="list-disc list-inside text-xs space-y-0.5 opacity-80">
                <li>Use at least 6 characters</li>
                <li>Mix uppercase, lowercase, numbers, and symbols</li>
                <li>Don't reuse passwords from other accounts</li>
            </ul>
        </div>
    </div>

</div>

<?php include VIEWS_PATH . '/layouts/app_footer.php'; ?>
