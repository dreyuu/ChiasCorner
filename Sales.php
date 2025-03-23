<?php

$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 3).png';
include 'inc/navbar.php';
?>
<link rel="stylesheet" href="css/sales.css">


<div class="sales-container">
    <div class="sales-content">
        <div class="sales-filter">
            <label for="date-from">DATE FROM:</label>
            <input type="date" id="date-from">
            <label for="date-to">DATE TO:</label>
            <input type="date" id="date-to">
            <label for="category">CATEGORY:</label>
            <select id="category">
                <option value="all">All</option>
            </select>
            <button class="generate-btn" onclick="generateReport()">GENERATE</button>
        </div>

        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    
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

<script>
    new Chart(document.getElementById('salesChart'), {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [{
                label: 'Total Sales (Months)',
                data: [10000, 20000, 30000, 40000, 50000, 60000, 70000],
                backgroundColor: ['red', 'blue', 'yellow', 'purple', 'gray', 'lightblue', 'orange']
            }]
        }
    });

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

    function generateReport() {
        alert('Generating report...');
    }
</script>

</body>

</html>
