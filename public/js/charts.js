/**
 * Initialize charts for the Owner Dashboard
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Check if Chart.js is loaded and we have data
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js is not loaded.');
        return;
    }

    // Set common Chart.js defaults for dark mode compatibility if needed
    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.color = '#94a3b8'; // text-slate-400
    
    // ----------------------------------------------------------------
    // Hours Trend Line Chart
    // ----------------------------------------------------------------
    if (window.chartDataHours && document.getElementById('hoursTrendChart')) {
        const ctxHours = document.getElementById('hoursTrendChart').getContext('2d');
        
        // Format dates to be more readable (e.g. 'Jan 10')
        const formattedLabels = window.chartDataHours.labels.map(dateStr => {
            const date = new Date(dateStr);
            // Quick check if invalid date
            if (isNaN(date)) return dateStr;
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });

        new Chart(ctxHours, {
            type: 'line',
            data: {
                labels: formattedLabels,
                datasets: [{
                    label: 'Hours Worked',
                    data: window.chartDataHours.data,
                    borderColor: '#13ec13', // primary color
                    backgroundColor: 'rgba(19, 236, 19, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#13ec13',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#13ec13',
                    fill: true,
                    tension: 0.3 // smooth curves
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // hide legend since it's obvious
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' hrs';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.1)' // faint grid lines
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // ----------------------------------------------------------------
    // Reports by Category Doughnut Chart
    // ----------------------------------------------------------------
    if (window.chartDataReports && document.getElementById('reportsCategoryChart')) {
        const ctxReports = document.getElementById('reportsCategoryChart').getContext('2d');
        
        // Ensure there is data, otherwise show empty state pie
        let labels = window.chartDataReports.labels;
        let data = window.chartDataReports.data;
        let backgroundColors = [
            '#13ec13', // Primary
            '#3b82f6', // Blue
            '#f59e0b', // Amber
            '#ef4444', // Red
            '#8b5cf6', // Purple
            '#64748b'  // Slate
        ];

        if (data.length === 0) {
            labels = ['No Data'];
            data = [1];
            backgroundColors = ['#e2e8f0']; // Slate 200 light placeholder
        }

        new Chart(ctxReports, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColors,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 15
                        }
                    }
                }
            }
        });
    }
});
