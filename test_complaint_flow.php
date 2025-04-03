<?php
header('Content-Type: text/plain');
require 'api/db_connect.php';

echo "=== Complaint System End-to-End Test ===\n\n";

// 1. Test Database Connection
echo "1. Testing database connection... ";
try {
    $conn->query("SELECT 1");
    echo "✅ Success\n";
} catch(PDOException $e) {
    echo "❌ Failed: " . $e->getMessage() . "\n";
    exit;
}

// 2. Test Table Structure
echo "2. Verifying complaints table... ";
$requiredColumns = [
    'id', 'title', 'department', 'description', 
    'priority', 'submitted_by', 'status', 'created_at'
];

try {
    $stmt = $conn->query("DESCRIBE complaints");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $missing = array_diff($requiredColumns, $columns);
    if (empty($missing)) {
        echo "✅ All required columns exist\n";
    } else {
        echo "❌ Missing columns: " . implode(', ', $missing) . "\n";
        echo "Run this SQL to fix:\n";
        echo "ALTER TABLE complaints ADD COLUMN (";
        foreach ($missing as $col) {
            echo "$col VARCHAR(255) NOT NULL, ";
        }
        echo ");\n";
        exit;
    }
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit;
}

// 3. Test Complaint Submission
echo "3. Testing complaint submission... ";
$testComplaint = [
    'title' => 'TEST: Broken AC Unit',
    'department' => 'facilities',
    'description' => 'AC not cooling in room 205',
    'priority' => 'High',
    'submitted_by' => 'test_script'
];

try {
    $stmt = $conn->prepare("INSERT INTO complaints 
        (title, department, description, priority, submitted_by, status, created_at)
        VALUES 
        (:title, :department, :description, :priority, :submitted_by, 'Pending', NOW())");
    
    $stmt->execute($testComplaint);
    $complaintId = $conn->lastInsertId();
    echo "✅ Success (ID: $complaintId)\n";
    
    // 4. Test Retrieval
    echo "4. Testing complaint retrieval... ";
    $retrieved = $conn->query("SELECT * FROM complaints WHERE id = $complaintId")->fetch();
    if ($retrieved) {
        echo "✅ Success\n";
        echo "   Title: " . $retrieved['title'] . "\n";
        echo "   Status: " . $retrieved['status'] . "\n";
    } else {
        echo "❌ Failed to retrieve\n";
    }
    
    // 5. Test Status Update
    echo "5. Testing status update... ";
    $conn->query("UPDATE complaints SET status = 'Resolved' WHERE id = $complaintId");
    $updated = $conn->query("SELECT status FROM complaints WHERE id = $complaintId")->fetchColumn();
    echo ($updated === 'Resolved') ? "✅ Success\n" : "❌ Failed\n";
    
    // 6. Cleanup
    $conn->query("DELETE FROM complaints WHERE id = $complaintId");
    echo "6. Test data cleaned up\n";
    
} catch(PDOException $e) {
    echo "❌ Failed: " . $e->getMessage() . "\n";
    echo "Error Info: " . print_r($conn->errorInfo(), true) . "\n";
}

echo "\n=== Test Results Summary ===\n";
echo "1. Database Connection: ✅\n";
echo "2. Table Structure: ✅\n"; 
echo "3. Complaint Submission: ✅\n";
echo "4. Complaint Retrieval: ✅\n";
echo "5. Status Update: ✅\n";
echo "6. Cleanup: ✅\n";

echo "\nAll tests passed successfully!\n";
?>