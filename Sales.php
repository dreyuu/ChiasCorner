<?php
$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 3).png';
include 'inc/navbar.php';
?>
<link rel="stylesheet" href="css/sales.css">
<!-- JS Libraries -->
<!-- html2canvas and jsPDF CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<!-- jsPDF AutoTable plugin -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="sales-container">

    <!-- Sidebar Filters -->
    <aside class="sales-filter">
        <form id="sales-report-form">
            <h2>Filters</h2>
            <label for="date-from">DATE FROM:</label>
            <input type="date" id="date-from">
            <label for="date-to">DATE TO:</label>
            <input type="date" id="date-to">
            <label for="category" style="display: none;">CATEGORY:</label>
            <select id="category" style="display: none;">
                <option value="all" selected>All Categories</option>
                <option value="Samgyupsal">Samgyupsal</option>
                <option value="Chicken Wings">Chicken Wings</option>
                <option value="Sizzling">Sizzling</option>
                <option value="Drinks">Drinks</option>
                <option value="Add-Ons">Add-Ons</option>
                <option value="Others">Others</option>
            </select>

            <button class="generate-btn">GENERATE</button>
            <div class="generate-buttons">
                <button class="download-btn">Download PDF</button>
                <button class="clear-btn">Clear</button>
            </div>
        </form>
    </aside>

    <!-- Main Dashboard -->
    <div class="dashboard">

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-box" id="total-sales">TOTAL SALES <span>₱ 0.00</span></div>
            <div class="stat-box" id="customer-served">CUSTOMERS SERVED <span>0</span></div>
            <div class="stat-box" id="avg-per-customer">AVG SALE PER CUSTOMER <span>0.00</span></div>
            <div class="stat-box" id="avg-daily-sales">AVG DAILY SALES <span>0.00</span></div>
            <div class="stat-box" id="best-seller">BEST SELLER <span>N/A</span></div>
            <div class="stat-box" id="total-items-sold">TOTAL ITEMS SOLD <span>0</span></div>
            <div class="stat-box" id="salesToday">SALES TODAY <span>0.00</span></div>
            <div class="stat-box" id="salesWeek">SALES THIS WEEK <span>0.00</span></div>
            <div class="stat-box" id="salesMonth">SALES THIS MONTH <span>0.00</span></div>
            <div class="stat-box" id="top-staff">TOP STAFF <span>N/A</span></div>
            <div class="stat-box" id="lowest-staff">LOWEST STAFF <span>N/A</span></div>
        </div>

        <!-- Charts Section -->
        <div class="chart-section">
            <div class="chart-container">
                <h3>Monthly Sales</h3>
                <canvas id="salesChart"></canvas>
                <p class="no-sales-data">No sales data available.</p>
            </div>
            <div class="chart-container">
                <h3>Category Breakdown</h3>
                <canvas id="categoryChart"></canvas>
                <p class="no-category-data">No category data available.</p>
            </div>
        </div>

        <!-- Products Section -->
        <div class="products-section">
            <div class="products-box">
                <h3>Top 5 Products</h3>
                <ul id="top-products-list"></ul>
            </div>
            <div class="products-box">
                <h3>Least 5 Products</h3>
                <ul id="least-products-list"></ul>
            </div>
        </div>

        <!-- Staff Sales Table -->
        <div class="staff-sales">
            <h3>Staff Sales</h3>
            <div class="table-wrapper">
                <table id="staff-sales-table">
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th>Sales Today</th>
                            <th>Total Sales</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<footer class="footer">
    © 2025 Chia's Corner. All Rights Reserved. | Where Every Bite is Unlimited Delight
</footer>


