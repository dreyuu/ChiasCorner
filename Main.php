<?php include 'connection.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chia's Corner Dashboard</title>

    <!-- LOGO NI CHINA'S -->
    <link rel="icon" href="Capstone Assets/LogoMain.ico" sizes="any" type="image/png">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            padding: 13px 40px;
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
            gap: 10px;
        }

        .icons button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            transition: transform 0.2s ease-in-out;
        }

        .icons button:hover {
            transform: scale(1.1);
        }

        .icons img {
            width: 35px;
            height: 35px;
        }


        /* Charts Section */

        .charts-section {
            background: url('Capstone Assets/Log-in Form BG.png') center/cover no-repeat;
            padding: 5%;
        }

        .charts {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 50px;
            width: 30%;
            height: 300px;
        }

        .charts-section h1 {
            font-size: 3rem;
            font-weight: bold;
            font-family: Arial, sans-serif;
            color: white; 
            text-shadow: 
                3px 3px 5px rgba(0, 0, 0, 0.7), 
                -3px -3px 5px rgba(0, 0, 0, 0.7),
                3px -3px 5px rgba(0, 0, 0, 0.7),
                -3px 3px 5px rgba(0, 0, 0, 0.7);
        }

        .charts-section h2 {
            font-size: 2rem;
            font-weight: bold;
            color: white; 
            font-family: Arial;
            text-shadow: 
                3px 3px 5px rgba(0, 0, 0, 0.7), 
                -3px -3px 5px rgba(0, 0, 0, 0.7),
                3px -3px 5px rgba(0, 0, 0, 0.7),
                -3px 3px 5px rgba(0, 0, 0, 0.7); 
        }

        /* stats container */

        .exp {
            background: url('Capstone Assets/Log-in Form BG (Version 3).png') center/cover no-repeat;
            padding: 2%;
            width: 100%;
        }

        .chart-container.small {
            width: 60%; 
            height: 50%; 
            display: flex;
            align-items: center;
            flex-direction: column;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 3px 3px 8px rgba(0, 0, 0, 0.2);
        }   

        .chart-container.small h3 {
            margin-bottom: 2%;
            font-size: 1.5rem;
            color: black;
        }

        .stats-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            max-width: 1100px;
            margin-top: 20px;
            width: 100%;
            margin-left: 20%;
            margin-top: 2%;
            margin-bottom: 2%;
            
            
        }

        .sales-stats {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            width: 45%;
        }

        .stat-box {
            background: white;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            font-size: 18px;
            margin-right: 12%;
            font-weight: bold;
            width: 89%;
            color: black;
            box-shadow: 3px 3px 8px rgba(0, 0, 0, 0.2);
        }

        .stat-box span {
            display: block;
            font-size: 26px;
            margin-top: 1.6rem;
            background-color:  #FFD428;
            border-radius: 5px;
        }


        /* Footer Section */
        .footer {
            background: #141414;
            color: #FFD428;
            padding: 15px;
            font-size: 20px;
            font-weight: bold;
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

        /* Page transition loading screen */

        #pageLoader {
                position: fixed;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.6);
                backdrop-filter: blur(5px); 
                z-index: 3000;
                display: flex;
                justify-content: center;
                align-items: center;
                flex-direction: column;
                visibility: hidden;
                opacity: 0;
                transition: opacity 0.3s ease-in-out;
        }

        /* Spinning Loader */

            .loader {
                border: 6px solid rgba(255, 212, 40, 0.5);
                border-top: 6px solid black;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                animation: spin 1s linear infinite;
                margin-bottom: 10px;
        }

        /* Loading Animation */

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
        }   

        /* Loading Text */
        
            .loading-text {
                font-size: 18px;
                font-weight: bold;
                color: black;
                text-transform: uppercase;
                text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.2);
        }

        /* Dropdown container */

        .dropdown-container {
                position: relative;
                display: inline-block;
        }

        /* Style for MENUS button */

            .dropdown-btn {
                text-decoration: none;
                olor: black;
                font-weight: bold;
                padding: 10px 15px;
                display: inline-block;
        }

        /* Dropdown menu */

            .dropdown {
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
                background: #C18E2D;
                border: 3px solid black;
                border-radius: 10px;
                width: 100%;
                display: none;
                flex-direction: column;
                box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.3);
                padding: 10px 0;
        }

        /* Dropdown items */

            .dropdown-item {
                background: #FFD428;
                color: black;
                font-size: 0.7rem;
                text-align: center;
                padding: 12px;
                border: 3px solid black;
                border-radius: 40px !important;
                margin: 5px auto;
                width: 90%;
                text-decoration: none;
                transition: background 0.3s, transform 0.2s;
        }

        /* Hover effect */

            .dropdown-item:hover {
                background: darkred;
                color: white;
                transform: scale(1.05);
        }

        /* Show dropdown on hover */

            .dropdown-container:hover .dropdown {
                display: flex;
        }

        /* Smooth fade-in effect */

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(-5px);
            }
                to {
                    opacity: 1;
                    transform: translateY(0);
        }
    }

    /* Modal Background Blur */

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(8px);
            z-index: 998;
    }

    /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            width: 75%;
            max-width: 1100px;
            background: url('Capstone Assets/Log-in Form BG (Version 2).png') center/cover no-repeat;
            border-radius: 20px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            padding: 5%;
            z-index: 999;
            opacity: 0;
            transition: transform 0.3s ease-out, opacity 0.3s ease-out;
            
    }

    /* Active Modal (Show Animation) */
        .modal.show {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
    }

    /* Hide Animation */
        .modal.hide {
             transform: translate(-50%, -50%) scale(0.8);
            opacity: 0;
    }

    /* Modal Header */
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
    }   

    /* Back Button */

        .back-button {
            color: white;
            cursor: pointer;
            font-size: 20px;
            font-weight: bold;
            transition: transform 0.2s ease, opacity 0.3s ease;
            background: rgba(0, 0, 0, 0.75);
            padding: 10px 20px;
            border-radius: 10px;
            width: 15%;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.4);
    }

        .back-button:hover {
            transform: scale(1.05);
            opacity: 0.9;
    }


        .back-button:active {
            transform: scale(0.9);
    }

    /* NOTIFICATIONS titl */

        .title {
            font-size: 2.5rem;
            font-weight: bold;
            color: white;
            text-align: right;
            background: rgba(0, 0, 0, 0.75);
            padding: 8px 20px;
            border-radius: 10px;
            display: inline-block;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.4);
            margin-left: auto;
    }


    /* Table Styling */

        .notification-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 40px;
            
    }

        .notification-table th {
            background: #FFD233;
            padding: 15px;
            text-align: center;
            font-size: 18px;
            color: black;
            
            
    }

        .notification-table td {
            padding: 15px;
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            color: black;
            text-align: center;
            font-weight: bold;
    }

        .striped:nth-child(odd) {
            background: #FFF9D0;
    }

        .striped:nth-child(even) {
            background: #FFE799;
    }

    /* Action Buttons */
        .action-btn {
            padding: 8px 18px;
            border: 2px solid black;
            border-radius: 20px;
            cursor: pointer;
            background: white;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease-in-out;
    }

    /* Hover and Active Effects */
        .action-btn:hover {
            background: black;
            color: white;
            transform: scale(1.05);
    }

        .action-btn:active {
            transform: scale(0.95);
    }

    /* Responsive Design */
        @media (max-width: 768px) {
            .modal {
                width: 90%;
        }

            .back-button {
                font-size: 18px;
        }

            .title {
                font-size: 22px;
        }

            .notification-table th, .notification-table td {
                 font-size: 14px;
                padding: 10px;
        }

            .action-btn {
                font-size: 14px;
                padding: 6px 12px;
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

    <!-- Chian's Header with Navigation, Notification & Logout Icons -->

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
        <button id="notificationBtn">
            <img src="Capstone Assets/pngegg (12).png" alt="Notifications" class="notification-icon">
        </button>
        <button id="logoutBtn">
            <img src="Capstone Assets/logouticon.png" alt="Logout">
        </button>
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


    <!-- Chian's Charts Section -->

    <div class="charts-section">
        <h1>Welcome to Chia's Corner, MAEM!</h1>
        <div class="charts">
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="menuChart"></canvas>
            </div>
        </div>
        <h2>Total Sales This Month & Top Menu Picks</h2>
    </div>

    <!-- Chian's Stats Section -->

<div class= "exp">
    <div class="stats-container">
        <div class="sales-stats">
            <div class="stat-box">TOTAL SALES <span>₱ 103,230</span></div>
            <div class="stat-box">CUSTOMER SERVED <span>321</span></div>
            <div class="stat-box">BEST SELLER <span>SIZZLING HAKDOG</span></div>
            <div class="stat-box">TOTAL EXPENSES <span>₱ 25,325</span></div>
            <div class="stat-box">NET PROFIT <span>₱ 77,905</span></div>
        </div>

        <div class="chart-container small">
            <h3>SALES BREAKDOWN BY CATEGORY</h3>
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>


    <!-- Chian's Footer Section -->

    <footer class="footer">
        © 2023 Chia's Corner. All Rights Reserved. | Where Every Bite is Unlimited Delight
    </footer>


    <!-- Chian's JavaScript to Fetch Data & Update Charts -->

    <script>
        const salesChartCtx = document.getElementById('salesChart').getContext('2d');
        const menuChartCtx = document.getElementById('menuChart').getContext('2d');

        let salesChart = new Chart(salesChartCtx, {
            type: 'bar',
            data: { labels: [], datasets: [{ label: 'Sales', data: [], backgroundColor: 'blue' }] },
            options: { responsive: true }
        });

        let menuChart = new Chart(menuChartCtx, {
            type: 'pie',
            data: { labels: [], datasets: [{ data: [], backgroundColor: ['red', 'green', 'blue', 'yellow'] }] },
            options: { responsive: true }
        });

        function fetchChartData() {
            fetch('fetch_chart_data.php')
                .then(response => response.json())
                .then(data => {
                    salesChart.data.labels = data.sales.labels;
                    salesChart.data.datasets[0].data = data.sales.values;
                    salesChart.update();

                    menuChart.data.labels = data.menu.labels;
                    menuChart.data.datasets[0].data = data.menu.values;
                    menuChart.update();
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        fetchChartData();
        setInterval(fetchChartData, 5000);

        new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: ['Samgyupsal', 'Chicken Wings', 'Sizzlings', 'Others'],
            datasets: [{
                data: [35200, 28500, 22830, 16700],
                backgroundColor: ['#FFC107', '#DC3545', '#FF8C00', '#8B4513']
            }]
        }
    });

    // loading screen function 

     document.addEventListener("DOMContentLoaded", function () {
        const links = document.querySelectorAll("a"); 

        links.forEach(link => {
            link.addEventListener("click", function (event) {
                if (link.target !== "_blank" && link.href !== "#") { 
                    event.preventDefault(); 
                    document.getElementById("pageLoader").style.visibility = "visible";
                    document.getElementById("pageLoader").style.opacity = "1";

                    setTimeout(() => {
                        window.location.href = link.href; 
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

// Remove Notification when clicking "Remove" button

document.querySelectorAll('.action-btn.remove-btn').forEach(button => {
    button.addEventListener('click', function() {
        this.closest('tr').remove();
    });
});

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

</body>
</html>
