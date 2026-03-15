<?php
/**
 * Expense Model
 * Handles database operations for farm expenses with multi-tenant scoping.
 */

class ExpenseModel extends BaseModel
{
    protected string $table = 'farm_expenses';

    /**
     * Insert a new expense record.
     */
    public function insert(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (
                    tenant_id, farm_id, crop_id, category, amount, currency, description, expense_date
                ) VALUES (
                    :tenant_id, :farm_id, :crop_id, :category, :amount, :currency, :description, :expense_date
                )";
        
        $params = [
            'farm_id'      => $data['farm_id'],
            'crop_id'      => $data['crop_id'] ?? null,
            'category'     => $data['category'],
            'amount'       => $data['amount'],
            'currency'     => $data['currency'] ?? 'GHS',
            'description'  => $data['description'] ?? null,
            'expense_date' => $data['expense_date']
        ];

        $this->scopedQuery($sql, $params);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Get expenses for a farm with optional filters.
     */
    public function getByFarm(int $farmId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $params = ['farm_id' => $farmId];
        $where = ["farm_id = :farm_id AND {$this->tenantColumn} = :tenant_id"];

        if (!empty($filters['category'])) {
            $where[] = "category = :category";
            $params['category'] = $filters['category'];
        }

        if (!empty($filters['crop_id'])) {
            $where[] = "crop_id = :crop_id";
            $params['crop_id'] = $filters['crop_id'];
        }

        if (!empty($filters['start_date'])) {
            $where[] = "expense_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $where[] = "expense_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        $whereSql = implode(' AND ', $where);
        
        // Count total for pagination
        $countSql = "SELECT COUNT(*) FROM {$this->table} WHERE {$whereSql}";
        $total = (int) $this->scopedQuery($countSql, $params)->fetchColumn();

        // Get data
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT e.*, c.name as crop_name 
                FROM {$this->table} e
                LEFT JOIN crops c ON e.crop_id = c.id
                WHERE {$whereSql} 
                ORDER BY e.expense_date DESC, e.id DESC 
                LIMIT {$perPage} OFFSET {$offset}";
        
        $data = $this->scopedQuery($sql, $params)->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage)
        ];
    }

    /**
     * Get expense breakdown by category for a farm and period.
     */
    public function getCategoryBreakdown(int $farmId, string $startDate, string $endDate): array
    {
        $sql = "SELECT category, SUM(amount) as total 
                FROM {$this->table} 
                WHERE farm_id = :farm_id 
                AND {$this->tenantColumn} = :tenant_id
                AND expense_date BETWEEN :start_date AND :end_date 
                GROUP BY category";
        
        return $this->scopedQuery($sql, [
            'farm_id' => $farmId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ])->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get total expenses for a specific crop/harvest.
     */
    public function getTotalForCrop(int $cropId): float
    {
        $sql = "SELECT SUM(amount) FROM {$this->table} 
                WHERE crop_id = :crop_id AND {$this->tenantColumn} = :tenant_id";
        return (float) $this->scopedQuery($sql, ['crop_id' => $cropId])->fetchColumn();
    }
}
