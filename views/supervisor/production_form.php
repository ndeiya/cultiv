<?php
/**
 * Production Recording Form
 * Supervisor interface for recording piece-rate production.
 */
$active_nav = 'production';
include VIEWS_PATH . '/layouts/app_header.php';
?>

<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">inventory_2</span>
            Record Production
        </h1>
        <a href="/supervisor/production" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition">
            ← Back to List
        </a>
    </div>

    <form method="POST" action="/supervisor/production" class="bg-white dark:bg-slate-900 rounded-xl p-6 shadow-sm border border-primary/5 space-y-6">
        <input type="hidden" name="_csrf_token" value="<?= generate_csrf_token() ?>">

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Worker Selection -->
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                    Worker <span class="text-red-500">*</span>
                </label>
                <select name="user_id" required class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800">
                    <option value="">Select Worker</option>
                    <?php foreach ($workers as $worker): ?>
                        <?php if ($worker['role'] === 'worker'): ?>
                            <option value="<?= $worker['id'] ?>"><?= e($worker['name']) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Record Date -->
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                    Date <span class="text-red-500">*</span>
                </label>
                <input type="date" name="record_date" value="<?= date('Y-m-d') ?>" required
                    class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Crop Selection (Optional) -->
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                    Crop (Optional)
                </label>
                <select name="crop_id" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800">
                    <option value="">None</option>
                    <?php foreach ($crops as $crop): ?>
                        <option value="<?= $crop['id'] ?>"><?= e($crop['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Unit Type -->
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                    Unit Type <span class="text-red-500">*</span>
                </label>
                <select name="unit_type" required class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800">
                    <option value="kg">Kilograms (kg)</option>
                    <option value="crate">Crate</option>
                    <option value="bunch">Bunch</option>
                    <option value="bag">Bag</option>
                    <option value="ton">Ton</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Quantity -->
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                    Quantity <span class="text-red-500">*</span>
                </label>
                <input type="number" name="quantity" step="0.01" min="0" required
                    placeholder="0.00"
                    class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800">
            </div>

            <!-- Unit Rate -->
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                    Rate per Unit (GHS) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="unit_rate" step="0.01" min="0" required
                    placeholder="0.00"
                    class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800">
            </div>
        </div>

        <!-- Notes -->
        <div>
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                Notes (Optional)
            </label>
            <textarea name="notes" rows="3"
                placeholder="Additional notes about this production record..."
                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800"></textarea>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-3">
            <a href="/supervisor/production" class="px-6 py-3 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-3 bg-primary text-slate-900 font-bold rounded-lg hover:bg-primary/90 transition">
                Save Production Record
            </button>
        </div>
    </form>
</div>

<?php include VIEWS_PATH . '/layouts/app_footer.php'; ?>
