<?php
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

echo "<h2>Testing JOIN Operations</h2>";

// Test 1: LEFT JOIN
echo "<h3>Test 1: LEFT JOIN</h3>";
$sql1 = "SELECT incidents.title, users.name as reporter FROM incidents LEFT JOIN users ON incidents.reported_by = users.user_id LIMIT 5";
echo "<p><strong>SQL:</strong> " . htmlspecialchars($sql1) . "</p>";
try {
    $stmt1 = $conn->prepare($sql1);
    $stmt1->execute();
    $result1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
    echo "<p style='color:green;'>✅ SUCCESS - Rows returned: " . count($result1) . "</p>";
    echo "<pre>" . print_r($result1, true) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ ERROR: " . $e->getMessage() . "</p>";
}

// Test 2: RIGHT JOIN
echo "<hr><h3>Test 2: RIGHT JOIN</h3>";
$sql2 = "SELECT users.name, roles.role_name FROM users RIGHT JOIN roles ON users.role_id = roles.role_id LIMIT 5";
echo "<p><strong>SQL:</strong> " . htmlspecialchars($sql2) . "</p>";
try {
    $stmt2 = $conn->prepare($sql2);
    $stmt2->execute();
    $result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    echo "<p style='color:green;'>✅ SUCCESS - Rows returned: " . count($result2) . "</p>";
    echo "<pre>" . print_r($result2, true) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ ERROR: " . $e->getMessage() . "</p>";
}

// Test 3: Test what the form sends
echo "<hr><h3>Test 3: Form Simulation</h3>";
$testData = [
    'from_table' => 'incidents',
    'select_columns' => 'incidents.title, users.name',
    'join_type' => 'LEFT',
    'join_table' => 'users',
    'join_condition' => 'incidents.reported_by = users.user_id'
];

echo "<p><strong>Form Data:</strong></p>";
echo "<pre>" . print_r($testData, true) . "</pre>";

$selectClause = $testData['select_columns'];
$fromTable = $testData['from_table'];
$query = "SELECT {$selectClause} FROM {$fromTable}";

if (!empty($testData['join_table']) && !empty($testData['join_type'])) {
    $joinType = $testData['join_type'];
    $joinTable = $testData['join_table'];
    $joinCondition = $testData['join_condition'];

    $query .= " {$joinType} JOIN {$joinTable}";
    if (!empty($joinCondition)) {
        $query .= " ON {$joinCondition}";
    }
}

$query .= " LIMIT 5";

echo "<p><strong>Generated SQL:</strong> " . htmlspecialchars($query) . "</p>";

try {
    $stmt3 = $conn->prepare($query);
    $stmt3->execute();
    $result3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    echo "<p style='color:green;'>✅ SUCCESS - Rows returned: " . count($result3) . "</p>";
    echo "<pre>" . print_r($result3, true) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ ERROR: " . $e->getMessage() . "</p>";
}
?>
