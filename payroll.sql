-- Database
CREATE DATABASE IF NOT EXISTS payroll_system;
USE payroll_system;

-- Admins Table (no changes)
CREATE TABLE `admins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `full_name` VARCHAR(100),
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20),
  `password` VARCHAR(255) NOT NULL,
  `profile_image` VARCHAR(255) DEFAULT 'default.png',
  `user_type` ENUM('Super Admin','Admin') DEFAULT 'Admin',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Insert Admin Data 
INSERT INTO `admins` (`id`, `username`, `full_name`, `email`, `phone`, `password`, `profile_image`, `user_type`, `created_at`)
VALUES
(1, 'admin1', 'Pawon Shrestha', 'pawon@mail.com', '9808420035', '$2y$10$RjWfw7CR4iRphyt483zPseYL51SYew0JHzCTPnAY7X.LmaZ/zpJB6', 'default.png', 'Admin', '2025-06-09 09:05:00'),
(2, 'admin2', 'Pujan Tandukar', 'pujan@mail.com', '9808445785', '$2y$10$RjWfw7CR4iRphyt483zPseYL51SYew0JHzCTPnAY7X.LmaZ/zpJB6', 'default.png', 'Admin', '2025-06-09 09:05:00');
-- Password = admin123

-- Employees Table with AES fields as VARCHAR(512)
CREATE TABLE employees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id VARCHAR(20) NOT NULL UNIQUE,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  fullName VARCHAR(100) NOT NULL,
  email VARCHAR(100),
  phone VARCHAR(20),
  dob VARCHAR(255),
  gender VARCHAR(20),
  emergencyContact VARCHAR(255),
  addressStreet VARCHAR(255),
  addressCity VARCHAR(255),
  designation VARCHAR(100),
  department VARCHAR(100),
  salary VARCHAR(255),
  joiningDate DATE NOT NULL,
  bankName VARCHAR(255),
  accountNumber VARCHAR(255),
  pan VARCHAR(255),
  workType VARCHAR(20),
  startTime TIME,
  endTime TIME,
  role ENUM('admin','employee') DEFAULT 'employee',
  profile_image VARCHAR(255) DEFAULT 'default.png',
  leave_balance DECIMAL(5,2) DEFAULT 15,
  marital_status ENUM('Single', 'Married') DEFAULT 'Single',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- Attendance Table (added overtime_hours)
    CREATE TABLE attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id VARCHAR(20),
        date DATE,
        check_in TIME DEFAULT NULL,
        check_out TIME DEFAULT NULL,
        status VARCHAR(20),
        overtime_hours DECIMAL(5,2) DEFAULT 0,
        UNIQUE KEY unique_attendance (employee_id, date),
        FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
    );


-- Leave Requests Table
CREATE TABLE leave_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id VARCHAR(20),
  leave_type VARCHAR(50),
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  reason TEXT,
  status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  applied_on DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
);

-- Payslips Table 
CREATE TABLE IF NOT EXISTS payslips (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id VARCHAR(20) NOT NULL,
  month VARCHAR(7) NOT NULL,
  generated_on DATETIME DEFAULT CURRENT_TIMESTAMP,
  total_present_days INT DEFAULT 0,
  full_paid_leave_days DECIMAL(5,2) DEFAULT 0,
  half_paid_leave_days DECIMAL(5,2) DEFAULT 0,
  total_absent_days INT DEFAULT 0,
  basic_salary DECIMAL(15,2) NOT NULL DEFAULT 0,
  allowances DECIMAL(15,2) DEFAULT 0,
  gross_salary DECIMAL(15,2) NOT NULL DEFAULT 0,
  net_salary DECIMAL(15,2) NOT NULL DEFAULT 0,
  festival_bonus DECIMAL(15,2) DEFAULT 0,
  overtime_pay DECIMAL(15,2) DEFAULT 0,
  overtime_hours DECIMAL(10,2) DEFAULT 0,
  ssf_employee DECIMAL(15,2) DEFAULT 0,
  ssf_employer DECIMAL(15,2) DEFAULT 0,
  pf_employee DECIMAL(15,2) DEFAULT 0,
  pf_employer DECIMAL(15,2) DEFAULT 0,
  tax_deduction DECIMAL(15,2) DEFAULT 0,
  status ENUM('Paid', 'Unpaid') DEFAULT 'Unpaid',
  UNIQUE KEY unique_employee_month (employee_id, month),
  FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Salaries Table
CREATE TABLE salaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20),
    month INT,
    year INT,
    basic_salary DECIMAL(10, 2),
    paid_status VARCHAR(20) DEFAULT 'Unpaid',
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Leave Staus Table
CREATE TABLE leave_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) NOT NULL,
    leave_type VARCHAR(50) NOT NULL,
    total_allocated INT DEFAULT 0,
    used INT DEFAULT 0,
    last_updated DATE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    UNIQUE(employee_id, leave_type)
);


-- Audit Logs Table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) NULL,
    admin_id INT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_employee_id (employee_id),
    INDEX idx_admin_id (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;









































--insert for attendance 
-- Insert attendance from 2025-03-01 up to the last Sunday before today
INSERT INTO attendance (employee_id, date, check_in, check_out, status, overtime_hours)
WITH 
last_sunday AS (
    SELECT CURDATE() - INTERVAL (DAYOFWEEK(CURDATE())) DAY AS ls
),
RECURSIVE dates AS (
    SELECT DATE('2025-03-01') AS date
    UNION ALL
    SELECT DATE_ADD(date, INTERVAL 1 DAY)
    FROM dates, last_sunday
    WHERE DATE_ADD(date, INTERVAL 1 DAY) <= (SELECT ls FROM last_sunday)
)
SELECT 
    e.employee_id,
    d.date,
    CASE 
        WHEN DAYOFWEEK(d.date) IN (1,7) THEN NULL
        WHEN e.employee_id = 'EMP0002' THEN '12:00:00'
        WHEN e.employee_id = 'EMP0004' THEN '13:00:00'
        ELSE '10:00:00'
    END AS check_in,
    CASE 
        WHEN DAYOFWEEK(d.date) IN (1,7) THEN NULL
        WHEN e.employee_id = 'EMP0002' AND d.date = '2025-06-20' THEN '16:36:00'
        WHEN e.employee_id = 'EMP0002' THEN '18:30:00'
        WHEN e.employee_id = 'EMP0004' THEN '18:00:00'
        ELSE '18:00:00'
    END AS check_out,
    CASE 
        WHEN DAYOFWEEK(d.date) IN (1,7) THEN 'Absent'
        ELSE 'Present'
    END AS status,
    0 AS overtime_hours
FROM (
      SELECT 'EMP0001' AS employee_id
      UNION ALL SELECT 'EMP0002'
      UNION ALL SELECT 'EMP0003'
      UNION ALL SELECT 'EMP0004'
) e
CROSS JOIN dates d;

