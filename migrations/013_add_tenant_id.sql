-- Phase 1.1: Add tenant_id to all existing tables for multi-tenancy isolation
-- This migration adds tenant_id column to all tables and populates with tenant_id=1

-- Add tenant_id to farms table
ALTER TABLE farms ADD COLUMN tenant_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE farms ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
ALTER TABLE farms ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE farms ALTER COLUMN tenant_id DROP DEFAULT;

-- Add tenant_id to users table
ALTER TABLE users ADD COLUMN tenant_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE users ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
ALTER TABLE users ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE users ALTER COLUMN tenant_id DROP DEFAULT;

-- Add tenant_id to attendance table
ALTER TABLE attendance ADD COLUMN tenant_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE attendance ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
ALTER TABLE attendance ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE attendance ALTER COLUMN tenant_id DROP DEFAULT;

-- Add tenant_id to reports table
ALTER TABLE reports ADD COLUMN tenant_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE reports ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
ALTER TABLE reports ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE reports ALTER COLUMN tenant_id DROP DEFAULT;

-- Add tenant_id to report_photos table (via report_id relationship, but add for consistency)
ALTER TABLE report_photos ADD COLUMN tenant_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE report_photos ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
ALTER TABLE report_photos ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE report_photos ALTER COLUMN tenant_id DROP DEFAULT;

-- Add tenant_id to crops table
ALTER TABLE crops ADD COLUMN tenant_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE crops ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
ALTER TABLE crops ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE crops ALTER COLUMN tenant_id DROP DEFAULT;

-- Add tenant_id to animals table
ALTER TABLE animals ADD COLUMN tenant_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE animals ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
ALTER TABLE animals ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE animals ALTER COLUMN tenant_id DROP DEFAULT;

-- Add tenant_id to equipment table
ALTER TABLE equipment ADD COLUMN tenant_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE equipment ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
ALTER TABLE equipment ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE equipment ALTER COLUMN tenant_id DROP DEFAULT;

-- Add tenant_id to inventory table
ALTER TABLE inventory ADD COLUMN tenant_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE inventory ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
ALTER TABLE inventory ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE inventory ALTER COLUMN tenant_id DROP DEFAULT;

-- Add tenant_id to worker_payment_profiles table
ALTER TABLE worker_payment_profiles ADD COLUMN tenant_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE worker_payment_profiles ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
ALTER TABLE worker_payment_profiles ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE worker_payment_profiles ALTER COLUMN tenant_id DROP DEFAULT;

-- Add tenant_id to payroll_periods table
ALTER TABLE payroll_periods ADD COLUMN tenant_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE payroll_periods ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
ALTER TABLE payroll_periods ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE payroll_periods ALTER COLUMN tenant_id DROP DEFAULT;

-- Add tenant_id to payroll_records table
ALTER TABLE payroll_records ADD COLUMN tenant_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE payroll_records ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
ALTER TABLE payroll_records ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE payroll_records ALTER COLUMN tenant_id DROP DEFAULT;

-- Add tenant_id to payroll_adjustments table
ALTER TABLE payroll_adjustments ADD COLUMN tenant_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE payroll_adjustments ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
ALTER TABLE payroll_adjustments ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE payroll_adjustments ALTER COLUMN tenant_id DROP DEFAULT;

-- Add tenant_id to salary_advances table
ALTER TABLE salary_advances ADD COLUMN tenant_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE salary_advances ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
ALTER TABLE salary_advances ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE salary_advances ALTER COLUMN tenant_id DROP DEFAULT;

-- Add tenant_id to payments table
ALTER TABLE payments ADD COLUMN tenant_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE payments ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
ALTER TABLE payments ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE payments ALTER COLUMN tenant_id DROP DEFAULT;

-- Add tenant_id to audit_logs table
ALTER TABLE audit_logs ADD COLUMN tenant_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE audit_logs ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
ALTER TABLE audit_logs ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE audit_logs ALTER COLUMN tenant_id DROP DEFAULT;

-- Update report_photos tenant_id based on parent report
UPDATE report_photos rp
INNER JOIN reports r ON rp.report_id = r.id
SET rp.tenant_id = r.tenant_id;
