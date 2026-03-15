<?php
/**
 * Notification Model
 * Database operations for the notifications table.
 */

class NotificationModel extends BaseModel
{
    protected string $table = 'notifications';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a new notification record.
     */
    public function create(array $data): int
    {
        $tenantId = $this->getCurrentTenantId();
        $stmt = $this->db->prepare('
            INSERT INTO notifications (tenant_id, user_id, type, title, body, data_json, channel)
            VALUES (:tenant_id, :user_id, :type, :title, :body, :data_json, :channel)
        ');
        
        $stmt->execute([
            'tenant_id' => $tenantId,
            'user_id'   => $data['user_id'],
            'type'      => $data['type'],
            'title'     => $data['title'],
            'body'      => $data['body'],
            'data_json' => isset($data['data_json']) ? json_encode($data['data_json']) : null,
            'channel'   => $data['channel'] ?? 'in_app'
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Get unread notifications for a user.
     */
    public function getUnread(int $userId): array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM notifications WHERE user_id = :user_id AND read_at IS NULL ORDER BY created_at DESC',
            ['user_id' => $userId]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(int $id): bool
    {
        $stmt = $this->scopedQuery(
            'UPDATE notifications SET read_at = NOW() WHERE id = :id AND tenant_id = :tenant_id',
            ['id' => $id]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(int $userId): int
    {
        $stmt = $this->scopedQuery(
            'UPDATE notifications SET read_at = NOW() WHERE user_id = :user_id AND read_at IS NULL AND tenant_id = :tenant_id',
            ['user_id' => $userId]
        );
        return $stmt->rowCount();
    }

    /**
     * Update sent_at timestamp.
     */
    public function markAsSent(int $id): bool
    {
        $stmt = $this->scopedQuery(
            'UPDATE notifications SET sent_at = NOW() WHERE id = :id AND tenant_id = :tenant_id',
            ['id' => $id]
        );
        return $stmt->rowCount() > 0;
    }
}
