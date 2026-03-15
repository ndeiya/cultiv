-- Phase 2: Create attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    user_id INT NOT NULL,
    clock_in DATETIME NOT NULL,
    clock_out DATETIME NULL,
    clock_in_lat DECIMAL(10,7),
    clock_in_lng DECIMAL(10,7),
    clock_out_lat DECIMAL(10,7),
    clock_out_lng DECIMAL(10,7),
    total_minutes INT DEFAULT 0,
    status ENUM('normal','late','incomplete') DEFAULT 'normal',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_clock_in (clock_in),
    INDEX idx_farm_id (farm_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
