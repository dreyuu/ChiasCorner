<?php
require '../../connection.php'; // Adjust path as needed

$status = isset($_GET['status']) ? $_GET['status'] : 'active'; // Default to active

try {
    $stmt = $connect->prepare("SELECT p.*, COALESCE(m.name, 'All') AS applicable_menu 
                                FROM promotions p 
                                LEFT JOIN menu m ON p.applicable_menu_id = m.menu_id 
                                WHERE p.status = :status 
                                ORDER BY p.start_date ASC");

    $stmt->bindParam(":status", $status);
    $stmt->execute();
    $promos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($promos);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
