<?php
/**
 * Sidebar Navigation
 * Desktop sidebar links — adapts based on user role.
 */

$role = $user['role'] ?? 'worker';
$active_nav = $active_nav ?? 'dashboard';

// Define navigation items per role
$navItems = match($role) {
    'owner' => [
        ['id' => 'dashboard',  'icon' => 'dashboard',    'label' => 'Dashboard',  'url' => '/owner/dashboard'],
        ['id' => 'workforce',  'icon' => 'group',        'label' => 'Workforce',  'url' => '/owner/workers'],
        ['id' => 'roster',     'icon' => 'calendar_month', 'label' => 'Shift Roster', 'url' => '/owner/roster'],
        ['id' => 'attendance', 'icon' => 'schedule',     'label' => 'Attendance', 'url' => '/owner/attendance'],
        ['id' => 'reports',    'icon' => 'description',  'label' => 'Reports',    'url' => '/owner/reports'],
        ['id' => 'crops',      'icon' => 'grass',        'label' => 'Crops',      'url' => '/owner/crops'],
        ['id' => 'animals',    'icon' => 'pets',         'label' => 'Animals',    'url' => '/owner/animals'],
        ['id' => 'equipment',  'icon' => 'build',        'label' => 'Equipment',  'url' => '/owner/equipment'],
        ['id' => 'inventory',  'icon' => 'inventory_2',  'label' => 'Inventory',  'url' => '/owner/inventory'],
        ['id' => 'payroll',    'icon' => 'payments',     'label' => 'Payroll',    'url' => '/owner/payroll'],
        ['id' => 'settings',   'icon' => 'settings',     'label' => 'Settings',   'url' => '/owner/settings'],
        ['id' => 'profile',    'icon' => 'person',       'label' => 'Profile',    'url' => '/owner/profile'],
    ],
    'supervisor' => [
        ['id' => 'dashboard',  'icon' => 'dashboard',    'label' => 'Dashboard',  'url' => '/supervisor/dashboard'],
        ['id' => 'roster',     'icon' => 'calendar_month', 'label' => 'Shift Roster', 'url' => '/supervisor/roster'],
        ['id' => 'attendance', 'icon' => 'schedule',     'label' => 'Attendance', 'url' => '/supervisor/attendance'],
        ['id' => 'reports',    'icon' => 'description',  'label' => 'Reports',    'url' => '/supervisor/reports'],
        ['id' => 'crops',      'icon' => 'grass',        'label' => 'Crops',      'url' => '/supervisor/crops'],
        ['id' => 'animals',    'icon' => 'pets',         'label' => 'Animals',    'url' => '/supervisor/animals'],
        ['id' => 'equipment',  'icon' => 'build',        'label' => 'Equipment',  'url' => '/supervisor/equipment'],
        ['id' => 'inventory',  'icon' => 'inventory_2',  'label' => 'Inventory',  'url' => '/supervisor/inventory'],
        ['id' => 'profile',    'icon' => 'person',       'label' => 'Profile',    'url' => '/supervisor/profile'],
    ],
    'accountant' => [
        ['id' => 'dashboard',  'icon' => 'dashboard',    'label' => 'Dashboard',  'url' => '/accountant/dashboard'],
        ['id' => 'payroll',    'icon' => 'payments',     'label' => 'Payroll',    'url' => '/accountant/payroll'],
        ['id' => 'payments',   'icon' => 'account_balance_wallet', 'label' => 'Payments', 'url' => '/accountant/payments'],
        ['id' => 'reports',    'icon' => 'bar_chart',    'label' => 'Reports',    'url' => '/accountant/reports'],
        ['id' => 'profile',    'icon' => 'person',       'label' => 'Profile',    'url' => '/accountant/profile'],
    ],
    default => [ // worker
        ['id' => 'dashboard', 'icon' => 'dashboard',   'label' => 'Dashboard', 'url' => '/worker/dashboard'],
        ['id' => 'reports',   'icon' => 'description', 'label' => 'Reports',   'url' => '/worker/reports'],
        ['id' => 'crops',     'icon' => 'grass',       'label' => 'Crops',      'url' => '/worker/crops'],
        ['id' => 'animals',   'icon' => 'pets',        'label' => 'Animals',    'url' => '/worker/animals'],
        ['id' => 'equipment', 'icon' => 'build',       'label' => 'Equipment',  'url' => '/worker/equipment'],
        ['id' => 'inventory', 'icon' => 'inventory_2', 'label' => 'Inventory',  'url' => '/worker/inventory'],
        ['id' => 'history',   'icon' => 'history',     'label' => 'History',   'url' => '/worker/history'],
        ['id' => 'payslips',  'icon' => 'receipt',     'label' => 'Payslips',  'url' => '/worker/payslips'],
        ['id' => 'profile',   'icon' => 'person',      'label' => 'Profile',   'url' => '/worker/profile'],
    ],
};

foreach ($navItems as $item):
    $isActive = $active_nav === $item['id'];
    $activeClasses = $isActive
        ? 'bg-primary/10 text-primary'
        : 'text-slate-600 dark:text-slate-400 hover:bg-primary/5 hover:text-primary transition-colors';
    $fillStyle = $isActive ? 'style="font-variation-settings: \'FILL\' 1"' : '';
?>
    <a href="<?= $item['url'] ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $activeClasses ?>">
        <span class="material-symbols-outlined text-xl" <?= $fillStyle ?>><?= $item['icon'] ?></span>
        <span class="text-sm <?= $isActive ? 'font-semibold' : 'font-medium' ?>"><?= $item['label'] ?></span>
    </a>
<?php endforeach; ?>
