<?php
/**
 * Shared Profile View
 * View and edit user profile — works for all roles.
 */
$active_nav = 'profile';
include VIEWS_PATH . '/layouts/app_header.php';
$csrf = generate_csrf_token();
?>

<div class="max-w-3xl mx-auto space-y-6">

    <!-- Flash Messages -->
    <?php if ($success = get_flash('success')): ?>
        <div class="bg-primary/20 text-slate-900 dark:text-primary px-4 py-3 rounded-xl font-medium flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">check_circle</span>
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error = get_flash('error')): ?>
        <div class="bg-red-500/10 text-red-500 px-4 py-3 rounded-xl font-medium flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">error</span>
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <!-- Profile Card -->
    <section class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row items-center gap-6 mb-6">
            <!-- Avatar -->
            <div class="w-20 h-20 rounded-full bg-primary/20 flex items-center justify-center flex-shrink-0">
                <span class="text-3xl font-bold text-primary"><?= strtoupper(substr($profile['name'] ?? 'U', 0, 1)) ?></span>
            </div>
            <div class="text-center sm:text-left">
                <h2 class="text-xl font-bold"><?= e($profile['name'] ?? '') ?></h2>
                <span class="inline-block mt-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-primary/20 text-primary">
                    <?= e($profile['role'] ?? '') ?>
                </span>
                <p class="text-xs text-slate-500 mt-1">Member since <?= date('M j, Y', strtotime($profile['created_at'] ?? 'now')) ?></p>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="flex flex-wrap gap-3 border-t border-slate-100 dark:border-slate-800 pt-4">
            <a href="/<?= htmlspecialchars($user['role']) ?>/change-password" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-lg font-bold text-sm hover:bg-primary/10 hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-lg">lock</span>
                Change Password
            </a>
            <a href="/<?= e($user['role']) ?>/notifications" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-lg font-bold text-sm hover:bg-primary/10 hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-lg">notifications</span>
                Notifications
            </a>
        </div>
    </section>

    <!-- Edit Profile Form -->
    <section class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-primary/10 shadow-sm">
        <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">edit</span>
            Edit Profile
        </h3>

        <form action="/<?= htmlspecialchars($user['role']) ?>/profile/update" method="POST" class="space-y-5">
            <input type="hidden" name="_csrf_token" value="<?= $csrf ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Full Name *</label>
                    <input type="text" name="name" value="<?= e($profile['name'] ?? '') ?>" required
                        class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Email Address</label>
                    <input type="email" name="email" value="<?= e($profile['email'] ?? '') ?>"
                        class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary px-4 py-2.5">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Phone Number</label>
                    <input type="tel" name="phone" value="<?= e($profile['phone'] ?? '') ?>"
                        class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Role</label>
                    <input type="text" value="<?= e(ucfirst($profile['role'] ?? '')) ?>" disabled
                        class="w-full bg-slate-100 dark:bg-slate-800/50 border-none rounded-lg px-4 py-2.5 text-slate-400 cursor-not-allowed">
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-2">
                <a href="/<?= e($user['role']) ?>/dashboard" 
                   class="px-6 py-3 font-bold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                    class="px-8 py-3 bg-primary text-slate-900 font-black rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all flex items-center gap-2">
                    <span>Save Changes</span>
                    <span class="material-symbols-outlined text-lg">save</span>
                </button>
            </div>
        </form>
    </section>

</div>

<?php include VIEWS_PATH . '/layouts/app_footer.php'; ?>
