<?php
/**
 * Shared Widget - Pending Submissions
 * Shows a list of recent items awaiting approval.
 */
$pendingItems = $stats['pendingSummary'] ?? [];
$role = current_user()['role'];
?>

<div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm p-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-sm">rule</span>
            Pending Approvals
        </h3>
        <a href="/<?= $role ?>/approvals" class="text-[10px] font-bold text-primary hover:underline uppercase tracking-wider">View All</a>
    </div>

    <?php if (empty($pendingItems)): ?>
        <div class="text-center py-6 text-xs text-slate-400">
            <span class="material-symbols-outlined text-2xl mb-1 block">task_alt</span>
            No pending submissions
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($pendingItems as $item): ?>
                <div class="flex items-center justify-between gap-3 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50 group">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="size-8 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center shrink-0 border border-primary/5 shadow-sm">
                            <?php
                            $icon = match($item['type']) {
                                'crop' => 'eco',
                                'animal' => 'pets',
                                'inventory' => 'inventory_2',
                                'equipment' => 'construction',
                                'report' => 'description',
                                default => 'help_outline'
                            };
                            ?>
                            <span class="material-symbols-outlined text-primary text-sm"><?= $icon ?></span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold truncate text-slate-900 dark:text-white"><?= e($item['title']) ?></p>
                            <p class="text-[10px] text-slate-500 uppercase"><?= e($item['type']) ?> • <?= date('M j', strtotime($item['created_at'])) ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="quickApprove('<?= $item['type'] ?>', <?= $item['id'] ?>)" class="p-1.5 text-green-600 hover:bg-green-50 rounded-md transition-colors" title="Approve">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
async function quickApprove(type, id) {
    if (!confirm(`Approve this ${type}?`)) return;

    const endpoint = '/<?= $role ?>/approvals/approve';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ type, id })
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
