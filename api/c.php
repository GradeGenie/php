<?php


#
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
require_once __DIR__ . '/../load_env.php';
// Check if this file has already been included
if (!defined('C_PHP_INCLUDED')) {
    // Define a constant to mark this file as included
    define('C_PHP_INCLUDED', true);
    
    // Stripe API Keys and Site URL
    if (!defined('STRIPE_PUBLISHABLE_KEY')) {
        define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '');
    }
    if (!defined('STRIPE_SECRET_KEY')) {
        define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY'] ?? '');
    }
    if (!defined('SITE_URL')) {
        define('SITE_URL', $_ENV['SITE_URL'] ?? 'https://getgradegenie.com');
    }
    
    // Mark database as included
    if (!defined('DB_INCLUDED')) {
        define('DB_INCLUDED', true);
    }
}
#
#
// Database credentials for production environment
// Based on deployment configuration at app.getgradegenie.com/new
// $host = 'localhost';
// $username = 'root';
// $password = 'JustWing1t';
// $database = 'grady';

$host = $_ENV['DB_HOST'] ?? 'localhost';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';
$database = $_ENV['DB_NAME'] ?? 'gradegenie';

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
#
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
##
#
#
?>
