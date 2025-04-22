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
        loginForm.addEventListener("submit", (e) => {
            e.preventDefault();

            let errors = [];
            errors = authenticateUser(username.value, password.value);

            if (errors.length > 0) {
                showAlert("warning-alert", errors.join(" | "));
                return;
            }

            let formData = new FormData();
            formData.append("username", username.value);
            formData.append("password", password.value);

            fetch("db_queries/select_queries/get_user.php", {
                method: "POST",
                body: formData
            })
                .then((response) => response.json())
                .then((data) => {
                    try { 
                        if (data.success) {
                            showAlert("success-alert", "Login successful!");
                            // show loading screen
                            localStorage.setItem('jwt_token', data.token);
                            pageLoader();
                            setTimeout(() => {
                                loginForm.reset(); // Reset the form
                                window.location.href = "main.php";
                            }, 3000);
                        } else {
                            showAlert("warning-alert", "Invalid username or password");
                            loginForm.reset(); // Reset the form
                        }
                    } catch (error) {
                        console.error(error);
                        showAlert("warning-alert", "An error occurred. Please try again later");
                        loginForm.reset(); // Reset the form
                    }
                })
                .catch((error) => {
                    console.error(error);
                    showAlert("error-alert", "An error occurred. Please try again later");
                    loginForm.reset(); // Reset the form
                })

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


