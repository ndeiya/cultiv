-- Add acquisition_date column to equipment table
ALTER TABLE equipment ADD COLUMN acquisition_date DATE NULL AFTER status;
