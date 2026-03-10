<?php
/**
 * Cache Clearing Script
 * Access this file via: https://hardware.rektech.work/clear-cache.php
 * 
 * IMPORTANT: Delete this file after use for security!
 */

// Security: Only allow from specific IP or with a secret key
$secret_key = 'your-secret-key-here'; // Change this!
if (!isset($_GET['key']) || $_GET['key'] !== $secret_key) {
    die('Unauthorized');
}

echo "<h1>Clearing Laravel Cache...</h1>";
echo "<pre>";

// Change to Laravel root directory
chdir(__DIR__ . '/..');

// Clear route cache
echo "\n=== Clearing Route Cache ===\n";
exec('php artisan route:clear 2>&1', $output1, $return1);
echo implode("\n", $output1);
echo "\nReturn code: " . $return1 . "\n";

// Clear config cache
echo "\n=== Clearing Config Cache ===\n";
exec('php artisan config:clear 2>&1', $output2, $return2);
echo implode("\n", $output2);
echo "\nReturn code: " . $return2 . "\n";

// Clear application cache
echo "\n=== Clearing Application Cache ===\n";
exec('php artisan cache:clear 2>&1', $output3, $return3);
echo implode("\n", $output3);
echo "\nReturn code: " . $return3 . "\n";

// Clear view cache
echo "\n=== Clearing View Cache ===\n";
exec('php artisan view:clear 2>&1', $output4, $return4);
echo implode("\n", $output4);
echo "\nReturn code: " . $return4 . "\n";

// Optimize clear
echo "\n=== Optimize Clear ===\n";
exec('php artisan optimize:clear 2>&1', $output5, $return5);
echo implode("\n", $output5);
echo "\nReturn code: " . $return5 . "\n";

echo "\n=== DONE! ===\n";
echo "All caches cleared successfully!\n";
echo "\n⚠️ IMPORTANT: Delete this file now for security!\n";
echo "</pre>";
?>
