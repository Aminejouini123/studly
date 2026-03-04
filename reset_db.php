<?php
require_once __DIR__ . '/vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

$conn = DriverManager::getConnection([
    'driver' => 'pdo_mysql',
    'host' => '127.0.0.1',
    'port' => 3306,
    'user' => 'root',
    'password' => '',
    'dbname' => 'Projet_db'
]);

// Drop all tables to reset
echo "Dropping existing tables...\n";
$tables = [
    'messenger_messages',
    'project_task',
    'task',
    'pomodoro_session',
    'event',
    'motivation',
    'exam',
    'activity',
    'project',
    'member_group',
    '`group`',
    'course',
    'user'
];

foreach ($tables as $table) {
    try {
        $conn->executeStatement("DROP TABLE IF EXISTS $table");
        echo "Dropped $table\n";
    } catch (\Exception $e) {
        echo "Error dropping $table: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Creating tables ===\n";

// Create tables in proper order (dependencies first)
$sqls = [
    "CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, date_of_birth DATE DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, statut VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4",
    
    "CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, course_file VARCHAR(255) DEFAULT NULL, course_link VARCHAR(255) DEFAULT NULL, teacher_email VARCHAR(255) NOT NULL, semester VARCHAR(255) NOT NULL, difficulty_level VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, priority VARCHAR(255) NOT NULL, coefficient DOUBLE PRECISION NOT NULL, status VARCHAR(50) NOT NULL, duration INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_169E6FB2A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4",
    
    "CREATE TABLE activity (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, file VARCHAR(255) DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, duration INT NOT NULL, status VARCHAR(255) NOT NULL, difficulty VARCHAR(255) NOT NULL, level VARCHAR(255) NOT NULL, course_id INT DEFAULT NULL, INDEX IDX_AC74095A591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4",
    
    "CREATE TABLE exam (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, date DATETIME NOT NULL, duration INT NOT NULL, grade DOUBLE PRECISION DEFAULT NULL, difficulty VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, file VARCHAR(255) NOT NULL, link VARCHAR(255) DEFAULT NULL, course_id INT DEFAULT NULL, INDEX IDX_38BBA6C6591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4",
    
    "CREATE TABLE motivation (id INT AUTO_INCREMENT NOT NULL, motivation_level INT NOT NULL, emotion VARCHAR(255) NOT NULL, preparation VARCHAR(255) NOT NULL, reward VARCHAR(255) NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_E06073EDA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4",
    
    "CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, duration INT NOT NULL, location VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, priority VARCHAR(255) NOT NULL, difficulty INT NOT NULL, date DATE NOT NULL, motivation_id INT DEFAULT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_3BAE0AA78EDBCD4E (motivation_id), INDEX IDX_3BAE0AA7A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4",
    
    "CREATE TABLE pomodoro_session (id INT AUTO_INCREMENT NOT NULL, duration INT NOT NULL, user_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_C123CB08A76ED395 (user_id), INDEX IDX_C123CB0871F64A97 (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4",
    
    "CREATE TABLE objective (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, estimated_duration VARCHAR(255) NOT NULL, real_duration INT NOT NULL, priority VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, reason VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4",
    
    "CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, repeat_count INT NOT NULL, status VARCHAR(255) NOT NULL, difficulty INT NOT NULL, impact DOUBLE PRECISION NOT NULL, objective_id INT DEFAULT NULL, INDEX IDX_527EDB2573484933 (objective_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4",
    
    "CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, creator_id INT NOT NULL, capacity INT NOT NULL, group_photo VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id), INDEX IDX_6DC044C561220EA6 (creator_id)) DEFAULT CHARACTER SET utf8mb4",
    
    "CREATE TABLE member_group (id INT AUTO_INCREMENT NOT NULL, group_id INT NOT NULL, UNIQUE INDEX UNIQ_FE1D136FE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4",
    
    "CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, resource VARCHAR(255) NOT NULL, deadline DATE NOT NULL, type VARCHAR(255) NOT NULL, group_id INT DEFAULT NULL, INDEX IDX_2FB3D0EEFE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4",
    
    "CREATE TABLE project_task (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, project_id INT DEFAULT NULL, INDEX IDX_6BEF133D166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4",
    
    "CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4",
];

// Add foreign keys
$fks = [
    "ALTER TABLE course ADD CONSTRAINT FK_169E6FB2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)",
    "ALTER TABLE activity ADD CONSTRAINT FK_AC74095A591CC992 FOREIGN KEY (course_id) REFERENCES course (id)",
    "ALTER TABLE exam ADD CONSTRAINT FK_38BBA6C6591CC992 FOREIGN KEY (course_id) REFERENCES course (id)",
    "ALTER TABLE motivation ADD CONSTRAINT FK_E06073EDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)",
    "ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA78EDBCD4E FOREIGN KEY (motivation_id) REFERENCES motivation (id)",
    "ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)",
    "ALTER TABLE pomodoro_session ADD CONSTRAINT FK_C123CB08A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)",
    "ALTER TABLE pomodoro_session ADD CONSTRAINT FK_C123CB0871F64A97 FOREIGN KEY (event_id) REFERENCES event (id)",
    "ALTER TABLE task ADD CONSTRAINT FK_527EDB2573484933 FOREIGN KEY (objective_id) REFERENCES objective (id)",
    "ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C561220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)",
    "ALTER TABLE member_group ADD CONSTRAINT FK_FE1D136FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)",
    "ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)",
    "ALTER TABLE project_task ADD CONSTRAINT FK_6BEF133D166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)",
];

// Execute table creations
foreach ($sqls as $sql) {
    try {
        $conn->executeStatement($sql);
        echo "✓ Created table\n";
    } catch (\Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Creating foreign key constraints ===\n";
// Execute foreign keys
foreach ($fks as $sql) {
    try {
        $conn->executeStatement($sql);
        echo "✓ " . substr($sql, 0, 50) . "...\n";
    } catch (\Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Schema creation complete! ===\n";

$conn->close();
?>
