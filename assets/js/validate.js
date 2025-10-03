// validate.js
// Basic client-side form validation for registration and login

function validateForm() {
  // Registration form validation
  var username = document.forms[0]["username"].value.trim();
  var email = document.forms[0]["email"].value.trim();
  var password = document.forms[0]["password"].value;
  var confirm = document.forms[0]["confirm_password"].value;
  if (username.length < 3) {
    alert("Username must be at least 3 characters.");
    return false;
  }
  if (!email.match(/^[^@\s]+@[^@\s]+\.[^@\s]+$/)) {
    alert("Invalid email address.");
    return false;
  }
  if (password.length < 6) {
    alert("Password must be at least 6 characters.");
    return false;
  }
  if (password !== confirm) {
    alert("Passwords do not match.");
    return false;
  }
  return true;
}

function validateLogin() {
  // Login form validation
  var email = document.forms[0]["email"].value.trim();
  var password = document.forms[0]["password"].value;
  if (!email.match(/^[^@\s]+@[^@\s]+\.[^@\s]+$/)) {
    alert("Invalid email address.");
    return false;
  }
  if (password.length < 1) {
    alert("Password is required.");
    return false;
  }
  return true;
}
