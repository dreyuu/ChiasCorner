<?php include 'connection.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chia's Corner Menu</title>

    <!-- LOGO NI CHINA'S -->
    <link rel="icon" href="Capstone Assets/LogoMain.ico" sizes="any" type="image/png">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700&display=swap" rel="stylesheet">


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        /* Fade-in animation on page load */

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        body {
            background: #000;
            color: #fff;
            text-align: center;
        }

        .header {
            background: #FFD428;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            position: sticky;
            top: 0;
            width: 100%;
             z-index: 1000;
        }


        .logo {
            font-size: 24px;
            font-weight: bold;
            color: black;
            text-transform: uppercase;
            text-align: center;
            line-height: 1;
            margin-left: 1%;
        }

        .logo span {
            display: block;
            font-size: 16px;
        }

        /* Navigation Bar */

        .nav {
            display: flex;
            gap: 15px;
        }

        .nav a {
            text-decoration: none;
            background: #FFD428;
            color: black;
            padding: 13px 15px;
            border-radius: 5px;
            border: solid 2px black;
            font-weight: bold;
            transition: all 0.3s ease-in-out;
        }

        .nav a:hover {
            background: black;
            color: #FFD428;
        }

        /* Icons */
        
        .icons {
            display: flex;
            gap: 15px;
        }

        .icons img {
            width: 35px;
            height: 35px;
            cursor: pointer;
            transition: transform 0.3s ease-in-out;
        }

        .icons img:hover {
            transform: scale(1.1);
        }

        /* Notification Bell Animation */
        .notification-icon {
            position: relative;
            animation: bellShake 1s infinite alternate;
        }

        @keyframes bellShake {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(10deg); }
        }

        @media (max-width: 768px) {
            .charts {
                flex-direction: column;
                align-items: center;
            }

            .contacts {
                flex-direction: column;
                text-align: center;
            }

            .contacts-image img {
                margin-top: 10px;
                width: 150px;
            }
        }

    </style>
</head>

<body>

<!-- Chian's Header with Navigation, Notification & Logout Icons -->

<div class="header">
        <div class="logo">
            CHIA'S <br> CORNER
        </div>
        <div class="nav">
            <a href="Main.php">HOME</a>
            <a href="Menu.php">MENUS</a>
            <a href="Sales.php">SALES</a>
            <a href="Inventory.php">INVENTORY</a>
            <a href="account.php">ACCOUNTS</a>
        </div>
        <div class="icons">
            <img src="Capstone Assets/pngegg (12).png" alt="Notifications" class="notification-icon">
            <img src="Capstone Assets/logouticon.png" alt="Logout">
        </div>
 </div>
    
</body>
</html>