<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session
if (session_destroy()) {
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
} else {
    // Return error response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Logout failed'
    ]);
}
?>