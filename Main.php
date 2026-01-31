<?php
include 'connection.php';
$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 2).png';
include 'inc/navbar.php';
?>

<link rel="stylesheet" href="css/main.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<main class="charts-section">
    <section class="bg-section">
        <header class="header-text">
            <h1 id="welcomeTitle">Welcome</h1>
        </header>

        <!-- ================= ADMIN SECTION ================= -->
        <section id="adminSection" style="display:none;">
            <div class="dashboard-grid">
                <!-- LEFT COLUMN: STATS -->
                <div class="stats-column">
                    <div class="stat-box" id="statTotalSales">Total Sales<span>₱ 0.00</span></div>
                    <div class="stat-box" id="statCustomers">Customers Served<span>0</span></div>
                    <div class="stat-box" id="statToday">Sales Today<span>₱ 0.00</span></div>
                    <div class="stat-box" id="statWeek">Sales This Week<span>₱ 0.00</span></div>
                    <div class="stat-box" id="statMonth">Sales This Month<span>₱ 0.00</span></div>
                </div>

                <!-- RIGHT COLUMN: CHARTS -->
                <div class="charts-column">
                    <div class="chart-container ratio">
                        <h3>Monthly Sales</h3>
                        <canvas id="adminSalesChart"></canvas>
                    </div>

                    <div class="chart-container ratio">
                        <h3>Category Breakdown</h3>
                        <canvas id="adminCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </section>
    </section>
    <!-- STAFF PERFORMANCE -->
    <div class="chart-container wide">
        <h3>Staff Performance</h3>
        <table id="staffTable" class="table">
            <thead>
                <tr>
                    <th>Staff</th>
                    <th>Sales Today</th>
                    <th>Monthly Sales</th>
                    <th>Total Sales</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- TOP & LEAST PRODUCTS -->
    <div class="bottom-grid">
        <div class="chart-container small">
            <h3>Top Products</h3>
            <ul id="topProductsList"></ul>
        </div>

        <div class="chart-container small">
            <h3>Least Products</h3>
            <ul id="leastProductsList"></ul>
        </div>
    </div>



    <!-- ================= EMPLOYEE SECTION ================= -->
    <section id="employeeSection" style="display:none;">
        <div class="exp">
            <div class="stats-container">
                <div class="sales-stats">
                    <div class="chart-container small">
                        <h3>My Completed Orders (Today)</h3>
                        <div id="myOrdersList"></div>
                    </div>
                </div>

                <div class="sales-stats">
                    <div class="chart-container small">
                        <h3>Active Promotions</h3>
                        <ul id="promoList"></ul>
                    </div>

                    <div class="chart-container small">
                        <h3>My Stats</h3>
                        <div class="center-chart">
                            <div class="stat-box" id="empOrdersServed">Orders Served<span>0</span></div>
                            <div class="stat-box" id="empSalesToday">My Sales Today<span>₱ 0.00</span></div>
                            <div class="stat-box" id="empSalesWeek">My Sales Week<span>₱ 0.00</span></div>
                            <div class="stat-box" id="empSalesMonth">My Sales Month<span>₱ 0.00</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="footer">
    © 2023 Chia's Corner. All Rights Reserved. | Where Every Bite is Unlimited Delight
</footer>

