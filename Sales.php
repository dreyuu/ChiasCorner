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
                            <th>Sales This Month</th>
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
    let salesChart, categoryChart;
    let currentSalesData = null;

    document.addEventListener("DOMContentLoaded", function() {
        // 1. Load initial data (no dates = all time/default)
        fetchDashboardData();

        // 2. Generate Button Event
        document.querySelector(".generate-btn").addEventListener("click", function(e) {
            e.preventDefault();
            const dateFrom = document.getElementById("date-from").value;
            const dateTo = document.getElementById("date-to").value;

            if (!dateFrom || !dateTo) {
                alert("Please select both Date From and Date To.");
                return;
            }
            fetchDashboardData(dateFrom, dateTo);
        });

        // 3. Clear Button Event
        document.querySelector(".clear-btn").addEventListener("click", function(e) {
            e.preventDefault();
            document.getElementById("date-from").value = "";
            document.getElementById("date-to").value = "";
            fetchDashboardData(); // Reload default
        });

        // 4. Download PDF Event
        document.querySelector(".download-btn").addEventListener("click", function(e) {
            e.preventDefault();
            generatePDF();
        });
    });

    // Main function to call both APIs
    function fetchDashboardData(dateFrom = "", dateTo = "") {
        // Show loading state if needed
        // loader.show();

        // Update Sales Cards & Tables
        fetch(`db_queries/select_queries/fetch_sales.php?dateFrom=${dateFrom}&dateTo=${dateTo}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) console.error(data.error);
                currentSalesData = data; // Store for PDF
                updateSalesUI(data);
            })
            .catch(err => console.error("Sales API Error:", err));

        // Update Charts
        fetch(`db_queries/select_queries/fetch_graph.php?dateFrom=${dateFrom}&dateTo=${dateTo}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) console.error(data.error);
                updateChartsUI(data);
            })
            .catch(err => console.error("Graph API Error:", err));

        // loader.hide();
    }

    // Function to update Text, Cards, and Tables
    function updateSalesUI(data) {
        if (!data) return;

        // Update Text Cards
        setText("total-sales", "₱ " + data.totalSales);
        setText("customer-served", data.customersServed);
        setText("avg-per-customer", "₱ " + data.avgSalePerCustomer);
        setText("avg-daily-sales", "₱ " + data.avgDailySales);
        setText("best-seller", data.bestSeller);
        setText("total-items-sold", data.totalItemsSold);

        setText("salesToday", "₱ " + data.todaySales);
        setText("salesWeek", "₱ " + data.weekSales);
        setText("salesMonth", "₱ " + data.monthSales);

        setText("top-staff", data.topStaff);
        setText("lowest-staff", data.lowestStaff);

        // Update Top/Least Products Lists
        updateList("top-products-list", data.topProducts);
        updateList("least-products-list", data.leastProducts);

        // Update Staff Table
        const tbody = document.querySelector("#staff-sales-table tbody");
        tbody.innerHTML = "";
        if (data.staffSales && data.staffSales.length > 0) {
            data.staffSales.forEach(s => {
                // salesToday is not strictly relevant in date range filter, so we focus on total_sales
                tbody.innerHTML += `<tr>
                    <td>${s.name}</td>
                    <td>₱ ${numberWithCommas(s.sales_today)}</td>
                    <td>₱ ${numberWithCommas(s.sales_month)}</td>
                    <td>₱ ${numberWithCommas(s.total_sales)}</td>
                </tr>`;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="3" class="text-center">No records found</td></tr>`;
        }
    }

    // Function to update Charts
    function updateChartsUI(data) {
        // 1. Monthly Sales Chart
        const monthlyData = data.monthlySales || [];
        const monthLabels = monthlyData.map(d => d.sales_month); // Returns YYYY-MM
        const monthTotals = monthlyData.map(d => d.total);

        // Logic to show "No Data" message
        if (monthlyData.length === 0) {
            document.querySelector(".no-sales-data").style.display = "block";
            if (salesChart) salesChart.destroy();
        } else {
            document.querySelector(".no-sales-data").style.display = "none";
            salesChart = renderChart("salesChart", salesChart, "bar", monthLabels, monthTotals, "Sales", "#FFD428");
        }

        // 2. Category Chart
        const catData = data.categorySales || [];
        const catLabels = catData.map(d => d.category);
        const catTotals = catData.map(d => d.total);
        const colors = ["#FFB300", "#E91E63", "#9C27B0", "#2196F3", "#4CAF50", "#FF5722"];

        if (catData.length === 0) {
            document.querySelector(".no-category-data").style.display = "block";
            if (categoryChart) categoryChart.destroy();
        } else {
            document.querySelector(".no-category-data").style.display = "none";
            categoryChart = renderChart("categoryChart", categoryChart, "doughnut", catLabels, catTotals, "Revenue", colors);
        }
    }

    // Chart.js Helper
    function renderChart(canvasId, chartInstance, type, labels, data, label, colors) {
        const ctx = document.getElementById(canvasId).getContext("2d");
        if (chartInstance) chartInstance.destroy();

        return new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: type === 'bar' ? {
                    y: {
                        beginAtZero: true
                    }
                } : {}
            }
        });
    }

    // DOM Helper
    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.querySelector("span").textContent = value;
    }

    function updateList(id, items) {
        const list = document.getElementById(id);
        list.innerHTML = "";
        if (items && items.length > 0) {
            items.forEach(i => list.innerHTML += `<li>${i.name} — ${i.total_sold}</li>`);
        } else {
            list.innerHTML = "<li>No data</li>";
        }
    }

    function numberWithCommas(x) {
        return parseFloat(x).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
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
            doc.text(`Total Sales: Php ${data.totalSales}`, margin + 2, y + offset);
            offset += 6;
            doc.text(`Customers Served: ${data.customersServed}`, margin + 2, y + offset);
            offset += 6;
            doc.text(`Avg Sale per Customer: Php ${data.avgSalePerCustomer}`, margin + 2, y + offset);
            offset += 6;
            doc.text(`Avg Daily Sales: Php ${data.avgDailySales}`, margin + 2, y + offset);
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
            doc.text(`Sales Today: Php ${data.todaySales}`, margin + 2, y);
            y += 6;
            doc.text(`Sales This Week: Php ${data.weekSales}`, margin + 2, y);
            y += 6;
            doc.text(`Sales This Month: Php ${data.monthSales}`, margin + 2, y);
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

            const staffRows = data.staffSales.map(s => [s.name, `Php ${s.sales_today}`, `Php ${s.sales_month}`, `Php ${s.total_sales}`]);
            doc.autoTable({
                head: [
                    ["Staff", "Today Sales", "Sales This Month", "Total Sales"]
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
