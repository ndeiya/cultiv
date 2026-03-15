-- Migration: 023_notifications.sql
-- Create notifications table for Phase 6

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    user_id INT NOT NULL,
    type VARCHAR(60) NOT NULL,
    title VARCHAR(120) NOT NULL,
    body TEXT NOT NULL,
    data_json JSON NULL,
    channel SET('in_app', 'push', 'whatsapp', 'sms') DEFAULT 'in_app',
    read_at DATETIME NULL,
    sent_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_user (tenant_id, user_id),
    INDEX idx_type (type),
    INDEX idx_created (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
