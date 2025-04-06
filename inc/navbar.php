<?php
session_start();

// Prevent caching for this page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Expires: Thu, 19 Nov 1981 08:52:00 GMT"); // This is an old date in the past to prevent caching

// Check if the user is logged out
if (!isset($_SESSION['username']) && !isset($_SESSION['user_id']) && !isset($_SESSION['logged_in'])) {
    // Prevent caching if the user is logged out
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Chia's Corner Inventory </title>
    <link rel="stylesheet" href="css/style.css">

    <!-- LOGO NI CHINA'S -->
    <link rel="icon" href="Capstone Assets/LogoMain.ico" sizes="any" type="image/png">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700&display=swap" rel="stylesheet">

</head>

<body style="background: url('<?php echo $backgroundImage; ?>') center/cover;">

    <!-- Page Transition Loader -->

    <div id="pageLoader">
        <div class="loader"></div>
        <div class="loading-text">Loading...</div>
    </div>

    <div class="header">
        <div class="logo">CHIA'S <br> CORNER</div>
        <div class="nav">
            <a href="Main.php">HOME</a>

            <div class="dropdown-container">
                <a href="Menu.php" class="dropdown-btn">MENUS</a>
                <div class="dropdown">
                    <a href="orderlist.php" class="dropdown-item">ORDER LIST</a>
                    <a href="orderhistory.php" class="dropdown-item">ORDER HISTORY</a>
                </div>
            </div>

            <a href="Sales.php">SALES</a>
            <a href="Inventory.php">INVENTORY</a>
            <a href="account.php">ACCOUNTS</a>
        </div>
        <div class="icons">
            <img src="Capstone Assets/pngegg (12).png" alt="Notifications" class="notification-icon">
            <a href="inc/logout.php">
                <img src="Capstone Assets/logouticon.png" alt="Logout">
            </a>
        </div>
    </div>

    <!-- Background Blur Overlay -->

    <div id="modalOverlay" class="modal-overlay"></div>

    <!-- Notification Modal -->

    <div id="notificationModal" class="modal">
        <div class="modal-header">
            <div class="back-button" onclick="closeModal()">BACK</div>
            <div class="title">NOTIFICATIONS</div>
        </div>

        <table class="notification-table">
            <thead>
                <tr>
                    <th>Messages</th>
                    <th>Date & Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr class="striped">
                    <td>Your Bulgogi is almost expired.</td>
                    <td>February 20, 2025 / 10:23PM</td>
                    <td>
                        <button class="action-btn" onclick="removeNotification(this)">Remove</button>
                        <button class="action-btn">View</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        // loading screen function 

        document.addEventListener("DOMContentLoaded", function() {
            const links = document.querySelectorAll("a");

            links.forEach(link => {
                link.addEventListener("click", function(event) {
                    if (link.target !== "_blank" && link.href !== "#") {
                        event.preventDefault();
                        document.getElementById("pageLoader").style.visibility = "visible";
                        document.getElementById("pageLoader").style.opacity = "1";

                        setTimeout(() => {
                            window.location.href = link.href;
                            document.getElementById("pageLoader").style.visibility = "hidden";
                            document.getElementById("pageLoader").style.opacity = "0";
                        }, 1000);

                    }
                });
            });
        });


        // Open Modal when clicking the notification icon

        document.querySelector('.notification-icon').addEventListener('click', function() {
            document.getElementById('modalOverlay').style.display = 'block';
            document.getElementById('notificationModal').style.display = 'block';
        });

        // Close Modal when clicking the back button

        document.querySelector('.back-button').addEventListener('click', function() {
            document.getElementById('modalOverlay').style.display = 'none';
            document.getElementById('notificationModal').style.display = 'none';
        });

        // Function to open the modal

        function openModal() {
            document.getElementById('modalOverlay').style.display = 'block';
            document.getElementById('notificationModal').style.display = 'block';
        }

        // Function to close the modal

        function closeModal() {
            document.getElementById('modalOverlay').style.display = 'none';
            document.getElementById('notificationModal').style.display = 'none';
        }

        // View Notification when clicking "View" button

        document.querySelectorAll('.action-btn.view-btn').forEach(button => {
            button.addEventListener('click', function() {
                alert("Viewing notification: " + this.closest('tr').querySelector('.message-text').innerText);
            });
        });


        // Open Modal with animation

        document.querySelector('.notification-icon').addEventListener('click', function() {
            let modal = document.getElementById('notificationModal');
            let overlay = document.getElementById('modalOverlay');

            overlay.style.display = 'block';
            modal.style.display = 'block';

            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        });

        // Close Modal with animation

        document.querySelector('.back-button').addEventListener('click', function() {
            let modal = document.getElementById('notificationModal');
            let overlay = document.getElementById('modalOverlay');

            modal.classList.remove('show');
            modal.classList.add('hide');

            setTimeout(() => {
                modal.style.display = 'none';
                overlay.style.display = 'none';
                modal.classList.remove('hide');
            }, 300);
        });
    </script>
