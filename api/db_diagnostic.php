<?php
header('Content-Type: text/plain');
require 'db_connect.php';

echo "=== Database Connection Test ===\n";
try {
    $conn->query("SELECT 1");
    echo "✅ Successfully connected to database\n";
} catch(PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    exit;
}

echo "\n=== Database Structure Verification ===\n";

// Check if complaints table exists
$tables = $conn->query("SHOW TABLES LIKE 'complaints'")->fetchAll();
if (count($tables) === 0) {
    echo "❌ 'complaints' table does not exist\n";
    echo "Run this SQL to create it:\n";
    echo file_get_contents('sql/setup_db.sql');
    exit;
} else {
    echo "✅ 'complaints' table exists\n";
}

// Check table structure
$columns = [
    'id' => 'int',
    'title' => 'varchar',
    'department' => 'varchar',
    'description' => 'text',
    'priority' => 'varchar',
    'submitted_by' => 'varchar',
    'status' => 'varchar',
    'created_at' => 'timestamp',
    'resolved_at' => 'timestamp'
];

$stmt = $conn->query("DESCRIBE complaints");
$tableStructure = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\nTable Structure:\n";
foreach ($tableStructure as $column) {
    echo "- {$column['Field']}: {$column['Type']}";
    if ($column['Null'] === 'NO') echo " (NOT NULL)";
    if ($column['Key'] === 'PRI') echo " (PRIMARY KEY)";
    echo "\n";
}

echo "\n=== Test Data Insertion ===\n";
try {
    $testData = [
        'title' => 'Test Complaint',
        'department' => 'facilities',
        'description' => 'This is a test complaint',
        'priority' => 'Medium',
        'submitted_by' => 'diagnostic_script'
    ];
    
    $stmt = $conn->prepare("INSERT INTO complaints 
        (title, department, description, priority, submitted_by, status, created_at)
        VALUES 
        (:title, :department, :description, :priority, :submitted_by, 'Pending', NOW())");
    
    $stmt->execute($testData);
    $id = $conn->lastInsertId();
    
    echo "✅ Successfully inserted test complaint (ID: $id)\n";
    
    // Clean up
    $conn->query("DELETE FROM complaints WHERE id = $id");
    echo "✅ Test record cleaned up\n";
    
} catch(PDOException $e) {
    echo "❌ Insert test failed: " . $e->getMessage() . "\n";
    echo "Error details: " . print_r($conn->errorInfo(), true) . "\n";
}

echo "\n=== Current Complaints Count ===\n";
$count = $conn->query("SELECT COUNT(*) FROM complaints")->fetchColumn();
echo "Total complaints in database: $count\n";

echo "\nDiagnostic complete. Check above for any ❌ errors.\n";
?>