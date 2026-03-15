-- Phase 1.2: Add hash-chain fields to audit_logs for integrity verification
ALTER TABLE audit_logs ADD COLUMN previous_hash VARCHAR(64) NULL AFTER entity_id;
ALTER TABLE audit_logs ADD COLUMN row_hash VARCHAR(64) NOT NULL AFTER previous_hash;
ALTER TABLE audit_logs ADD INDEX idx_row_hash (row_hash);
ALTER TABLE audit_logs ADD INDEX idx_previous_hash (previous_hash);

-- Update existing records to have hash values (for migration)
-- This will be handled by the application code
