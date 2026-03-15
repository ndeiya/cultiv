<?php
/**
 * Shared Notifications / Activity Feed View
 * Shows recent audit log entries for the farm. Works for all roles.
 */
$active_nav = 'profile';
include VIEWS_PATH . '/layouts/app_header.php';
$role = $user['role'] ?? 'worker';

// Icon mapping for audit actions
function getActionIcon(string $action): array {
    return match(true) {
        str_contains($action, 'create')          => ['add_circle', 'text-green-600 bg-green-100 dark:bg-green-900/30'],
        str_contains($action, 'update')          => ['edit', 'text-blue-600 bg-blue-100 dark:bg-blue-900/30'],
        str_contains($action, 'delete')          => ['delete', 'text-red-600 bg-red-100 dark:bg-red-900/30'],
        str_contains($action, 'login')           => ['login', 'text-primary bg-primary/10'],
        str_contains($action, 'logout')          => ['logout', 'text-amber-600 bg-amber-100 dark:bg-amber-900/30'],
        str_contains($action, 'clock')           => ['schedule', 'text-blue-600 bg-blue-100 dark:bg-blue-900/30'],
        str_contains($action, 'password')        => ['lock', 'text-amber-600 bg-amber-100 dark:bg-amber-900/30'],
        str_contains($action, 'profile')         => ['person', 'text-blue-600 bg-blue-100 dark:bg-blue-900/30'],
        str_contains($action, 'payroll')         => ['payments', 'text-green-600 bg-green-100 dark:bg-green-900/30'],
        str_contains($action, 'report')          => ['description', 'text-blue-600 bg-blue-100 dark:bg-blue-900/30'],
        str_contains($action, 'resolve')         => ['check_circle', 'text-green-600 bg-green-100 dark:bg-green-900/30'],
        str_contains($action, 'setting')         => ['settings', 'text-slate-600 bg-slate-100 dark:bg-slate-800'],
        default                                   => ['info', 'text-slate-600 bg-slate-100 dark:bg-slate-800'],
    };
}

function formatAction(string $action): string {
    return ucfirst(str_replace('_', ' ', $action));
}

function timeAgo(string $datetime): string {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . 'y ago';
    if ($diff->m > 0) return $diff->m . 'mo ago';
    if ($diff->d > 0) return $diff->d . 'd ago';
    if ($diff->h > 0) return $diff->h . 'h ago';
    if ($diff->i > 0) return $diff->i . 'm ago';
    return 'Just now';
}
?>

<div class="max-w-3xl mx-auto space-y-6">

    <!-- Back Link -->
    <a href="/<?= htmlspecialchars($role) ?>/profile" 
       class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-sm mr-1">arrow_back</span>
        Back to Profile
    </a>

    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">notifications</span>
            Activity Feed
        </h2>
        <span class="text-xs text-slate-500 font-medium"><?= count($logs ?? []) ?> recent activities</span>
    </div>

    <!-- Activity Timeline -->
    <?php if (!empty($logs)): ?>
        <section class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm overflow-hidden">
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                <?php 
                $currentDate = '';
                foreach ($logs as $log): 
                    $logDate = date('M j, Y', strtotime($log['created_at']));
                    $iconData = getActionIcon($log['action']);
                ?>
                    <?php if ($logDate !== $currentDate): ?>
                        <?php $currentDate = $logDate; ?>
                        <div class="px-4 py-2 bg-slate-50 dark:bg-slate-900/50">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider"><?= $logDate ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <!-- Action Icon -->
                        <div class="w-9 h-9 rounded-lg <?= $iconData[1] ?> flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-lg"><?= $iconData[0] ?></span>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm">
                                <span class="font-semibold"><?= htmlspecialchars($log['user_name'] ?? 'System') ?></span>
                                <span class="text-slate-500"><?= htmlspecialchars(formatAction($log['action'])) ?></span>
                                <?php if (!empty($log['entity'])): ?>
                                    <span class="font-medium"><?= htmlspecialchars($log['entity']) ?></span>
                                    <?php if (!empty($log['entity_id'])): ?>
                                        <span class="text-slate-400">#<?= (int)$log['entity_id'] ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] text-slate-400 font-medium"><?= date('g:i A', strtotime($log['created_at'])) ?></span>
                                <span class="text-[10px] text-slate-300 dark:text-slate-600">•</span>
                                <span class="text-[10px] text-slate-400 font-medium"><?= timeAgo($log['created_at']) ?></span>
                                <?php if (!empty($log['user_role'])): ?>
                                    <span class="text-[10px] text-slate-300 dark:text-slate-600">•</span>
                                    <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-500">
                                        <?= htmlspecialchars($log['user_role']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php else: ?>
        <section class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm p-12 text-center">
            <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600 mb-3 block">notifications_off</span>
            <p class="text-sm font-medium text-slate-500">No activity yet.</p>
            <p class="text-xs text-slate-400 mt-1">Actions across the farm will appear here.</p>
        </section>
    <?php endif; ?>
</div>

<?php include VIEWS_PATH . '/layouts/app_footer.php'; ?>
