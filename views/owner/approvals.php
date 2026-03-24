<?php
/**
 * Owner/Supervisor - Pending Approvals View
 */
$page_title = 'Pending Approvals';
$active_nav = 'approvals';
require_once VIEWS_PATH . '/layouts/app_header.php';

$has_pending = !empty($crops) || !empty($animals) || !empty($equipment) || !empty($inventory) || !empty($reports);
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black tracking-tight">Pending Approvals</h2>
            <p class="text-slate-500">Review and approve submissions from farm workers.</p>
        </div>
    </div>

    <?php if (!$has_pending): ?>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-12 text-center border border-primary/10 shadow-sm mt-8">
            <span class="material-symbols-outlined text-6xl text-slate-200 mb-4">task_alt</span>
            <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Everything is up to date!</h3>
            <p class="text-slate-500">There are no pending submissions awaiting your approval at this time.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 gap-8 pb-24">
            
            <!-- Bulk Action Bar (Sticky) -->
            <div id="bulk-action-bar" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-slate-900 text-white px-6 py-4 rounded-2xl shadow-2xl border border-white/10 flex items-center gap-6 transition-transform duration-300 translate-y-full">
                <div class="flex items-center gap-3 border-r border-white/10 pr-6">
                    <span id="selected-count" class="bg-primary text-slate-900 size-6 rounded-full flex items-center justify-center text-xs font-black">0</span>
                    <span class="text-sm font-bold tracking-tight">Items Selected</span>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="processBulk('approve')" class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition-all text-xs font-bold">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        Bulk Approve
                    </button>
                    <button onclick="processBulk('reject')" class="flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl transition-all text-xs font-bold">
                        <span class="material-symbols-outlined text-sm">cancel</span>
                        Bulk Reject
                    </button>
                    <button onclick="deselectAll()" class="px-2 py-2 hover:bg-white/10 rounded-xl transition-colors text-xs opacity-50 hover:opacity-100">Cancel</button>
                </div>
            </div>

            <!-- Pending Reports -->
            <?php if (!empty($reports)): ?>
            <section class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">description</span>
                        <h3 class="text-lg font-bold">Pending Reports (<?= count($reports) ?>)</h3>
                    </div>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-400 cursor-pointer">
                        <input type="checkbox" onchange="toggleGroup('report', this.checked)" class="size-4 rounded border-slate-300 text-primary focus:ring-primary">
                        Select All
                    </label>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm overflow-hidden text-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-primary/10">
                                <th class="px-6 py-4 w-12 text-center"></th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Worker / Date</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Category</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Description</th>
                                <th class="px-6 py-4 text-right px-6">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <?php foreach ($reports as $report): ?>
                            <tr class="hover:bg-primary/5 transition-colors group">
                                <td class="px-6 py-4 text-center">
                                    <input type="checkbox" name="selected_items[]" value="<?= $report['id'] ?>" data-type="report" onchange="updateSelection()" class="size-4 rounded border-slate-300 text-primary focus:ring-primary">
                                </td>
                                <td class="px-6 py-4 font-medium">
                                    <div class="font-bold text-slate-900 dark:text-white"><?= e($report['reporter_name']) ?></div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-tighter"><?= date('M j, Y h:i A', strtotime($report['created_at'])) ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 font-bold text-[10px] uppercase">
                                        <?= e($report['category']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 truncate max-w-xs" title="<?= e($report['description']) ?>">
                                    <?= e($report['description']) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button onclick="processApproval('report', <?= $report['id'] ?>, 'approve')" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Approve">
                                            <span class="material-symbols-outlined">check_circle</span>
                                        </button>
                                        <button onclick="processApproval('report', <?= $report['id'] ?>, 'reject')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Reject">
                                            <span class="material-symbols-outlined">cancel</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <!-- Pending Crops -->
            <?php if (!empty($crops)): ?>
            <section class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">eco</span>
                        <h3 class="text-lg font-bold">New Crops (<?= count($crops) ?>)</h3>
                    </div>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-400 cursor-pointer">
                        <input type="checkbox" onchange="toggleGroup('crop', this.checked)" class="size-4 rounded border-slate-300 text-primary focus:ring-primary">
                        Select All
                    </label>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm overflow-hidden text-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-primary/10">
                                <th class="px-6 py-4 w-12 text-center"></th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Crop Detail</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Field</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Stage / Health</th>
                                <th class="px-6 py-4 text-right px-6">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <?php foreach ($crops as $crop): ?>
                            <tr class="hover:bg-primary/5 transition-colors group">
                                <td class="px-6 py-4 text-center">
                                    <input type="checkbox" name="selected_items[]" value="<?= $crop['id'] ?>" data-type="crop" onchange="updateSelection()" class="size-4 rounded border-slate-300 text-primary focus:ring-primary">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900 dark:text-white"><?= e($crop['name']) ?></div>
                                    <div class="text-[10px] text-slate-400">Planted: <?= date('M j, Y', strtotime($crop['planting_date'])) ?></div>
                                </td>
                                <td class="px-6 py-4"><?= e($crop['field_name'] ?: 'N/A') ?></td>
                                <td class="px-6 py-4 space-x-1">
                                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 font-bold text-[10px] uppercase"><?= e($crop['growth_stage']) ?></span>
                                    <span class="px-2 py-0.5 rounded bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400 font-bold text-[10px] uppercase"><?= e($crop['health_status']) ?></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button onclick="processApproval('crop', <?= $crop['id'] ?>, 'approve')" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Approve">
                                            <span class="material-symbols-outlined">check_circle</span>
                                        </button>
                                        <button onclick="processApproval('crop', <?= $crop['id'] ?>, 'reject')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Reject">
                                            <span class="material-symbols-outlined">cancel</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <!-- Pending Animals -->
            <?php if (!empty($animals)): ?>
            <section class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">pets</span>
                        <h3 class="text-lg font-bold">Registered Animals (<?= count($animals) ?>)</h3>
                    </div>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-400 cursor-pointer">
                        <input type="checkbox" onchange="toggleGroup('animal', this.checked)" class="size-4 rounded border-slate-300 text-primary focus:ring-primary">
                        Select All
                    </label>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm overflow-hidden text-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-primary/10">
                                <th class="px-6 py-4 w-12 text-center"></th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Tag / Species</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Breed / weight</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Health</th>
                                <th class="px-6 py-4 text-right px-6">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <?php foreach ($animals as $animal): ?>
                            <tr class="hover:bg-primary/5 transition-colors group">
                                <td class="px-6 py-4 text-center">
                                    <input type="checkbox" name="selected_items[]" value="<?= $animal['id'] ?>" data-type="animal" onchange="updateSelection()" class="size-4 rounded border-slate-300 text-primary focus:ring-primary">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900 dark:text-white"><?= e($animal['tag_number']) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= e($animal['species']) ?></div>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <div><?= e($animal['breed'] ?: 'N/A') ?></div>
                                    <div><?= e($animal['weight'] ?: '0') ?> kg</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400 font-bold text-[10px] uppercase"><?= e($animal['health_status']) ?></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button onclick="processApproval('animal', <?= $animal['id'] ?>, 'approve')" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Approve">
                                            <span class="material-symbols-outlined">check_circle</span>
                                        </button>
                                        <button onclick="processApproval('animal', <?= $animal['id'] ?>, 'reject')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Reject">
                                            <span class="material-symbols-outlined">cancel</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <!-- Pending Inventory -->
            <?php if (!empty($inventory)): ?>
            <section class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">inventory_2</span>
                        <h3 class="text-lg font-bold">New Inventory Items (<?= count($inventory) ?>)</h3>
                    </div>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-400 cursor-pointer">
                        <input type="checkbox" onchange="toggleGroup('inventory', this.checked)" class="size-4 rounded border-slate-300 text-primary focus:ring-primary">
                        Select All
                    </label>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm overflow-hidden text-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-primary/10">
                                <th class="px-6 py-4 w-12 text-center"></th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Item Name</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Quantity / Unit</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Storage</th>
                                <th class="px-6 py-4 text-right px-6">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <?php foreach ($inventory as $item): ?>
                            <tr class="hover:bg-primary/5 transition-colors group">
                                <td class="px-6 py-4 text-center">
                                    <input type="checkbox" name="selected_items[]" value="<?= $item['id'] ?>" data-type="inventory" onchange="updateSelection()" class="size-4 rounded border-slate-300 text-primary focus:ring-primary">
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white"><?= e($item['item_name']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="font-bold"><?= number_format($item['quantity'], 2) ?></span>
                                    <span class="text-[10px] text-slate-400 uppercase font-black"><?= e($item['unit']) ?></span>
                                </td>
                                <td class="px-6 py-4"><?= e($item['storage_location'] ?: 'N/A') ?></td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button onclick="processApproval('inventory', <?= $item['id'] ?>, 'approve')" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Approve">
                                            <span class="material-symbols-outlined">check_circle</span>
                                        </button>
                                        <button onclick="processApproval('inventory', <?= $item['id'] ?>, 'reject')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Reject">
                                            <span class="material-symbols-outlined">cancel</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <!-- Pending Equipment -->
            <?php if (!empty($equipment)): ?>
            <section class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">construction</span>
                        <h3 class="text-lg font-bold">New Equipment (<?= count($equipment) ?>)</h3>
                    </div>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-400 cursor-pointer">
                        <input type="checkbox" onchange="toggleGroup('equipment', this.checked)" class="size-4 rounded border-slate-300 text-primary focus:ring-primary">
                        Select All
                    </label>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm overflow-hidden text-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-primary/10">
                                <th class="px-6 py-4 w-12 text-center"></th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Name</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Status</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Acquired</th>
                                <th class="px-6 py-4 text-right px-6">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <?php foreach ($equipment as $item): ?>
                            <tr class="hover:bg-primary/5 transition-colors group">
                                <td class="px-6 py-4 text-center">
                                    <input type="checkbox" name="selected_items[]" value="<?= $item['id'] ?>" data-type="equipment" onchange="updateSelection()" class="size-4 rounded border-slate-300 text-primary focus:ring-primary">
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white"><?= e($item['name']) ?></td>
                                <td class="px-6 py-4 uppercase font-black text-[10px] text-slate-400"><?= e($item['status']) ?></td>
                                <td class="px-6 py-4 text-xs"><?= $item['acquisition_date'] ? date('M j, Y', strtotime($item['acquisition_date'])) : 'N/A' ?></td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button onclick="processApproval('equipment', <?= $item['id'] ?>, 'approve')" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Approve">
                                            <span class="material-symbols-outlined">check_circle</span>
                                        </button>
                                        <button onclick="processApproval('equipment', <?= $item['id'] ?>, 'reject')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Reject">
                                            <span class="material-symbols-outlined">cancel</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

        </div>
    <?php endif; ?>
</div>

<script>
function toggleGroup(type, checked) {
    document.querySelectorAll(`input[data-type="${type}"]`).forEach(cb => {
        cb.checked = checked;
    });
    updateSelection();
}

function updateSelection() {
    const selected = document.querySelectorAll('input[name="selected_items[]"]:checked');
    const bar = document.getElementById('bulk-action-bar');
    const count = document.getElementById('selected-count');

    if (selected.length > 0) {
        bar.classList.remove('translate-y-full');
        count.textContent = selected.length;
    } else {
        bar.classList.add('translate-y-full');
    }
}

function deselectAll() {
    document.querySelectorAll('input[name="selected_items[]"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('input[onchange^="toggleGroup"]').forEach(cb => cb.checked = false);
    updateSelection();
}

async function processBulk(action) {
    const selected = document.querySelectorAll('input[name="selected_items[]"]:checked');
    if (selected.length === 0) return;

    if (!confirm(`Are you sure you want to ${action} ${selected.length} items?`)) return;

    const items = Array.from(selected).map(cb => ({
        type: cb.getAttribute('data-type'),
        id: cb.value
    }));

    const endpoint = `/<?= $role ?>/approvals/bulk-${action}`;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ items })
        });

        const result = await response.json();
        if (result.success) {
            window.location.reload();
        } else {
            alert(result.message || 'Bulk operation failed.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('A network error occurred.');
    }
}

async function processApproval(type, id, action) {
    const confirmMsg = action === 'approve' 
        ? `Are you sure you want to approve this ${type}?` 
        : `Are you sure you want to reject this ${type}?`;
        
    if (!confirm(confirmMsg)) return;

    const endpoint = `/<?= $role ?>/approvals/${action}`;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ type, id })
        });

        const result = await response.json();
        if (result.success) {
            window.location.reload();
        } else {
            alert(result.message || 'Operation failed.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('A network error occurred.');
    }
}
</script>

<?php require_once VIEWS_PATH . '/layouts/app_footer.php'; ?>
