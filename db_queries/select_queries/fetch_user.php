<?php
include_once '../../connection.php';

// Get raw POST data
$inputData = json_decode(file_get_contents("php://input"), true);

$userId = $inputData['userId'] ?? null;  // Access userId from decoded JSON

try {
    if ($userId) {
        // If user_id is provided, fetch that specific user's data
        $query = "SELECT user_id, name, username, email, user_type, date_created FROM users WHERE user_id = :user_id";
        
        $stmt = $connect->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);  // Bind the user_id parameter
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);  // Fetch single user's data
    } else {
        // If no user_id is provided, fetch all users
        $query = "SELECT user_id, name, username, email, user_type, date_created FROM users";
        
        $stmt = $connect->prepare($query);
        $stmt->execute();
        $userData = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Fetch all users' data
    }

    echo json_encode(['success' => true, 'data' => $userData]);  // Return the data as JSON

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching data: ' . $e->getMessage()]);
}
?>