<script>
    const jwtKey = 'jwt_token';
    const apiUrl = 'db_queries/select_queries/fetch_dashboard_data.php';

    function parseJwt(token) {
        try {
            const base64 = token.split('.')[1];
            const json = atob(base64.replace(/-/g, '+').replace(/_/g, '/'));
            return JSON.parse(json);
        } catch {
            return null;
        }
    }

    const token = localStorage.getItem(jwtKey);
    if (!token) window.location.href = 'index.php';
    const payload = parseJwt(token);
    if (!payload) {
        localStorage.removeItem(jwtKey);
        window.location.href = 'index.php';
    }

    const userType = payload.user_type;
    const userId = payload.user_id;
    const name = payload.name || payload.username || 'User';

    document.getElementById('welcomeTitle').textContent = `Welcome, ${name}!`;

    let adminSalesChart = null;
    let adminCategoryChart = null;

    async function loadDashboard() {
        try {
            const url = new URL(apiUrl, window.location.href);
            url.searchParams.set('user_id', userId);
            url.searchParams.set('user_type', userType);

            const res = await fetch(url);
            const data = await res.json();

            if (data.error) return console.error('API Error:', data);
            userType === 'admin' ? renderAdmin(data) : renderEmployee(data);

        } catch (err) {
            console.error('Load error:', err);
        }
    }

    function renderAdmin(data) {
        document.getElementById('adminSection').style.display = '';
        document.getElementById('employeeSection').style.display = 'none';

        document.querySelector('#statTotalSales span').textContent = '₱ ' + (data.totalSales || '0.00');
        document.querySelector('#statCustomers span').textContent = data.customersServed ?? 0;
        document.querySelector('#statToday span').textContent = '₱ ' + (data.todaySales || '0.00');
        document.querySelector('#statWeek span').textContent = '₱ ' + (data.weekSales || '0.00');
        document.querySelector('#statMonth span').textContent = '₱ ' + (data.monthSales || '0.00');

        // STAFF
        const tbody = document.querySelector('#staffTable tbody');
        tbody.innerHTML = (data.staffSales || [])
            .map(s => `<tr><td>${s.name}</td><td>₱ ${s.salesToday}</td><td>₱ ${s.salesMonth}</td><td>₱ ${s.totalSales}</td></tr>`)
            .join('') || '<tr><td colspan="3">No data available</td></tr>';

        // PRODUCTS
        fillList('topProductsList', data.topProducts, i => `${i.name} — ${i.total_sold}`, 'No Top Products Data');
        fillList('leastProductsList', data.leastProducts, i => `${i.name} — ${i.total_sold}`, 'No Least Products Data');


        // CHARTS
        renderChart('adminSalesChart', adminSalesChart, 'bar',
                data.monthlySales.map(r => `${r.yr}-${String(r.mo).padStart(2,'0')}`),
                data.monthlySales.map(r => parseFloat(r.total)), 'Monthly Sales')
            .then(chart => adminSalesChart = chart);

        renderChart('adminCategoryChart', adminCategoryChart, 'doughnut',
                data.categorySales.map(r => r.category),
                data.categorySales.map(r => parseFloat(r.total)))
            .then(chart => adminCategoryChart = chart);
    }

    function renderEmployee(data) {
        document.getElementById('adminSection').style.display = 'none';
        document.getElementById('employeeSection').style.display = '';

        const tbody = document.querySelector('#staffTable tbody');
        tbody.innerHTML = `<tr>
            <td>${data.employee.name}</td>
            <td>₱ ${data.personalSalesToday || '0.00'}</td>
            <td>₱ ${data.personalSalesMonth || '0.00'}</td>
            <td>₱ ${data.personalTotalSales || '0.00'}</td>
        </tr>`;

        fillList('promoList', data.activePromotions, p =>
            `${p.name} — ${p.discount_type === 'percentage' ? p.discount_value + '%' : '₱' + p.discount_value}`,
            'No active promotions'
        );

        // PRODUCTS
        fillList('topProductsList', data.personalTopProducts, i => `${i.name} — ${i.total_sold}`, 'No Top Products Data');
        fillList('leastProductsList', data.personalLeastProducts, i => `${i.name} — ${i.total_sold}`, 'No Least Products Data');

        const list = document.getElementById('myOrdersList');
        list.innerHTML = (data.ordersToday || [])
            .map(o => `<div class="order-row">
                <strong>Order #${o.order_id}</strong> — ₱ ${o.total_price} — ${o.dine}
                <div class="muted">${o.order_time}</div>
                <div>${o.items_ordered}</div>
                </div>`)
            .join('') || '<p>No completed orders today.</p>';

        document.querySelector('#empOrdersServed span').textContent = data.ordersServedToday ?? 0;
        document.querySelector('#empSalesToday span').textContent = '₱ ' + (data.personalSalesToday || '0.00');
        document.querySelector('#empSalesWeek span').textContent = '₱ ' + (data.personalSalesWeek || '0.00');
        document.querySelector('#empSalesMonth span').textContent = '₱ ' + (data.personalSalesMonth || '0.00');
    }

    function fillList(id, arr, formatter = i => `${i.name} — ${i.total_sold || ''}`, message) {
        const list = document.getElementById(id);
        list.innerHTML = (arr && arr.length) ?
            arr.map(i => `<li>${formatter(i)}</li>`).join('') :
            `<li>${message || 'No data available'}</li>`;
    }

    async function renderChart(canvasId, existing, type, labels, dataVals, label = '') {
        if (existing) existing.destroy();
        const ctx = document.getElementById(canvasId).getContext('2d');
        return new Chart(ctx, {
            type,
            data: {
                labels,
                datasets: [{
                    label,
                    data: dataVals,
                    backgroundColor: ['#FFD428', '#FFCE56', '#FFC107', '#4BC0C0', '#9966FF']
                }]
            },
            options: {
                responsive: true,
                scales: type === 'bar' ? {
                    y: {
                        beginAtZero: true
                    }
                } : {}
            }
        });
    }

    loadDashboard();

    // Initialize manager
    const pusherManager = new PusherManager("<?php echo $_ENV['PUSHER_KEY']; ?>", "<?php echo $_ENV['PUSHER_CLUSTER']; ?>");

    // Fetch users on add or update
    pusherManager.bind('users-channel', 'modify-user', loadDashboard, 200);
    pusherManager.bind('orders-channel', 'modify-order', loadDashboard, 200);
    pusherManager.bind('promo-channel', 'modify-promo', loadDashboard, 200);
</script>
