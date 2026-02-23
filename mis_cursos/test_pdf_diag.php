<?php
// Simple Diagnostic Script
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnostic Check</h1>";
echo "<p>If you can see this, the PHP file is being executed by the server.</p>";
echo "<p>File path: " . __FILE__ . "</p>";
echo "<p>GET Parameters: ";
print_r($_GET);
echo "</p>";
?>