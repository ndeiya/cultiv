<?php
/**
 * Task Management View
 * Used by supervisors and owners to assign and track tasks.
 */
include VIEWS_PATH . '/layouts/app_header.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold">Task Management</h2>
            <p class="text-sm text-slate-500">Assign and track farm duties</p>
        </div>
        <button onclick="openModal('addTaskModal')" class="bg-primary text-slate-900 px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 shadow-sm hover:brightness-95">
            <span class="material-symbols-outlined text-sm">add</span>
            Assign New Task
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-primary/10 shadow-sm">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Worker</label>
                <select name="assigned_to" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary">
                    <option value="">All Workers</option>
                    <?php foreach ($workers as $worker): ?>
                        <option value="<?= $worker['id'] ?>" <?= ($filters['assigned_to'] ?? '') == $worker['id'] ? 'selected' : '' ?>>
                            <?= e($worker['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Status</label>
                <select name="status" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= ($filters['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="in_progress" <?= ($filters['status'] ?? '') == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="completed" <?= ($filters['status'] ?? '') == 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= ($filters['status'] ?? '') == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Due Date</label>
                <input type="date" name="due_date" value="<?= e($filters['due_date'] ?? '') ?>" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-slate-100 dark:bg-slate-800 py-2 rounded-lg text-sm font-bold hover:bg-primary/10 hover:text-primary transition-colors">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Task List -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-primary/10">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Task</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Assigned To</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    <?php if (empty($tasks)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 italic">
                                No tasks found matching your filters.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($tasks as $task): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-sm"><?= e($task['title']) ?></div>
                                <div class="text-[10px] text-slate-500 truncate max-w-xs"><?= e($task['description']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="size-6 rounded-full bg-primary/20 flex items-center justify-center text-primary text-[10px] font-bold">
                                        <?= strtoupper(substr($task['assigned_to_name'], 0, 1)) ?>
                                    </div>
                                    <span class="text-sm"><?= e($task['assigned_to_name']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $priority_class = [
                                    'high' => 'bg-red-500/10 text-red-500',
                                    'medium' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                    'low' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                ][$task['priority']] ?? 'bg-slate-100 text-slate-600';
                                ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?= $priority_class ?>">
                                    <?= e($task['priority']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                <?= $task['due_date'] ? date('M j, Y', strtotime($task['due_date'])) : '--' ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $status_class = [
                                    'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                    'pending' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'in_progress' => 'bg-orange-500/10 text-orange-500',
                                    'cancelled' => 'bg-slate-100 text-slate-600 dark:bg-slate-800'
                                ][$task['status']] ?? 'bg-slate-100 text-slate-600';
                                ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?= $status_class ?>">
                                    <?= str_replace('_', ' ', e($task['status'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button onclick="editTask(<?= $task['id'] ?>)" class="p-1 hover:text-primary transition-colors">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </button>
                                    <button onclick="deleteTask(<?= $task['id'] ?>)" class="p-1 hover:text-red-500 transition-colors">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div id="addTaskModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="min-h-screen px-4 flex items-center justify-center">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('addTaskModal')"></div>
        <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-md w-full p-6 border border-primary/10">
            <h3 class="text-lg font-bold mb-4">Assign New Task</h3>
            <form id="addTaskForm" onsubmit="event.preventDefault(); submitTask();" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">Title</label>
                    <input type="text" name="title" required class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg p-3 text-sm focus:ring-2 focus:ring-primary" placeholder="E.g. Irrigation Check - Sector B">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">Description</label>
                    <textarea name="description" rows="3" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg p-3 text-sm focus:ring-2 focus:ring-primary" placeholder="Task details..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">Assigned To</label>
                        <select name="assigned_to" required class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary">
                            <option value="">Select Worker</option>
                            <?php foreach ($workers as $worker): ?>
                                <option value="<?= $worker['id'] ?>"><?= e($worker['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">Priority</label>
                        <select name="priority" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">Due Date</label>
                        <input type="date" name="due_date" value="<?= date('Y-m-d') ?>" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">Related Type</label>
                        <select name="related_type" id="related_type" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg text-sm focus:ring-2 focus:ring-primary">
                            <option value="general">General</option>
                            <option value="crop">Crop</option>
                            <option value="animal">Animal</option>
                            <option value="equipment">Equipment</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('addTaskModal')" class="flex-1 py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold rounded-lg hover:brightness-95 transition-all">Cancel</button>
                    <button type="submit" class="flex-1 py-3 bg-primary text-slate-900 font-bold rounded-lg hover:brightness-95 shadow-lg shadow-primary/20 transition-all">Assign Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

async function submitTask() {
    const form = document.getElementById('addTaskForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    // Add CSRF header
    const csrfToken = data.csrf_token;
    delete data.csrf_token;

    try {
        const response = await fetch('/api/tasks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.success) {
            window.location.reload();
        } else {
            alert(result.message || 'Error occurred');
        }
    } catch (err) {
        console.error(err);
        alert('Network error');
    }
}

async function deleteTask(id) {
    if (!confirm('Are you sure you want to delete this task?')) return;
    
    try {
        const response = await fetch('/api/tasks', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
            },
            body: JSON.stringify({ id: id })
        });

        const result = await response.json();
        if (result.success) {
            window.location.reload();
        } else {
            alert(result.message || 'Error occurred');
        }
    } catch (err) {
        console.error(err);
        alert('Network error');
    }
}
</script>

<?php include VIEWS_PATH . '/layouts/app_footer.php'; ?>
