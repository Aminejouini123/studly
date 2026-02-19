<?php
require_once __DIR__ . '/vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

$conn = DriverManager::getConnection([
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'user' => 'root',
    'password' => 'root',
    'dbname' => 'Projet_db'
]);

$sm = $conn->createSchemaManager();

echo "=== EVENT TABLE STRUCTURE ===\n";
if ($sm->tablesExist(['event'])) {
    $eventCols = $sm->listTableColumns('event');
    foreach ($eventCols as $col) {
        echo "- " . $col->getName() . " (" . $col->getType()->getName() . ")\n";
    }
    echo "\nuser_id exists: " . (isset($eventCols['user_id']) ? "YES" : "NO") . "\n";
} else {
    echo "EVENT table does NOT exist!\n";
}

echo "\n=== MOTIVATION TABLE STRUCTURE ===\n";
if ($sm->tablesExist(['motivation'])) {
    $motCols = $sm->listTableColumns('motivation');
    foreach ($motCols as $col) {
        echo "- " . $col->getName() . " (" . $col->getType()->getName() . ")\n";
    }
    echo "\nuser_id exists: " . (isset($motCols['user_id']) ? "YES" : "NO") . "\n";
} else {
    echo "MOTIVATION table does NOT exist!\n";
}

echo "\n=== GROUP TABLE STRUCTURE ===\n";
if ($sm->tablesExist(['group'])) {
    $groupCols = $sm->listTableColumns('group');
    foreach ($groupCols as $col) {
        echo "- " . $col->getName() . " (" . $col->getType()->getName() . ")\n";
    }
    echo "\ncreator_id exists: " . (isset($groupCols['creator_id']) ? "YES" : "NO") . "\n";
    echo "created_at exists: " . (isset($groupCols['created_at']) ? "YES" : "NO") . "\n";
} else {
    echo "GROUP table does NOT exist!\n";
}

$conn->close();
?>
