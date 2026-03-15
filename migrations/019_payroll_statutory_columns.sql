-- Phase 3.1: Add statutory deduction columns to payroll_records

ALTER TABLE payroll_records 
ADD COLUMN paye_deduction DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER gross_pay,
ADD COLUMN ssnit_employee DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER paye_deduction,
ADD COLUMN ssnit_employer DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER ssnit_employee,
ADD COLUMN other_deductions DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER ssnit_employer;

-- Update net_pay calculation to include statutory deductions
-- Note: net_pay should be: gross_pay - paye_deduction - ssnit_employee - other_deductions
