<?php 
include_once '../../connection.php';


try {
    $query = "SELECT name, username, email, user_type, date_created FROM users";

    $stmt = $connect->prepare($query);
    $stmt->execute();
    $userData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $userData]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching data: ' . $e->getMessage()]);
}
?>
