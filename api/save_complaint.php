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
$title = filter_var($data['title'] ?? '', FILTER_SANITIZE_STRING);
$description = filter_var($data['description'] ?? '', FILTER_SANITIZE_STRING);
$department = filter_var($data['department'] ?? '', FILTER_SANITIZE_STRING);
$priority = filter_var($data['priority'] ?? '', FILTER_SANITIZE_STRING);

// Validate required fields
if (empty($title) || empty($description) || empty($department) || empty($priority)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    // Insert complaint into database
    $stmt = $conn->prepare("INSERT INTO complaints 
        (title, department, description, priority, submitted_by, status) 
        VALUES (:title, :department, :description, :priority, :submitted_by, 'Pending')");
    
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':department', $department);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':priority', $priority);
    $stmt->bindParam(':submitted_by', $_SESSION['username']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'id' => $conn->lastInsertId(),
            'message' => 'Complaint submitted successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit complaint']);
    }
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>