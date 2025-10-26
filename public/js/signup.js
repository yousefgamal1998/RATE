// RATE - Signup Page JavaScript
// JavaScript functionality for the signup page

document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("signupForm");
  if (!form) return;

  const password = document.getElementById("password");
  const confirmPassword = document.getElementById("confirmPassword");
  const submitBtn = document.querySelector('button[type="submit"]');

  // Password validation
  function validatePassword() {
    const passwordValue = password.value;
    const confirmValue = confirmPassword.value;

    if (passwordValue.length < 8) {
      password.classList.add("border-rate-red");
      password.classList.remove("border-white/20");
      return false;
    } else {
      password.classList.remove("border-rate-red");
      password.classList.add("border-white/20");
    }

    if (passwordValue !== confirmValue) {
      confirmPassword.classList.add("border-rate-red");
      confirmPassword.classList.remove("border-white/20");
      return false;
    } else {
      confirmPassword.classList.remove("border-rate-red");
      confirmPassword.classList.add("border-white/20");
    }

    return true;
  }

  // Real-time validation
  if (password) password.addEventListener("input", validatePassword);
  if (confirmPassword)
    confirmPassword.addEventListener("input", validatePassword);

  // Form submission
  form.addEventListener("submit", function (e) {
    e.preventDefault();

    if (!validatePassword()) {
      alert("Please check your password requirements");
      return;
    }

    // Disable button and show loading
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = "Creating Account...";
    }

    // Simulate account creation
    setTimeout(() => {
      alert("Account created successfully! Welcome to RATE");
      // Redirect to main page or dashboard
      window.location.href = "index.php";
    }, 2000);
  });

  // Form validation on input
  const inputs = form.querySelectorAll("input");
  inputs.forEach((input) => {
    input.addEventListener("blur", function () {
      if (this.value.trim() === "") {
        this.classList.add("border-rate-red");
        this.classList.remove("border-white/20");
      } else {
        this.classList.remove("border-rate-red");
        this.classList.add("border-white/20");
      }
    });
  });
});
