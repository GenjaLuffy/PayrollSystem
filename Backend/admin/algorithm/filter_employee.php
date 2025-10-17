<?php
// includes/filter_employees.php

include './includes/connect.php';

// Fetch all departments
$sql_departments = "SELECT DISTINCT department FROM employees ORDER BY department";
$dept_result = $con->query($sql_departments);

$selectedDept = isset($_GET['dept']) ? $_GET['dept'] : '';

// Fetch employees (filtered by department if selected)
if (!empty($selectedDept)) {
    $sql_employees = "SELECT employee_id, fullName, department, designation, salary 
                      FROM employees WHERE department = ?";
    $stmt = $con->prepare($sql_employees);
    $stmt->bind_param("s", $selectedDept);
    $stmt->execute();
    $emp_result = $stmt->get_result();
} else {
    $sql_employees = "SELECT employee_id, fullName, department, designation, salary FROM employees";
    $emp_result = $con->query($sql_employees);
}
?>
