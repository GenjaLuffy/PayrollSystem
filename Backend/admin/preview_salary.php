<?php
include './includes/connect.php';
include './algorithm/PayrollCalculator.php';

$employeeId = $_POST['employee_id'] ?? '';
$monthYear = $_POST['month_year'] ?? '';

if (!$employeeId || !$monthYear) { echo "Missing employee or month."; exit; }

[$year,$month]=explode('-',$monthYear);

try {
    $payroll = new PayrollCalculator($con, $employeeId, (int)$month, (int)$year);
    $preview = $payroll->generatePayslip();

    function fmt($value){ return is_numeric($value)?number_format($value,2):htmlspecialchars($value); }

    $deductions=['ssf_employee','ssf_employer','pf_employee','pf_employer','tax_deduction'];
    echo "<ul class='list-group'>";
    foreach($preview as $key=>$value){
        $cls=in_array($key,$deductions)?'text-danger':'text-success';
        if(in_array($key,['employee_id','employee_name','month','present_days','full_paid_leave_days','half_paid_leave_days','absent_days','overtime_hours'])) $cls='';
        echo "<li class='list-group-item d-flex justify-content-between'>
                <strong>".ucwords(str_replace('_',' ',$key))."</strong>
                <span class='$cls'>".fmt($value)."</span>
              </li>";
    }
    echo "</ul>";
    echo "<input type='hidden' id='salaryStatus' value='Pending'>";
} catch(Exception $e){
    echo "<p class='text-danger'>".$e->getMessage()."</p>";
}
?>
