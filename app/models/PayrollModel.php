<?php
/**
 * Payroll Model
 * Database operations for payroll management.
 */

class PayrollModel extends BaseModel
{
    protected string $table = 'payroll_records';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get payment profile for a specific user (scoped to current tenant).
     */
    public function getPaymentProfile(int $userId): ?array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM worker_payment_profiles WHERE user_id = :user_id AND tenant_id = :tenant_id LIMIT 1',
            ['user_id' => $userId]
        );
        $profile = $stmt->fetch();
        return $profile ?: null;
    }

    /**
     * Create or update a payment profile (automatically includes tenant_id).
     */
    public function savePaymentProfile(array $data): bool
    {
        $tenantId = $this->getCurrentTenantId();
        $stmt = $this->db->prepare('
            INSERT INTO worker_payment_profiles (tenant_id, user_id, payment_type, hourly_rate, daily_rate, monthly_salary, unit_rate, overtime_rate, overtime_threshold)
            VALUES (:tenant_id, :user_id, :payment_type, :hourly_rate, :daily_rate, :monthly_salary, :unit_rate, :overtime_rate, :overtime_threshold)
            ON DUPLICATE KEY UPDATE 
                payment_type = VALUES(payment_type),
                hourly_rate = VALUES(hourly_rate),
                daily_rate = VALUES(daily_rate),
                monthly_salary = VALUES(monthly_salary),
                unit_rate = VALUES(unit_rate),
                overtime_rate = VALUES(overtime_rate),
                overtime_threshold = VALUES(overtime_threshold)
        ');
        return $stmt->execute([
            'tenant_id' => $tenantId,
            'user_id' => $data['user_id'],
            'payment_type' => $data['payment_type'],
            'hourly_rate' => $data['hourly_rate'] ?? 0.00,
            'daily_rate' => $data['daily_rate'] ?? 0.00,
            'monthly_salary' => $data['monthly_salary'] ?? 0.00,
            'unit_rate' => $data['unit_rate'] ?? 0.00,
            'overtime_rate' => $data['overtime_rate'] ?? 0.00,
            'overtime_threshold' => $data['overtime_threshold'] ?? 0
        ]);
    }

    /**
     * Create a new payroll period (automatically includes tenant_id).
     */
    public function createPeriod(int $farmId, string $start, string $end): int
    {
        $tenantId = $this->getCurrentTenantId();
        $stmt = $this->db->prepare('
            INSERT INTO payroll_periods (tenant_id, farm_id, period_start, period_end, status)
            VALUES (:tenant_id, :farm_id, :period_start, :period_end, "draft")
        ');
        $stmt->execute([
            'tenant_id' => $tenantId,
            'farm_id' => $farmId,
            'period_start' => $start,
            'period_end' => $end
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Get all payroll periods for a farm (scoped to current tenant).
     */
    public function getPeriods(int $farmId): array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM payroll_periods WHERE farm_id = :farm_id AND tenant_id = :tenant_id ORDER BY period_end DESC',
            ['farm_id' => $farmId]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a specific payroll period (scoped to current tenant).
     */
    public function getPeriod(int $id): ?array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM payroll_periods WHERE id = :id AND tenant_id = :tenant_id LIMIT 1',
            ['id' => $id]
        );
        $period = $stmt->fetch();
        return $period ?: null;
    }

    /**
     * Get all payroll records for a specific period (scoped to current tenant).
     */
    public function getRecordsByPeriod(int $periodId): array
    {
        $stmt = $this->scopedQuery('
            SELECT pr.*, u.name as worker_name 
            FROM payroll_records pr
            JOIN users u ON pr.user_id = u.id
            WHERE pr.payroll_period_id = :period_id AND pr.tenant_id = :tenant_id
        ', ['period_id' => $periodId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Save a payroll record (automatically includes tenant_id).
     */
    public function saveRecord(array $data): int
    {
        $tenantId = $this->getCurrentTenantId();
        $stmt = $this->db->prepare('
            INSERT INTO payroll_records (tenant_id, payroll_period_id, user_id, total_hours, regular_pay, overtime_pay, bonus_amount, deduction_amount, gross_pay, paye_deduction, ssnit_employee, ssnit_employer, other_deductions, net_pay, status, generated_at)
            VALUES (:tenant_id, :payroll_period_id, :user_id, :total_hours, :regular_pay, :overtime_pay, :bonus_amount, :deduction_amount, :gross_pay, :paye_deduction, :ssnit_employee, :ssnit_employer, :other_deductions, :net_pay, :status, NOW())
            ON DUPLICATE KEY UPDATE 
                total_hours = VALUES(total_hours),
                regular_pay = VALUES(regular_pay),
                overtime_pay = VALUES(overtime_pay),
                bonus_amount = VALUES(bonus_amount),
                deduction_amount = VALUES(deduction_amount),
                gross_pay = VALUES(gross_pay),
                paye_deduction = VALUES(paye_deduction),
                ssnit_employee = VALUES(ssnit_employee),
                ssnit_employer = VALUES(ssnit_employer),
                other_deductions = VALUES(other_deductions),
                net_pay = VALUES(net_pay),
                status = VALUES(status),
                generated_at = NOW()
        ');
        $data['tenant_id'] = $tenantId;
        // Set defaults for new columns if not provided
        $data['paye_deduction'] = $data['paye_deduction'] ?? 0.00;
        $data['ssnit_employee'] = $data['ssnit_employee'] ?? 0.00;
        $data['ssnit_employer'] = $data['ssnit_employer'] ?? 0.00;
        $data['other_deductions'] = $data['other_deductions'] ?? ($data['deduction_amount'] ?? 0.00);
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Add an adjustment to a payroll record (automatically includes tenant_id).
     */
    public function addAdjustment(array $data): bool
    {
        $tenantId = $this->getCurrentTenantId();
        $stmt = $this->db->prepare('
            INSERT INTO payroll_adjustments (tenant_id, payroll_record_id, type, reason, amount)
            VALUES (:tenant_id, :payroll_record_id, :type, :reason, :amount)
        ');
        $data['tenant_id'] = $tenantId;
        return $stmt->execute($data);
    }

    /**
     * Get adjustments for a specific payroll record (scoped to current tenant).
     */
    public function getAdjustments(int $recordId): array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM payroll_adjustments WHERE payroll_record_id = :record_id AND tenant_id = :tenant_id',
            ['record_id' => $recordId]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get active salary advances for a user (scoped to current tenant).
     */
    public function getSalaryAdvances(int $userId): array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM salary_advances WHERE user_id = :user_id AND status = "active" AND tenant_id = :tenant_id',
            ['user_id' => $userId]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update salary advance balance (scoped to current tenant).
     */
    public function updateSalaryAdvanceBalance(int $id, float $newBalance): bool
    {
        $status = $newBalance <= 0 ? 'cleared' : 'active';
        $stmt = $this->scopedQuery(
            'UPDATE salary_advances SET remaining_balance = :balance, status = :status WHERE id = :id AND tenant_id = :tenant_id',
            ['balance' => $newBalance, 'status' => $status, 'id' => $id]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Create a payment record (automatically includes tenant_id).
     */
    public function createPayment(array $data): bool
    {
        $tenantId = $this->getCurrentTenantId();
        $stmt = $this->db->prepare('
            INSERT INTO payments (tenant_id, payroll_record_id, payment_method, transaction_reference, paid_at)
            VALUES (:tenant_id, :payroll_record_id, :payment_method, :transaction_reference, NOW())
        ');
        $data['tenant_id'] = $tenantId;
        if ($stmt->execute($data)) {
            // Update payroll record status to 'paid'
            $stmt = $this->scopedQuery(
                'UPDATE payroll_records SET status = "paid" WHERE id = :record_id AND tenant_id = :tenant_id',
                ['record_id' => $data['payroll_record_id']]
            );
            return $stmt->rowCount() > 0;
        }
        return false;
    }

    /**
     * Get all payroll records for a worker (scoped to current tenant).
     */
    public function getWorkerPayslips(int $userId): array
    {
        $stmt = $this->scopedQuery('
            SELECT pr.*, pp.period_start, pp.period_end 
            FROM payroll_records pr
            JOIN payroll_periods pp ON pr.payroll_period_id = pp.id
            WHERE pr.user_id = :user_id AND pr.tenant_id = :tenant_id
            ORDER BY pp.period_end DESC
        ', ['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
