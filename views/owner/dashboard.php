<?php
/**
 * Owner Dashboard
 */
$active_nav = 'dashboard';
include VIEWS_PATH . '/layouts/app_header.php';
?>

<div class="space-y-6">
    
    <!-- Stat Cards Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-primary">group</span>
                <span class="text-xs font-medium text-slate-500">Workers Present</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold"><?= htmlspecialchars($stats['workersPresent']) ?></span>
                <span class="text-[10px] font-bold text-slate-400">/ <?= htmlspecialchars($stats['totalWorkers']) ?></span>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-primary">timer</span>
                <span class="text-xs font-medium text-slate-500">Total Hours Today</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold"><?= htmlspecialchars($stats['totalHoursToday']) ?>h</span>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-primary">payments</span>
                <span class="text-xs font-medium text-slate-500">Expenses This Month</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold">GHS <?= number_format($stats['totalExpensesMonth'] ?? 0, 2) ?></span>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-sm text-primary">description</span>
                <span class="text-xs font-medium text-slate-500">Open Reports</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold"><?= htmlspecialchars($stats['openReports']) ?></span>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm p-4 md:col-span-2">
            <h3 class="text-sm font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-sm">trending_up</span>
                Hours Worked (Last 7 Days)
            </h3>
            <div class="h-64 w-full">
                <canvas id="hoursTrendChart"></canvas>
            </div>
        </div>
        
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm p-4">
            <h3 class="text-sm font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-sm">pie_chart</span>
                Reports by Category
            </h3>
            <div class="h-64 w-full flex items-center justify-center">
                <canvas id="reportsCategoryChart"></canvas>
            </div>
        </div>

        <!-- NEW: Expense Breakdown -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm p-4">
            <?php 
                $expense_breakdown = $stats['expenseBreakdown'] ?? [];
                include VIEWS_PATH . '/owner/dashboard/widgets/expenses.php'; 
            ?>
        </div>
    </div>

    <!-- Quick Management -->
    <div class="grid md:grid-cols-2 gap-4">
        <!-- Recent Activity -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm p-4">
            <h3 class="text-sm font-bold mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-sm">history</span>
                Recent Activity
            </h3>
            <div class="text-center py-8 text-sm text-slate-400">
                <span class="material-symbols-outlined text-3xl mb-2 block">inbox</span>
                No recent activity
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-primary/10 shadow-sm p-4">
            <h3 class="text-sm font-bold mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-sm">bolt</span>
                Quick Actions
            </h3>
            <div class="space-y-2">
                <a href="/owner/workers" class="flex items-center gap-3 p-3 rounded-lg hover:bg-primary/5 transition-colors">
                    <span class="material-symbols-outlined text-primary">person_add</span>
                    <span class="text-sm font-medium">Manage Workers</span>
                </a>
                <a href="/owner/attendance" class="flex items-center gap-3 p-3 rounded-lg hover:bg-primary/5 transition-colors">
                    <span class="material-symbols-outlined text-primary">fact_check</span>
                    <span class="text-sm font-medium">View Attendance</span>
                </a>
                <a href="/owner/reports" class="flex items-center gap-3 p-3 rounded-lg hover:bg-primary/5 transition-colors">
                    <span class="material-symbols-outlined text-primary">assignment</span>
                    <span class="text-sm font-medium">Review Reports</span>
                </a>
                <a href="/owner/payroll" class="flex items-center gap-3 p-3 rounded-lg hover:bg-primary/5 transition-colors">
                    <span class="material-symbols-outlined text-primary">calculate</span>
                    <span class="text-sm font-medium">Payroll</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    window.chartDataHours = <?= json_encode($stats['chartDataHours']) ?>;
    window.chartDataReports = <?= json_encode($stats['chartDataReports']) ?>;
</script>
<script src="/js/charts.js"></script>

<?php include VIEWS_PATH . '/layouts/app_footer.php'; ?>
