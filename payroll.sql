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

-- Insert Admin Data (fixed duplicates)
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
  status ENUM('Paid', 'Unpaid') DEFAULT 'Unpaid',
  UNIQUE KEY (employee_id, month),
  FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Salaries Table
CREATE TABLE salaries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT NOT NULL,
  month INT NOT NULL,
  year INT NOT NULL,
  basic_salary DECIMAL(10,2) NOT NULL,
  paid BOOLEAN DEFAULT 0,
  payment_date DATE DEFAULT NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
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