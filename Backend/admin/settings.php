<?php
include './includes/header.php'
?>

        <!-- Main Content -->
        <main class="col-md-10 content">
            <h2>Settings</h2>
            <form method="post" action="/settings/save">

                <!-- Company Info -->
                <div class="section-title">Company Information</div>
                <div class="mb-3">
                    <label for="companyName" class="form-label">Company Name</label>
                    <input type="text" class="form-control" id="companyName" name="companyName" placeholder="Enter company name" required />
                </div>
                <div class="mb-3">
                    <label for="companyAddress" class="form-label">Company Address</label>
                    <input type="text" class="form-control" id="companyAddress" name="companyAddress" placeholder="Enter company address" />
                </div>
                <div class="mb-3">
                    <label for="contactEmail" class="form-label">Contact Email</label>
                    <input type="email" class="form-control" id="contactEmail" name="contactEmail" placeholder="Enter contact email" />
                </div>
                <div class="mb-3">
                    <label for="contactPhone" class="form-label">Contact Phone</label>
                    <input type="tel" class="form-control" id="contactPhone" name="contactPhone" placeholder="Enter contact phone" />
                </div>

                <!-- Payroll Settings -->
                <div class="section-title">Payroll Settings</div>
                <div class="mb-3">
                    <label for="salaryCycle" class="form-label">Salary Payment Cycle</label>
                    <select class="form-select" id="salaryCycle" name="salaryCycle" required>
                        <option value="monthly">Monthly</option>
                        <option value="bi-weekly">Bi-weekly</option>
                        <option value="weekly">Weekly</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="defaultCurrency" class="form-label">Default Currency</label>
                    <input type="text" class="form-control" id="defaultCurrency" name="defaultCurrency" placeholder="e.g. USD" maxlength="3" />
                </div>
                <div class="mb-3">
                    <label for="taxPercentage" class="form-label">Tax Percentage (%)</label>
                    <input type="number" class="form-control" id="taxPercentage" name="taxPercentage" min="0" max="100" step="0.01" />
                </div>
                <div class="mb-3">
                    <label for="overtimeMultiplier" class="form-label">Overtime Multiplier</label>
                    <input type="number" class="form-control" id="overtimeMultiplier" name="overtimeMultiplier" min="1" step="0.1" value="1.5" />
                </div>

                <!-- Attendance Settings -->
                <div class="section-title">Attendance Settings</div>
                <div class="mb-3">
                    <label for="workingHours" class="form-label">Working Hours per Day</label>
                    <input type="number" class="form-control" id="workingHours" name="workingHours" min="1" max="24" step="0.5" value="8" />
                </div>
                <div class="mb-3">
                    <label for="officeStartTime" class="form-label">Office Start Time</label>
                    <input type="time" class="form-control" id="officeStartTime" name="officeStartTime" value="09:00" />
                </div>
                <div class="mb-3">
                    <label for="officeEndTime" class="form-label">Office End Time</label>
                    <input type="time" class="form-control" id="officeEndTime" name="officeEndTime" value="18:00" />
                </div>
                <div class="mb-3">
                    <label for="lateMarkThreshold" class="form-label">Late Mark Threshold (minutes)</label>
                    <input type="number" class="form-control" id="lateMarkThreshold" name="lateMarkThreshold" min="0" max="60" step="1" value="10" />
                </div>

                <!-- Leave Management -->
                <div class="section-title">Leave Management</div>
                <div class="mb-3">
                    <label for="leaveTypes" class="form-label">Leave Types (comma separated)</label>
                    <input type="text" class="form-control" id="leaveTypes" name="leaveTypes" placeholder="e.g. Sick, Casual, Paid, Unpaid" />
                </div>
                <div class="mb-3">
                    <label for="leaveEntitlement" class="form-label">Leave Entitlement (days/year)</label>
                    <input type="number" class="form-control" id="leaveEntitlement" name="leaveEntitlement" min="0" max="365" />
                </div>
                <div class="mb-3">
                    <label for="carryForward" class="form-label">Carry Forward Leaves Allowed</label>
                    <select class="form-select" id="carryForward" name="carryForward">
                        <option value="yes">Yes</option>
                        <option value="no" selected>No</option>
                    </select>
                </div>

                <!-- User & Security -->
                <div class="section-title">User & Security</div>
                <div class="mb-3">
                    <label for="passwordPolicy" class="form-label">Password Policy</label>
                    <input type="text" class="form-control" id="passwordPolicy" name="passwordPolicy" placeholder="E.g. Min 8 chars, special characters required" />
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="enable2FA" name="enable2FA" />
                    <label class="form-check-label" for="enable2FA">Enable Two-Factor Authentication (2FA)</label>
                </div>

                <!-- Notifications -->
                <div class="section-title">Notifications</div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="notifyPayroll" name="notifyPayroll" />
                    <label class="form-check-label" for="notifyPayroll">Notify on Payroll Processing</label>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="notifyLeave" name="notifyLeave" />
                    <label class="form-check-label" for="notifyLeave">Notify on Leave Requests</label>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="notifyAttendance" name="notifyAttendance" />
                    <label class="form-check-label" for="notifyAttendance">Notify on Attendance Alerts</label>
                </div>

                <button type="submit" class="btn btn-primary mt-4">Save Settings</button>
            </form>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
