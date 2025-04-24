<?php
$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 3).png';
include 'inc/navbar.php';
?>
<link rel="stylesheet" href="css/sales.css">
<!-- html2canvas and jsPDF CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<div class="sales-container">
    <div class="sales-content">
        <div class="sales-filter">
            <form id="sales-report-form" method="POST">
                <label for="date-from">DATE FROM:</label>
                <input type="date" id="date-from">
                <label for="date-to">DATE TO:</label>
                <input type="date" id="date-to">
                <label for="category" style="display: none;">CATEGORY:</label>
                <select id="category" style="display: none;">
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
                <div class="generate-buttons">
                    <button class="download-btn">Download PDF</button>
                    <button class="clear-btn">Clear</button>
                </div>
            </form>
        </div>
        <div class="chart-container" id="salesChartContainer">
            <canvas id="salesChart"></canvas>
            <p class="no-sales-data" style='text-align:center;display:none; color:red;'>No sales data available.</p>
        </div>
    </div>


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
            <p class="no-category-data" style='text-align:center;display:none; color:red;'>No category data available.</p>
        </div>
    </div>
</div>


<!-- Chian's Footer Section -->
<footer class="footer">
    © 2023 Chia's Corner. All Rights Reserved. | Where Every Bite is Unlimited Delight
</footer>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        loadChartData();
        loadSalesData();

        document.querySelector(".generate-btn").addEventListener("click", function(e) {
            e.preventDefault();
            generateReport();
        });

        async function generatePDF() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF("p", "mm", "a4");
            const margin = 20;
            let y = margin;

            // Title
            doc.setFontSize(18);
            doc.text("Sales Summary Report", margin, y);
            y += 10;

            // Fetch data
            const dateFrom = document.getElementById("date-from").value;
            const dateTo = document.getElementById("date-to").value;
            const category = document.getElementById("category").value;

            try {
                // Load stats
                const salesRes = await fetch(`db_queries/select_queries/fetch_sales.php?dateFrom=${dateFrom}&dateTo=${dateTo}&category=${category}`);
                const salesData = await salesRes.json();

                // Format section
                doc.setFontSize(12);
                doc.setTextColor(50);

                doc.text(`Date Range: ${dateFrom || 'All'} to ${dateTo || 'All'}`, margin, y);
                y += 8;
                doc.text(`Category: ${category}`, margin, y);
                y += 12;

                doc.setFont(undefined, "bold");
                doc.text("Sales Summary:", margin, y);
                doc.setFont(undefined, "normal");
                y += 8;

                doc.text(`Total Sales:  ${salesData.totalSales ?? 0}`, margin, y);
                y += 8;
                doc.text(`Customers Served: ${salesData.customersServed ?? 0}`, margin, y);
                y += 8;
                doc.text(`Best Seller: ${salesData.bestSeller ?? 'N/A'}`, margin, y);
                y += 8;
                doc.text(`VAT Amount: ${salesData.vat_amount ?? 'N/A'}`, margin, y);
                y += 8;
                doc.text(`Net Sales: ${salesData.net_sales ?? 'N/A'}`, margin, y);
                y += 12;

                // Optional: Add category breakdown
                const graphRes = await fetch(`db_queries/select_queries/fetch_graph.php?dateFrom=${dateFrom}&dateTo=${dateTo}`);
                const graphData = await graphRes.json();

                const categories = graphData.categorySales || [];

                if (categories.length > 0) {
                    doc.setFont(undefined, "bold");
                    doc.text("Category Breakdown:", margin, y);
                    doc.setFont(undefined, "normal");
                    y += 8;

                    categories.forEach(cat => {
                        doc.text(`• ${cat.category}:  ${cat.total}`, margin, y);
                        y += 6;
                    });
                }

                doc.save("Sales_Report.pdf");
            } catch (err) {
                console.error("Failed to generate report:", err);
                alert("Failed to generate PDF.");
            }
        }





        document.querySelector(".download-btn").addEventListener("click", function(event) {
            event.preventDefault(); // Prevent the default form submission
            generatePDF(); // Call the function to generate the PDF
        });

        document.querySelector('.clear-btn').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('date-from').value = '';
            document.getElementById('date-to').value = '';

            document.querySelector(".no-sales-data").style.display = 'none'; // Hide chart
            document.querySelector(".no-category-data").style.display = 'none'; // Hide chart
            // Regenerate the report and charts
            generateReport();
        });

    });

    let salesChart, categoryChart;

    function generateReport() {
        let dateFrom = document.getElementById("date-from").value;
        let dateTo = document.getElementById("date-to").value;
        let category = document.getElementById("category").value;

        loadSalesData(dateFrom, dateTo, category);
        loadChartData(dateFrom, dateTo);
    }

    function loadSalesData(dateFrom = "", dateTo = "", category = "all") {
        fetch(`db_queries/select_queries/fetch_sales.php?dateFrom=${dateFrom}&dateTo=${dateTo}&category=${category}`)
            .then(response => response.json())
            .then(data => {
                if (!data || Object.keys(data).length === 0) {
                    document.querySelector(".stats-container").innerHTML = "<p style='text-align:center; color:red;'>No sales data available.</p>";
                    return;
                }
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
                let monthlySales = data.monthlySales || [];
                let categorySales = data.categorySales || [];

                // Hide the sales chart container if no monthly sales data is available
                if (monthlySales.length === 0) {
                    document.getElementById("salesChart").style.display = 'none'; // Hide chart
                    document.querySelector(".no-sales-data").style.display = 'block'; // Show no data message
                } else {
                    document.querySelector(".no-sales-data").style.display = 'none';
                    document.getElementById("salesChartContainer").style.display = 'block'; // Show chart
                    const months = monthlySales.map(item => `Month ${item.month}`);
                    const totals = monthlySales.map(item => item.total);

                    salesChart = updateChart("salesChart", salesChart, "bar", months, totals, "Total Sales", "#FFD428");
                }

                // Hide the category chart container if no category sales data is available
                if (categorySales.length === 0) {
                    document.getElementById("categoryChart").style.display = 'none'; // Hide chart
                    document.querySelector(".no-category-data").style.display = 'block'; // Show no data message
                } else {
                    document.querySelector(".no-category-data").style.display = 'none'; // Hide chart
                    document.getElementById("categoryContainer").style.display = 'block'; // Show chart
                    const categories = categorySales.map(item => item.category);
                    const categoryTotals = categorySales.map(item => item.total);

                    categoryChart = updateChart("categoryChart", categoryChart, "doughnut", categories, categoryTotals, "Sales Breakdown", [
                        "#FFB300", "#9C27B0", "#FF9800", "#009688", "#8BC34A", "#BDBDBD"
                    ]);
                }
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
