<?php
/**
 * Shared - Equipment Management View
 */
$page_title = 'Equipment Management';
$active_nav = 'operations';
require_once VIEWS_PATH . '/layouts/app_header.php';
?>

<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold">Equipment & Machinery</h2>
            <p class="text-sm text-slate-500">Monitor status, track maintenance, and manage farm assets.</p>
        </div>
        <?php if (in_array($user['role'], ['owner', 'supervisor'])): ?>
        <button onclick="document.getElementById('addEquipmentModal').showModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-slate-900 font-bold rounded-lg hover:brightness-95 transition-all w-fit">
            <span class="material-symbols-outlined text-sm">add_circle</span>
            Add Equipment
        </button>
        <?php endif; ?>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-primary">agriculture</span>
                <span class="text-xs font-medium text-slate-500">Total Assets</span>
            </div>
            <div class="text-2xl font-bold"><?= count($equipment) ?></div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-green-500">check_circle</span>
                <span class="text-xs font-medium text-slate-500">Working</span>
            </div>
            <div class="text-2xl font-bold">
                <?= count(array_filter($equipment, fn($e) => $e['status'] === 'working')) ?>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-amber-500">build</span>
                <span class="text-xs font-medium text-slate-500">In Maintenance</span>
            </div>
            <div class="text-2xl font-bold">
                <?= count(array_filter($equipment, fn($e) => $e['status'] === 'maintenance')) ?>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-red-500">error</span>
                <span class="text-xs font-medium text-slate-500">Broken</span>
            </div>
            <div class="text-2xl font-bold">
                <?= count(array_filter($equipment, fn($e) => $e['status'] === 'broken')) ?>
            </div>
        </div>
    </div>

    <!-- Equipment Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Asset Name</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Last Maintenance</th>
                        <th class="px-6 py-4">Next Maintenance</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($equipment)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                No equipment found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($equipment as $item): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($item['name']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $badge = match($item['status']) {
                                        'working' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                        'maintenance' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                        'broken' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        default => 'bg-slate-100 text-slate-600'
                                    };
                                    ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase <?= $badge ?>">
                                        <?= htmlspecialchars($item['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                    <?= $item['last_maintenance'] ? date('M j, Y', strtotime($item['last_maintenance'])) : '-' ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if ($item['next_maintenance']): 
                                        $isOverdue = strtotime($item['next_maintenance']) < time();
                                        $color = $isOverdue ? 'text-red-500 font-bold' : 'text-slate-600 dark:text-slate-400';
                                    ?>
                                        <span class="<?= $color ?>"><?= date('M j, Y', strtotime($item['next_maintenance'])) ?></span>
                                    <?php else: ?>
                                        <span class="text-slate-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if (in_array($user['role'], ['owner', 'supervisor'])): ?>
                                        <button onclick='openEditModal(<?= json_encode($item) ?>)' class="p-2 text-slate-400 hover:text-primary transition-colors">
                                            <span class="material-symbols-outlined text-lg">edit_note</span>
                                        </button>
                                        <form action="/equipment/delete" method="POST" onsubmit="return confirm('Delete this equipment?')" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                                                <span class="material-symbols-outlined text-lg">delete</span>
                                            </button>
                                        </form>
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

<!-- Add Equipment Modal -->
<dialog id="addEquipmentModal" class="modal bg-transparent p-0 w-full max-w-md backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl overflow-hidden border border-primary/10">
        <div class="p-6 border-b border-primary/10 flex items-center justify-between">
            <h3 class="text-xl font-bold">Add New Equipment</h3>
            <button onclick="document.getElementById('addEquipmentModal').close()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="/equipment" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Equipment Name *</label>
                <input type="text" name="name" required placeholder="e.g. Tractor, Water Pump" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
            </div>
            
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status</label>
                <select name="status" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3 appearance-none">
                    <option value="working">Working</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="broken">Broken</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Last Maintenance</label>
                    <input type="date" name="last_maintenance" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Next Maintenance</label>
                    <input type="date" name="next_maintenance" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-primary text-slate-900 font-bold rounded-xl hover:brightness-95 transition-all mt-6 shadow-lg shadow-primary/20">
                Register Equipment
            </button>
        </form>
    </div>
</dialog>

<!-- Edit Equipment Modal -->
<dialog id="editEquipmentModal" class="modal bg-transparent p-0 w-full max-w-md backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl overflow-hidden border border-primary/10">
        <div class="p-6 border-b border-primary/10 flex items-center justify-between">
            <h3 class="text-xl font-bold">Edit Equipment</h3>
            <button onclick="document.getElementById('editEquipmentModal').close()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="/equipment/update" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Equipment Name *</label>
                <input type="text" name="name" id="edit_name" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
            </div>
            
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status</label>
                <select name="status" id="edit_status" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3 appearance-none">
                    <option value="working">Working</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="broken">Broken</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Last Maintenance</label>
                    <input type="date" name="last_maintenance" id="edit_last_maintenance" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Next Maintenance</label>
                    <input type="date" name="next_maintenance" id="edit_next_maintenance" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-primary text-slate-900 font-bold rounded-xl hover:brightness-95 transition-all mt-6 shadow-lg shadow-primary/20">
                Update Record
            </button>
        </form>
    </div>
</dialog>

<script>
function openEditModal(item) {
    document.getElementById('edit_id').value = item.id;
    document.getElementById('edit_name').value = item.name;
    document.getElementById('edit_status').value = item.status;
    document.getElementById('edit_last_maintenance').value = item.last_maintenance || '';
    document.getElementById('edit_next_maintenance').value = item.next_maintenance || '';
    document.getElementById('editEquipmentModal').showModal();
}
</script>

<?php require_once VIEWS_PATH . '/layouts/app_footer.php'; ?>
