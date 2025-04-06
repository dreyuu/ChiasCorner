<?php

$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 3).png';
include 'inc/navbar.php';
?>
<link rel="stylesheet" href="css/sales.css">


<div class="sales-container">
    <div class="sales-content">
        <div class="sales-filter">
            <form id="sales-report-form" method="POST">
                <label for="date-from">DATE FROM:</label>
                <input type="date" id="date-from">
                <label for="date-to">DATE TO:</label>
                <input type="date" id="date-to">
                <label for="category">CATEGORY:</label>
                <select id="category">
                    <option value="all" selected>All</option>
                    <option value="Samgyupsal">Samgyupsal</option>
                    <option value="Chicken Wings">Chicken Wings</option>
                    <option value="Sizzling">Sizzling</option>
                    <option value="Drinks">Drinks</option>
                    <option value="Add-Ons">Add-Ons</option>
                    <option value="Others">Others</option>
                </select>
                <!-- Removed the button inside the select -->
                <button class="generate-btn">GENERATE</button>

            </form>
        </div>
        <div class="chart-container" id="salesChartContainer">
            <canvas id="salesChart"></canvas>
        </div>
    </div>


    <div class="stats-container">
        <div class="sales-stats">
            <div class="stat-box" id="total-sales">TOTAL SALES <span>₱ </span></div>
            <div class="stat-box" id="customer-served">CUSTOMER SERVED <span></span></div>
            <div class="stat-box" id="best-seller">BEST SELLER <span></span></div>
            <div class="stat-box" id="total-expenses">TOTAL EXPENSES <span>₱ </span></div>
            <div class="stat-box" id="net-profit">NET PROFIT <span>₱ </span></div>
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        loadSalesData();
        loadChartData();

        document.querySelector(".generate-btn").addEventListener("click", function(e) {
            e.preventDefault();
            generateReport();
        });
    });

    let salesChart, categoryChart;

    function generateReport() {
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

                document.getElementById("total-sales").innerHTML = `TOTAL SALES <span>₱ ${data.totalSales ?? 0}</span>`;
                document.getElementById("customer-served").innerHTML = `CUSTOMER SERVED <span>${data.customersServed ?? 0}</span>`;
                document.getElementById("best-seller").innerHTML = `BEST SELLER <span>${data.bestSeller || "N/A"}</span>`;
                document.getElementById("total-expenses").innerHTML = `TOTAL EXPENSES <span>₱ ${data.totalExpenses ?? 0}</span>`;
                document.getElementById("net-profit").innerHTML = `NET PROFIT <span>₱ ${data.netProfit ?? 0}</span>`;
            })
            .catch(error => console.error("Error loading sales data:", error));
    }

    function loadChartData(dateFrom = "", dateTo = "") {
        fetch(`db_queries/select_queries/fetch_graph.php?dateFrom=${dateFrom}&dateTo=${dateTo}`)
            .then(response => response.json())
            .then(data => {
                // console.log("Fetched Chart Data:", data);

                if (!data || !data.topMenus) {
                    console.warn("No bestsellers data available.");
                    document.getElementById("salesChartContainer").innerHTML = "<p style='text-align:center; color:red;'>No bestsellers found.</p>";
                    return;
                }

                let monthlySales = data.monthlySales || [];
                let categorySales = data.categorySales || [];

                // Update Monthly Sales Chart
                salesChart = updateChart("salesChart", salesChart, "bar",
                    monthlySales.map(item => `Month ${item.month}`),
                    monthlySales.map(item => item.total),
                    "Total Sales",
                    "blue"
                );

                // Update Category Breakdown Chart
                categoryChart = updateChart("categoryChart", categoryChart, "doughnut",
                    categorySales.map(item => item.category),
                    categorySales.map(item => item.total),
                    "Sales Breakdown",
                    ["red", "blue", "yellow", "green"]
                );
            })
            .catch(error => console.error("Error fetching chart data:", error));
    }

    function updateChart(canvasId, chartInstance, chartType, labels, data, label, backgroundColors) {
        let ctx = document.getElementById(canvasId).getContext("2d");

        if (chartInstance) {
            chartInstance.destroy();
        }

        return new Chart(ctx, {
            type: chartType,
            data: {
                labels: labels,
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
