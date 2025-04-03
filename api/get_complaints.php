<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get department from query parameter or session
$department = isset($_GET['department']) 
    ? filter_var($_GET['department'], FILTER_SANITIZE_STRING)
    : $_SESSION['department'];

try {
    // Prepare base query
    $query = "SELECT c.*, u.name as submitted_by_name 
              FROM complaints c
              JOIN users u ON c.submitted_by = u.username";
    
    // Add department filter for non-admin users
    if ($_SESSION['department'] !== 'admin') {
        $query .= " WHERE c.department = :department";
    }
    
    // Add sorting
    $query .= " ORDER BY c.created_at DESC";
    
    $stmt = $conn->prepare($query);
    
    // Bind parameter if needed
    if ($_SESSION['department'] !== 'admin') {
        $stmt->bindParam(':department', $_SESSION['department']);
    }
    
    $stmt->execute();
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'complaints' => $complaints
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>