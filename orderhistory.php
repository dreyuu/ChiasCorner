<?php
include_once 'connection.php';
$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 2).png';
include 'inc/navbar.php';

try {

    // Query to get order history with items
    $sql = "SELECT oh.order_id, 
                    GROUP_CONCAT(m.name SEPARATOR ', ') AS items_ordered,
                    oh.paid_amount,
                    oh.total_price,
                    (oh.paid_amount - oh.total_price) AS change_given,
                    oh.payment_status,
                    pm.payment_method,
                    oh.order_date
            FROM order_history oh
            JOIN order_items oi ON oh.order_id = oi.order_id
            JOIN menu m ON oi.menu_id = m.menu_id
            LEFT JOIN payments pm ON oh.order_id = pm.order_id
            GROUP BY oh.order_id
            ORDER BY oh.archived_date DESC";

    $stmt = $connect->prepare($sql);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<link rel="stylesheet" href="css/orderlist.css">

<div class="wrapper">
    <!-- Sort & Search Bar -->
    <div class="search-container">
        <select id="sortSelect">
            <option value="0">Sort by Order ID</option>
            <option value="3">Sort by Total Price</option>
            <option value="5">Sort by Payment Method (Cash/Gcash)</option>
            <option value="6">Sort by Date & Time</option>
        </select>
        <input type="text" id="search" placeholder="Search..." onkeyup="filterTable()">
    </div>

    <!-- Orders Table -->

    <div class="table-container">
        <table id="orderTable">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Items Ordered</th>
                    <th>Payment Received</th>
                    <th>Bill Total</th>
                    <th>Change Given</th>
                    <th>Payment Method</th>
                    <th>Date & Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['items_ordered']); ?></td>
                            <td>$<?php echo number_format($order['paid_amount'], 2); ?></td>
                            <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                            <td>$<?php echo number_format($order['change_given'], 2); ?></td>
                            <td><?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></td>
                            <td><?php echo date("Y-m-d H:i", strtotime($order['order_date'])); ?></td>
                            <td id="actions">
                                <button class="action-button view-btn" onclick="viewOrder(<?php echo $order['order_id']; ?>)">View</button>
                                <button class="action-button remove-btn" onclick="deleteOrder(<?php echo $order['order_id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No archived orders found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chian's Footer Section -->
<footer class="footer">
    © 2023 Chia's Corner. All Rights Reserved. | Where Every Bite is Unlimited Delight
</footer>
</div>
<script>
    // Alert messages for buttons

    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', () => alert('Order Removed!'));
    });

    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', () => alert('Viewing Order Details...'));
    });


    // Sorting function
    function sortTable(columnIndex) {
        let table = document.getElementById("orderTable");
        let rows = Array.from(table.rows).slice(1); // Exclude header row
        let isNumeric = columnIndex === 0 || columnIndex === 3 || columnIndex === 6; // Order ID, Total Price, Date & Time
        let isAlphabetic = columnIndex === 5; // Payment Method column

        let sortedRows = rows.sort((a, b) => {
            let valA = a.cells[columnIndex].innerText.trim();
            let valB = b.cells[columnIndex].innerText.trim();

            if (isNumeric) {
                valA = parseFloat(valA.replace('$', '')) || 0;
                valB = parseFloat(valB.replace('$', '')) || 0;
                return valA - valB;
            } else if (isAlphabetic) {
                return valA.localeCompare(valB);
            }
            return valA > valB ? 1 : -1;
        });

        let sortedAsc = table.getAttribute("data-sort") === "asc";
        if (sortedAsc) sortedRows.reverse();
        table.setAttribute("data-sort", sortedAsc ? "desc" : "asc");

        for (let row of sortedRows) {
            table.tBodies[0].appendChild(row);
        }
    }


    // Dropdown Sort
    document.getElementById("sortSelect").addEventListener("change", function() {
        sortTable(parseInt(this.value));
    });

    // Search Filter
    function filterTable() {
        let searchInput = document.getElementById("search").value.toLowerCase();
        let rows = document.querySelectorAll("#orderTable tbody tr");

        rows.forEach(row => {
            let rowText = row.innerText.toLowerCase();
            row.style.display = rowText.includes(searchInput) ? "" : "none";
        });
    }
</script>

</body>

</html>
