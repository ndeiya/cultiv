<?php
/**
 * Accountant Mobile Bottom Navigation
 */
$active_nav = $active_nav ?? 'dashboard';

$items = [
    ['id' => 'dashboard', 'icon' => 'dashboard',              'label' => 'Payroll',  'url' => '/accountant/dashboard'],
    ['id' => 'payments',  'icon' => 'account_balance_wallet', 'label' => 'Payments', 'url' => '/accountant/payments'],
    ['id' => 'reports',   'icon' => 'bar_chart',              'label' => 'Reports',  'url' => '/accountant/reports'],
    ['id' => 'profile',   'icon' => 'person',                 'label' => 'Profile',  'url' => '/accountant/profile'],
];

foreach ($items as $item):
    $isActive = $active_nav === $item['id'];
    $textClass = $isActive ? 'text-primary' : 'text-slate-400';
    $fillStyle = $isActive ? 'style="font-variation-settings: \'FILL\' 1"' : '';
?>
    <a href="<?= $item['url'] ?>" class="flex flex-col items-center gap-0.5 py-1 px-3 <?= $textClass ?>">
        <span class="material-symbols-outlined text-xl" <?= $fillStyle ?>><?= $item['icon'] ?></span>
        <span class="text-[10px] font-bold uppercase"><?= $item['label'] ?></span>
    </a>
<?php endforeach; ?>
