const loginForm =
    document.getElementById("loginForm");

const password =
    document.getElementById("password");

const togglePassword =
    document.getElementById("togglePassword");

const loginError =
    document.getElementById("loginError");

if (togglePassword && password) {

    /* Show / hide password */

    togglePassword.addEventListener(
        "click",
        function () {

            if (password.type === "password") {

                password.type = "text";

                togglePassword.textContent =
                    "Hide";

            } else {

                password.type = "password";

                togglePassword.textContent =
                    "Show";
            }

        }
    );
}

if (loginForm && password && loginError) {

    /* Login */

    loginForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();

            const email =
                document.getElementById("email").value.trim();

            const passwordValue =
                password.value.trim();

            if (!email || !passwordValue) {

                loginError.textContent =
                    "Please enter your email and password.";

                loginError.style.display =
                    "block";

                return;
            }

            /*
             * Temporary frontend login.
             *
             * This will later be replaced by:
             *
             * POST /api/auth/login
             *
             * when we connect the Node.js backend.
             */

            if (
                email === "admin@aquapump.com" &&
                passwordValue === "admin123"
            ) {

                window.location.href =
                    "index.html";

            } else {

                loginError.textContent =
                    "Invalid email or password.";

                loginError.style.display =
                    "block";
            }

        }
    );
}