<?php
$conn = new PDO('mysql:host=127.0.0.1:3306;dbname=Projet_db', 'root', '');

echo "Fixing database tables...\n";

// Fix exam table - make file nullable
try {
    $conn->exec('ALTER TABLE exam MODIFY file VARCHAR(255) DEFAULT NULL');
    echo "✓ Fixed exam.file to be nullable\n";
} catch (\Exception $e) {
    echo "✗ Error fixing exam: " . $e->getMessage() . "\n";
}

// Drop pomodoro_session and recreate it correctly
try {
    $conn->exec('DROP TABLE IF EXISTS pomodoro_session');
    echo "✓ Dropped pomodoro_session\n";
} catch (\Exception $e) {
    echo "✗ Error dropping pomodoro_session: " . $e->getMessage() . "\n";
}

try {
    $sql = 'CREATE TABLE pomodoro_session (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, type VARCHAR(255) NOT NULL, duration INT NOT NULL, status VARCHAR(255) NOT NULL, started_at DATETIME DEFAULT NULL, ended_at DATETIME DEFAULT NULL, INDEX IDX_6FFF4BB271F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4';
    $conn->exec($sql);
    echo "✓ Created pomodoro_session with correct schema\n";
} catch (\Exception $e) {
    echo "✗ Error creating pomodoro_session: " . $e->getMessage() . "\n";
}

try {
    $conn->exec('ALTER TABLE pomodoro_session ADD CONSTRAINT FK_6FFF4BB271F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
    echo "✓ Added foreign key constraint\n";
} catch (\Exception $e) {
    echo "✗ Error adding constraint: " . $e->getMessage() . "\n";
}

echo "\nDatabase schema fixed!\n";
$conn = null;
?>
