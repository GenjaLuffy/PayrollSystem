<?php
include './includes/header.php';
include './includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $result = $con->query("SELECT employee_id FROM employees ORDER BY id DESC LIMIT 1");
  if ($result && $row = $result->fetch_assoc()) {
    $lastId = intval(substr($row['employee_id'], 3));
    $newId = $lastId + 1;
  } else {
    $newId = 1;
  }
  $employeeId = 'EMP' . str_pad($newId, 4, '0', STR_PAD_LEFT);

  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $fullname = $_POST['fullName'];
  $email = $_POST['email'];
  $phone = $_POST['phone'] ?: null;
  $dob = $_POST['dob'] ?: null;
  $gender = $_POST['gender'] ?: null;
  $emergencyContact = $_POST['emergencyContact'] ?: null;
  $addressStreet = $_POST['addressStreet'] ?? null;
  $addressCity = $_POST['addressCity'] ?? null;
  $designation = $_POST['designation'];
  $department = $_POST['department'];
  $salary = $_POST['salary'];
  $joiningDate = $_POST['joiningDate'];
  $bankName = $_POST['bankName'] ?: null;
  $accountNumber = $_POST['accountNumber'] ?: null;
  $pan = $_POST['pan'] ?: null;
  $workType = $_POST['workType'] ?: null;
  $startTime = $_POST['startTime'] ?: null;
  $endTime = $_POST['endTime'] ?: null;
  $profile_image = 'default.png';
  $leave_balance = 15.0;
  $marital_status = $_POST['maritalStatus'] ?? 'Single';

  $stmt = $con->prepare("INSERT INTO employees 
    (employee_id, username, password, fullName, email, phone, dob, gender, emergencyContact, 
    addressStreet, addressCity, designation, department, salary, joiningDate, bankName, accountNumber, pan, 
    workType, startTime, endTime, role, profile_image, leave_balance, marital_status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'employee', ?, ?, ?)");

  $stmt->bind_param(
    "sssssssssssssdssssssssss",
    $employeeId,
    $username,
    $password,
    $fullname,
    $email,
    $phone,
    $dob,
    $gender,
    $emergencyContact,
    $addressStreet,
    $addressCity,
    $designation,
    $department,
    $salary,
    $joiningDate,
    $bankName,
    $accountNumber,
    $pan,
    $workType,
    $startTime,
    $endTime,
    $profile_image,
    $leave_balance,
    $marital_status
  );


  if ($stmt->execute()) {
    header("Location: employees.php?status=success");
    exit();
  } else {
    error_log("SQL Error: " . $stmt->error);
    echo "Error: Could not add employee. Please check the logs.";
  }

  $stmt->close();
  $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Employee</title>
  <link rel="stylesheet" href="./assets/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <?php include './includes/sidebar.php'; ?>
      <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 content">
        <div class="card p-4 mt-4">
          <h2 class="mb-4 text-dark">Add New Employee</h2>
          <form id="addEmployeeForm" method="post" action="" onsubmit="return validateForm()">
            <div id="formMessage" class="mb-3 text-danger"></div>

            <!-- Basic Info -->
            <div class="row g-3">
              <div class="col-md-6">
                <label for="fullName" class="form-label">Full Name</label>
                <input type="text" name="fullName" class="form-control" id="fullName" required />
              </div>
              <div class="col-md-6">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" class="form-control" id="username" required />
              </div>
              <div class="col-md-6">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" id="email" required />
              </div>
              <div class="col-md-6">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                  <input type="password" name="password" class="form-control" id="password" required />
                  <span class="input-group-text" id="togglePassword"><i class="bi bi-eye"></i></span>
                </div>
              </div>
              <div class="col-md-6">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <div class="input-group">
                  <input type="password" name="confirmPassword" class="form-control" id="confirmPassword" required />
                  <span class="input-group-text" id="toggleConfirmPassword"><i class="bi bi-eye"></i></span>
                </div>
              </div>
            </div>

            <!-- Contact Info -->
            <div class="form-section mt-4">
              <h5>Contact Details</h5>
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="phone" class="form-label">Phone Number</label>
                  <input type="tel" name="phone" class="form-control" id="phone" />
                </div>
                <div class="col-md-6">
                  <label for="dob" class="form-label">Date of Birth</label>
                  <input type="date" name="dob" class="form-control" id="dob" />
                </div>
                <div class="col-md-6">
                  <label for="gender" class="form-label">Gender</label>
                  <select name="gender" id="gender" class="form-select">
                    <option value="" selected disabled>Select gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                    <option value="Prefer not to say">Prefer not to say</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label for="emergencyContact" class="form-label">Emergency Contact</label>
                  <input type="tel" name="emergencyContact" class="form-control" id="emergencyContact" />
                </div>
              </div>
            </div>

            <!-- Address Info -->
            <div class="form-section mt-4">
              <h5>Address</h5>
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="addressStreet" class="form-label">Street</label>
                  <input type="text" name="addressStreet" class="form-control" id="addressStreet" />
                </div>
                <div class="col-md-6">
                  <label for="addressCity" class="form-label">City</label>
                  <input type="text" name="addressCity" class="form-control" id="addressCity" />
                </div>
              </div>
            </div>

            <!-- Job Info -->
            <div class="form-section mt-4">
              <h5>Job Details</h5>
              <div class="row g-3">
                <!-- Department Dropdown -->
                <div class="col-md-6">
                  <label for="department" class="form-label">Department</label>
                  <select name="department" id="department" class="form-select" required>
                    <option value="" disabled selected>Select Department</option>
                    <option value="HR">HR</option>
                    <option value="Finance">Finance</option>
                    <option value="IT">IT</option>
                    <option value="Sales">Sales</option>
                    <option value="Marketing">Marketing</option>
                  </select>
                </div>

                <!-- Designation Dropdown (Dynamic) -->
                <div class="col-md-6">
                  <label for="designation" class="form-label">Designation</label>
                  <select name="designation" id="designation" class="form-select" required>
                    <option value="" disabled selected>Select Designation</option>
                  </select>
                </div>

                <!-- Salary -->
                <div class="col-md-6">
                  <label for="salary" class="form-label">Salary (Rs)</label>
                  <input type="number" name="salary" class="form-control" id="salary" required min="0" step="0.01" />
                </div>

                <!-- Joining Date -->
                <div class="col-md-6">
                  <label for="joiningDate" class="form-label">Date of Joining</label>
                  <input type="date" name="joiningDate" class="form-control" id="joiningDate" required />
                </div>

                <!-- Marital Status -->
                <div class="col-md-6">
                  <label for="maritalStatus" class="form-label">Marital Status</label>
                  <select name="maritalStatus" id="maritalStatus" class="form-select">
                    <option value="Single">Single</option>
                    <option value="Married">Married</option>
                  </select>
                </div>
              </div>
            </div>
            <!-- Bank Info -->
            <div class="form-section mt-4">
              <h5>Bank Details</h5>
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="bankName" class="form-label">Bank Name</label>
                  <input type="text" name="bankName" class="form-control" id="bankName" />
                </div>
                <div class="col-md-6">
                  <label for="accountNumber" class="form-label">Account Number</label>
                  <input type="text" name="accountNumber" class="form-control" id="accountNumber" />
                </div>
                <div class="col-md-6">
                  <label for="pan" class="form-label">PAN Number</label>
                  <input type="text" name="pan" class="form-control" id="pan" />
                </div>
              </div>
            </div>

            <!-- Work Schedule -->
            <div class="form-section mt-4">
              <h5>Work Schedule</h5>
              <div class="row g-3">
                <div class="col-md-4">
                  <label for="workType" class="form-label">Work Type</label>
                  <select name="workType" id="workType" class="form-select" required>
                    <option value="" selected disabled>Select work type</option>
                    <option value="Full Time">Full Time</option>
                    <option value="Part Time">Part Time</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label for="startTime" class="form-label">Start Time</label>
                  <input type="time" name="startTime" class="form-control" id="startTime" />
                </div>
                <div class="col-md-4">
                  <label for="endTime" class="form-label">End Time</label>
                  <input type="time" name="endTime" class="form-control" id="endTime" />
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-4 text-end">
              <button type="submit" class="btn btn-primary px-4">Add Employee</button>
            </div>
          </form>
        </div>
      </main>
    </div>
  </div>

  <script src="./assets/script/script.js"></script>
  <script>
    const designations = {
      HR: ["HR Manager", "HR Officer", "Recruiter", "Training Officer", "Payroll Officer", "HR Assistant"],
      Finance: ["Finance Manager", "Accountant", "Accounts Officer", "Payroll Accountant", "Financial Analyst", "Internal Auditor", "Cashier"],
      IT: ["IT Manager", "Software Developer", "Web Developer", "System Administrator", "Network Engineer", "Database Administrator", "IT Support"],
      Sales: ["Sales Manager", "Sales Executive", "Business Development Officer", "Customer Relationship Manager", "Sales Coordinator", "Telemarketer"],
      Marketing: ["Marketing Manager", "Digital Marketing Specialist", "Social Media Manager", "Content Creator", "Graphic Designer", "SEO Specialist", "Brand Manager"]
    };

    const departmentSelect = document.getElementById("department");
    const designationSelect = document.getElementById("designation");

    // Disable designation until a department is selected
    designationSelect.disabled = true;

    departmentSelect.addEventListener("change", function() {
      const dept = this.value;
      designationSelect.innerHTML = '<option value="" disabled selected>Select Designation</option>';

      if (designations[dept]) {
        designations[dept].forEach(role => {
          const option = document.createElement("option");
          option.value = role;
          option.textContent = role;
          designationSelect.appendChild(option);
        });
        designationSelect.disabled = false; // enable once department is selected
      } else {
        designationSelect.disabled = true;
      }
    });
  </script>
</body>

</html>