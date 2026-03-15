<?php require __DIR__ . '/../../layouts/app_header.php'; ?>

<div class="max-w-6xl mx-auto px-4 py-8 mb-24 md:mb-0">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black tracking-tight mb-2">Farm Expenses</h2>
            <p class="text-slate-500 dark:text-slate-400">Track and manage your farm's operational costs.</p>
        </div>
        <button onclick="document.getElementById('add-expense-modal').classList.remove('hidden')" class="px-6 py-3 bg-primary text-slate-900 font-bold rounded-lg hover:brightness-95 hover:shadow-lg hover:shadow-primary/30 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined">add</span>
            <span>Record Expense</span>
        </button>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6 shadow-sm">
            <p class="font-bold"><?= htmlspecialchars($_SESSION['flash_success']) ?></p>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 shadow-sm">
            <p class="font-bold"><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-primary/10 shadow-sm mb-8">
        <form action="/owner/expenses" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Category</label>
                <select name="category" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-2 text-sm">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($filters['category'] ?? '') === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Related Crop</label>
                <select name="crop_id" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-2 text-sm">
                    <option value="">All Crops</option>
                    <?php foreach ($crops as $crop): ?>
                        <option value="<?= $crop['id'] ?>" <?= (int)($filters['crop_id'] ?? 0) === (int)$crop['id'] ? 'selected' : '' ?>><?= htmlspecialchars($crop['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Date From</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($filters['start_date'] ?? '') ?>" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-2 text-sm">
            </div>
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Date To</label>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($filters['end_date'] ?? '') ?>" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-2 text-sm">
                </div>
                <button type="submit" class="p-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors">
                    <span class="material-symbols-outlined">search</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-primary/5">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Date</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Category</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Related Crop</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Description</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Amount</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary/5">
                    <?php if (empty($expenses)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">No expense records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $expense): ?>
                            <tr class="hover:bg-primary/5 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium"><?= date('M d, Y', strtotime($expense['expense_date'])) ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-bold uppercase bg-slate-100 dark:bg-slate-800 rounded">
                                        <?= htmlspecialchars($expense['category']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    <?= $expense['crop_name'] ? htmlspecialchars($expense['crop_name']) : '<span class="italic text-slate-400">N/A</span>' ?>
                                </td>
                                <td class="px-6 py-4 text-sm max-w-xs truncate" title="<?= htmlspecialchars($expense['description'] ?? '') ?>">
                                    <?= htmlspecialchars($expense['description'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 font-black">
                                    <?= htmlspecialchars($expense['currency']) ?> <?= number_format($expense['amount'], 2) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="/owner/expenses/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this expense record?')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="id" value="<?= $expense['id'] ?>">
                                        <button type="submit" class="text-red-500 hover:text-red-700 transition-colors">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($pagination['last_page'] > 1): ?>
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between border-t border-primary/5">
                <p class="text-xs font-bold text-slate-500">
                    Showing <?= count($expenses) ?> of <?= $pagination['total'] ?> records
                </p>
                <div class="flex gap-2">
                    <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                        <a href="?page=<?= $i ?>&<?= http_build_query($filters) ?>" 
                           class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all <?= (int)$pagination['page'] === $i ? 'bg-primary text-slate-900' : 'bg-white dark:bg-slate-900 border border-primary/10 hover:border-primary/50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Expense Modal -->
<div id="add-expense-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-2xl shadow-2xl border border-primary/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-primary/5 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
            <h3 class="text-lg font-black">Record New Expense</h3>
            <button onclick="document.getElementById('add-expense-modal').classList.add('hidden')" class="p-2 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <form action="/owner/expenses" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Date</label>
                    <input type="date" name="expense_date" required value="<?= date('Y-m-d') ?>" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Category</label>
                    <select name="category" required class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>"><?= ucfirst($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold mb-1">Amount (GHS)</label>
                <div class="relative">
                    <span class="absolute left-3 top-3 text-slate-400 font-bold">GHS</span>
                    <input type="number" name="amount" step="0.01" min="0.01" required placeholder="0.00" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3 pl-12">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold mb-1">Related Crop (Optional)</label>
                <select name="crop_id" class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                    <option value="">No specific crop</option>
                    <?php foreach ($crops as $crop): ?>
                        <option value="<?= $crop['id'] ?>"><?= htmlspecialchars($crop['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold mb-1">Description</label>
                <textarea name="description" rows="3" placeholder="Enter details..." class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3 resize-none"></textarea>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" onclick="document.getElementById('add-expense-modal').classList.add('hidden')" class="flex-1 py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-3 bg-primary text-slate-900 font-bold rounded-lg hover:brightness-95 hover:shadow-lg hover:shadow-primary/30 transition-all">
                    Save Expense
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../../layouts/app_footer.php'; ?>
