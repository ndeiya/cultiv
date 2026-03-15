<?php
/**
 * Production Records List
 * Supervisor view of all production records.
 */
$active_nav = 'production';
include VIEWS_PATH . '/layouts/app_header.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">inventory_2</span>
            Production Records
        </h1>
        <div class="flex items-center gap-3">
            <input type="date" id="production-date" value="<?= e($current_date) ?>" 
                class="px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-sm">
            <button onclick="loadProduction()" class="px-4 py-2 bg-primary text-slate-900 font-bold rounded-lg hover:bg-primary/90 transition">
                Load Records
            </button>
            <a href="/supervisor/production/create" class="px-4 py-2 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition">
                + Record Production
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-primary/5 overflow-hidden">
        <?php if (empty($records)): ?>
            <div class="p-12 text-center">
                <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4 block">inventory_2</span>
                <p class="text-slate-500 dark:text-slate-400">No production records found for this date.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-400">Worker</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-400">Crop</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-400">Quantity</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-400">Unit Rate</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-400">Total Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-600 dark:text-slate-400">Recorded</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <?php 
                        $grandTotal = 0;
                        foreach ($records as $record): 
                            $grandTotal += $record['total_amount'];
                        ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3">
                                    <div class="font-semibold"><?= e($record['worker_name']) ?></div>
                                </td>
                                <td class="px-4 py-3">
                                    <?= $record['crop_name'] ? e($record['crop_name']) : '<span class="text-slate-400">—</span>' ?>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium"><?= number_format($record['quantity'], 2) ?></div>
                                    <div class="text-xs text-slate-500"><?= e($record['unit_type']) ?></div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-medium">GHS <?= number_format($record['unit_rate'], 2) ?></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-bold text-green-600 dark:text-green-400">
                                        GHS <?= number_format($record['total_amount'], 2) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm"><?= date('M j, Y', strtotime($record['created_at'])) ?></div>
                                    <div class="text-xs text-slate-500"><?= date('g:i A', strtotime($record['created_at'])) ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-slate-800 border-t-2 border-slate-200 dark:border-slate-700">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right font-bold text-slate-700 dark:text-slate-300">
                                Grand Total:
                            </td>
                            <td class="px-4 py-3 font-bold text-lg text-green-600 dark:text-green-400">
                                GHS <?= number_format($grandTotal, 2) ?>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function loadProduction() {
    const date = document.getElementById('production-date').value;
    window.location.href = `/supervisor/production?date=${date}`;
}
</script>

<?php include VIEWS_PATH . '/layouts/app_footer.php'; ?>
