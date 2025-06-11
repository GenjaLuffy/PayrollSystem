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

-- Default Admin Insert
INSERT INTO `admins` (`id`, `username`, `full_name`, `email`, `phone`, `password`, `profile_image`, `user_type`, `created_at`)
VALUES
(1, 'admin', 'Pawon Shrestha', 'admin@mail.com', '9808420035', '$2y$10$RjWfw7CR4iRphyt483zPseYL51SYew0JHzCTPnAY7X.LmaZ/zpJB6', 'default.png', 'Admin', '2025-06-09 09:05:00');
-- Password = admin123

-- Employees Table
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
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Attendance Table
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50),
    date DATE,
    check_in TIME DEFAULT NULL,
    check_out TIME DEFAULT NULL,
    status VARCHAR(20),
    UNIQUE KEY unique_attendance (employee_id, date)
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
  status ENUM('Paid', 'Unpaid') DEFAULT 'Unpaid',
  UNIQUE KEY (employee_id, month),
  FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
);
