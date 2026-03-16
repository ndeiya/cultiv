-- Add approval_status to farm operations tables
ALTER TABLE crops ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved';
ALTER TABLE animals ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved';
ALTER TABLE equipment ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved';
ALTER TABLE inventory ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved';

-- Add 'pending' status to reports (if not already there, reports status is open/resolved currently)
-- Actually reports table has open/resolved. I'll change it to have 'pending' for workers.
ALTER TABLE reports MODIFY COLUMN status ENUM('pending', 'open', 'resolved') DEFAULT 'pending';
