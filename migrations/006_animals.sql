-- Phase 2: Create animals table
CREATE TABLE IF NOT EXISTS animals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    tag_number VARCHAR(100),
    species VARCHAR(100),
    breed VARCHAR(100),
    health_status ENUM('good','sick','injured'),
    weight DECIMAL(8,2),
    vaccination_due DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
