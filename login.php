<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chia's Corner Login</title>
    <link rel="stylesheet" href="css/style.css">

    <!-- LOGO NI CHINA'S -->

    <link rel="icon" href="Capstone Assets/LogoMain.ico" sizes="any" type="image/png">

    <!-- STYLE SHEETS & NI CHINA'S -->

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700&display=swap" rel="stylesheet">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: url('Capstone Assets/Log-in Form BG (Version 3).png') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            padding: 20px;
        }

        /* Page transition loading screen */
        /* Page Loader */
        #pageLoader {
            position: fixed;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(5px);
            /* Adds a blur effect for better UX */
            z-index: 2000;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            visibility: hidden;
            /* Keep this to prevent interactions */
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        /* Alerts */
        .alerts {
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 10px;
            box-sizing: border-box;
            pointer-events: none;
            transition: top 0.5s;
            align-items: center;
            z-index: 3000 !important;
        }

        /* Loading Animation */

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Loading Text */

        .loading-text {
            font-size: 18px;
            font-weight: bold;
            color: black;
            text-transform: uppercase;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.2);
        }

        .container {
            display: flex;
            max-width: 900px;
            width: 100%;
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
        }

        .left {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 0;
        }

        .left img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .right {
            flex: 1;
            padding: 40px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            width: 195px;
            margin: 0 auto 15px;
        }

        .input-box {
            position: relative;
            width: 100%;
            margin: 10px 0;
        }

        .input-box input {
            width: 100%;
            padding: 12px;
            border: 2px solid black;
            border-radius: 8px;
            font-size: 16px;
            outline: none;
            padding-right: 40px;
        }

        .input-box input:focus {
            border-color: #FF9800;
            box-shadow: 0px 0px 8px rgba(255, 152, 0, 0.6);
        }

        .input-box input.incorrect {
            border-color: #f06272 !important;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 26%;
            cursor: pointer;
            font-size: 18px;
            color: gray;
            opacity: 0.6;
        }

        .toggle-password:hover {
            opacity: 1;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: #FFD428;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s ease-in-out;
            margin-top: 10px;
        }

        .login-btn:hover {
            background: #FF9800;
        }

        .forgot-pass {
            margin-top: 10px;
            color: gray;
            font-size: 14px;
            text-decoration: none;
            transition: 0.3s;
            border: none;
            outline: none;
            background: transparent;
            cursor: pointer;
        }

        .forgot-pass:hover {
            color: #FF9800;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .left {
                height: 250px;
            }

            .right {
                padding: 20px;
            }
        }
    </style>

</head>


<body>
    <!-- Page Transition Loader -->

    <div id="pageLoader">
        <div class="loader"></div>
        <div class="loading-text">Loading...</div>
    </div>

    <div class="alerts">
        <div id="success-alert" class="alert alert-success"></div>
        <div id="error-alert" class="alert alert-danger"></div>
        <div id="warning-alert" class="alert alert-warning"></div>
    </div>
    <div class="container">
        <div class="left">
            <img src="Capstone Assets/Log-in Form Poster.png" alt="Login Poster" class="left-img">
        </div>
        <div class="right">
            <form method="POST" id="loginForm" novalidate>
                <img src="Capstone Assets/LogoMain.png" alt="Chia's Corner" class="logo">
                <div class="input-box">
                    <input type="text" id="username" placeholder="Enter Your Username.." required>
                </div>
                <div class="input-box">
                    <input type="password" id="password" placeholder="Enter Your Password.." required>
                    <span class="toggle-password" onclick="togglePassword()">
                        <i id="eye-icon" class="fa-solid fa-eye"></i>
                    </span>
                </div>
                <button class="login-btn" type="submit" id="submitBtn">LOGIN</button>
                <button type="button" class="forgot-pass">Forgot Password?</button>
            </form>
        </div>
    </div>


    <script src="js/login.js"></script>
    <script>
        function togglePassword() {
            var passwordInput = document.getElementById("password");
            var eyeIcon = document.getElementById("eye-icon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }
    </script>
</body>

</html>
