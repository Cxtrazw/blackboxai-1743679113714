<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get and validate input
$data = json_decode(file_get_contents('php://input'), true);
$id = filter_var($data['id'] ?? '', FILTER_VALIDATE_INT);
$status = filter_var($data['status'] ?? '', FILTER_SANITIZE_STRING);

// Validate input
if (!$id || !in_array($status, ['Pending', 'Resolved'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    // First verify the complaint exists and belongs to user's department (unless admin)
    $stmt = $conn->prepare("SELECT department FROM complaints WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Complaint not found']);
        exit;
    }
    
    $complaint = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // For non-admin users, verify department match
    if ($_SESSION['department'] !== 'admin' && $complaint['department'] !== $_SESSION['department']) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized for this department']);
        exit;
    }

    // Update the complaint status
    $updateStmt = $conn->prepare("UPDATE complaints 
                                SET status = :status, 
                                    resolved_at = IF(:status = 'Resolved', NOW(), NULL) 
                                WHERE id = :id");
    
    $updateStmt->bindParam(':status', $status);
    $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($updateStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Complaint status updated',
            'resolved_at' => ($status === 'Resolved') ? date('Y-m-d H:i:s') : null
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>