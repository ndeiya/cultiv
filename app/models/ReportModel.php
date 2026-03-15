<?php
/**
 * Report Model
 * Database operations for reports and report_photos
 */

class ReportModel extends BaseModel
{
    protected string $table = 'reports';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Insert a new report and its optional photos.
     */
    public function insert(array $data, array $photos = []): int
    {
        try {
            $this->db->beginTransaction();

            $tenantId = $this->getCurrentTenantId();
            $stmt = $this->db->prepare('
                INSERT INTO reports (tenant_id, farm_id, user_id, category, related_type, related_id, description, severity, status)
                VALUES (:tenant_id, :farm_id, :user_id, :category, :related_type, :related_id, :description, :severity, :status)
            ');
            
            $stmt->execute([
                'tenant_id' => $tenantId,
                'farm_id' => $data['farm_id'],
                'user_id' => $data['user_id'],
                'category' => $data['category'],
                'related_type' => $data['related_type'] ?? null,
                'related_id' => $data['related_id'] ?? null,
                'description' => $data['description'],
                'severity' => $data['severity'] ?? 'low',
                'status' => 'open'
            ]);

            $reportId = (int) $this->db->lastInsertId();

            if (!empty($photos)) {
                $photoStmt = $this->db->prepare('
                    INSERT INTO report_photos (tenant_id, report_id, file_path)
                    VALUES (:tenant_id, :report_id, :file_path)
                ');
                
                foreach ($photos as $photo) {
                    $photoStmt->execute([
                        'tenant_id' => $tenantId,
                        'report_id' => $reportId,
                        'file_path' => $photo
                    ]);
                }
            }

            $this->db->commit();
            return $reportId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get reports by farm ID with optional filters.
     */
    public function getByFarm(int $farmId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $sql = "
            SELECT r.*, u.name as reporter_name
            FROM reports r
            JOIN users u ON r.user_id = u.id
            WHERE r.farm_id = :farm_id AND r.tenant_id = :tenant_id
        ";
        $params = [
            'farm_id' => $farmId,
            'tenant_id' => $this->getCurrentTenantId()
        ];

        if (!empty($filters['status'])) {
            $sql .= " AND r.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['worker'])) {
            $sql .= " AND r.user_id = :worker";
            $params['worker'] = $filters['worker'];
        }
        
        if (!empty($filters['date'])) {
            $sql .= " AND DATE(r.created_at) = :date";
            $params['date'] = $filters['date'];
        }
        
        if (!empty($filters['category'])) {
            $sql .= " AND r.category = :category";
            $params['category'] = $filters['category'];
        }

        $sql .= " ORDER BY r.created_at DESC";

        $result = paginate($this->db, $sql, $params, $page, $perPage);
        $result['data'] = $this->attachPhotosToReports($result['data']);
        
        return $result;
    }

    /**
     * Get reports by user ID with optional filters.
     */
    public function getByUser(int $userId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $sql = "SELECT * FROM reports WHERE user_id = :user_id AND tenant_id = :tenant_id";
        $params = [
            'user_id' => $userId,
            'tenant_id' => $this->getCurrentTenantId()
        ];

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['date'])) {
            $sql .= " AND DATE(created_at) = :date";
            $params['date'] = $filters['date'];
        }
        
        if (!empty($filters['category'])) {
            $sql .= " AND category = :category";
            $params['category'] = $filters['category'];
        }

        $sql .= " ORDER BY created_at DESC";

        $result = paginate($this->db, $sql, $params, $page, $perPage);
        $result['data'] = $this->attachPhotosToReports($result['data']);
        
        return $result;
    }

    /**
     * Get a single report by ID.
     */
    public function getById(int $id, int $farmId): ?array
    {
        $stmt = $this->scopedQuery('
            SELECT r.*, u.name as reporter_name 
            FROM reports r
            JOIN users u ON r.user_id = u.id
            WHERE r.id = :id AND r.farm_id = :farm_id AND r.tenant_id = :tenant_id
            LIMIT 1
        ', ['id' => $id, 'farm_id' => $farmId]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($report) {
            $photoStmt = $this->db->prepare('SELECT file_path FROM report_photos WHERE report_id = :report_id');
            $photoStmt->execute(['report_id' => $id]);
            $report['photos'] = $photoStmt->fetchAll(PDO::FETCH_COLUMN);
        }

        return $report ?: null;
    }

    /**
     * Update the status of a report.
     */
    public function updateStatus(int $id, int $farmId, string $status): bool
    {
        $stmt = $this->scopedQuery('
            UPDATE reports 
            SET status = :status 
            WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id
        ', [
            'id' => $id,
            'farm_id' => $farmId,
            'status' => $status
        ]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Helper to attach photos to a list of reports.
     */
    private function attachPhotosToReports(array $reports): array
    {
        if (empty($reports)) {
            return [];
        }
        
        $reportIds = array_column($reports, 'id');
        $placeholders = implode(',', array_fill(0, count($reportIds), '?'));
        
        $stmt = $this->db->prepare("SELECT report_id, file_path FROM report_photos WHERE report_id IN ($placeholders)");
        $stmt->execute($reportIds);
        $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $photosByReport = [];
        foreach ($photos as $photo) {
            $photosByReport[$photo['report_id']][] = $photo['file_path'];
        }
        
        foreach ($reports as &$report) {
            $report['photos'] = $photosByReport[$report['id']] ?? [];
        }
        
        return $reports;
    }
}
