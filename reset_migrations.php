<?php
$dsn = "mysql:host=127.0.0.1;dbname=Projet_db;charset=utf8mb4";
$pdo = new PDO($dsn, "root", "");
$pdo->query("TRUNCATE TABLE doctrine_migration_versions;");
echo "Migration table truncated\n";
