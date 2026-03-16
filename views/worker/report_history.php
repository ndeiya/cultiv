<?php require __DIR__ . '/../layouts/app_header.php'; ?>

<!-- Main Content -->
<div class="max-w-6xl mx-auto px-4 py-8 mb-24 md:mb-0">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-3xl">history</span>
            <h2 class="text-3xl font-black tracking-tight">My Reports</h2>
        </div>
        <a href="/worker/reports/create" class="flex items-center justify-center gap-2 px-6 py-3 bg-primary text-slate-900 font-bold rounded-xl hover:brightness-95 hover:shadow-lg hover:shadow-primary/30 transition-all transition-transform active:scale-95">
            <span class="material-symbols-outlined">add_circle</span>
            Submit New Report
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if ($msg = get_flash('success')): ?>
        <div class="bg-primary/10 border-l-4 border-primary text-slate-900 dark:text-primary p-4 rounded-lg mb-6 shadow-sm">
            <p class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined">check_circle</span>
                <?= e($msg) ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="flex flex-col gap-1 mb-6">
        <h3 class="text-2xl font-bold tracking-tight">Activity Log</h3>
        <p class="text-slate-500 text-sm">Review, track, and manage all your submitted daily farm reports.</p>
    </div>

    <!-- Filters placeholder -->
    <form method="GET" action="/worker/reports" class="flex flex-col md:flex-row gap-4 items-center mb-6">
        <div class="flex gap-2 w-full overflow-x-auto pb-2 md:pb-0 no-scrollbar">
            <?php 
            $cat = $_GET['category'] ?? ''; 
            $cats = ['' => 'All Reports', 'crop' => 'Crops', 'animal' => 'Livestock', 'equipment' => 'Equipment', 'general' => 'General'];
            foreach ($cats as $val => $label): 
                $active = ($cat === $val);
            ?>
                <button type="submit" name="category" value="<?= $val ?>" class="px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors <?= $active ? 'bg-primary text-slate-900 border-transparent shadow-sm' : 'bg-white dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:border-primary/50 hover:text-primary' ?>">
                    <?= $label ?>
                </button>
            <?php endforeach; ?>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-primary/10 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-primary/10">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Report Date</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Type / Location</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                    <?php if (empty($reports)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                No reports found. Get started by submitting a new report!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reports as $report): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold"><?= date('M j, Y', strtotime($report['created_at'])) ?></span>
                                    <span class="text-xs text-slate-500"><?= date('h:i A', strtotime($report['created_at'])) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <?php 
                                        $iconMap = ['crop' => 'eco', 'animal' => 'pets', 'equipment' => 'construction', 'general' => 'assignment'];
                                        $icon = $iconMap[$report['category']] ?? 'description';
                                        ?>
                                        <span class="material-symbols-outlined text-primary text-lg"><?= $icon ?></span>
                                        <span class="text-sm font-bold capitalize"><?= htmlspecialchars($report['category']) ?></span>
                                    </div>
                                    <div class="flex items-center gap-1 text-slate-500">
                                        <span class="material-symbols-outlined text-[14px]">location_on</span>
                                        <span class="text-xs">ID: <?= htmlspecialchars($report['related_id'] ?: 'N/A') ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm truncate max-w-xs" title="<?= htmlspecialchars($report['description']) ?>">
                                    <?= htmlspecialchars($report['description']) ?>
                                </div>
                                <?php if (!empty($report['photos'])): ?>
                                    <div class="flex gap-1 mt-2">
                                        <?php foreach (array_slice($report['photos'], 0, 3) as $photo): ?>
                                            <div class="w-8 h-8 rounded bg-slate-200 overflow-hidden">
                                                <img src="<?= e($photo) ?>" class="w-full h-full object-cover" loading="lazy">
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if(count($report['photos']) > 3): ?>
                                            <div class="w-8 h-8 rounded bg-slate-100 flex items-center justify-center text-xs text-slate-500 text-slate-900 border border-slate-300">
                                                +<?= count($report['photos']) - 3 ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($report['status'] === 'pending'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-amber-500/10 text-amber-600 flex items-center gap-1 w-fit">
                                        <span class="material-symbols-outlined text-[10px]">schedule</span> Pending Approval
                                    </span>
                                <?php elseif ($report['status'] === 'open'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-orange-500/10 text-orange-500">Open</span>
                                <?php elseif ($report['status'] === 'resolved'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Resolved</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400"><?= htmlspecialchars($report['status']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php require __DIR__ . '/../shared/pagination.php'; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/app_footer.php'; ?>
