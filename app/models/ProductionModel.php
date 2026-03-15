<?php
/**
 * Production Model
 * Database operations for production records (piece-rate tracking).
 */

class ProductionModel extends BaseModel
{
    protected string $table = 'production_records';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a production record.
     */
    public function create(array $data): int
    {
        $tenantId = $this->getCurrentTenantId();
        $totalAmount = ($data['quantity'] ?? 0) * ($data['unit_rate'] ?? 0);
        
        $stmt = $this->db->prepare('
            INSERT INTO production_records (tenant_id, farm_id, user_id, crop_id, record_date, unit_type, quantity, unit_rate, total_amount, notes, recorded_by)
            VALUES (:tenant_id, :farm_id, :user_id, :crop_id, :record_date, :unit_type, :quantity, :unit_rate, :total_amount, :notes, :recorded_by)
        ');
        $stmt->execute([
            'tenant_id' => $tenantId,
            'farm_id' => $data['farm_id'],
            'user_id' => $data['user_id'],
            'crop_id' => $data['crop_id'] ?? null,
            'record_date' => $data['record_date'],
            'unit_type' => $data['unit_type'],
            'quantity' => $data['quantity'],
            'unit_rate' => $data['unit_rate'],
            'total_amount' => $totalAmount,
            'notes' => $data['notes'] ?? null,
            'recorded_by' => $data['recorded_by'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Get production records for a user within a date range.
     */
    public function getUserProduction(int $userId, string $startDate, string $endDate): array
    {
        $stmt = $this->scopedQuery('
            SELECT pr.*, c.name as crop_name
            FROM production_records pr
            LEFT JOIN crops c ON pr.crop_id = c.id
            WHERE pr.user_id = :user_id 
            AND pr.record_date BETWEEN :start_date AND :end_date 
            AND pr.tenant_id = :tenant_id
            ORDER BY pr.record_date DESC, pr.created_at DESC
        ', [
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total production amount for a user within a date range.
     */
    public function getTotalProductionAmount(int $userId, string $startDate, string $endDate): float
    {
        $stmt = $this->scopedQuery('
            SELECT COALESCE(SUM(total_amount), 0) as total
            FROM production_records
            WHERE user_id = :user_id 
            AND record_date BETWEEN :start_date AND :end_date 
            AND tenant_id = :tenant_id
        ', [
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Get production records for a farm on a specific date.
     */
    public function getFarmProductionByDate(int $farmId, string $date): array
    {
        $stmt = $this->scopedQuery('
            SELECT pr.*, u.name as worker_name, c.name as crop_name
            FROM production_records pr
            JOIN users u ON pr.user_id = u.id
            LEFT JOIN crops c ON pr.crop_id = c.id
            WHERE pr.farm_id = :farm_id 
            AND pr.record_date = :date 
            AND pr.tenant_id = :tenant_id
            ORDER BY pr.created_at DESC
        ', [
            'farm_id' => $farmId,
            'date' => $date
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update a production record.
     */
    public function update(int $id, array $data): bool
    {
        $totalAmount = ($data['quantity'] ?? 0) * ($data['unit_rate'] ?? 0);
        $stmt = $this->scopedQuery('
            UPDATE production_records 
            SET quantity = :quantity, unit_rate = :unit_rate, total_amount = :total_amount, 
                unit_type = :unit_type, crop_id = :crop_id, notes = :notes, updated_at = NOW()
            WHERE id = :id AND tenant_id = :tenant_id
        ', [
            'id' => $id,
            'quantity' => $data['quantity'],
            'unit_rate' => $data['unit_rate'],
            'total_amount' => $totalAmount,
            'unit_type' => $data['unit_type'],
            'crop_id' => $data['crop_id'] ?? null,
            'notes' => $data['notes'] ?? null
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete a production record.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->scopedQuery(
            'DELETE FROM production_records WHERE id = :id AND tenant_id = :tenant_id',
            ['id' => $id]
        );
        return $stmt->rowCount() > 0;
    }
}
