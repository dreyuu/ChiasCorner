<?php
include_once __DIR__ . '/../../connection.php';

include_once __DIR__ . '/../../components/system_log.php';
include_once __DIR__ . '/../../components/pusher_helper.php';
require __DIR__ . '/../../components/logger.php';  // Load the Composer autoloader
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode the incoming JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    $batch_id = isset($data['batch_id']) ? $data['batch_id'] : null;
    $ownerId = isset($data['owner_id']) ? $data['owner_id'] : null;

    if ($batch_id) {
        try {
            // Query to delete the batch
            $query = "DELETE FROM stock_batches WHERE batch_id = :batch_id";
            $stmt = $connect->prepare($query);
            $stmt->execute([':batch_id' => $batch_id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Batch removed successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Batch not found or already removed.']);
            }
            PusherHelper::send("inventory-channel", "modify-inventory", ["msg" => "Item removed successfully"]);
            logAction(
                $connect,
                $ownerID,        // admin who created the user
                'INVENTORY',          // NOT AUTH
                'REMOVE INVENTORY',   // specific action type
                "Item Removed: $batch_id"
            );
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error removing batch: ' . $e->getMessage()]);
            logError("Database error removing batch: " . $e->getMessage(), "ERROR");
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No batch ID provided.']);
        logError("No batch ID provided for removal.", "ERROR");
    }
}
