<?php
/**
 * Dashboard Service
 * Handles data aggregation and statistics for the various role dashboards.
 */

class DashboardService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get statistics for the Owner Dashboard
     * 
     * @param int $farmId
     * @return array
     */
    public function getOwnerStats(int $farmId): array
    {
        // 1. Workers Present Today
        $stmt = $this->db->prepare("SELECT COUNT(DISTINCT user_id) as count FROM attendance WHERE farm_id = ? AND DATE(clock_in) = CURDATE()");
        $stmt->execute([$farmId]);
        $workersPresent = $stmt->fetchColumn() ?: 0;

        // Total workers in farm
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE farm_id = ? AND role = 'worker' AND status = 'active'");
        $stmt->execute([$farmId]);
        $totalWorkers = $stmt->fetchColumn() ?: 0;

        // 2. Total Hours Today
        $stmt = $this->db->prepare("SELECT SUM(total_minutes) FROM attendance WHERE farm_id = ? AND DATE(clock_in) = CURDATE()");
        $stmt->execute([$farmId]);
        $totalMinutesToday = $stmt->fetchColumn() ?: 0;
        $totalHoursToday = floor($totalMinutesToday / 60);

        // 3. Payroll Cost (Total Paid overall or this month) - Let's do total paid for this year or just total
        $stmt = $this->db->prepare("
            SELECT SUM(pr.net_pay) 
            FROM payroll_records pr 
            JOIN payroll_periods pp ON pr.payroll_period_id = pp.id 
            WHERE pp.farm_id = ? AND pr.status = 'paid'
        ");
        $stmt->execute([$farmId]);
        $payrollCost = $stmt->fetchColumn() ?: 0;

        // 4. Open Reports
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM reports WHERE farm_id = ? AND status = 'open'");
        $stmt->execute([$farmId]);
        $openReports = $stmt->fetchColumn() ?: 0;

        // 5. Total Tasks Pending
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tasks WHERE farm_id = ? AND status NOT IN ('completed', 'cancelled')");
        $stmt->execute([$farmId]);
        $pendingTasks = $stmt->fetchColumn() ?: 0;

        // --- Chart Data ---

        // A. Hours Trend (last 7 days)
        $stmt = $this->db->prepare("
            SELECT DATE(clock_in) as date, SUM(total_minutes) as total_minutes 
            FROM attendance 
            WHERE farm_id = ? AND clock_in >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
            GROUP BY DATE(clock_in) 
            ORDER BY date ASC
        ");
        $stmt->execute([$farmId]);
        $hoursTrendRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fill missing days
        $hoursTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $hoursTrend[$date] = 0;
        }
        foreach ($hoursTrendRaw as $row) {
            if (isset($hoursTrend[$row['date']])) {
                $hoursTrend[$row['date']] = round($row['total_minutes'] / 60, 1);
            }
        }

        $chartDataHours = [
            'labels' => array_keys($hoursTrend),
            'data' => array_values($hoursTrend)
        ];

        // B. Report Categories
        $stmt = $this->db->prepare("
            SELECT category, COUNT(*) as count 
            FROM reports 
            WHERE farm_id = ? 
            GROUP BY category
        ");
        $stmt->execute([$farmId]);
        $reportCategoriesRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $chartDataReports = [
            'labels' => [],
            'data' => []
        ];
        foreach ($reportCategoriesRaw as $row) {
            $cat = $row['category'] ?: 'Uncategorized';
            $chartDataReports['labels'][] = $cat;
            $chartDataReports['data'][] = (int)$row['count'];
        }

        // C. Expense Breakdown
        $stmt = $this->db->prepare("
            SELECT category, SUM(amount) as total 
            FROM farm_expenses 
            WHERE farm_id = ? 
            AND expense_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
            GROUP BY category
        ");
        $stmt->execute([$farmId]);
        $expenseBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // D. Total Expenses This Month
        $stmt = $this->db->prepare("
            SELECT SUM(amount) 
            FROM farm_expenses 
            WHERE farm_id = ? 
            AND MONTH(expense_date) = MONTH(CURDATE()) 
            AND YEAR(expense_date) = YEAR(CURDATE())
        ");
        $stmt->execute([$farmId]);
        $totalExpensesMonth = $stmt->fetchColumn() ?: 0;

        return [
            'workersPresent' => $workersPresent,
            'totalWorkers' => $totalWorkers,
            'totalHoursToday' => $totalHoursToday,
            'totalMinutesToday' => $totalMinutesToday,
            'payrollCost' => $payrollCost,
            'openReports' => $openReports,
            'chartDataHours' => $chartDataHours,
            'chartDataReports' => $chartDataReports,
            'expenseBreakdown' => $expenseBreakdown,
            'totalExpensesMonth' => $totalExpensesMonth,
            'pendingTasks' => $pendingTasks
        ];
    }

    /**
     * Get statistics for the Supervisor Dashboard
     * 
     * @param int $farmId
     * @return array
     */
    public function getSupervisorStats(int $farmId): array
    {
        // 1. Workers Present Today
        $stmt = $this->db->prepare("SELECT COUNT(DISTINCT user_id) as count FROM attendance WHERE farm_id = ? AND DATE(clock_in) = CURDATE()");
        $stmt->execute([$farmId]);
        $workersPresent = $stmt->fetchColumn() ?: 0;

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE farm_id = ? AND role = 'worker' AND status = 'active'");
        $stmt->execute([$farmId]);
        $totalWorkers = $stmt->fetchColumn() ?: 0;

        // 2. Open Reports
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM reports WHERE farm_id = ? AND status = 'open'");
        $stmt->execute([$farmId]);
        $openReports = $stmt->fetchColumn() ?: 0;

        // 3. Crop Alerts
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM crops WHERE farm_id = ? AND health_status IN ('warning', 'bad')");
        $stmt->execute([$farmId]);
        $cropAlerts = $stmt->fetchColumn() ?: 0;

        // 4. Equipment Alerts
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM equipment WHERE farm_id = ? AND status IN ('maintenance', 'broken')");
        $stmt->execute([$farmId]);
        $equipmentIssues = $stmt->fetchColumn() ?: 0;

        // 5. Pending Tasks count
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tasks WHERE farm_id = ? AND status NOT IN ('completed', 'cancelled')");
        $stmt->execute([$farmId]);
        $pendingTasks = $stmt->fetchColumn() ?: 0;

        // 5. Team Attendance (Today)
        $stmt = $this->db->prepare("
            SELECT u.name, a.status, a.clock_in, a.total_minutes 
            FROM attendance a 
            JOIN users u ON a.user_id = u.id 
            WHERE a.farm_id = ? AND DATE(a.clock_in) = CURDATE()
            ORDER BY a.clock_in DESC LIMIT 5
        ");
        $stmt->execute([$farmId]);
        $teamAttendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 7. Recent Tasks (Activity Feed)
        $stmt = $this->db->prepare("
            SELECT t.*, u.name as assigned_to_name 
            FROM tasks t 
            JOIN users u ON t.assigned_to = u.id 
            WHERE t.farm_id = ? 
            ORDER BY t.created_at DESC LIMIT 5
        ");
        $stmt->execute([$farmId]);
        $recentTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'workersPresent' => $workersPresent,
            'totalWorkers' => $totalWorkers,
            'openReports' => $openReports,
            'cropAlerts' => $cropAlerts,
            'equipmentIssues' => $equipmentIssues,
            'teamAttendance' => $teamAttendance,
            'pendingTasks' => $pendingTasks,
            'recentTasks' => $recentTasks
        ];
    }

    /**
     * Get statistics for the Worker Dashboard
     * 
     * @param int $userId
     * @return array
     */
    public function getWorkerStats(int $userId): array
    {
        // Get recent payslip
        $stmt = $this->db->prepare("
            SELECT pr.*, pp.period_start, pp.period_end 
            FROM payroll_records pr 
            JOIN payroll_periods pp ON pr.payroll_period_id = pp.id 
            WHERE pr.user_id = ? 
            ORDER BY pp.period_end DESC 
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $recentPayslip = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get active tasks
        $stmt = $this->db->prepare("
            SELECT t.*, creator.name as created_by_name 
            FROM tasks t
            LEFT JOIN users creator ON t.created_by = creator.id
            WHERE t.assigned_to = ? AND t.status NOT IN ('completed', 'cancelled')
            ORDER BY t.priority DESC, t.due_date ASC
        ");
        $stmt->execute([$userId]);
        $activeTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'recentPayslip' => $recentPayslip,
            'activeTasks' => $activeTasks
        ];
    }

    /**
     * Get statistics for the Accountant Dashboard
     * 
     * @param int $farmId
     * @return array
     */
    public function getAccountantStats(int $farmId): array
    {
        // Total Payroll Cost
        $stmt = $this->db->prepare("
            SELECT SUM(pr.net_pay) 
            FROM payroll_records pr 
            JOIN payroll_periods pp ON pr.payroll_period_id = pp.id 
            WHERE pp.farm_id = ?
        ");
        $stmt->execute([$farmId]);
        $totalPayroll = $stmt->fetchColumn() ?: 0;

        // Pending Payments
        $stmt = $this->db->prepare("
            SELECT SUM(pr.net_pay) 
            FROM payroll_records pr 
            JOIN payroll_periods pp ON pr.payroll_period_id = pp.id 
            WHERE pp.farm_id = ? AND pr.status IN ('pending', 'approved')
        ");
        $stmt->execute([$farmId]);
        $pendingPayments = $stmt->fetchColumn() ?: 0;

        // Recent Transactions
        $stmt = $this->db->prepare("
            SELECT p.id, p.payment_method, p.paid_at, pr.net_pay as amount, u.name as worker_name 
            FROM payments p 
            JOIN payroll_records pr ON p.payroll_record_id = pr.id 
            JOIN users u ON pr.user_id = u.id 
            JOIN payroll_periods pp ON pr.payroll_period_id = pp.id 
            WHERE pp.farm_id = ? 
            ORDER BY p.paid_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$farmId]);
        $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'totalPayroll' => $totalPayroll,
            'pendingPayments' => $pendingPayments,
            'recentTransactions' => $recentTransactions
        ];
    }

    /**
     * Get a summary of pending items across all modules for the dashboard.
     */
    public function getPendingSummary(int $farmId, int $limit = 5): array
    {
        $pending = [];

        // Crops
        $stmt = $this->db->prepare("SELECT id, name as title, 'crop' as type, created_at FROM crops WHERE farm_id = ? AND approval_status = 'pending' ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$farmId, $limit]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $pending[] = $row;

        // Animals
        $stmt = $this->db->prepare("SELECT id, tag_number as title, 'animal' as type, created_at FROM animals WHERE farm_id = ? AND approval_status = 'pending' ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$farmId, $limit]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $pending[] = $row;

        // Inventory
        $stmt = $this->db->prepare("SELECT id, item_name as title, 'inventory' as type, created_at FROM inventory WHERE farm_id = ? AND approval_status = 'pending' ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$farmId, $limit]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $pending[] = $row;

        // Equipment
        $stmt = $this->db->prepare("SELECT id, name as title, 'equipment' as type, created_at FROM equipment WHERE farm_id = ? AND approval_status = 'pending' ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$farmId, $limit]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $pending[] = $row;

        // Reports
        $stmt = $this->db->prepare("SELECT r.id, CONCAT(u.name, ' - ', r.category) as title, 'report' as type, r.created_at 
                                   FROM reports r 
                                   JOIN users u ON r.user_id = u.id 
                                   WHERE r.farm_id = ? AND r.status = 'pending' 
                                   ORDER BY r.created_at DESC LIMIT ?");
        $stmt->execute([$farmId, $limit]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $pending[] = $row;

        // Sort all by created_at DESC
        usort($pending, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return array_slice($pending, 0, $limit);
    }

    /**
     * Get recent important activities filtered by role.
     */
    public function getRecentActivity(int $farmId, string $role, int $limit = 10): array
    {
        $stmt = $this->db->prepare('
            SELECT al.*, u.name as user_name, u.role as user_role
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE u.farm_id = :farm_id 
              AND al.is_important = 1
              AND (al.target_roles IS NULL OR FIND_IN_SET(:role, al.target_roles))
            ORDER BY al.created_at DESC
            LIMIT :limit
        ');
        
        // PDO doesn't always handle LIMIT with execute array well if not explicitly bound
        $stmt->bindValue(':farm_id', $farmId, PDO::PARAM_INT);
        $stmt->bindValue(':role', $role, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
