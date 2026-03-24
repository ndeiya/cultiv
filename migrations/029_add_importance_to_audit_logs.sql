-- Migration: 029_add_importance_to_audit_logs.sql
-- Add importance and target roles to audit_logs table

ALTER TABLE audit_logs ADD COLUMN is_important TINYINT(1) DEFAULT 0 AFTER entity_id;
ALTER TABLE audit_logs ADD COLUMN target_roles VARCHAR(255) NULL AFTER is_important;

-- Create an index for faster filtering of notifications
ALTER TABLE audit_logs ADD INDEX idx_important_roles (is_important, tenant_id);
