<?php
/**
 * Owner System Settings
 * Refactored to use shared app_header/app_footer layout.
 */
$active_nav = 'settings';
include VIEWS_PATH . '/layouts/app_header.php';
?>

<div class="max-w-4xl mx-auto space-y-6">

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-primary/20 text-slate-900 dark:text-primary px-4 py-3 rounded-xl font-medium flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">check_circle</span>
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-500/10 text-red-500 px-4 py-3 rounded-xl font-medium flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">error</span>
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="/owner/settings/update" method="POST" class="space-y-6">
        <!-- General Settings -->
        <section class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-primary/10 shadow-sm">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">domain</span>
                Farm Details
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Farm Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($farm['name'] ?? '') ?>" required 
                        class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Geofence Radius (meters)</label>
                    <input type="number" name="geofence_radius" value="<?= htmlspecialchars($farm['geofence_radius'] ?? 200) ?>" required 
                        class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary px-4 py-2.5">
                </div>
            </div>
        </section>

        <!-- Operations Settings -->
        <section class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-primary/10 shadow-sm">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">payments</span>
                Payroll Configuration
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Overtime Threshold (Hours/Week)</label>
                    <input type="number" name="overtime_threshold" value="<?= htmlspecialchars($farm['overtime_threshold'] ?? 40) ?>" required 
                        class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Default Payment Type</label>
                    <select name="default_payment_type" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary px-4 py-2.5">
                        <?php
                        $paymentType = $farm['default_payment_type'] ?? 'hourly';
                        $options = [
                            'hourly' => 'Hourly Rate',
                            'daily' => 'Daily Rate',
                            'monthly' => 'Monthly Salary',
                            'unit' => 'Pay Per Unit'
                        ];
                        foreach ($options as $val => $label) {
                            $selected = ($val === $paymentType) ? 'selected' : '';
                            echo "<option value=\"$val\" $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </section>

        <!-- WhatsApp Settings -->
        <section class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-primary/10 shadow-sm">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">chat</span>
                WhatsApp Business API (Meta Cloud)
            </h3>
            <p class="text-sm text-slate-500 mb-4 italic">Used for automated alerts and notifications to workers and supervisors.</p>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Phone Number ID</label>
                    <input type="text" name="wa_phone_number_id" value="<?= htmlspecialchars($farm['wa_phone_number_id'] ?? '') ?>" placeholder="e.g. 106555123456789"
                        class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary px-4 py-2.5 font-mono text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Permanent Access Token</label>
                    <textarea name="wa_access_token" rows="3" placeholder="EAABw..."
                        class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary px-4 py-2.5 font-mono text-xs"><?= htmlspecialchars($farm['wa_access_token'] ?? '') ?></textarea>
                </div>
            </div>
        </section>

        <div class="flex justify-end gap-4 pt-4">
            <a href="/owner/dashboard" class="px-6 py-3 font-bold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-8 py-3 bg-primary text-slate-900 font-black rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all flex items-center gap-2">
                <span>Save Settings</span>
                <span class="material-symbols-outlined text-lg">save</span>
            </button>
        </div>
    </form>
</div>

<?php include VIEWS_PATH . '/layouts/app_footer.php'; ?>
