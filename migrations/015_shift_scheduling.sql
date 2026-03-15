-- Phase 2.1: Create shift scheduling tables

-- Shift Templates: Reusable shift definitions (e.g., "Morning Shift 6am-2pm")
CREATE TABLE IF NOT EXISTS shift_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    farm_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    break_duration_minutes INT DEFAULT 0,
    is_recurring BOOLEAN DEFAULT TRUE,
    days_of_week VARCHAR(20) DEFAULT '1,2,3,4,5', -- Comma-separated: 1=Monday, 7=Sunday
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_farm_id (farm_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Shift Assignments: Actual scheduled shifts for workers on specific dates
CREATE TABLE IF NOT EXISTS shift_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    farm_id INT NOT NULL,
    shift_template_id INT NULL, -- NULL if one-off shift
    user_id INT NOT NULL,
    assigned_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    break_duration_minutes INT DEFAULT 0,
    status ENUM('scheduled','confirmed','no_show','completed','cancelled') DEFAULT 'scheduled',
    notes TEXT NULL,
    created_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_template_id) REFERENCES shift_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_farm_id (farm_id),
    INDEX idx_user_id (user_id),
    INDEX idx_assigned_date (assigned_date),
    INDEX idx_status (status),
    UNIQUE KEY unique_user_date (tenant_id, user_id, assigned_date, start_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add shift_assignment_id to attendance table
ALTER TABLE attendance ADD COLUMN shift_assignment_id INT NULL AFTER user_id;
ALTER TABLE attendance ADD FOREIGN KEY (shift_assignment_id) REFERENCES shift_assignments(id) ON DELETE SET NULL;
ALTER TABLE attendance ADD INDEX idx_shift_assignment_id (shift_assignment_id);
