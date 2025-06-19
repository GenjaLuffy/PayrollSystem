// late Time for add attendance

document.addEventListener("DOMContentLoaded", function () {
  const statusSelect = document.getElementById("status");
  const timeInputs = document.querySelectorAll(".time-input");

  function toggleTimeInputs() {
    const value = statusSelect.value;
    if (value === "Present" || value === "Late") {
      timeInputs.forEach((el) => (el.style.display = "block"));
    } else {
      timeInputs.forEach((el) => {
        el.style.display = "none";
        el.querySelector("input").value = "";
      });
    }
  }

  statusSelect.addEventListener("change", toggleTimeInputs);
  toggleTimeInputs();
});

// Password Show script

document
  .querySelector("#togglePassword")
  .addEventListener("click", function () {
    const password = document.querySelector("#password");
    const type =
      password.getAttribute("type") === "password" ? "text" : "password";
    password.setAttribute("type", type);
    this.firstElementChild.classList.toggle("bi-eye");
    this.firstElementChild.classList.toggle("bi-eye-slash");
  });

document
  .querySelector("#toggleConfirmPassword")
  .addEventListener("click", function () {
    const confirmPassword = document.querySelector("#confirmPassword");
    const type =
      confirmPassword.getAttribute("type") === "password" ? "text" : "password";
    confirmPassword.setAttribute("type", type);
    this.firstElementChild.classList.toggle("bi-eye");
    this.firstElementChild.classList.toggle("bi-eye-slash");
  });

// Edit-Profile picature Script

const input = document.getElementById("profilePicInput");
const img = document.getElementById("profilePicPreview");

input.addEventListener("change", (e) => {
  const file = e.target.files[0];
  if (file) {
    img.src = URL.createObjectURL(file);
  }
});

// leave View Script

document.addEventListener("DOMContentLoaded", function () {
  const modalReasonContent = document.getElementById("modalReasonContent");

  // Handle View Reason button
  document.querySelectorAll(".view-reason-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const leaveId = this.getAttribute("data-id");
      modalReasonContent.textContent = "Loading...";

      fetch(`get_reason.php?id=${leaveId}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            modalReasonContent.textContent = data.reason;
          } else {
            modalReasonContent.textContent = "Error: " + data.message;
          }
        })
        .catch(() => {
          modalReasonContent.textContent = "Failed to fetch reason.";
        });
    });
  });

  // Handle Approve/Reject button
  document.querySelectorAll(".update-status-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const leaveId = this.getAttribute("data-id");
      const newStatus = this.getAttribute("data-status");

      fetch("update_leave_status.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${leaveId}&status=${newStatus}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            document.querySelector(`.status-${leaveId}`).textContent =
              newStatus;
            // Disable both buttons after update
            button
              .closest("td")
              .querySelectorAll("button.update-status-btn")
              .forEach((btn) => (btn.disabled = true));
          } else {
            alert("Failed to update status: " + data.message);
          }
        })
        .catch(() => {
          alert("Error updating status.");
        });
    });
  });
});

//validate form to add employee

function validateForm() {
  const name = document.getElementById("fullName").value.trim();
  const phone = document.getElementById("phone").value.trim();
  const errorBox = document.getElementById("formMessage");
  errorBox.textContent = "";

  const nameValid = /^[A-Za-z\s]+$/.test(name);
  const phoneValid = /^(98|97|96)\d{8}$/.test(phone);

  if (!nameValid) {
    errorBox.textContent = "Full name must contain only letters and spaces.";
    return false;
  }

  if (phone && !phoneValid) {
    errorBox.textContent = "Please enter a valid Nepali phone number.";
    return false;
  }

  const pass = document.getElementById("password").value;
  const confirmPass = document.getElementById("confirmPassword").value;
  if (pass !== confirmPass) {
    errorBox.textContent = "Passwords do not match.";
    return false;
  }

  return true;
}
