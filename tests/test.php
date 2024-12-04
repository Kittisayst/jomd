<?php
require_once 'includes/config.php';
require_once 'includes/Database.php';

try {
    echo "Testing database connection...\n";
    $db = Database::getInstance();
    
    echo "Initializing tables...\n";
    $db->initTables();
    
    echo "Testing file creation...\n";
    $testData = [
        'title' => 'Test File',
        'file_name' => 'test.md',
        'content' => '# Test Content'
    ];
    
    $result = $db->saveFile($testData);
    echo "File created with ID: " . $result . "\n";
    
    echo "Testing file retrieval...\n";
    $files = $db->getAllFiles();
    echo "Found " . count($files) . " files\n";
    
    echo "All tests passed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}