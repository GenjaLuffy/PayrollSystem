<?php
session_start();
include './includes/connect.php';
include './includes/auth.php';
include './algorithm/PayrollCalculator.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $employeeId = $_POST['employee_id'] ?? '';
    $monthYear = $_POST['month_year'] ?? '';

    if(!$employeeId || !$monthYear){
        $_SESSION['error']="Employee and Month-Year are required.";
        header("Location: payroll.php"); exit;
    }

    [$year,$month]=explode('-',$monthYear);
    $month=(int)$month; $year=(int)$year;
    $monthYearStr=sprintf("%d-%02d",$year,$month);

    // check if already processed
    $check_sql="SELECT id FROM payslips WHERE employee_id=? AND month=?";
    $stmt=$con->prepare($check_sql);
    $stmt->bind_param("ss",$employeeId,$monthYearStr);
    $stmt->execute();
    $result=$stmt->get_result();
    if($result->num_rows>0){
        $_SESSION['error']="Payslip already processed for $employeeId ($monthYear)";
        $stmt->close();
        header("Location: payroll.php"); exit;
    }
    $stmt->close();

    try{
        $payroll=new PayrollCalculator($con,$employeeId,$month,$year);
        $payroll->generateAndStorePayslip();

        $_SESSION['success']="Salary processed and marked as Paid for $employeeId ($monthYear)";

        // Audit log
        $action="Processed Salary";
        $details="Salary processed for employee $employeeId for $monthYear";
        $sql="INSERT INTO audit_logs (employee_id, action, details) VALUES (?,?,?)";
        $stmt=$con->prepare($sql);
        $stmt->bind_param("sss",$employeeId,$action,$details);
        $stmt->execute();
        $stmt->close();

    } catch(Exception $e){
        $_SESSION['error']="Error processing salary: ".$e->getMessage();
    }

    header("Location: payroll.php"); exit;
}

// redirect default
header("Location: payroll.php"); exit;
?>
