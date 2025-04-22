<?php
include 'connection.php';

$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 2).png';
include 'inc/navbar.php';
?>


<link rel="stylesheet" href="css/main.css">

<!-- html2canvas and jsPDF CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<!-- Chian's Charts Section -->
<form id="sales-report-form" method="POST" style="visibility: hidden;">
    <label for="date-from">DATE FROM:</label>
    <input type="date" id="date-from">
    <label for="date-to">DATE TO:</label>
    <input type="date" id="date-to">
    <label for="category">CATEGORY:</label>
    <select id="category">
        <option value="all">All</option>
    </select>
    <button class="generate-btn">GENERATE</button>
</form>

<div class="charts-section">
    <h1 class="header-text">Welcome to Chia's Corner, MAEM!</h1>
    <div class="charts">
        <div class="chart-container">
            <h2 class="hero">Total Sales</h2>
            <canvas id="salesChart"></canvas>
        </div>
        <div class="chart-container">
            <h2 class="hero">Top Menu Picks</h2>
            <canvas id="menuChart"></canvas>
        </div>
    </div>
    <h2 class="header-text">Total Sales This Month & Top Menu Picks</h2>
</div>

<!-- Chian's Stats Section -->
<div class="exp">
    <div class="stats-container">
        <div class="sales-stats">
            <div class="stat-box" id="total-sales">TOTAL SALES <span>₱ </span></div>
            <div class="stat-box" id="customer-served">CUSTOMER SERVED <span></span></div>
            <div class="stat-box" id="best-seller">BEST SELLER <span></span></div>
            <div class="stat-box" id="vat-summary">Vat Summary<span></span></div>
            <div class="stat-box" id="net-sales">Net Sales <span></span></div>
        </div>

        <div class="chart-container small" id="categoryContainer">
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

<!-- <script src="js/navbar.js"></script> -->
<script>

    document.addEventListener("DOMContentLoaded", function() {
        loadSalesData();
        loadChartData();
        document.querySelector(".generate-btn").addEventListener("click", generateReport);
    });

    let salesChart, categoryChart, menuChart;

    function generateReport(event) {
        event.preventDefault();

        let dateFrom = document.getElementById("date-from").value;
        let dateTo = document.getElementById("date-to").value;
        let category = document.getElementById("category").value;

        // console.log(`Fetching report from ${dateFrom || "ALL TIME"} to ${dateTo || "ALL TIME"} for category: ${category}`);

        loadSalesData(dateFrom, dateTo, category);
        loadChartData(dateFrom, dateTo);
    }

    function loadSalesData(dateFrom = "", dateTo = "", category = "all") {
        fetch(`db_queries/select_queries/fetch_sales.php?dateFrom=${dateFrom}&dateTo=${dateTo}&category=${category}`)
            .then(response => response.json())
            .then(data => {
                if (!data || Object.keys(data).length === 0) {
                    console.warn("No sales data available.");
                    document.querySelector(".stats-container").innerHTML = "<p style='text-align:center; color:red;'>No sales data available.</p>";
                    return;
                }
                // console.log(data)
                document.getElementById("total-sales").innerHTML = `TOTAL SALES <span>₱ ${data.totalSales ?? 0}</span>`;
                document.getElementById("customer-served").innerHTML = `CUSTOMER SERVED <span>${data.customersServed ?? 0}</span>`;
                document.getElementById("best-seller").innerHTML = `BEST SELLER <span>${data.bestSeller || "N/A"}</span>`;
                document.getElementById("vat-summary").innerHTML = `VAT SUMMARY <span>${data.vat_amount || "N/A"}</span>`;
                document.getElementById("net-sales").innerHTML = `NET SALES <span>${data.net_sales || "N/A"}</span>`;

            })
            .catch(error => console.error("Error loading sales data:", error));
    }

    function loadChartData(dateFrom = "", dateTo = "") {
        fetch(`db_queries/select_queries/fetch_graph.php?dateFrom=${dateFrom}&dateTo=${dateTo}`)
            .then(response => response.json())
            .then(data => {
                // console.log("Fetched Data:", data); 

                if (!data || !data.topMenus) {
                    console.warn("No bestsellers data available.");
                    document.getElementById("salesChartContainer").innerHTML = "<p style='text-align:center; color:red;'>No bestsellers found.</p>";
                    return;
                }

                let topMenus = data.topMenus || [];
                let monthlySales = data.monthlySales || [];
                let categorySales = data.categorySales || [];

                // Update Best Sellers Chart
                menuChart = updateChart("menuChart", menuChart, "doughnut",
                    topMenus.map(item => item.name || "Unknown"),
                    topMenus.map(item => item.total_quantity || 0),
                    "Best Sellers",
                    ["#FFD428", "#FFCE56", "#FFC107", "#4BC0C0", "#9966FF"]
                );

                // Update Monthly Sales Chart
                salesChart = updateChart("salesChart", salesChart, "bar",
                    monthlySales.map(item => `Month ${item.month}`),
                    monthlySales.map(item => item.total),
                    "Total Sales",
                    "#FFD428"
                );

                // Update Category Breakdown Chart
                categoryChart = updateChart("categoryChart", categoryChart, "doughnut",
                    categorySales.map(item => item.category),
                    categorySales.map(item => item.total),
                    "Sales Breakdown",
                    ["#FFB300", "#9C27B0", "#FF9800", "#009688", "#8BC34A", "#BDBDBD"]
                );
            })
            .catch(error => console.error("Error fetching chart data:", error));
    }

    function updateChart(canvasId, chartInstance, chartType, labels, data, label, backgroundColors) {
        // Maximum length of label before truncating and adding ellipsis
        const MAX_LABEL_LENGTH = 10;

        // Truncate labels if they exceed the specified length
        const truncatedLabels = labels.map((label) => {
            return label.length > MAX_LABEL_LENGTH ? label.substring(0, MAX_LABEL_LENGTH) + '...' : label;
        });

        let ctx = document.getElementById(canvasId).getContext("2d");

        if (chartInstance) {
            chartInstance.destroy();
        }

        return new Chart(ctx, {
            type: chartType,
            data: {
                labels: truncatedLabels, // Use the truncated labels here
                datasets: [{
                    label: label,
                    data: data,
                    backgroundColor: backgroundColors
                }]
            },
            options: {
                responsive: true,
                scales: chartType === "bar" ? {
                    y: {
                        beginAtZero: true
                    }
                } : {}
            }
        });
    }
</script>

</body>

</html>
