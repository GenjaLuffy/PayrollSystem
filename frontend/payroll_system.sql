CREATE DATABASE IF NOT EXISTS payroll_system;
USE payroll_system;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    role ENUM('admin', 'employee') DEFAULT 'employee',
    department VARCHAR(50),
    designation VARCHAR(50),
    date_joined DATE NOT NULL
);

-- Attendance Table
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20),
    date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Leave') NOT NULL,
    check_in_time TIME,
    check_out_time TIME,
    UNIQUE (employee_id, date),
    FOREIGN KEY (employee_id) REFERENCES users(employee_id) ON DELETE CASCADE
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
    FOREIGN KEY (employee_id) REFERENCES users(employee_id) ON DELETE CASCADE
);

-- Salary Table
CREATE TABLE salary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20),
    basic DECIMAL(10,2) NOT NULL,
    hra DECIMAL(10,2),
    da DECIMAL(10,2),
    other_allowances DECIMAL(10,2),
    deductions DECIMAL(10,2),
    net_salary DECIMAL(10,2),
    FOREIGN KEY (employee_id) REFERENCES users(employee_id) ON DELETE CASCADE
);

-- Payslips Table
CREATE TABLE payslips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20),
    month VARCHAR(7), -- Format: YYYY-MM
    generated_on DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_present_days INT,
    total_leave_days INT,
    total_absent_days INT,
    gross_salary DECIMAL(10,2),
    net_salary DECIMAL(10,2),
    status ENUM('Paid', 'Unpaid') DEFAULT 'Unpaid',
    UNIQUE (employee_id, month),
    FOREIGN KEY (employee_id) REFERENCES users(employee_id) ON DELETE CASCADE
);
