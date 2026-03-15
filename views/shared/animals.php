<?php
/**
 * Shared - Livestock Management View
 */
$page_title = 'Livestock Management';
$active_nav = 'operations';
require_once VIEWS_PATH . '/layouts/app_header.php';
?>

<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold">Livestock & Animals</h2>
            <p class="text-sm text-slate-500">Manage tags, health status, and vaccination schedules.</p>
        </div>
        <?php if (in_array($user['role'], ['owner', 'supervisor'])): ?>
        <button onclick="document.getElementById('addAnimalModal').showModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-slate-900 font-bold rounded-lg hover:brightness-95 transition-all w-fit">
            <span class="material-symbols-outlined text-sm">add_circle</span>
            Register Animal
        </button>
        <?php endif; ?>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-primary">pets</span>
                <span class="text-xs font-medium text-slate-500">Total Animals</span>
            </div>
            <div class="text-2xl font-bold"><?= count($animals) ?></div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-green-500">health_and_safety</span>
                <span class="text-xs font-medium text-slate-500">Healthy</span>
            </div>
            <div class="text-2xl font-bold">
                <?= count(array_filter($animals, fn($a) => $a['health_status'] === 'good')) ?>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-amber-500">vaccines</span>
                <span class="text-xs font-medium text-slate-500">Vaccination Due</span>
            </div>
            <div class="text-2xl font-bold">
                <?= count(array_filter($animals, fn($a) => !empty($a['vaccination_due']) && strtotime($a['vaccination_due']) < strtotime('+7 days'))) ?>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-red-500">emergency</span>
                <span class="text-xs font-medium text-slate-500">Medical Attention</span>
            </div>
            <div class="text-2xl font-bold">
                <?= count(array_filter($animals, fn($a) => $a['health_status'] !== 'good')) ?>
            </div>
        </div>
    </div>

    <!-- Animals Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Tag #</th>
                        <th class="px-6 py-4">Species / Breed</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Weight</th>
                        <th class="px-6 py-4">Next Vaccination</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($animals)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">pets</span>
                                <p class="text-slate-500">No animals registered yet.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($animals as $animal): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-primary"><?= htmlspecialchars($animal['tag_number']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-900 dark:text-white"><?= htmlspecialchars($animal['species']) ?></span>
                                        <span class="text-xs text-slate-500"><?= htmlspecialchars($animal['breed'] ?? '-') ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $badge = match($animal['health_status']) {
                                        'good'    => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                        'sick'    => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                        'injured' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        default   => 'bg-slate-100 text-slate-600'
                                    };
                                    ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase <?= $badge ?>">
                                        <?= htmlspecialchars($animal['health_status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                    <?= $animal['weight'] ? htmlspecialchars($animal['weight']) . ' kg' : '-' ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if ($animal['vaccination_due']): 
                                        $isOverdue = strtotime($animal['vaccination_due']) < time();
                                        $isSoon = !$isOverdue && strtotime($animal['vaccination_due']) < strtotime('+7 days');
                                        $color = $isOverdue ? 'text-red-500 font-bold' : ($isSoon ? 'text-amber-500 font-bold' : 'text-slate-600 dark:text-slate-400');
                                    ?>
                                        <span class="<?= $color ?>"><?= date('M j, Y', strtotime($animal['vaccination_due'])) ?></span>
                                    <?php else: ?>
                                        <span class="text-slate-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if (in_array($user['role'], ['owner', 'supervisor'])): ?>
                                        <button onclick='openEditModal(<?= json_encode($animal) ?>)' class="p-2 text-slate-400 hover:text-primary transition-colors">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </button>
                                        <form action="/animals/delete" method="POST" onsubmit="return confirm('Delete this animal record?')" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <input type="hidden" name="id" value="<?= $animal['id'] ?>">
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

<!-- Add Animal Modal -->
<dialog id="addAnimalModal" class="modal bg-transparent p-0 w-full max-w-md backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl overflow-hidden border border-primary/10">
        <div class="p-6 border-b border-primary/10 flex items-center justify-between">
            <h3 class="text-xl font-bold">Register Animal</h3>
            <button onclick="document.getElementById('addAnimalModal').close()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="/animals" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tag Number *</label>
                <input type="text" name="tag_number" required placeholder="e.g. COW-001" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Species *</label>
                    <input type="text" name="species" required placeholder="e.g. Cattle" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Breed</label>
                    <input type="text" name="breed" placeholder="e.g. Friesian" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Weight (kg)</label>
                    <input type="number" step="0.01" name="weight" placeholder="0.00" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Health Status</label>
                    <select name="health_status" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3 appearance-none">
                        <option value="good">Healthy</option>
                        <option value="sick">Sick</option>
                        <option value="injured">Injured</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Next Vaccination Due</label>
                <input type="date" name="vaccination_due" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
            </div>

            <button type="submit" class="w-full py-3 bg-primary text-slate-900 font-bold rounded-xl hover:brightness-95 transition-all mt-6">
                Register Animal
            </button>
        </form>
    </div>
</dialog>

<!-- Edit Animal Modal -->
<dialog id="editAnimalModal" class="modal bg-transparent p-0 w-full max-w-md backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl overflow-hidden border border-primary/10">
        <div class="p-6 border-b border-primary/10 flex items-center justify-between">
            <h3 class="text-xl font-bold">Edit Animal</h3>
            <button onclick="document.getElementById('editAnimalModal').close()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="/animals/update" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tag Number *</label>
                <input type="text" name="tag_number" id="edit_tag_number" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Species *</label>
                    <input type="text" name="species" id="edit_species" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Breed</label>
                    <input type="text" name="breed" id="edit_breed" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Weight (kg)</label>
                    <input type="number" step="0.01" name="weight" id="edit_weight" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Health Status</label>
                    <select name="health_status" id="edit_health_status" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3 appearance-none">
                        <option value="good">Healthy</option>
                        <option value="sick">Sick</option>
                        <option value="injured">Injured</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Next Vaccination Due</label>
                <input type="date" name="vaccination_due" id="edit_vaccination_due" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
            </div>

            <button type="submit" class="w-full py-3 bg-primary text-slate-900 font-bold rounded-xl hover:brightness-95 transition-all mt-6">
                Update Record
            </button>
        </form>
    </div>
</dialog>

<script>
function openEditModal(animal) {
    document.getElementById('edit_id').value = animal.id;
    document.getElementById('edit_tag_number').value = animal.tag_number;
    document.getElementById('edit_species').value = animal.species;
    document.getElementById('edit_breed').value = animal.breed || '';
    document.getElementById('edit_weight').value = animal.weight || '';
    document.getElementById('edit_health_status').value = animal.health_status;
    document.getElementById('edit_vaccination_due').value = animal.vaccination_due || '';
    document.getElementById('editAnimalModal').showModal();
}
</script>

<?php require_once VIEWS_PATH . '/layouts/app_footer.php'; ?>