<script>
    // Global variables
    let salesChart, categoryChart;
    let currentSalesData = null;

    document.addEventListener("DOMContentLoaded", function() {
        loadChartData();
        loadSalesData();

        document.querySelector(".generate-btn").addEventListener("click", function(e) {
            e.preventDefault();
            generateReport();
        });

        document.querySelector(".download-btn").addEventListener("click", function(e) {
            e.preventDefault();
            generatePDF();
        });

        document.querySelector('.clear-btn').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('date-from').value = '';
            document.getElementById('date-to').value = '';
            generateReport();
        });
    });

    // Generate report based on filters
    function generateReport() {
        try {
            loader.show()
            let dateFrom = document.getElementById("date-from").value;
            let dateTo = document.getElementById("date-to").value;
            let category = document.getElementById("category").value;

            loadSalesData(dateFrom, dateTo, category);
            loadChartData(dateFrom, dateTo);
        } catch (error) {
            console.error("Error generating report:", error);
        } finally {
            loader.hide()
        }

    }

    // Load sales stats and tables
    function loadSalesData(dateFrom = "", dateTo = "", category = "all") {
        fetch(`db_queries/select_queries/fetch_sales.php?dateFrom=${dateFrom}&dateTo=${dateTo}&category=${category}`)
            .then(res => res.json())
            .then(data => {
                if (!data) return;

                // Store data for PDF
                currentSalesData = data;

                // Populate stats
                const bestSellerName = data.bestSeller || (data.topProducts && data.topProducts.length ? data.topProducts[0].name : "N/A");
                const statsMap = {
                    "total-sales": data.totalSales,
                    "customer-served": data.customersServed,
                    "avg-per-customer": data.avgSalePerCustomer,
                    "avg-daily-sales": data.avgDailySales,
                    "best-seller": bestSellerName,
                    "total-items-sold": data.totalItemsSold,
                    "salesToday": data.todaySales,
                    "salesWeek": data.weekSales,
                    "salesMonth": data.monthSales,
                    "top-staff": data.topStaff,
                    "lowest-staff": data.lowestStaff
                };
                Object.keys(statsMap).forEach(id => {
                    document.getElementById(id).querySelector("span").textContent = statsMap[id] ?? "0";
                });

                // Top products
                const topList = document.getElementById("top-products-list");
                topList.innerHTML = "";
                (data.topProducts || []).forEach(p => {
                    topList.innerHTML += `<li>${p.name} — ${p.total_sold}</li>`;
                });

                // Least products
                const leastList = document.getElementById("least-products-list");
                leastList.innerHTML = "";
                (data.leastProducts || []).forEach(p => {
                    leastList.innerHTML += `<li>${p.name} — ${p.total_sold}</li>`;
                });

                // Staff sales table
                const tbody = document.querySelector("#staff-sales-table tbody");
                tbody.innerHTML = "";
                (data.staffSales || []).forEach(s => {
                    tbody.innerHTML += `<tr><td>${s.name}</td><td>₱ ${s.salesToday}</td><td>₱ ${s.totalSales}</td></tr>`;
                });
            });
    }

    // Load chart data
    function loadChartData(dateFrom = "", dateTo = "") {
        fetch(`db_queries/select_queries/fetch_graph.php?dateFrom=${dateFrom}&dateTo=${dateTo}`)
            .then(res => res.json())
            .then(data => {
                // Monthly sales bar chart
                const monthlySales = data.monthlySales || [];
                if (monthlySales.length) {
                    document.querySelector(".no-sales-data").style.display = 'none';
                    const labels = monthlySales.map(d => `Month ${d.month}`);
                    const totals = monthlySales.map(d => d.total);
                    salesChart = updateChart("salesChart", salesChart, "bar", labels, totals, "Monthly Sales", "#FFD428");
                } else document.querySelector(".no-sales-data").style.display = 'block';

                // Category sales doughnut chart
                const categorySales = data.categorySales || [];
                if (categorySales.length) {
                    document.querySelector(".no-category-data").style.display = 'none';
                    const labels = categorySales.map(d => d.category);
                    const totals = categorySales.map(d => d.total);
                    const colors = ["#FFB300", "#9C27B0", "#FF9800", "#009688", "#8BC34A", "#BDBDBD"];
                    categoryChart = updateChart("categoryChart", categoryChart, "doughnut", labels, totals, "Category Sales", colors);
                } else document.querySelector(".no-category-data").style.display = 'block';
            });
    }

    // Update or create chart
    function updateChart(canvasId, chartInstance, type, labels, data, label, colors) {
        let ctx = document.getElementById(canvasId).getContext("2d");
        if (chartInstance) chartInstance.destroy();
        return new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: data,
                    backgroundColor: colors
                }]
            },
            options: {
                responsive: true,
                scales: type === "bar" ? {
                    y: {
                        beginAtZero: true
                    }
                } : {}
            }
        });
    }

    // Generate PDF using cached sales data
    async function generatePDF() {
        try {
            loader.show()

            if (!currentSalesData) {
                CustomAlert.alert("No sales data available to generate PDF!", 'error');
                return;
            }

            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF("p", "mm", "a4");
            const margin = 15;
            let y = margin;

            // --- Title ---
            doc.setFontSize(22);
            doc.setTextColor("#FFD428");
            doc.setFont(undefined, "bold");
            doc.text("Sales Summary Report", doc.internal.pageSize.getWidth() / 2, y, {
                align: "center"
            });
            y += 12;

            // --- Date Range & Category ---
            const dateFrom = document.getElementById("date-from").value || "All";
            const dateTo = document.getElementById("date-to").value || "All";
            // const category = document.getElementById("category").value || "all";

            doc.setFontSize(12);
            doc.setTextColor("#333");
            doc.setFont(undefined, "normal");
            doc.text(`Date Range: ${dateFrom} to ${dateTo}`, margin, y);
            y += 6;
            doc.text(`Category: ${category}`, margin, y);
            y += 10;

            // --- Sales Summary Box ---
            doc.setFillColor("#FFD428");
            doc.rect(margin, y, 180, 50, "F");
            doc.setTextColor("#000");
            doc.setFont(undefined, "bold");
            doc.text("Summary:", margin + 2, y + 6);
            doc.setFont(undefined, "normal");

            const data = currentSalesData; // use cached data
            const bestSellerName = data.bestSeller || (data.topProducts && data.topProducts.length ? data.topProducts[0].name : "N/A");
            let offset = 12;
            doc.text(`Total Sales: P ${data.totalSales}`, margin + 2, y + offset);
            offset += 6;
            doc.text(`Customers Served: ${data.customersServed}`, margin + 2, y + offset);
            offset += 6;
            doc.text(`Avg Sale per Customer: P ${data.avgSalePerCustomer}`, margin + 2, y + offset);
            offset += 6;
            doc.text(`Avg Daily Sales: P ${data.avgDailySales}`, margin + 2, y + offset);
            offset += 6;
            doc.text(`Best Seller: ${bestSellerName || "N/A"}`, margin + 2, y + offset);
            offset += 6;
            doc.text(`Total Items Sold: ${data.totalItemsSold}`, margin + 2, y + offset);

            y += 60;

            // --- Time-Based Sales ---
            doc.setFont(undefined, "bold");
            doc.setTextColor("#FFD428");
            doc.text("Time-Based Sales:", margin, y);
            doc.setFont(undefined, "normal");
            doc.setTextColor("#333");
            y += 6;
            doc.text(`Sales Today: ₱ ${data.todaySales}`, margin + 2, y);
            y += 6;
            doc.text(`Sales This Week: ₱ ${data.weekSales}`, margin + 2, y);
            y += 6;
            doc.text(`Sales This Month: ₱ ${data.monthSales}`, margin + 2, y);
            y += 10;

            // --- Top / Least Products ---
            doc.setFont(undefined, "bold");
            doc.setTextColor("#FFD428");
            doc.text("Top Products:", margin, y);
            doc.setFont(undefined, "normal");
            doc.setTextColor("#333");
            y += 6;
            data.topProducts.forEach(p => {
                doc.text(`• ${p.name} — ${p.total_sold}`, margin + 2, y);
                y += 5;
            });
            y += 2;

            doc.setFont(undefined, "bold");
            doc.setTextColor("#FFD428");
            doc.text("Least Products:", margin, y);
            doc.setFont(undefined, "normal");
            doc.setTextColor("#333");
            y += 6;
            data.leastProducts.forEach(p => {
                doc.text(`• ${p.name} — ${p.total_sold}`, margin + 2, y);
                y += 5;
            });
            y += 8;

            // --- Staff Sales Table ---
            doc.setFont(undefined, "bold");
            doc.setTextColor("#FFD428");
            doc.text("Staff Sales:", margin, y);
            y += 6;

            const staffRows = data.staffSales.map(s => [s.name, `P ${s.salesToday}`, `P ${s.totalSales}`]);
            doc.autoTable({
                head: [
                    ["Staff", "Today Sales", "Total Sales"]
                ],
                body: staffRows,
                startY: y,
                theme: 'grid',
                headStyles: {
                    fillColor: "#FFD428",
                    textColor: "#000",
                    fontStyle: "bold"
                },
                bodyStyles: {
                    textColor: "#333"
                },
                styles: {
                    cellPadding: 3,
                    fontSize: 10
                },
                margin: {
                    left: margin,
                    right: margin
                }
            });

            doc.save("Sales_Report.pdf");
        } catch (error) {
            console.error("Error generating PDF:", error);
        } finally {
            loader.hide()
        }
    }

    function loadSales() {
        loadSalesData();
        loadChartData();
    }

    // Initialize manager
    const pusherManager = new PusherManager("<?php echo $_ENV['PUSHER_KEY']; ?>", "<?php echo $_ENV['PUSHER_CLUSTER']; ?>");

    // Fetch users on add or update
    pusherManager.bind('users-channel', 'modify-user', loadSales, 200);
    pusherManager.bind('orders-channel', 'modify-order', loadSales, 200);
    pusherManager.bind('promo-channel', 'modify-promo', loadSales, 200);
</script>


</body>

</html>
