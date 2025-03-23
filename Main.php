<?php
include 'connection.php';

$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 2).png';
include 'inc/navbar.php';
?>
<link rel="stylesheet" href="css/main.css">

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

<div class="exp">
    <div class="stats-container">
        <div class="sales-stats">
            <div class="stat-box" id="total-sales">TOTAL SALES <span>₱ 103,230</span></div>
            <div class="stat-box" id="customer-served">CUSTOMER SERVED <span>321</span></div>
            <div class="stat-box" id="best-seller">BEST SELLER <span>SIZZLING HAKDOG</span></div>
            <div class="stat-box" id="total-expenses">TOTAL EXPENSES <span>₱ 25,325</span></div>
            <div class="stat-box" id="net-profit">NET PROFIT <span>₱ 77,905</span></div>
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
        data: {
            labels: [],
            datasets: [{
                label: 'Sales',
                data: [],
                backgroundColor: 'blue'
            }]
        },
        options: {
            responsive: true
        }
    });

    let menuChart = new Chart(menuChartCtx, {
        type: 'pie',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: ['red', 'green', 'blue', 'yellow']
            }]
        },
        options: {
            responsive: true
        }
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
</script>

</body>

</html>
