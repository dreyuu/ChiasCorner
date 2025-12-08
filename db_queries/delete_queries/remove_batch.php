<?php
include_once __DIR__ . '/../../connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode the incoming JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    $batch_id = isset($data['batch_id']) ? $data['batch_id'] : null;

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
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error removing batch: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No batch ID provided.']);
    }
}
