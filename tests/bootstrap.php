<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Automatically setup test database before running tests
$setupScript = dirname(__DIR__) . '/Tests/setup-test-db.sh';
if (file_exists($setupScript)) {
    echo "Running test database setup...\n";
    $output = [];
    $returnVar = 0;
    exec($setupScript . ' 2>&1', $output, $returnVar);
    
    if ($returnVar === 0) {
        echo "Test database setup completed successfully.\n";
    } else {
        echo "Test database setup failed:\n";
        echo implode("\n", $output) . "\n";
        exit(1);
    }
} else {
    echo "Warning: Test database setup script not found at $setupScript\n";
}

// Register shutdown function to cleanup test database after all tests
register_shutdown_function(function() {
    $cleanupScript = dirname(__DIR__) . '/Tests/cleanup-test-db.sh';
    if (file_exists($cleanupScript)) {
        echo "\nRunning test database cleanup...\n";
        $output = [];
        $returnVar = 0;
        exec($cleanupScript . ' 2>&1', $output, $returnVar);
        
        if ($returnVar === 0) {
            echo "Test database cleanup completed successfully.\n";
        } else {
            echo "Test database cleanup failed:\n";
            echo implode("\n", $output) . "\n";
        }
    }
});
