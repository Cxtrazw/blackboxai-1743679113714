<?php
session_start();
require 'db_connect.php';

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$department = $data['department'] ?? '';

try {
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND department = :department");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':department', $department);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['department'] = $user['department'];
            $_SESSION['name'] = $user['name'];
            
            // Return success response
            echo json_encode([
                'success' => true,
                'department' => $user['department'],
                'name' => $user['name']
            ]);
            exit;
        }
    }

    // Return error if authentication fails
    echo json_encode([
        'success' => false,
        'message' => 'Invalid username, password, or department'
    ]);

} catch(PDOException $e) {
    // Handle database errors
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>