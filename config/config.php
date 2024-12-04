<?php
// Debug information
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Base paths
define('ROOT_PATH', realpath(dirname(__DIR__)));
define('BASE_PATH', "jomd");
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('DB_PATH', ROOT_PATH . '/database/markdown.db');
define('DB_BACKUP_PATH', __DIR__ . '/backups/');
define('LOG_PATH', __DIR__ . '/logs/');

// Create required directories with proper permissions
$directories = [
    dirname(DB_PATH),
    UPLOADS_PATH
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (!mkdir($dir, 0777, true)) {
            error_log("Failed to create directory: {$dir}");
        }
    }
    if (!is_writable($dir)) {
        error_log("Directory not writable: {$dir}");
    }
}

// Database connection check
try {
    if (!class_exists('SQLite3')) {
        throw new Exception('SQLite3 is not installed');
    }
    
    $testDb = new SQLite3(DB_PATH);
    $testDb->close();
} catch (Exception $e) {
    error_log("Database connection test failed: " . $e->getMessage());
}

// Error settings
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors in browser
date_default_timezone_set('Asia/Vientiane');

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['md', 'markdown']);