-- Phase 2: Create payroll tables
CREATE TABLE IF NOT EXISTS worker_payment_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    payment_type ENUM('hourly','daily','monthly','unit'),
    hourly_rate DECIMAL(10,2),
    daily_rate DECIMAL(10,2),
    monthly_salary DECIMAL(10,2),
    unit_rate DECIMAL(10,2),
    overtime_rate DECIMAL(10,2),
    overtime_threshold INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payroll_periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    period_start DATE,
    period_end DATE,
    status ENUM('draft','finalized','paid') DEFAULT 'draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payroll_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payroll_period_id INT NOT NULL,
    user_id INT NOT NULL,
    total_hours DECIMAL(8,2),
    regular_pay DECIMAL(10,2),
    overtime_pay DECIMAL(10,2),
    bonus_amount DECIMAL(10,2) DEFAULT 0.00,
    deduction_amount DECIMAL(10,2) DEFAULT 0.00,
    gross_pay DECIMAL(10,2),
    net_pay DECIMAL(10,2),
    status ENUM('pending','approved','paid') DEFAULT 'pending',
    generated_at DATETIME,
    FOREIGN KEY (payroll_period_id) REFERENCES payroll_periods(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payroll_adjustments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payroll_record_id INT NOT NULL,
    type ENUM('bonus','deduction'),
    reason VARCHAR(255),
    amount DECIMAL(10,2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payroll_record_id) REFERENCES payroll_records(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS salary_advances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2),
    remaining_balance DECIMAL(10,2),
    issued_date DATE,
    status ENUM('active','cleared'),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payroll_record_id INT NOT NULL,
    payment_method ENUM('cash','bank','mobile_money'),
    transaction_reference VARCHAR(120),
    paid_at DATETIME,
    FOREIGN KEY (payroll_record_id) REFERENCES payroll_records(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
