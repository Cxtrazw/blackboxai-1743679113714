<?php
session_start();
require 'db_connect.php';

// Enable detailed error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Log session and POST data
error_log("Session data: " . print_r($_SESSION, true));
error_log("POST data: " . print_r($_POST, true));
$input = file_get_contents('php://input');
error_log("Raw input: " . $input);
$data = json_decode($input, true);
error_log("Decoded JSON: " . print_r($data, true));

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("Unauthorized access attempt - no user_id in session");
    echo json_encode(['success' => false, 'message' => 'Unauthorized - please login']);
    exit;
}

// Validate required fields
$required = ['title', 'description', 'department', 'priority'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        error_log("Missing required field: $field");
        echo json_encode(['success' => false, 'message' => "Missing $field"]);
        exit;
    }
}

try {
    // Prepare SQL with all fields
    $sql = "INSERT INTO complaints 
            (title, department, description, priority, submitted_by, status, created_at)
            VALUES 
            (:title, :department, :description, :priority, :submitted_by, 'Pending', NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':title', $data['title']);
    $stmt->bindParam(':department', $data['department']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':priority', $data['priority']);
    $stmt->bindParam(':submitted_by', $_SESSION['username']);

    if ($stmt->execute()) {
        $complaintId = $conn->lastInsertId();
        error_log("Complaint saved successfully. ID: $complaintId");
        echo json_encode([
            'success' => true,
            'id' => $complaintId,
            'message' => 'Complaint saved successfully'
        ]);
    } else {
        error_log("Execute failed: " . print_r($stmt->errorInfo(), true));
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save complaint',
            'error' => $stmt->errorInfo()
        ]);
    }
} catch(PDOException $e) {
    error_log("Database exception: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'error' => $e->getMessage()
    ]);
}

// Log the final database connection status
error_log("Database connection status: " . print_r($conn->getAttribute(PDO::ATTR_CONNECTION_STATUS), true));
?>