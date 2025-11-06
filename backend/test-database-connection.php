<?php
/**
 * Test script to verify Database connection using shared Database.php
 * This script can be used to test database connectivity in both Docker and local environments
 */

// Include autoloader if running from a service directory
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use App\Core\Database;

echo "=== Database Connection Test ===\n";

// Test environment variables
echo "\n1. Testing Environment Variables:\n";
$envVars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
foreach ($envVars as $var) {
    $envValue = $_ENV[$var] ?? getenv($var);
    $status = $envValue ? '✓' : '✗';
    echo "   {$status} {$var}: " . ($envValue ?: 'NOT SET') . "\n";
}

// Test database connection
echo "\n2. Testing Database Connection:\n";
try {
    $db = Database::getConnection();
    if ($db) {
        echo "   ✓ Database connection successful!\n";
        
        // Test a simple query
        $stmt = $db->query("SELECT 1 as test");
        $result = $stmt->fetch();
        if ($result && $result['test'] == 1) {
            echo "   ✓ Database query test successful!\n";
        } else {
            echo "   ✗ Database query test failed\n";
        }
    } else {
        echo "   ✗ Database connection failed - returned null\n";
    }
} catch (Exception $e) {
    echo "   ✗ Database connection error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>