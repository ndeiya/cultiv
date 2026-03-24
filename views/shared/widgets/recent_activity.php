<?php
/**
 * Shared Recent Activity Widget
 * Expects $activities array.
 */
if (!isset($activities)) $activities = [];

// Icon mapping (local copy of helper)
if (!function_exists('time_ago')) {
    function time_ago(string $datetime): string {
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
}

if (!function_exists('getWidgetActionIcon')) {
    function getWidgetActionIcon(string $action): array {
        return match(true) {
            str_contains($action, 'create')          => ['add_circle', 'text-green-600 bg-green-100 dark:bg-green-900/30'],
            str_contains($action, 'update')          => ['edit', 'text-blue-600 bg-blue-100 dark:bg-blue-900/30'],
            str_contains($action, 'delete')          => ['delete', 'text-red-600 bg-red-100 dark:bg-red-900/30'],
            str_contains($action, 'clock')           => ['schedule', 'text-blue-600 bg-blue-100 dark:bg-blue-900/30'],
            str_contains($action, 'payroll')         => ['payments', 'text-green-600 bg-green-100 dark:bg-green-900/30'],
            str_contains($action, 'report')          => ['description', 'text-blue-600 bg-blue-100 dark:bg-blue-900/30'],
            str_contains($action, 'resolve')         => ['check_circle', 'text-green-600 bg-green-100 dark:bg-green-900/30'],
            str_contains($action, 'completed')       => ['verified', 'text-green-600 bg-green-100 dark:bg-green-900/30'],
            str_contains($action, 'failed')          => ['warning', 'text-red-600 bg-red-100 dark:bg-red-900/30'],
            default                                   => ['info', 'text-slate-600 bg-slate-100 dark:bg-slate-800'],
        };
    }
}

if (!function_exists('formatWidgetAction')) {
    function formatWidgetAction(string $action): string {
        return ucfirst(str_replace('_', ' ', $action));
    }
}
?>

<div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm p-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-sm">history</span>
            Recent Activity
        </h3>
        <a href="/profile/notifications" class="text-[10px] font-bold text-primary hover:underline">View All</a>
    </div>

    <div class="space-y-3">
        <?php if (empty($activities)): ?>
            <div class="text-center py-6 text-xs text-slate-400">
                <span class="material-symbols-outlined text-2xl mb-1 block">inbox</span>
                No recent important activity
            </div>
        <?php else: ?>
            <?php foreach (array_slice($activities, 0, 5) as $activity): 
                $iconData = getWidgetActionIcon($activity['action']);
            ?>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg <?= $iconData[1] ?> flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-base"><?= $iconData[0] ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] leading-tight">
                            <span class="font-bold"><?= htmlspecialchars($activity['user_name'] ?? 'System') ?></span>
                            <span class="text-slate-500"><?= htmlspecialchars(formatWidgetAction($activity['action'])) ?></span>
                            <span class="font-medium"><?= htmlspecialchars($activity['entity'] ?? '') ?></span>
                        </p>
                        <p class="text-[10px] text-slate-400 mt-0.5">
                            <?= date('g:i A', strtotime($activity['created_at'])) ?> • <?= time_ago($activity['created_at']) ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
