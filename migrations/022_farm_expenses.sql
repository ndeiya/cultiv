-- Migration: Create farm_expenses table
-- Phase 5.2: Expense Tracking

CREATE TABLE IF NOT EXISTS farm_expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    farm_id INT NOT NULL,
    crop_id INT NULL,
    category ENUM('seed', 'fertilizer', 'pesticide', 'labor', 'equipment', 'fuel', 'utilties', 'other') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'GHS',
    description TEXT,
    expense_date DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY (farm_id) REFERENCES farms(id),
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE SET NULL,
    
    INDEX (tenant_id),
    INDEX (farm_id),
    INDEX (expense_date),
    INDEX (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
