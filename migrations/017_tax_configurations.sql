-- Phase 3.1: Create tax_configurations table for Ghana PAYE/SSNIT

CREATE TABLE IF NOT EXISTS tax_configurations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    country_code CHAR(2) NOT NULL DEFAULT 'GH',
    tax_type ENUM('PAYE','SSNIT_EMPLOYEE','SSNIT_EMPLOYER') NOT NULL,
    year INT NOT NULL,
    band_from DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    band_to DECIMAL(12,2) NULL, -- NULL means no upper limit
    rate DECIMAL(5,4) NOT NULL, -- e.g., 0.0550 for 5.5%
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_country_year_type (country_code, year, tax_type),
    INDEX idx_tenant_id (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Ghana 2025 PAYE bands (Progressive tax brackets)
-- Source: Ghana Revenue Authority 2025 tax brackets
INSERT INTO tax_configurations (tenant_id, country_code, tax_type, year, band_from, band_to, rate) VALUES
-- PAYE Bands (Monthly income in GHS)
(1, 'GH', 'PAYE', 2025, 0.00, 402.00, 0.0000),      -- 0% for first 402 GHS
(1, 'GH', 'PAYE', 2025, 402.00, 512.00, 0.0500),   -- 5% for next 110 GHS (402-512)
(1, 'GH', 'PAYE', 2025, 512.00, 642.00, 0.1000),   -- 10% for next 130 GHS (512-642)
(1, 'GH', 'PAYE', 2025, 642.00, 3642.00, 0.1750),  -- 17.5% for next 3000 GHS (642-3642)
(1, 'GH', 'PAYE', 2025, 3642.00, NULL, 0.2500);     -- 25% for above 3642 GHS

-- SSNIT Employee Contribution (5.5% of gross)
INSERT INTO tax_configurations (tenant_id, country_code, tax_type, year, band_from, band_to, rate) VALUES
(1, 'GH', 'SSNIT_EMPLOYEE', 2025, 0.00, NULL, 0.0550);

-- SSNIT Employer Contribution (13% of gross)
INSERT INTO tax_configurations (tenant_id, country_code, tax_type, year, band_from, band_to, rate) VALUES
(1, 'GH', 'SSNIT_EMPLOYER', 2025, 0.00, NULL, 0.1300);
