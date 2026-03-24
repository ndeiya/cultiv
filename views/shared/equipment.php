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
        <?php if (in_array($user['role'], ['owner', 'supervisor', 'worker'])): ?>
        <button onclick="document.getElementById('addEquipmentModal').showModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-slate-900 font-bold rounded-lg hover:brightness-95 transition-all w-fit">
            <span class="material-symbols-outlined text-sm">add_circle</span>
            <?= $user['role'] === 'worker' ? 'Submit New Equipment' : 'Add Equipment' ?>
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
                        <th class="px-6 py-4">
                            <input type="checkbox" id="selectAll" class="rounded border-slate-300 text-primary focus:ring-primary">
                        </th>
                        <th class="px-6 py-4">Asset Name</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Acquired On</th>
                        <th class="px-6 py-4">Last Maintenance</th>
                        <th class="px-6 py-4">Next Maintenance</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($equipment)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                No equipment found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($equipment as $item): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <input type="checkbox" class="item-checkbox rounded border-slate-300 text-primary focus:ring-primary" value="<?= $item['id'] ?>">
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($item['name']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if (($item['approval_status'] ?? 'approved') === 'pending'): ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 flex items-center gap-1 w-fit">
                                            <span class="material-symbols-outlined text-[10px]">schedule</span> Pending
                                        </span>
                                    <?php elseif (($item['approval_status'] ?? 'approved') === 'rejected'): ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Rejected</span>
                                    <?php else: ?>
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
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                    <?= $item['acquisition_date'] ? date('M j, Y', strtotime($item['acquisition_date'])) : '-' ?>
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
                                        <?php elseif ($user['role'] === 'worker' && ($item['approval_status'] ?? 'approved') === 'pending'): ?>
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

    <!-- Bulk Action Bar -->
    <div id="bulkActionBar" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-slate-900 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-6 z-50 transition-all duration-300 translate-y-[200%] opacity-0 pointer-events-none">
        <div class="flex items-center gap-2 border-r border-slate-700 pr-6">
            <span class="bg-primary text-slate-900 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold font-display" id="selectedCount">0</span>
            <span class="text-sm font-bold uppercase tracking-wider">Items Selected</span>
        </div>
        <div class="flex items-center gap-3">
            <?php if (in_array($user['role'], ['owner', 'supervisor'])): ?>
            <button onclick="performBulkDelete()" class="flex items-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-lg transition-colors">
                <span class="material-symbols-outlined text-sm">delete</span>
                Delete Selection
            </button>
            <?php endif; ?>
            <button onclick="location.reload()" class="text-slate-400 hover:text-white text-xs font-bold uppercase tracking-wider">Cancel</button>
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
            
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status</label>
                    <select name="status" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3 appearance-none">
                        <option value="working">Working</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="broken">Broken</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Acquisition Date</label>
                    <input type="date" name="acquisition_date" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
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
            
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status</label>
                    <select name="status" id="edit_status" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3 appearance-none">
                        <option value="working">Working</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="broken">Broken</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Acquisition Date</label>
                    <input type="date" name="acquisition_date" id="edit_acquisition_date" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
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
    document.getElementById('edit_acquisition_date').value = item.acquisition_date || '';
    document.getElementById('edit_last_maintenance').value = item.last_maintenance || '';
    document.getElementById('edit_next_maintenance').value = item.next_maintenance || '';
    document.getElementById('editEquipmentModal').showModal();
}

// Bulk Actions Logic
const selectAll = document.getElementById('selectAll');
const checkboxes = document.querySelectorAll('.item-checkbox');
const bulkActionBar = document.getElementById('bulkActionBar');
const selectedCountElem = document.getElementById('selectedCount');

function updateBulkBar() {
    const selected = document.querySelectorAll('.item-checkbox:checked');
    if (selected.length > 0) {
        selectedCountElem.textContent = selected.length;
        bulkActionBar.classList.remove('translate-y-[200%]', 'opacity-0', 'pointer-events-none');
    } else {
        bulkActionBar.classList.add('translate-y-[200%]', 'opacity-0', 'pointer-events-none');
    }
}

if (selectAll) {
    selectAll.addEventListener('change', () => {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateBulkBar();
    });
}

checkboxes.forEach(cb => {
    cb.addEventListener('change', updateBulkBar);
});

async function performBulkDelete() {
    const selected = Array.from(document.querySelectorAll('.item-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) return;

    if (!confirm(`Are you sure you want to delete ${selected.length} items?`)) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const role = '<?= $user['role'] ?>';
    
    try {
        const response = await fetch(`/${role}/equipment/bulk-delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ ids: selected })
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
