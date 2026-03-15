-- Phase 2: Create reports and report_photos tables
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    user_id INT NOT NULL,
    category VARCHAR(50),
    related_type ENUM('crop','animal','equipment','general'),
    related_id INT NULL,
    description TEXT,
    severity ENUM('low','medium','high') DEFAULT 'low',
    status ENUM('open','resolved') DEFAULT 'open',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS report_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
