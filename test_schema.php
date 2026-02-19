<?php
require_once __DIR__ . '/vendor/autoload_runtime.php';

return function (array $context) {
    $entityManager = $context['doctrine.orm.entity_manager'];
    
    // Get the schema
    $schema = $entityManager->getConnection()->createSchemaManager();
    $groupTable = $schema->listTableColumns('group');
    
    echo "Group table columns:\n";
    foreach ($groupTable as $column) {
        echo "- " . $column->getName() . " (" . $column->getType()->getName() . ")\n";
    }
    
    // Check if creator_id exists
    if (isset($groupTable['creator_id'])) {
        echo "\n✓ creator_id column exists!\n";
    } else {
        echo "\n✗ creator_id column MISSING!\n";
    }
    
    // Check if created_at exists
    if (isset($groupTable['created_at'])) {
        echo "✓ created_at column exists!\n";
    } else {
        echo "✗ created_at column MISSING!\n";
    }
};
