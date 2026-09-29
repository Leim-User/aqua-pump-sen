// Temporary frontend login.
// Real authentication will be handled by PHP + MySQL.

const loginForm = document.getElementById("loginForm");
const signUpForm = document.getElementById("signUpForm");
const loginMessage = document.getElementById("loginMessage");
const forgotPassword = document.getElementById("register");

loginForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();

    if (!email || !password) {
        loginMessage.textContent = "Please enter your email and password.";
        loginMessage.style.color = "#ef4444";
        return;
    }

    loginMessage.textContent =
        "Demo login successful. PHP authentication is not connected yet.";
    loginMessage.style.color = "#18c889";
});

forgotPassword.addEventListener("click", function (event) {
    event.preventDefault();

    loginMessage.textContent =
        "Password recovery will be connected to the PHP backend.";
    loginMessage.style.color = "#8fa3ba";
});