-- Phase 9: Add system settings to farms
ALTER TABLE farms
ADD COLUMN IF NOT EXISTS overtime_threshold INT DEFAULT 40,
ADD COLUMN IF NOT EXISTS default_payment_type ENUM('hourly','daily','monthly','unit') DEFAULT 'hourly';
