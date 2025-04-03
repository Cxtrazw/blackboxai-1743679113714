<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

// Verify user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['department'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized - Admin only']);
    exit;
}

// Get and validate input
$data = json_decode(file_get_contents('php://input'), true);
$id = filter_var($data['id'] ?? '', FILTER_VALIDATE_INT);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid complaint ID']);
    exit;
}

try {
    // Verify complaint exists
    $stmt = $conn->prepare("SELECT id FROM complaints WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Complaint not found']);
        exit;
    }

    // Delete the complaint
    $deleteStmt = $conn->prepare("DELETE FROM complaints WHERE id = :id");
    $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($deleteStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Complaint deleted successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Deletion failed']);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>