-- Phase 1: Create farms table (needed for user authentication)
CREATE TABLE IF NOT EXISTS farms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    location VARCHAR(255),
    latitude DECIMAL(10,7),
    longitude DECIMAL(10,7),
    geofence_radius INT DEFAULT 200,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
