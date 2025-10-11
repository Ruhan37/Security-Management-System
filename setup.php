<?php
require_once 'config/database.php';

$database = new Database();

// Create database
if ($database->createDatabase()) {
    echo "Database created successfully!<br>";

    // Get connection to the specific database
    $conn = $database->getConnection();

    if ($conn) {
        // Read and execute SQL file
        $sql = file_get_contents('sql/setup.sql');

        // Split into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $conn->exec($statement);
                } catch(PDOException $e) {
                    echo "Error executing statement: " . $e->getMessage() . "<br>";
                }
            }
        }

        echo "Database setup completed successfully!<br>";
        echo "<a href='index.php'>Go to Main Application</a>";
    }
} else {
    echo "Failed to create database.";
}
?>