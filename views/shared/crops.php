<?php
/**
 * Shared - Crops Management View
 */
$page_title = 'Crops Management';
$active_nav = 'operations';
require_once VIEWS_PATH . '/layouts/app_header.php';
?>

<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold">Crops & Fields</h2>
            <p class="text-sm text-slate-500">Track planting, growth stages, and field health.</p>
        </div>
        <?php if (in_array($user['role'], ['owner', 'supervisor', 'worker'])): ?>
        <button onclick="document.getElementById('addCropModal').showModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-slate-900 font-bold rounded-lg hover:brightness-95 transition-all w-fit">
            <span class="material-symbols-outlined text-sm">add_circle</span>
            <?= $user['role'] === 'worker' ? 'Submit New Crop' : 'Add New Crop' ?>
        </button>
        <?php endif; ?>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-primary">potted_plant</span>
                <span class="text-xs font-medium text-slate-500">Total Crops</span>
            </div>
            <div class="text-2xl font-bold"><?= count($crops) ?></div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-green-500">check_circle</span>
                <span class="text-xs font-medium text-slate-500">Healthy</span>
            </div>
            <div class="text-2xl font-bold">
                <?= count(array_filter($crops, fn($c) => $c['health_status'] === 'good')) ?>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-amber-500">warning</span>
                <span class="text-xs font-medium text-slate-500">Issues</span>
            </div>
            <div class="text-2xl font-bold">
                <?= count(array_filter($crops, fn($c) => $c['health_status'] !== 'good')) ?>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-primary">payments</span>
                <span class="text-xs font-medium text-slate-500">Total Investment</span>
            </div>
            <div class="text-2xl font-bold">
                GHS <?= number_format(array_sum(array_column($crops, 'total_cost')), 2) ?>
            </div>
        </div>
    </div>

    <!-- Crops Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Crop Name</th>
                        <th class="px-6 py-4">Field / Location</th>
                        <th class="px-6 py-4">Growth Stage</th>
                        <th class="px-6 py-4">Health</th>
                        <th class="px-6 py-4">Harvest Cost</th>
                        <th class="px-6 py-4">Planted</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($crops)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">eco</span>
                                <p class="text-slate-500">No crops recorded yet.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($crops as $crop): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($crop['name']) ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                    <?= htmlspecialchars($crop['field_name'] ?? 'Not specified') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                        <?= htmlspecialchars($crop['growth_stage']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if (($crop['approval_status'] ?? 'approved') === 'pending'): ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 flex items-center gap-1 w-fit">
                                            <span class="material-symbols-outlined text-[10px]">schedule</span> Pending
                                        </span>
                                    <?php elseif (($crop['approval_status'] ?? 'approved') === 'rejected'): ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Rejected</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase <?= $badge ?>">
                                            <?= htmlspecialchars($crop['health_status']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 font-bold text-sm">
                                    GHS <?= number_format($crop['total_cost'] ?? 0, 2) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                    <?= $crop['planting_date'] ? date('M j, Y', strtotime($crop['planting_date'])) : '-' ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if (in_array($user['role'], ['owner', 'supervisor'])): ?>
                                        <button onclick='openEditModal(<?= json_encode($crop) ?>)' class="p-2 text-slate-400 hover:text-primary transition-colors">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </button>
                                        <form action="/crops/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this crop?')" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <input type="hidden" name="id" value="<?= $crop['id'] ?>">
                                            <button type="submit" class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                                                <span class="material-symbols-outlined text-lg">delete</span>
                                            </button>
                                        </form>
                                        <?php elseif ($user['role'] === 'worker' && ($crop['approval_status'] ?? 'approved') === 'pending'): ?>
                                            <span class="text-[10px] text-slate-400 italic">Awaiting Approval</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Crop Modal -->
<dialog id="addCropModal" class="modal bg-transparent p-0 w-full max-w-md backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl overflow-hidden border border-primary/10">
        <div class="p-6 border-b border-primary/10 flex items-center justify-between">
            <h3 class="text-xl font-bold">Add New Crop</h3>
            <button onclick="document.getElementById('addCropModal').close()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="/crops" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Crop Name *</label>
                <input type="text" name="name" required placeholder="e.g. Maize, Coffee" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
            </div>
            
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Field / Location</label>
                <input type="text" name="field_name" placeholder="e.g. Sector B-1" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Planting Date</label>
                    <input type="date" name="planting_date" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Expected Harvest</label>
                    <input type="date" name="expected_harvest" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Growth Stage</label>
                    <select name="growth_stage" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3 appearance-none">
                        <option value="Planting">Planting</option>
                        <option value="Vegetative">Vegetative</option>
                        <option value="Flowering">Flowering</option>
                        <option value="Fruiting">Fruiting</option>
                        <option value="Harvesting">Harvesting</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Health Status</label>
                    <select name="health_status" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3 appearance-none">
                        <option value="good">Good / Healthy</option>
                        <option value="warning">Warning / Issue</option>
                        <option value="bad">Bad / Critical</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Quantity to Add</label>
                <input type="number" name="quantity" value="1" min="1" max="100" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                <p class="text-[10px] text-slate-400">Specify how many identical records to create.</p>
            </div>

            <button type="submit" class="w-full py-3 bg-primary text-slate-900 font-bold rounded-xl hover:brightness-95 transition-all mt-6 shadow-lg shadow-primary/20">
                Save Crop Record
            </button>
        </form>
    </div>
</dialog>

<!-- Edit Crop Modal -->
<dialog id="editCropModal" class="modal bg-transparent p-0 w-full max-w-md backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl overflow-hidden border border-primary/10">
        <div class="p-6 border-b border-primary/10 flex items-center justify-between">
            <h3 class="text-xl font-bold">Edit Crop</h3>
            <button onclick="document.getElementById('editCropModal').close()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="/crops/update" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Crop Name *</label>
                <input type="text" name="name" id="edit_name" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
            </div>
            
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Field / Location</label>
                <input type="text" name="field_name" id="edit_field_name" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Planting Date</label>
                    <input type="date" name="planting_date" id="edit_planting_date" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Expected Harvest</label>
                    <input type="date" name="expected_harvest" id="edit_expected_harvest" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Growth Stage</label>
                    <select name="growth_stage" id="edit_growth_stage" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3 appearance-none">
                        <option value="Planting">Planting</option>
                        <option value="Vegetative">Vegetative</option>
                        <option value="Flowering">Flowering</option>
                        <option value="Fruiting">Fruiting</option>
                        <option value="Harvesting">Harvesting</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Health Status</label>
                    <select name="health_status" id="edit_health_status" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3 appearance-none">
                        <option value="good">Good / Healthy</option>
                        <option value="warning">Warning / Issue</option>
                        <option value="bad">Bad / Critical</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-primary text-slate-900 font-bold rounded-xl hover:brightness-95 transition-all mt-6 shadow-lg shadow-primary/20">
                Update Crop Record
            </button>
        </form>
    </div>
</dialog>

<script>
function openEditModal(crop) {
    document.getElementById('edit_id').value = crop.id;
    document.getElementById('edit_name').value = crop.name;
    document.getElementById('edit_field_name').value = crop.field_name || '';
    document.getElementById('edit_planting_date').value = crop.planting_date || '';
    document.getElementById('edit_expected_harvest').value = crop.expected_harvest || '';
    document.getElementById('edit_growth_stage').value = crop.growth_stage;
    document.getElementById('edit_health_status').value = crop.health_status;
    document.getElementById('editCropModal').showModal();
}
</script>

<?php require_once VIEWS_PATH . '/layouts/app_footer.php'; ?>
