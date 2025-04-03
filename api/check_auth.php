<?php
session_start();
require 'db_connect.php';

$response = [
    'authenticated' => false,
    'user' => null
];

if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $_SESSION['user_id']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $response = [
                'authenticated' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'department' => $user['department'],
                    'name' => $user['name']
                ]
            ];
        }
    } catch(PDOException $e) {
        // Log error but don't break the flow
        error_log("Auth check error: " . $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>