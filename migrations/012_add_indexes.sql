-- Phase 11: Add missing indexes for performance

-- Reports indexes
CREATE INDEX idx_reports_farm_id ON reports(farm_id);
CREATE INDEX idx_reports_user_id ON reports(user_id);
CREATE INDEX idx_reports_status ON reports(status);
CREATE INDEX idx_reports_created_at ON reports(created_at);
CREATE INDEX idx_report_photos_report_id ON report_photos(report_id);

-- Farm Operations indexes
CREATE INDEX idx_crops_farm_id ON crops(farm_id);
CREATE INDEX idx_animals_farm_id ON animals(farm_id);
CREATE INDEX idx_equipment_farm_id ON equipment(farm_id);
CREATE INDEX idx_inventory_farm_id ON inventory(farm_id);

-- Payroll indexes
CREATE INDEX idx_wpp_user_id ON worker_payment_profiles(user_id);
CREATE INDEX idx_pp_farm_id ON payroll_periods(farm_id);
CREATE INDEX idx_pr_period_id ON payroll_records(payroll_period_id);
CREATE INDEX idx_pr_user_id ON payroll_records(user_id);
CREATE INDEX idx_pa_record_id ON payroll_adjustments(payroll_record_id);
CREATE INDEX idx_sa_user_id ON salary_advances(user_id);
CREATE INDEX idx_pay_record_id ON payments(payroll_record_id);

-- Audit Logs indexes
CREATE INDEX idx_al_user_id ON audit_logs(user_id);
CREATE INDEX idx_al_entity ON audit_logs(entity, entity_id);
CREATE INDEX idx_al_created_at ON audit_logs(created_at);
