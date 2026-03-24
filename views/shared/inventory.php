<?php
/**
 * Shared - Inventory Management View
 */
$page_title = 'Inventory Management';
$active_nav = 'operations';
require_once VIEWS_PATH . '/layouts/app_header.php';
?>

<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold">Farm Inventory</h2>
            <p class="text-sm text-slate-500">Track stock levels of seeds, fertilizers, feeds, and supplies.</p>
        </div>
        <?php if (in_array($user['role'], ['owner', 'supervisor', 'worker'])): ?>
        <button onclick="document.getElementById('addItemModal').showModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-slate-900 font-bold rounded-lg hover:brightness-95 transition-all w-fit">
            <span class="material-symbols-outlined text-sm">add_box</span>
            <?= $user['role'] === 'worker' ? 'Submit New Item' : 'Add New Item' ?>
        </button>
        <?php endif; ?>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">
                            <input type="checkbox" id="selectAll" class="rounded border-slate-300 text-primary focus:ring-primary">
                        </th>
                        <th class="px-6 py-4">Item Name</th>
                        <th class="px-6 py-4">Current Stock</th>
                        <th class="px-6 py-4">Storage Location</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($inventory)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <span class="material-symbols-outlined text-4xl block mb-2 opacity-20">inventory_2</span>
                                Your inventory is empty.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($inventory as $item): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <input type="checkbox" class="item-checkbox rounded border-slate-300 text-primary focus:ring-primary" value="<?= $item['id'] ?>">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($item['item_name']) ?></span>
                                        <?php if (($item['approval_status'] ?? 'approved') === 'pending'): ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 flex items-center gap-1 w-fit">
                                                <span class="material-symbols-outlined text-[10px]">schedule</span> Pending
                                            </span>
                                        <?php elseif (($item['approval_status'] ?? 'approved') === 'rejected'): ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 w-fit">Rejected</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="text-lg font-black <?= $item['quantity'] <= 5 ? 'text-red-500' : 'text-slate-900 dark:text-white' ?>">
                                            <?= number_format($item['quantity'], 2) ?>
                                        </span>
                                        <span class="text-xs font-bold text-slate-400 uppercase"><?= htmlspecialchars($item['unit']) ?></span>
                                        
                                        <!-- Quick Update Button (Worker/Sup/Owner) -->
                                        <?php if (($item['approval_status'] ?? 'approved') === 'approved'): ?>
                                        <button onclick="openQuantityModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['item_name'], ENT_QUOTES) ?>', <?= $item['quantity'] ?>)" class="ml-2 p-1 text-primary hover:bg-primary/10 rounded transition-colors">
                                            <span class="material-symbols-outlined text-sm">edit_square</span>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                    <?= htmlspecialchars($item['storage_location'] ?? 'Not specified') ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if (in_array($user['role'], ['owner', 'supervisor'])): ?>
                                        <button onclick='openEditModal(<?= json_encode($item) ?>)' class="p-2 text-slate-400 hover:text-primary transition-colors">
                                            <span class="material-symbols-outlined text-lg">settings</span>
                                        </button>
                                        <form action="/inventory/delete" method="POST" onsubmit="return confirm('Remove this item from inventory?')" class="inline">
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

<!-- Add Item Modal -->
<dialog id="addItemModal" class="modal bg-transparent p-0 w-full max-w-md backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl overflow-hidden border border-primary/10">
        <div class="p-6 border-b border-primary/10 flex items-center justify-between">
            <h3 class="text-xl font-bold">Add New Item</h3>
            <button onclick="document.getElementById('addItemModal').close()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="/inventory" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Item Name *</label>
                <input type="text" name="item_name" required placeholder="e.g. Fertilize NPK, Chicken Feed" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Initial Quantity *</label>
                    <input type="number" step="0.01" name="quantity" required placeholder="0.00" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unit *</label>
                    <input type="text" name="unit" required placeholder="kg, L, bags" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
            </div>
            
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Storage Location</label>
                <input type="text" name="storage_location" placeholder="e.g. Barn A, Shelf 3" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
            </div>

            <button type="submit" class="w-full py-3 bg-primary text-slate-900 font-bold rounded-xl hover:brightness-95 transition-all mt-6 shadow-lg shadow-primary/20">
                Save Item
            </button>
        </form>
    </div>
</dialog>

<!-- Quantity Update Modal -->
<dialog id="quantityModal" class="modal bg-transparent p-0 w-full max-w-sm backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl overflow-hidden border border-primary/10">
        <div class="p-6 border-b border-primary/10">
            <h3 class="text-lg font-bold" id="qty_item_name">Update Stock</h3>
        </div>
        <form action="/inventory/update-quantity" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="id" id="qty_id">
            
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">New Quantity</label>
                <input type="number" step="0.01" name="quantity" id="qty_input" required class="w-full text-2xl font-bold bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-4 text-center">
            </div>

            <div class="flex gap-2">
                <button type="button" onclick="document.getElementById('quantityModal').close()" class="flex-1 py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 font-bold rounded-xl">Cancel</button>
                <button type="submit" class="flex-1 py-3 bg-primary text-slate-900 font-bold rounded-xl">Update</button>
            </div>
        </form>
    </div>
</dialog>

<!-- Edit Full Details Modal -->
<dialog id="editItemModal" class="modal bg-transparent p-0 w-full max-w-md backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl overflow-hidden border border-primary/10">
        <div class="p-6 border-b border-primary/10 flex items-center justify-between">
            <h3 class="text-xl font-bold">Edit Item Details</h3>
            <button onclick="document.getElementById('editItemModal').close()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="/inventory/update" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Item Name *</label>
                <input type="text" name="item_name" id="edit_item_name" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Quantity *</label>
                    <input type="number" step="0.01" name="quantity" id="edit_quantity" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unit *</label>
                    <input type="text" name="unit" id="edit_unit" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
            </div>
            
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Storage Location</label>
                <input type="text" name="storage_location" id="edit_storage_location" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
            </div>

            <button type="submit" class="w-full py-3 bg-primary text-slate-900 font-bold rounded-xl">Save Changes</button>
        </form>
    </div>
</dialog>

<script>
function openQuantityModal(id, name, currentQty) {
    document.getElementById('qty_id').value = id;
    document.getElementById('qty_item_name').innerText = name;
    document.getElementById('qty_input').value = currentQty;
    document.getElementById('quantityModal').showModal();
}

function openEditModal(item) {
    document.getElementById('edit_id').value = item.id;
    document.getElementById('edit_item_name').value = item.item_name;
    document.getElementById('edit_quantity').value = item.quantity;
    document.getElementById('edit_unit').value = item.unit;
    document.getElementById('edit_storage_location').value = item.storage_location || '';
    document.getElementById('editItemModal').showModal();
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
        const response = await fetch(`/${role}/inventory/bulk-delete`, {
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
