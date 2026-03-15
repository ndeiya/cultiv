<?php
/**
 * Expense Breakdown Widget
 * Displays a doughnut chart of expenses by category.
 */
?>
<div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-primary/10 shadow-sm">
    <div class="flex items-center justify-between mb-6">
        <h3 class="font-black text-lg">Expense Breakdown</h3>
        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Past 30 Days</span>
    </div>
    
    <div class="relative aspect-square max-h-[250px] mx-auto">
        <canvas id="expenseChart"></canvas>
    </div>

    <div class="mt-6 space-y-2">
        <?php 
        $colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#64748b', '#06b6d4'];
        foreach ($expense_breakdown as $index => $item): 
            $color = $colors[$index % count($colors)];
        ?>
            <div class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full" style="background-color: <?= $color ?>"></div>
                    <span class="text-slate-500 capitalize"><?= htmlspecialchars($item['category']) ?></span>
                </div>
                <span class="font-bold">GHS <?= number_format($item['total'], 2) ?></span>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($expense_breakdown)): ?>
            <p class="text-center text-slate-400 py-4 italic">No expenses recorded in this period.</p>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('expenseChart').getContext('2d');
    const data = <?= json_encode($expense_breakdown) ?>;
    
    if (data.length === 0) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(item => item.category.charAt(0).toUpperCase() + item.category.slice(1)),
            datasets: [{
                data: data.map(item => item.total),
                backgroundColor: <?= json_encode(array_slice($colors, 0, count($expense_breakdown))) ?>,
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' GHS ' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });
});
</script>
