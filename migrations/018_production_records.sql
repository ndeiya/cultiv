-- Phase 3.2: Create production_records table for piece-rate (per-unit) pay

CREATE TABLE IF NOT EXISTS production_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    farm_id INT NOT NULL,
    user_id INT NOT NULL,
    crop_id INT NULL, -- Optional: link to specific crop
    record_date DATE NOT NULL,
    unit_type ENUM('crate','kg','bunch','bag','ton','other') NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_rate DECIMAL(10,2) NOT NULL, -- Rate at time of recording
    total_amount DECIMAL(10,2) NOT NULL, -- quantity * unit_rate
    notes TEXT NULL,
    recorded_by INT NULL, -- Supervisor who recorded this
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE SET NULL,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_farm_id (farm_id),
    INDEX idx_user_id (user_id),
    INDEX idx_record_date (record_date),
    INDEX idx_crop_id (crop_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
