<?php
/**
 * Owner Mobile Bottom Navigation
 */
$active_nav = $active_nav ?? 'dashboard';

$items = [
    ['id' => 'dashboard',  'icon' => 'dashboard',   'label' => 'Dashboard',  'url' => '/owner/dashboard'],
    ['id' => 'workforce',  'icon' => 'group',       'label' => 'Workforce',  'url' => '/owner/workers'],
    ['id' => 'operations', 'icon' => 'agriculture', 'label' => 'Operations', 'url' => '/owner/crops'],
    ['id' => 'payroll',    'icon' => 'payments',    'label' => 'Payroll',    'url' => '/owner/payroll'],
    ['id' => 'more',       'icon' => 'more_horiz',  'label' => 'More',      'url' => '/owner/settings'],
];

foreach ($items as $item):
    $isActive = $active_nav === $item['id'];
    $textClass = $isActive ? 'text-primary' : 'text-slate-400';
    $fillStyle = $isActive ? 'style="font-variation-settings: \'FILL\' 1"' : '';
?>
    <a href="<?= $item['url'] ?>" class="flex flex-col items-center gap-0.5 py-1 px-2 <?= $textClass ?>">
        <span class="material-symbols-outlined text-xl" <?= $fillStyle ?>><?= $item['icon'] ?></span>
        <span class="text-[10px] font-bold uppercase"><?= $item['label'] ?></span>
    </a>
<?php endforeach; ?>
