document.addEventListener("DOMContentLoaded", function () {
    // Your JS code here

    const loginForm = document.getElementById("loginForm");
    const username = document.getElementById("username");
    const password = document.getElementById("password");
    const submitBtn = document.getElementById("submitBtn");

    function authenticateUser(username_input, password_input) {
        let errors = [];
        if (username_input === '' || username_input == null) {
            errors.push('Username is required.')
            username.classList.add("incorrect");
        }
        if (password_input === '' || password_input == null) {
            errors.push('Password is required.')
            password.classList.add("incorrect");
        }
        return errors;
    }

    const allInputs = [username, password].filter(input => input != null)

    allInputs.forEach(input => {
        input.addEventListener('input', () => {
            if (input.classList.contains('incorrect')) {
                input.classList.remove('incorrect')
            }
        })
    });

    if (loginForm) {
        const maxAttempts = 5;
        const cooldownTime = 30000; // 30 seconds
        const submitBtn = document.getElementById('submitBtn');

        // Load stored values
        let attemptCounter = parseInt(localStorage.getItem('login_attempts')) || 0;
        let cooldownEnd = parseInt(localStorage.getItem('login_cooldown')) || 0;

        // Check cooldown and update button
        const checkCooldown = () => {
            const now = Date.now();

            if (cooldownEnd > now) {
                const remaining = Math.ceil((cooldownEnd - now) / 1000);
                showAlert("warning-alert", `Too many failed attempts. Try again in ${remaining} seconds.`);
                submitBtn.disabled = true;
                submitBtn.textContent = `Wait ${remaining}s`;

                // Start live countdown on button
                const interval = setInterval(() => {
                    const rem = Math.ceil((cooldownEnd - Date.now()) / 1000);
                    if (rem > 0) {
                        submitBtn.textContent = `${rem}s`;
                    } else {
                        clearInterval(interval);
                        submitBtn.disabled = false;
                        submitBtn.textContent = "LOGIN";
                        localStorage.removeItem('login_cooldown');
                        attemptCounter = 0;
                        localStorage.setItem('login_attempts', attemptCounter);
                    }
                }, 1000);

                return true;
            } else {
                // cooldown expired
                submitBtn.disabled = false;
                submitBtn.textContent = "LOGIN";
                localStorage.removeItem('login_cooldown');
                attemptCounter = parseInt(localStorage.getItem('login_attempts')) || 0;
                return false;
            }
        };

        // Run cooldown check on page load
        checkCooldown();

        loginForm.addEventListener("submit", (e) => {
            e.preventDefault();

            if (checkCooldown()) return;

            let errors = [];
            errors = authenticateUser(username.value, password.value);

            if (errors.length > 0) {
                showAlert("warning-alert", errors.join(" | "));
                return;
            }

            let formData = new FormData();
            formData.append("username", username.value);
            formData.append("password", password.value);

            loader.show();

            fetch("db_queries/select_queries/get_user.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    try {
                        if (data.success) {
                            showAlert("success-alert", "Login successful!");
                            localStorage.setItem('jwt_token', data.token);
                            pageLoader();

                            setTimeout(() => {
                                loginForm.reset();
                                loader.hide();
                                window.location.href = "Main.php";
                            }, 3000);

                            // Reset attempts on success
                            attemptCounter = 0;
                            localStorage.removeItem('login_attempts');
                            localStorage.removeItem('login_cooldown');
                            submitBtn.disabled = false;
                            submitBtn.textContent = "LOGIN";
                        } else {
                            attemptCounter++;
                            localStorage.setItem('login_attempts', attemptCounter);

                            // Show attempt number in alert
                            showAlert("warning-alert", `${data.message} | Attempt ${attemptCounter} of ${maxAttempts}`);
                            loginForm.reset();
                            loader.hide();

                            if (attemptCounter >= maxAttempts) {
                                const now = Date.now();
                                cooldownEnd = now + cooldownTime;
                                localStorage.setItem('login_cooldown', cooldownEnd);
                                checkCooldown(); // start cooldown immediately
                            }
                        }
                    } catch (error) {
                        console.error(error);
                        showAlert("warning-alert", error.message || "An error occurred. Please try again later");
                        loginForm.reset();
                    }
                })
                .catch(error => {
                    console.error(error);
                    showAlert("error-alert", error.message || "An error occurred. Please try again later");
                    loginForm.reset();
                });
        });
    }


    function showAlert(alertId, message) {
        let alertBox = document.getElementById(alertId);
        if (alertBox) {
            alertBox.innerText = message;
            alertBox.style.visibility = "visible";  // Make alert visible
            alertBox.style.opacity = 1;             // Fade in
            alertBox.style.top = "0";              // Slide down

            setTimeout(() => {
                alertBox.style.opacity = 0;        // Fade out
                alertBox.style.top = "-70px";      // Slide up

                setTimeout(() => {
                    alertBox.style.visibility = "hidden";  // Hide alert after fading out
                    alertBox.innerText = "";               // Clear alert message
                }, 300);  // Delay visibility change to allow for the fade-out effect
            }, 3000);  // Alert stays for 3 seconds
        }
    }

    function pageLoader() {
        document.getElementById("pageLoader").style.visibility = "visible";
        document.getElementById("pageLoader").style.opacity = "1";

        setTimeout(() => {
            document.getElementById("pageLoader").style.visibility = "hidden";
            document.getElementById("pageLoader").style.opacity = "0";
        }, 3000);
    }

});




