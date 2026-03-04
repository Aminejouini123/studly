<?php
try {
    $dsn = 'mysql:host=127.0.0.1;port=3306;dbname=Projet_db;charset=utf8mb4';
    $user = 'root';
    $password = '';
    $pdo = new PDO($dsn, $user, $password);

    echo "--- GROUPS ---\n";
    $stmt = $pdo->query('SELECT id, category, creator_id FROM `group`');
    $groupsFound = false;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']} | Category: {$row['category']} | Creator: {$row['creator_id']}\n";
        $groupsFound = true;
    }
    if (!$groupsFound)
        echo "No groups found.\n";

    echo "\n--- CREATORS CHECK ---\n";
    $stmt = $pdo->query('SELECT DISTINCT creator_id FROM `group`');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $creatorStmt = $pdo->prepare('SELECT id, email FROM user WHERE id = ?');
        $creatorStmt->execute([$row['creator_id']]);
        $creator = $creatorStmt->fetch(PDO::FETCH_ASSOC);
        if (!$creator) {
            echo "ORPHANED GROUP: Creator ID {$row['creator_id']} not found in user table!\n";
        } else {
            echo "Creator ID {$row['creator_id']} found: {$creator['email']}\n";
        }
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
