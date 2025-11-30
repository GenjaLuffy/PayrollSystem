<?php
session_start();
include './includes/header.php';
include './includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate password match
    if ($_POST['password'] !== $_POST['confirmPassword']) {
        echo "<div class='alert alert-danger'>Passwords do not match!</div>";
    } else {
        // Generate employee ID
        $result = $con->query("SELECT employee_id FROM employees ORDER BY id DESC LIMIT 1");
        $newId = 1;
        if ($result && $row = $result->fetch_assoc()) {
            $lastId = intval(substr($row['employee_id'], 3));
            $newId = $lastId + 1;
        }
        $employeeId = 'EMP' . str_pad($newId, 4, '0', STR_PAD_LEFT);

        // Collect form data
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $fullname = $_POST['fullName'];
        $email = $_POST['email'];
        $phone = $_POST['phone'] ?: null;
        $dob = $_POST['dob'] ?: null;
        $gender = $_POST['gender'] ?: null;
        $emergencyContact = $_POST['emergencyContact'] ?: null;
        $addressStreet = $_POST['addressStreet'] ?: null;
        $addressCity = $_POST['addressCity'] ?: null;
        $designation = $_POST['designation'];
        $department = $_POST['department'];
        $salary = $_POST['salary'];
        $joiningDate = $_POST['joiningDate'];
        $marital_status = $_POST['maritalStatus'] ?? 'Single';
        $bankName = $_POST['bankName'] ?: null;
        $accountNumber = $_POST['accountNumber'] ?: null;
        $pan = $_POST['pan'] ?: null;
        $workType = $_POST['workType'] ?: null;
        $startTime = $_POST['startTime'] ?: null;
        $endTime = $_POST['endTime'] ?: null;
        $profile_image = 'default.png';
        $leave_balance = 15.0;

        // Insert into database
        $stmt = $con->prepare("INSERT INTO employees 
            (employee_id, username, password, fullName, email, phone, dob, gender, emergencyContact,
            addressStreet, addressCity, designation, department, salary, joiningDate, bankName, accountNumber, pan,
            workType, startTime, endTime, role, profile_image, leave_balance, marital_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'employee', ?, ?, ?)");

        $stmt->bind_param(
            "sssssssssssssdssssssssss",
            $employeeId, $username, $password, $fullname, $email, $phone, $dob, $gender, $emergencyContact,
            $addressStreet, $addressCity, $designation, $department, $salary, $joiningDate, $bankName,
            $accountNumber, $pan, $workType, $startTime, $endTime, $profile_image, $leave_balance, $marital_status
        );

        if ($stmt->execute()) {
            header("Location: employees.php?status=success");
            exit();
        } else {
            echo "<div class='alert alert-danger'>Error: Could not add employee. Check logs.</div>";
            error_log($stmt->error);
        }
        $stmt->close();
    }
    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Employee</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
<div class="container-fluid">
<div class="row">
<?php include './includes/sidebar.php'; ?>
<main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 content">
<div class="card p-4 mt-4">
<h2 class="mb-4">Add New Employee</h2>
<form method="post" action="" id="addEmployeeForm">

<!-- Basic Info -->
<div class="row g-3">
<div class="col-md-6">
<label class="form-label">Full Name</label>
<input type="text" name="fullName" class="form-control" required>
</div>
<div class="col-md-6">
<label class="form-label">Username</label>
<input type="text" name="username" class="form-control" required>
</div>
<div class="col-md-6">
<label class="form-label">Email</label>
<input type="email" name="email" class="form-control" required>
</div>
<div class="col-md-6">
<label class="form-label">Password</label>
<input type="password" name="password" class="form-control" required>
</div>
<div class="col-md-6">
<label class="form-label">Confirm Password</label>
<input type="password" name="confirmPassword" class="form-control" required>
</div>
</div>

<!-- Contact Info -->
<div class="row g-3 mt-3">
<div class="col-md-6">
<label class="form-label">Phone</label>
<input type="tel" name="phone" class="form-control">
</div>
<div class="col-md-6">
<label class="form-label">Date of Birth</label>
<input type="date" name="dob" class="form-control">
</div>
<div class="col-md-6">
<label class="form-label">Gender</label>
<select name="gender" class="form-select">
<option value="" disabled selected>Select</option>
<option value="Male">Male</option>
<option value="Female">Female</option>
<option value="Other">Other</option>
<option value="Prefer not to say">Prefer not to say</option>
</select>
</div>
<div class="col-md-6">
<label class="form-label">Emergency Contact</label>
<input type="tel" name="emergencyContact" class="form-control">
</div>
</div>

<!-- Address Info -->
<div class="row g-3 mt-3">
<div class="col-md-6">
<label class="form-label">Street</label>
<input type="text" name="addressStreet" class="form-control">
</div>
<div class="col-md-6">
<label class="form-label">City</label>
<input type="text" name="addressCity" class="form-control">
</div>
</div>

<!-- Job Info -->
<div class="row g-3 mt-3">
<div class="col-md-6">
<label class="form-label">Department</label>
<select name="department" id="department" class="form-select" required>
<option value="" disabled selected>Select Department</option>
<option value="HR">HR</option>
<option value="Finance">Finance</option>
<option value="IT">IT</option>
<option value="Sales">Sales</option>
<option value="Marketing">Marketing</option>
</select>
</div>
<div class="col-md-6">
<label class="form-label">Designation</label>
<select name="designation" id="designation" class="form-select" required disabled>
<option value="" disabled selected>Select Designation</option>
</select>
</div>
<div class="col-md-6">
<label class="form-label">Salary (Rs)</label>
<input type="number" name="salary" class="form-control" min="0" step="0.01" required>
</div>
<div class="col-md-6">
<label class="form-label">Joining Date</label>
<input type="date" name="joiningDate" class="form-control" required>
</div>
<div class="col-md-6">
<label class="form-label">Marital Status</label>
<select name="maritalStatus" class="form-select">
<option value="Single">Single</option>
<option value="Married">Married</option>
</select>
</div>
</div>

<!-- Bank Info -->
<div class="row g-3 mt-3">
<div class="col-md-6">
<label class="form-label">Bank Name</label>
<input type="text" name="bankName" class="form-control">
</div>
<div class="col-md-6">
<label class="form-label">Account Number</label>
<input type="text" name="accountNumber" class="form-control">
</div>
<div class="col-md-6">
<label class="form-label">PAN Number</label>
<input type="text" name="pan" class="form-control">
</div>
</div>

<!-- Work Schedule -->
<div class="row g-3 mt-3">
<div class="col-md-4">
<label class="form-label">Work Type</label>
<select name="workType" class="form-select">
<option value="" disabled selected>Select</option>
<option value="Full Time">Full Time</option>
<option value="Part Time">Part Time</option>
</select>
</div>
<div class="col-md-4">
<label class="form-label">Start Time</label>
<input type="time" name="startTime" class="form-control">
</div>
<div class="col-md-4">
<label class="form-label">End Time</label>
<input type="time" name="endTime" class="form-control">
</div>
</div>

<div class="mt-4 text-end">
<button type="submit" class="btn btn-primary">Add Employee</button>
</div>

</form>
</div>
</main>
</div>
</div>

<script>
const designations = {
HR: ["HR Manager","HR Officer","Recruiter"],
Finance: ["Finance Manager","Accountant","Payroll Accountant"],
IT: ["IT Manager","Developer","SysAdmin"],
Sales: ["Sales Manager","Sales Executive"],
Marketing: ["Marketing Manager","Digital Specialist"]
};

const deptSelect = document.getElementById("department");
const desigSelect = document.getElementById("designation");

deptSelect.addEventListener("change", function() {
desigSelect.innerHTML = '<option value="" disabled selected>Select Designation</option>';
if(designations[this.value]) {
designations[this.value].forEach(d=>{
let opt=document.createElement("option");
opt.value=d;
opt.textContent=d;
desigSelect.appendChild(opt);
});
desigSelect.disabled=false;
} else { desigSelect.disabled=true; }
});
</script>
</body>
</html>
