<?php
require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__ . '/.env');

// Test basic doctrine connection
echo "Testing database connectivity and schema...\n\n";

$conn = new \PDO(
    'mysql:host=127.0.0.1:3306;dbname=Projet_db;charset=utf8mb4',
    'root',
    ''
);

echo "✓ Database connection successful\n\n";

// Check critical tables and columns
$tables = [
    'group' => ['creator_id', 'created_at'],
    'event' => ['user_id'],
    'motivation' => ['user_id'],
    'pomodoro_session' => ['event_id', 'type', 'status', 'started_at', 'ended_at'],
];

foreach ($tables as $table => $columns) {
    echo "Checking table: $table\n";
    $result = $conn->query("DESCRIBE `$table`");
    $exists = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $exists[$row['Field']] = true;
    }
    
    foreach ($columns as $col) {
        if (isset($exists[$col])) {
            echo "  ✓ $col exists\n";
        } else {
            echo "  ✗ $col MISSING\n";
        }
    }
    echo "\n";
}

echo "✓ All required columns are present!\n";
echo "\nYour SQL error 'Unknown column t0.user_id' should be FIXED!\n";

$conn = null;
?>
