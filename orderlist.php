<?php
include_once 'connection.php';
$backgroundImage = 'Capstone Assets/Log-in Form BG (Version 2).png';
include 'inc/navbar.php';

try {

    // Query to get orders and order items
    $sql = "SELECT o.order_id, 
                    GROUP_CONCAT(m.name SEPARATOR ', ') AS order_list
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN menu m ON oi.menu_id = m.menu_id
            GROUP BY o.order_id
            ORDER BY o.order_id DESC";

    $stmt = $connect->prepare($sql);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

?>
<link rel="stylesheet" href="css/orderlist.css">


<!-- Orders Table -->
<div class="wrapper">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Queue</th>
                    <th>Order ID</th>
                    <th>Order List</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php $queueNumber = 1001; // Queue number starts at 1 
                    ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo str_pad($queueNumber++, 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['order_list']); ?></td>
                            <td>
                                <button class="action-button remove-btn" onclick="removeOrder(<?php echo $order['order_id']; ?>)">Remove</button>
                                <button class="action-button view-btn" onclick="viewOrder(<?php echo $order['order_id']; ?>)">View</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No orders available</td>
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
<script>
    // Alert messages for buttons

    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', () => alert('Order Removed!'));
    });

    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', () => alert('Viewing Order Details...'));
    });
</script>

</body>

</html>
