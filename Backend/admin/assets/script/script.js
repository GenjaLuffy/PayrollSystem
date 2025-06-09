// late Time for add attendance
 
 document.addEventListener("DOMContentLoaded", function () {
      const statusSelect = document.getElementById("status");
      const timeInputs = document.querySelectorAll(".time-input");

      function toggleTimeInputs() {
        const value = statusSelect.value;
        if (value === "Present" || value === "Late") {
          timeInputs.forEach(el => el.style.display = "block");
        } else {
          timeInputs.forEach(el => {
            el.style.display = "none";
            el.querySelector("input").value = "";
          });
        }
      }

      statusSelect.addEventListener("change", toggleTimeInputs);
      toggleTimeInputs(); // Initial check on page load
    });



// Password Show script
      document.querySelector('#togglePassword').addEventListener('click', function () {
      const password = document.querySelector('#password');
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.firstElementChild.classList.toggle('bi-eye');
      this.firstElementChild.classList.toggle('bi-eye-slash');
    });

    document.querySelector('#toggleConfirmPassword').addEventListener('click', function () {
      const confirmPassword = document.querySelector('#confirmPassword');
      const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
      confirmPassword.setAttribute('type', type);
      this.firstElementChild.classList.toggle('bi-eye');
      this.firstElementChild.classList.toggle('bi-eye-slash');
    });