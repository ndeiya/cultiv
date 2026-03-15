-- Phase 1.1: Create tenants table (Foundation for multi-tenancy)
CREATE TABLE IF NOT EXISTS tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    domain VARCHAR(255) UNIQUE,
    status ENUM('active','suspended') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default tenant for existing data
INSERT INTO tenants (id, name, status) VALUES (1, 'Default Farm Organization', 'active');
