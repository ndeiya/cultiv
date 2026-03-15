-- Seed Data: Default farm + owner + sample users
-- Owner password: admin123  (hashed with password_hash)

-- Default farm
INSERT INTO farms (name, location, latitude, longitude, geofence_radius)
VALUES ('Demo Farm', 'Nairobi, Kenya', -1.2921000, 36.8219000, 200)
ON DUPLICATE KEY UPDATE name = name;

-- Owner account (password: admin123)
INSERT INTO users (farm_id, name, email, phone, password_hash, role, status)
VALUES (1, 'Farm Owner', 'owner@cultiv.com', '+254700000001',
        '$2y$10$O.KCUH8/nATV.iMFPkDVFecuq76V/XThJK074DKaoHu57GpC5d8e2', 'owner', 'active')
ON DUPLICATE KEY UPDATE name = name;

-- Supervisor account (password: admin123)
INSERT INTO users (farm_id, name, email, phone, password_hash, role, status)
VALUES (1, 'John Supervisor', 'supervisor@cultiv.com', '+254700000002',
        '$2y$10$O.KCUH8/nATV.iMFPkDVFecuq76V/XThJK074DKaoHu57GpC5d8e2', 'supervisor', 'active')
ON DUPLICATE KEY UPDATE name = name;

-- Worker accounts (password: admin123)
INSERT INTO users (farm_id, name, email, phone, password_hash, role, status)
VALUES (1, 'James Worker', 'worker@cultiv.com', '+254700000003',
        '$2y$10$O.KCUH8/nATV.iMFPkDVFecuq76V/XThJK074DKaoHu57GpC5d8e2', 'worker', 'active')
ON DUPLICATE KEY UPDATE name = name;

INSERT INTO users (farm_id, name, email, phone, password_hash, role, status)
VALUES (1, 'Mary Worker', 'worker2@cultiv.com', '+254700000004',
        '$2y$10$O.KCUH8/nATV.iMFPkDVFecuq76V/XThJK074DKaoHu57GpC5d8e2', 'worker', 'active')
ON DUPLICATE KEY UPDATE name = name;

-- Accountant account (password: admin123)
INSERT INTO users (farm_id, name, email, phone, password_hash, role, status)
VALUES (1, 'Sarah Accountant', 'accountant@cultiv.com', '+254700000005',
        '$2y$10$O.KCUH8/nATV.iMFPkDVFecuq76V/XThJK074DKaoHu57GpC5d8e2', 'accountant', 'active')
ON DUPLICATE KEY UPDATE name = name;
