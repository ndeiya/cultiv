<?php
/**
 * Owner - Workers List View
 */
$title = $title ?? 'Workforce Management';
require_once __DIR__ . '/../layouts/app_header.php';
?>

<div class="p-8">
    <div class="flex justify-between items-end mb-8">
        <div>
            <h2 class="text-3xl font-black tracking-tight mb-2">Workforce Management</h2>
            <p class="text-slate-500 dark:text-slate-400">Manage all staff members across the farm.</p>
        </div>
        <a href="/owner/workers/create" class="px-4 py-2 bg-primary text-slate-900 font-bold rounded-lg hover:brightness-95 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">person_add</span>
            Add Worker
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">check_circle</span>
            <?php echo htmlspecialchars($_SESSION['success']); ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-6 p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">error</span>
            <?php echo htmlspecialchars($_SESSION['error']); ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-slate-800/50 rounded-xl border border-primary/10 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-primary/10">
            <h3 class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">groups</span>
                Staff Directory
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Contact</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Joined</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php if (empty($workers)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                No workers found. Click "Add Worker" to get started.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($workers as $worker): 
                            $isCurrentUser = ($worker['id'] === current_user()['id']);
                        ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold">
                                            <?= strtoupper(substr($worker['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-slate-100 whitespace-nowrap">
                                                <?= htmlspecialchars($worker['name']) ?>
                                                <?php if ($isCurrentUser): ?>
                                                    <span class="ml-2 text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-500 px-2 py-0.5 rounded-full uppercase font-bold">You</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-600 dark:text-slate-400">
                                        <?php if ($worker['email']): ?>
                                            <div><?= htmlspecialchars($worker['email']) ?></div>
                                        <?php endif; ?>
                                        <?php if ($worker['phone']): ?>
                                            <div class="text-xs"><?= htmlspecialchars($worker['phone']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-medium capitalize text-slate-900 dark:text-slate-100">
                                        <?= htmlspecialchars($worker['role']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($worker['status'] === 'active'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Active</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                    <?= date('M j, Y', strtotime($worker['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="/owner/workers/edit?id=<?= $worker['id'] ?>" class="p-2 text-slate-400 hover:text-primary transition-colors tooltip" title="Edit">
                                            <span class="material-symbols-outlined text-sm">edit</span>
                                        </a>
                                        
                                        <?php if (!$isCurrentUser): ?>
                                            <form action="/owner/workers/toggle-status" method="POST" class="inline">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <input type="hidden" name="id" value="<?= $worker['id'] ?>">
                                                <button type="submit" class="p-2 text-slate-400 hover:text-orange-500 transition-colors tooltip" title="<?= $worker['status'] === 'active' ? 'Deactivate' : 'Activate' ?>">
                                                    <span class="material-symbols-outlined text-sm">
                                                        <?= $worker['status'] === 'active' ? 'block' : 'check_circle' ?>
                                                    </span>
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

<?php require_once __DIR__ . '/../layouts/app_footer.php'; ?>
