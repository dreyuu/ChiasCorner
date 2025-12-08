<?php
// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables ONCE
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
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

    <!-- PDF DOWNLOAD -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

</head>

<body style=" background: url('<?php echo $backgroundImage; ?>') center/cover;">

    <!-- Page Transition Loader -->

    <div id="pageLoader">
        <div class="loader"></div>
        <div class="loading-text">Loading...</div>
    </div>

    <!-- Mobile Header -->
    <div class="mobile-header">
        <div class="mobile-header-logo">
            <img src="Capstone Assets/LogoMain.png" alt="Chia's Corner" class="sidebar-logo-img">
            <a href="main.php" class="sidebar-logo">CHIA'S <br> CORNER</a>
        </div>
        <div class="hamburger" id="hamburger">&#9776;</div>
    </div>

    <!-- Sidebar for Mobile -->
    <div class="sidebar" id="sidebar">
        <div class="mobile-header-logo">
            <img src="Capstone Assets/LogoMain.png" alt="Chia's Corner" class="sidebar-logo-img">
            <h1 href="main.php" class="sidebar-logo">CHIA'S <br> CORNER</h1>
        </div>
        <a href="Main.php">HOME</a>
        <div class="sidebar-dropdown-container">
            <button class="dropdown-btn">MENUS</button>
            <div class="sidebar-dropdown">
                <a href="Menu.php" class="dropdown-item">TAKE ORDER</a>
                <a href="orderlist.php" class="dropdown-item">ORDER LIST</a>
                <a href="orderhistory.php" class="dropdown-item">ORDER HISTORY</a>
            </div>
        </div>
        <div class="admin mobile-admin">
            <a href="Sales.php">SALES</a>
            <!-- <a href="Inventory.php">INVENTORY</a> -->
            <a href="account.php">ACCOUNTS</a>
        </div>
        <a href="#" id="mobile-logout">
            <img src="Capstone Assets/logouticon.png" alt="Logout" class="logout-icon">
            Logout
        </a>
    </div>

    <div class="sidebar-overlay">
    </div>

    <div class="header">
        <a href="main.php" class="logo">CHIA'S <br> CORNER</a>
        <div class="nav">
            <a href="Main.php">HOME</a>
            <div class="dropdown-container">
                <button class="dropdown-btn">MENUS</button>
                <div class="dropdown">
                    <a href="Menu.php" class="dropdown-item">TAKE ORDER</a>
                    <a href="orderlist.php" class="dropdown-item">ORDER LIST</a>
                    <a href="orderhistory.php" class="dropdown-item">ORDER HISTORY</a>
                </div>
            </div>
            <div class="admin">
                <a href="Sales.php">SALES</a>
                <!-- <a href="Inventory.php">INVENTORY</a> -->
                <a href="account.php">ACCOUNTS</a>
            </div>
        </div>
        <div class="icons">
            <img src="Capstone Assets/pngegg (12).png" alt="Notifications" class="notification-icon" style="display: none;">
            <a href="#" id="logout-btn" class="logout-btn">
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
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="notification-body">

            </tbody>
        </table>
    </div>

    <?php include 'components/alert_component.php'; ?>
    <!-- <script src="/js/navbar.js"></script> -->
    <!-- Load Pusher JS library first -->
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>

    <!-- Then load your manager -->
    <script src="js/pusher_manager.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebarOverlay = document.querySelector('.sidebar-overlay');
            hamburger.addEventListener('click', () => {
                sidebar.classList.toggle('show');
                sidebarOverlay.style.display = 'flex';
            });

            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                sidebarOverlay.style.display = 'none';
            })
            // Optional: close sidebar when clicking a link
            document.querySelectorAll('#sidebar a').forEach(link => {
                link.addEventListener('click', () => {
                    sidebar.classList.remove('show');
                });
            });

            window.addEventListener("pageshow", function(event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });

            const dropdownContainer = document.querySelector('.dropdown-container')

            dropdownContainer.addEventListener('click', function(e) {
                e.preventDefault();
                dropdownContainer.classList.toggle('show');
            })

            const sidebarDropdownContainer = document.querySelector('.sidebar-dropdown-container')

            sidebarDropdownContainer.addEventListener('click', function(e) {
                e.preventDefault();
                sidebarDropdownContainer.classList.toggle('show');
            })

            const token = localStorage.getItem("jwt_token");
            // console.log("JWT Token: ", token);
            if (!token) {
                // Redirect to login page if token is not present
                // console.log("No token found. Redirecting to login page.");
                window.location.href = "index.php";
                return
            }
            try {
                const payloadBase64 = token.split('.')[1];
                const payloadJson = atob(payloadBase64);
                const payload = JSON.parse(payloadJson);

                const currentTime = Math.floor(Date.now() / 1000); // current time in seconds

                if (payload.exp && currentTime > payload.exp) {
                    // Token is expired
                    console.warn("Token expired. Redirecting to login.");
                    localStorage.removeItem("jwt_token");
                    window.location.href = "index.php";
                    return;
                }

                // Optional: Handle showing admin nav
                const admin = document.querySelectorAll('.admin');
                if (payload.user_type === 'admin' || payload.user_type === 'dev') {
                    admin.forEach(function(element) {
                        element.classList.add('show-nav');
                    });
                } else {
                    admin.forEach(function(element) {
                        element.classList.remove('show-nav');
                    });
                }


            } catch (e) {
                console.error("Invalid token or decoding failed.", e);
                localStorage.removeItem("jwt_token");
                window.location.href = "index.php";
            }



            const mobileLogoutBtn = document.getElementById("mobile-logout");

            if (mobileLogoutBtn) {
                mobileLogoutBtn.addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent default action

                    CustomAlert.confirm("Are you sure you want to logout?").then(result => {
                        if (!result) return;
                        // Remove JWT token from local storage // Remove JWT token from local storage
                        localStorage.removeItem("jwt_token");

                        // Redirect to the login page after logout
                        window.location.href = "index.php";
                    })
                })
            }

            const logoutBtn = document.getElementById("logout-btn");

            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent default action
                    CustomAlert.confirm("Are you sure you want to logout?").then(result => {
                        if (!result) return;
                        // Remove JWT token from local storage // Remove JWT token from local storage
                        localStorage.removeItem("jwt_token");

                        // Redirect to the login page after logout
                        window.location.href = "index.php";
                    })

                })
            }

            // Function to refresh the access token using the refresh token
            function refreshAccessToken() {
                const refreshToken = localStorage.getItem('refresh_token');

                if (!refreshToken) {
                    // No refresh token, redirect to login
                    window.location.href = 'index.php';
                    return;
                }

                // Make an API request to refresh the access token
                fetch('db_queries/update_queries/refresh_token.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            refresh_token: refreshToken
                        }),
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Store the new access token
                            localStorage.setItem('jwt_token', data.token);
                        } else {
                            // Token refresh failed, redirect to login
                            window.location.href = 'index.php';
                        }
                    })
                    .catch(error => {
                        console.error('Error refreshing token:', error);
                    });
            }

            // Function to check if the JWT is about to expire
            function checkTokenExpiration() {
                const token = localStorage.getItem('jwt_token');
                if (!token) return;

                const payloadBase64 = token.split('.')[1];
                const payloadJson = atob(payloadBase64);
                const payload = JSON.parse(payloadJson);

                const expTime = payload.exp * 1000; // Convert to milliseconds
                const currentTime = Date.now();

                // If the token is about to expire in 10 minutes or already expired, refresh it
                if (expTime - currentTime <= 10 * 60 * 1000) {
                    refreshAccessToken();
                }
            }

            // Run the token expiration check on page load and periodically
            document.addEventListener('DOMContentLoaded', () => {
                checkTokenExpiration();
                setInterval(checkTokenExpiration, 5 * 60 * 1000); // Check every 5 minutes
            });

        });

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
            markNotificationsAsRead();
            loadNotifications();
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

        function runCleanup() {
            fetch('db_queries/insert_queries/fetch_wastage.php') // 🔁 Change this to your actual PHP file path
                .then(response => response.json())
                .then(data => {
                    // if (data.success) {
                    //     console.log("✅ Cleanup ran successfully:", data.message);
                    // } else {
                    //     console.error("⚠️ Cleanup failed:", data.message);
                    // }
                })
                .catch(error => console.error("❌ Error running cleanup:", error));
        }

        // Calculate the delay until the next midnight
        function scheduleMidnightCleanup() {
            const now = new Date();
            const midnight = new Date();

            midnight.setHours(24, 0, 0, 0); // Set to 12:00 AM the next day
            const delay = midnight.getTime() - now.getTime();

            // Run cleanup at the next midnight
            setTimeout(() => {
                runCleanup(); // Run once at midnight
                setInterval(runCleanup, 24 * 60 * 60 * 1000);
                loadNotifications(); // Load notifications after cleanup
                // setInterval(runCleanup, 5000);
            }, delay);
        }

        runCleanup();
        scheduleMidnightCleanup();

        function loadNotifications() {
            fetch("db_queries/select_queries/fetch_notification.php")
                .then((response) => response.json())
                .then((data) => {
                    const tbody = document.getElementById("notification-body");
                    tbody.innerHTML = "";

                    if (data.success && data.notifications.length > 0) {
                        data.notifications.forEach((notif) => {
                            const row = document.createElement("tr");
                            row.classList.add("striped");

                            const createdAt = new Date(notif.created_at);
                            const formattedDate = createdAt.toLocaleString("en-US", {
                                year: "numeric",
                                month: "long",
                                day: "2-digit",
                                hour: "2-digit",
                                minute: "2-digit",
                                hour12: true,
                            });

                            row.innerHTML = `
                        <td>${notif.message}</td>
                        <td>${formattedDate}</td>
                        <td>${notif.status}</td>
                    `;
                            tbody.appendChild(row);
                        });
                    } else {
                        tbody.innerHTML = "<tr><td colspan='3'>No new notifications</td></tr>";
                    }
                })
                .catch((error) => {
                    console.error("Error fetching notifications:", error);
                });
        }

        loadNotifications();

        function markNotificationsAsRead() {
            fetch("db_queries/update_queries/update_notification.php", {
                    method: "POST",
                })
                .then((res) => res.json())
                .then((data) => {
                    if (!data.success) {
                        console.warn("Failed to mark as read:", data.message);
                    }
                })
                .catch((err) => console.error("Error updating notifications:", err));
        }
    </script>
