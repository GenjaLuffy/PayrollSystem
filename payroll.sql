-- Create Database
CREATE DATABASE IF NOT EXISTS payroll_system;
USE payroll_system;

-- Admins Table
CREATE TABLE `admins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `full_name` VARCHAR(100),
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20),
  `password` VARCHAR(255) NOT NULL,
  `profile_image` VARCHAR(255) DEFAULT 'default.png',
  `user_type` ENUM('Super Admin','Admin','Manager') DEFAULT 'Admin',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert Admin Data 
INSERT INTO `admins` (`id`, `username`, `full_name`, `email`, `phone`, `password`, `profile_image`, `user_type`, `created_at`)
VALUES
(1, 'admin1', 'Pawon Shrestha', 'pawon@mail.com', '9808420035', '$2y$10$RjWfw7CR4iRphyt483zPseYL51SYew0JHzCTPnAY7X.LmaZ/zpJB6', 'default.png', 'Admin', '2025-06-09 09:05:00'),
(2, 'admin2', 'Pujan Tandukar', 'pujan@mail.com', '9808445785', '$2y$10$RjWfw7CR4iRphyt483zPseYL51SYew0JHzCTPnAY7X.LmaZ/zpJB6', 'default.png', 'Admin', '2025-06-09 09:05:00');
-- Password = admin123



-- Employees Table (added leave_balance, marital_status)
CREATE TABLE employees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id VARCHAR(20) NOT NULL UNIQUE,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  fullName VARCHAR(100) NOT NULL,
  email VARCHAR(100),
  phone VARCHAR(20),
  dob DATE,
  gender VARCHAR(20),
  emergencyContact VARCHAR(20),
  addressStreet VARCHAR(100),
  addressCity VARCHAR(100),
  designation VARCHAR(100),
  department VARCHAR(100),
  salary DECIMAL(10, 2),
  joiningDate DATE NOT NULL,
  bankName VARCHAR(100),
  accountNumber VARCHAR(50),
  pan VARCHAR(20),
  workType VARCHAR(20),
  startTime TIME,
  endTime TIME,
  role ENUM('admin','employee') DEFAULT 'employee',
  profile_image VARCHAR(255) DEFAULT 'default.png',
  leave_balance DECIMAL(5,2) DEFAULT 15,
  marital_status ENUM('Single', 'Married') DEFAULT 'Single',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- Payslips Table (added deduction and bonus fields)
CREATE TABLE payslips (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id VARCHAR(20),
  month VARCHAR(7),
  generated_on DATETIME DEFAULT CURRENT_TIMESTAMP,
  total_present_days INT,
  total_leave_days INT,
  total_absent_days INT,
  gross_salary DECIMAL(10, 2),
  net_salary DECIMAL(10, 2),
  ssf_employee DECIMAL(10,2),
  ssf_employer DECIMAL(10,2),
  pf_employee DECIMAL(10,2),
  pf_employer DECIMAL(10,2),
  tax_deduction DECIMAL(10,2),
  festival_bonus DECIMAL(10,2),
  overtime_pay DECIMAL(10,2),
  overtime_hours DECIMAL(10,2) DEFAULT 0,
  status ENUM('Paid', 'Unpaid') DEFAULT 'Unpaid',
  UNIQUE KEY (employee_id, month),
  FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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


-- Audit Logs Table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20),
    admin_id INT,
    action VARCHAR(100),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;










































--insert for attendance 

INSERT INTO attendance (employee_id, date, check_in, check_out, status, overtime_hours)
SELECT 
    employee_id,
    date,
    CASE 
        WHEN DAYOFWEEK(date) = 1 THEN NULL -- Sunday
        WHEN DAYOFWEEK(date) = 7 THEN NULL -- Saturday
        WHEN employee_id = 'EMP0002' THEN '12:00:00'
        ELSE '10:00:00'
    END AS check_in,
    CASE 
        WHEN DAYOFWEEK(date) = 1 THEN NULL -- Sunday
        WHEN DAYOFWEEK(date) = 7 THEN NULL -- Saturday
        WHEN employee_id = 'EMP0002' AND date = '2025-06-20' THEN '16:36:00'
        WHEN employee_id = 'EMP0002' THEN '18:30:00'
        WHEN date = '2025-06-20' THEN '16:30:00'
        ELSE '18:00:00'
    END AS check_out,
    CASE 
        WHEN DAYOFWEEK(date) IN (1, 7) THEN 'Absent'
        ELSE 'Present'
    END AS status,
    0 AS overtime_hours
FROM (
    SELECT 'EMP0001' AS employee_id
    UNION
    SELECT 'EMP0002' AS employee_id
    UNION
    SELECT 'EMP0003' AS employee_id
) employees
CROSS JOIN (
    SELECT DATE('2025-03-01') + INTERVAL (n) DAY AS date
    FROM (
        SELECT a.N + b.N * 10 + c.N * 100 AS n
        FROM (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a
        CROSS JOIN (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b
        CROSS JOIN (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) c
    ) numbers
    WHERE DATE('2025-03-01') + INTERVAL (n) DAY <= '2025-06-20'
) dates;


