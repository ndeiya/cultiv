<?php
/**
 * Payroll Service
 * Handles business logic for payroll calculations.
 */

class PayrollService
{
    private PayrollModel $payrollModel;
    private AttendanceModel $attendanceModel;
    private UserModel $userModel;
    private TaxModel $taxModel;
    private ProductionModel $productionModel;
    private LeaveService $leaveService;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->payrollModel = new PayrollModel();
        $this->attendanceModel = new AttendanceModel();
        $this->userModel = new UserModel();
        $this->taxModel = new TaxModel();
        $this->productionModel = new ProductionModel();
        $this->leaveService = new LeaveService();
        $this->notificationService = new NotificationService();
    }

    /**
     * Generate payroll for all workers in a specific period.
     */
    public function generatePayroll(int $periodId): array
    {
        $period = $this->payrollModel->getPeriod($periodId);
        if (!$period) {
            throw new Exception("Payroll period not found.");
        }

        $farmId = $period['farm_id'];
        $startDate = $period['period_start'];
        $endDate = $period['period_end'];

        $workers = $this->userModel->getAllByFarm($farmId);
        $results = [
            'total_workers' => 0,
            'processed' => 0,
            'errors' => []
        ];

        foreach ($workers as $worker) {
            if ($worker['role'] !== 'worker') continue;
            
            $results['total_workers']++;
            try {
                $this->calculateWorkerPayroll($worker['id'], $periodId, $startDate, $endDate);
                $results['processed']++;
            } catch (Exception $e) {
                $results['errors'][] = [
                    'worker_id' => $worker['id'],
                    'worker_name' => $worker['name'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Calculate and save payroll record for a single worker.
     */
    private function calculateWorkerPayroll(int $userId, int $periodId, string $startDate, string $endDate): int
    {
        $profile = $this->payrollModel->getPaymentProfile($userId);
        if (!$profile) {
            throw new Exception("Payment profile not configured for worker ID $userId.");
        }

        // Fetch attendance records
        $attendance = $this->attendanceModel->getHistory($userId, [
            'from' => $startDate,
            'to' => $endDate
        ]);

        $totalMinutes = 0;
        foreach ($attendance as $record) {
            $totalMinutes += $record['total_minutes'] ?? 0;
        }

        $totalHours = $totalMinutes / 60;
        $regularHours = $totalHours;
        $overtimeHours = 0;

        // Overtime calculation
        if ($profile['overtime_threshold'] > 0 && $totalHours > $profile['overtime_threshold']) {
            $overtimeHours = $totalHours - $profile['overtime_threshold'];
            $regularHours = $profile['overtime_threshold'];
        }

        $regularPay = 0;
        $overtimePay = 0;

        // Wage calculation based on payment type
        switch ($profile['payment_type']) {
            case 'hourly':
                $regularPay = $regularHours * $profile['hourly_rate'];
                $overtimePay = $overtimeHours * $profile['overtime_rate'];
                break;
            case 'daily':
                // For daily, we count unique days in attendance
                $daysWorked = count(array_unique(array_map(function($a) {
                    return date('Y-m-d', strtotime($a['clock_in']));
                }, $attendance)));
                $regularPay = $daysWorked * $profile['daily_rate'];
                // Overtime might not apply to daily in the same way, but keeping it simple
                break;
            case 'monthly':
                $regularPay = $profile['monthly_salary']; // Assumes full month if simplified
                break;
            case 'unit':
                // Get total production amount for the period
                $regularPay = $this->productionModel->getTotalProductionAmount($userId, $startDate, $endDate);
                break;
        }

        $bonusAmount = 0;
        $deductionAmount = 0;

        // Fetch existing adjustments (if any were pre-added)
        // Note: For now we assume adjustments are added manually after generation or during.
        // But let's check for salary advances.
        $advances = $this->payrollModel->getSalaryAdvances($userId);
        foreach ($advances as $advance) {
            // Deduct as much as possible but don't make net pay negative? 
            // Or just deduct the full remaining balance if net pay allows.
            $deduction = min($advance['remaining_balance'], ($regularPay + $overtimePay) * 0.5); // Example: max 50% deduction
            if ($deduction > 0) {
                $deductionAmount += $deduction;
                // We'll update the advance balance later when the record is PAID or approved.
                // For now, just record the deduction.
            }
        }

        $grossPay = $regularPay + $overtimePay + $bonusAmount;
        
        // Calculate statutory deductions (PAYE and SSNIT) for Ghana
        // Convert to monthly equivalent for tax calculation
        $daysInPeriod = (strtotime($endDate) - strtotime($startDate)) / 86400 + 1;
        $monthlyEquivalent = ($grossPay / $daysInPeriod) * 30; // Approximate monthly
        
        $statutoryDeductions = $this->calculateStatutoryDeductions($monthlyEquivalent, 'GH', 2025);
        
        // Scale back to period amount
        $payeDeduction = ($statutoryDeductions['paye'] / 30) * $daysInPeriod;
        $ssnitEmployee = ($statutoryDeductions['ssnit_employee'] / 30) * $daysInPeriod;
        $ssnitEmployer = ($statutoryDeductions['ssnit_employer'] / 30) * $daysInPeriod;
        
        // Deduct unpaid leave days
        $unpaidLeaveDays = $this->leaveService->getUnpaidLeaveDays($userId, $startDate, $endDate);
        $dailyRate = 0;
        if ($profile['payment_type'] === 'daily') {
            $dailyRate = $profile['daily_rate'];
        } elseif ($profile['payment_type'] === 'monthly') {
            $dailyRate = $profile['monthly_salary'] / 30; // Approximate daily rate
        } elseif ($profile['payment_type'] === 'hourly') {
            $dailyRate = $profile['hourly_rate'] * 8; // Assume 8 hours per day
        }
        $unpaidLeaveDeduction = $unpaidLeaveDays * $dailyRate;
        
        // Other deductions (salary advances, etc.)
        $otherDeductions = $deductionAmount + $unpaidLeaveDeduction;
        
        // Net pay after all deductions
        $netPay = $grossPay - $payeDeduction - $ssnitEmployee - $otherDeductions;

        $recordData = [
            'payroll_period_id' => $periodId,
            'user_id' => $userId,
            'total_hours' => $totalHours,
            'regular_pay' => $regularPay,
            'overtime_pay' => $overtimePay,
            'bonus_amount' => $bonusAmount,
            'deduction_amount' => $otherDeductions, // Keep for backward compatibility
            'gross_pay' => $grossPay,
            'paye_deduction' => $payeDeduction,
            'ssnit_employee' => $ssnitEmployee,
            'ssnit_employer' => $ssnitEmployer,
            'other_deductions' => $otherDeductions,
            'net_pay' => $netPay,
            'status' => 'pending'
        ];

        $recordId = $this->payrollModel->saveRecord($recordData);

        // Notify worker that payslip is ready
        $this->notificationService->send($userId, 'payslip_ready', [
            'period_end' => $endDate
        ]);

        return $recordId;
    }

    /**
     * Calculate statutory deductions (PAYE and SSNIT) for Ghana.
     * 
     * @param float $grossMonthly Gross monthly salary
     * @param string $countryCode Country code (default: 'GH' for Ghana)
     * @param int $year Tax year (default: 2025)
     * @return array ['paye' => float, 'ssnit_employee' => float, 'ssnit_employer' => float, 'total_deductions' => float, 'net_pay' => float]
     */
    public function calculateStatutoryDeductions(float $grossMonthly, string $countryCode = 'GH', int $year = 2025): array
    {
        // Calculate PAYE (progressive tax)
        $bands = $this->taxModel->getBands($countryCode, 'PAYE', $year);
        $paye = 0;
        $remaining = $grossMonthly;

        foreach ($bands as $band) {
            $upper = $band['band_to'] ?? PHP_FLOAT_MAX;
            $bandFrom = (float)$band['band_from'];
            $rate = (float)$band['rate'];
            
            // Calculate taxable amount in this band
            $taxable = min($remaining, $upper - $bandFrom);
            
            if ($taxable <= 0) break;
            
            $paye += $taxable * $rate;
            $remaining -= $taxable;
        }

        // Calculate SSNIT (flat rates)
        $ssnitEmployeeRate = $this->taxModel->getRate($countryCode, 'SSNIT_EMPLOYEE', $year);
        $ssnitEmployerRate = $this->taxModel->getRate($countryCode, 'SSNIT_EMPLOYER', $year);
        
        $ssnitEmployee = $grossMonthly * $ssnitEmployeeRate;
        $ssnitEmployer = $grossMonthly * $ssnitEmployerRate;

        $totalDeductions = $paye + $ssnitEmployee;
        $netPay = $grossMonthly - $totalDeductions;

        return [
            'paye' => round($paye, 2),
            'ssnit_employee' => round($ssnitEmployee, 2),
            'ssnit_employer' => round($ssnitEmployer, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_pay' => round($netPay, 2)
        ];
    }
}
