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
        <?php if (in_array($user['role'], ['owner', 'supervisor'])): ?>
        <button onclick="document.getElementById('addItemModal').showModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-slate-900 font-bold rounded-lg hover:brightness-95 transition-all w-fit">
            <span class="material-symbols-outlined text-sm">add_box</span>
            Add New Item
        </button>
        <?php endif; ?>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Item Name</th>
                        <th class="px-6 py-4">Current Stock</th>
                        <th class="px-6 py-4">Storage Location</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($inventory)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                <span class="material-symbols-outlined text-4xl block mb-2 opacity-20">inventory_2</span>
                                Your inventory is empty.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($inventory as $item): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($item['item_name']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="text-lg font-black <?= $item['quantity'] <= 5 ? 'text-red-500' : 'text-slate-900 dark:text-white' ?>">
                                            <?= number_format($item['quantity'], 2) ?>
                                        </span>
                                        <span class="text-xs font-bold text-slate-400 uppercase"><?= htmlspecialchars($item['unit']) ?></span>
                                        
                                        <!-- Quick Update Button (Worker/Sup/Owner) -->
                                        <button onclick="openQuantityModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['item_name'], ENT_QUOTES) ?>', <?= $item['quantity'] ?>)" class="ml-2 p-1 text-primary hover:bg-primary/10 rounded transition-colors">
                                            <span class="material-symbols-outlined text-sm">edit_square</span>
                                        </button>
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
</script>

<?php require_once VIEWS_PATH . '/layouts/app_footer.php'; ?>
