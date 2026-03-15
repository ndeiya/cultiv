-- Phase 4.1: Add payslip storage fields to payroll_records

ALTER TABLE payroll_records 
ADD COLUMN payslip_path VARCHAR(255) NULL AFTER generated_at,
ADD COLUMN payslip_generated_at DATETIME NULL AFTER payslip_path;
