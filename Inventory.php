<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Chia's Corner Inventory </title>
    
    <!-- LOGO NI CHINA'S -->
    <link rel="icon" href="Capstone Assets/LogoMain.ico" sizes="any" type="image/png">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        html, body {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Fade-in animation on page load */

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        body {
            background: url('Capstone Assets/Log-in Form BG (Version 2).png') center/cover no-repeat;
            color: black;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
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

        /* Inventory Form */

        .inventory-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            border-radius: 12px;
            flex: 1;
        }

        .form-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: rgba(58, 57, 57, 0.9);
            border: solid 3px black;
            border-radius: 12px;
            color: white;
            position: relative;
        }

        .form-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            width: 70%;
            position: relative;
        }

        .form-group input,
        .form-group select {
            padding: 10px;
            border-radius: 6px;
            border: none;
            width: calc(45% - 5px);
            text-align: center;
        }

        .search-container button {
            background: #FFD428;
            border: solid 2px black;
        }

        select {
            background-color: #f8f8f8;
            border: 2px solid #ccc;
            border-radius: 5px;
            padding: 8px;
            font-size: 16px;
            color: black;
            cursor: pointer;
            width: 20%;
            transition: all 0.3s ease-in-out; 
            border-radius: 5rem !important;
            font-weight: bold;
        }

        /* Hover effect */

        select:hover {
            background-color: #e6e6e6;
            border-color: #999;
        }

        /* Focus effect */

        select:focus {
            outline: none;
            transform: scale(1.05);
        }

        /* Dropdown options */

        select option {
            background-color: black;
            color: #FFD428;
            font-size: 14px;
            padding: 10px;
            text-align: left;
            opacity: 0; 
            transition: opacity 0.3s ease-in-out; 
        }      


        select:focus option {
            opacity: 1; 
        }


        /* Add to Inventory Button */

        .add-btn {
            background: #FFD428;
            padding: 15px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            border-radius: 12px;
            width: 90%;
            text-align: center;
            font-size: 1.5rem;
            margin-top: 10px;
            transition: 0.3s;
            box-shadow: 3px 3px 8px rgba(0, 0, 0, 0.2);
        }

        .add-btn:hover {
            background: black;
            color: #FFD428;
        }

        /* Total Items Box */

        .total-items {
            padding: 20px;
            border-radius: 12px;
            width: 25%;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }

        .total-items h2 {
            margin: 0;
            font-size: 2.3rem;
            color: white;
        }

        .total-items span {
            font-size: 2.3rem;
            display: block;
            color: white;
            margin-top: 5px;
        }

        .waster {
            background: #FFD428 !important;
            color: black !important;
            padding: 10px 15px;
            border-radius: 5rem !important;
            width: 10%;
            font-size: 0.9rem;
            border: 2px solid black;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        .waster:hover {
            background: darkred;
            transform: scale(1.05);
        }       

        /* Search & Filters */
        
        .search-container {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-top: 20px;
            padding: 15px;
            border-radius: 12px;
            gap: 10px;
        }

        .search-container input {
            padding: 12px;
            border-radius: 6px;
            border: 2px solid black;
            text-align: left;
            width: 400px;
            border-radius: 5rem !important;
        }

        .search-container button,
        .search-container select {
            padding: 10px;
            border-radius: 6px;
            border: 2px solid black;
            text-align: center;
        }

        .search-container button {
            background: black;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        /* Inventory Table */

        .inventory-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        .inventory-table th,
        .inventory-table td {
            padding: 15px;
            text-align: center;
            background: rgba(255, 212, 40, 0.3);
        }

        .inventory-table tbody tr:hover {
            background: rgba(255, 212, 40, 0.3);
            transition: background 0.3s ease-in-out;
        }

        .inventory-table th {
            background: #FFD428;
            font-size: 18px;
        }

        .inventory-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        .action-btn {
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        .remove-btn {
            background: white !important;
            color: black !important;
            border: solid 2px black;
            width: 5rem;
            font-weight: bold;
            padding: 5px;
            border-radius: 20px;
        }

        .edit-btn {
            background: white !important;
            color: black !important;
            border: solid 2px black;
            width: 5rem;
            font-weight: bold;
            padding: 5px;
            border-radius: 20px;
        }

        /* Footer Section */

        .footer {
            background: #141414;
            color: #FFD428;
            padding: 15px;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            width: 100%;
            margin-top: auto;
        }

        /* Buttons Animation */
        
        .action-btn {
             padding: 8px 12px;
             border-radius: 6px;
             font-weight: bold;
            cursor: pointer;
            border: none;
            transition: 0.3s;
        }

        .remove-btn {
            background: red;
            color: white;
        }       

        .remove-btn:hover {
             background: darkred;
            transform: scale(1.1);
        }       

        .edit-btn {
            background: black;
            color: white;
        }

        .edit-btn:hover {
            background: gray;
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

        /* Page transition loading screen */

        #pageLoader {
                position: fixed;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.6); /* Semi-transparent white */
                backdrop-filter: blur(5px); /* Adds a blur effect for better UX */
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

    /* NOTIFICATIONS title */

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
        <img src="Capstone Assets/logouticon.png" alt="Logout">
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


    <div class="inventory-container">
        <div class="form-container">
            <div class="form-group">
                <input type="text" placeholder="Item Name">
                <input type="text" placeholder="Supplier Name">
                <input type="number" placeholder="Stock Quantity">
                <input type="number" placeholder="Item Cost">
                <input type="date" >
                <select>
                    <option>Category</option>
                </select>
                <button class="add-btn">Add to Inventory</button>
            </div>
            <div class="total-items">
                <h2>TOTAL ITEMS</h2>
                <span>1</span>
            </div>
        </div>

        <div class="search-container">
            <button class ="waster">Waste</button>
            <select>
                <option>Sort of...</option>
            </select>
            <input type="text" placeholder="Search...">
        </div>

        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Stock Quantity</th>
                    <th>Expiration Date</th>
                    <th>Supplier Name</th>
                    <th>Cost</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="inventoryBody">
                <tr>
                    <td>Sample Item</td>
                    <td>Category 1</td>
                    <td>10</td>
                    <td>2025-12-31</td>
                    <td>Supplier X</td>
                    <td>$5</td>
                    <td>

                    
                        <button class="edit-btn">Edit</button>
                        <button class="remove-btn">Remove</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Chian's Footer Section -->
    <footer class="footer">
        © 2023 Chia's Corner. All Rights Reserved. | Where Every Bite is Unlimited Delight
    </footer>
    </div>
    <script>

    // Edit Button Toggle (Edit -> Save -> Edit)

    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', function () {
            this.closest('tr').remove();
        });
    });

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            let row = this.closest('tr');
            let cells = row.querySelectorAll('td:not(:last-child)');

            if (this.textContent === "Edit") {
                cells.forEach(cell => {
                    cell.contentEditable = true;
                    cell.style.backgroundColor = "#FFF3CD";
                });
                this.textContent = "Save";
                this.style.background = "#28A745";
            } else {
                cells.forEach(cell => {
                    cell.contentEditable = false;
                    cell.style.backgroundColor = "";
                });
                this.textContent = "Edit";
                this.style.background = "black";
            }
        });
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
