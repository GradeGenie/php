<?php
/**
 * Debug Page
 * 
 * This page shows detailed error information and system state
 * for debugging purposes. ONLY USE IN DEVELOPMENT.
 */

// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Include database connection
require_once 'api/c.php';

// Function to check if a file exists and is readable
function check_file($path) {
    if (file_exists($path)) {
        return file_exists($path) && is_readable($path) ? 
            "<span style='color:green'>✓ Exists and readable</span>" : 
            "<span style='color:orange'>⚠ Exists but not readable</span>";
    } else {
        return "<span style='color:red'>✗ Does not exist</span>";
    }
}

// Function to check database connection
function check_database($conn) {
    if ($conn->connect_error) {
        return "<span style='color:red'>✗ Failed: " . htmlspecialchars($conn->connect_error) . "</span>";
    } else {
        return "<span style='color:green'>✓ Connected successfully</span>";
    }
}

// Function to check if a table exists
function check_table($conn, $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    return $result->num_rows > 0 ? 
        "<span style='color:green'>✓ Exists</span>" : 
        "<span style='color:red'>✗ Does not exist</span>";
}

// Function to get the last few lines of a log file
function get_log_tail($file, $lines = 10) {
    if (!file_exists($file)) return "Log file does not exist";
    
    $result = [];
    $fp = fopen($file, "r");
    
    // Set large buffer size to read the file efficiently
    $buffer = 4096;
    $line_count = 0;
    
    // Jump to the end of the file
    fseek($fp, 0, SEEK_END);
    $pos = ftell($fp);
    
    // Read backwards until we have enough lines
    while ($pos > 0 && $line_count < $lines) {
        // Move back one buffer from the current position
        $pos = max(0, $pos - $buffer);
        fseek($fp, $pos);
        
        // Read a buffer of data
        $data = fread($fp, $buffer);
        
        // Count the number of newlines in this buffer
        $line_count += substr_count($data, "\n");
        
        // If we have enough lines, break
        if ($line_count >= $lines) break;
    }
    
    // Get the last $lines of the file
    $lines_array = explode("\n", $data);
    $lines_array = array_slice($lines_array, -$lines);
    
    fclose($fp);
    return implode("\n", $lines_array);
}

// Check if we're testing a specific page
$test_url = isset($_GET['url']) ? $_GET['url'] : 'pricing.php?from=signup';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GradeGenie Debug Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .code {
            font-family: monospace;
            background-color: #f8f8f8;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            white-space: pre-wrap;
            margin: 10px 0;
            border: 1px solid #ddd;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
        }
        .success {
            color: #2ecc71;
            font-weight: bold;
        }
        .warning {
            color: #f39c12;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>GradeGenie Debug Page</h1>
        <p>This page provides detailed information about your system configuration and current state.</p>
        
        <div class="section">
            <h2>System Information</h2>
            <table>
                <tr>
                    <th>Item</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>PHP Version</td>
                    <td><?php echo phpversion(); ?></td>
                </tr>
                <tr>
                    <td>Server Software</td>
                    <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                </tr>
                <tr>
                    <td>Document Root</td>
                    <td><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></td>
                </tr>
                <tr>
                    <td>Current Script</td>
                    <td><?php echo $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown'; ?></td>
                </tr>
                <tr>
                    <td>Memory Limit</td>
                    <td><?php echo ini_get('memory_limit'); ?></td>
                </tr>
                <tr>
                    <td>Max Execution Time</td>
                    <td><?php echo ini_get('max_execution_time'); ?> seconds</td>
                </tr>
                <tr>
                    <td>Upload Max Filesize</td>
                    <td><?php echo ini_get('upload_max_filesize'); ?></td>
                </tr>
                <tr>
                    <td>Post Max Size</td>
                    <td><?php echo ini_get('post_max_size'); ?></td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <h2>File System Checks</h2>
            <table>
                <tr>
                    <th>File/Directory</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td>api/c.php</td>
                    <td><?php echo check_file(__DIR__ . '/api/c.php'); ?></td>
                </tr>
                <tr>
                    <td>api/webhook.php</td>
                    <td><?php echo check_file(__DIR__ . '/api/webhook.php'); ?></td>
                </tr>
                <tr>
                    <td>api/create_checkout_session.php</td>
                    <td><?php echo check_file(__DIR__ . '/api/create_checkout_session.php'); ?></td>
                </tr>
                <tr>
                    <td>api/check-subscription.php</td>
                    <td><?php echo check_file(__DIR__ . '/api/check-subscription.php'); ?></td>
                </tr>
                <tr>
                    <td>pricing.php</td>
                    <td><?php echo check_file(__DIR__ . '/pricing.php'); ?></td>
                </tr>
                <tr>
                    <td>logs/ directory</td>
                    <td><?php 
                        $logs_dir = __DIR__ . '/logs';
                        if (!file_exists($logs_dir)) {
                            echo "<span style='color:orange'>⚠ Does not exist - creating...</span>";
                            mkdir($logs_dir, 0777, true);
                            echo " <span style='color:green'>✓ Created successfully</span>";
                        } else {
                            echo is_writable($logs_dir) ? 
                                "<span style='color:green'>✓ Exists and writable</span>" : 
                                "<span style='color:red'>✗ Exists but not writable</span>";
                        }
                    ?></td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <h2>Database Connection</h2>
            <p>Database Status: <?php echo check_database($conn); ?></p>
            
            <?php if (!$conn->connect_error): ?>
            <h3>Database Tables</h3>
            <table>
                <tr>
                    <th>Table</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td>users</td>
                    <td><?php echo check_table($conn, 'users'); ?></td>
                </tr>
            </table>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>Session Information</h2>
            <div class="code">
                <?php 
                    echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "\n";
                    echo "Session ID: " . session_id() . "\n\n";
                    
                    echo "SESSION Variables:\n";
                    print_r($_SESSION);
                ?>
            </div>
        </div>
        
        <div class="section">
            <h2>Request Information</h2>
            <div class="code">
                <?php 
                    echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "\n";
                    echo "QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'Not set') . "\n\n";
                    
                    echo "GET Variables:\n";
                    print_r($_GET);
                    
                    echo "\nPOST Variables:\n";
                    print_r($_POST);
                ?>
            </div>
        </div>
        
        <div class="section">
            <h2>Log Files</h2>
            
            <h3>PHP Error Log</h3>
            <div class="code">
                <?php 
                    $error_log = __DIR__ . '/logs/php_errors.log';
                    echo file_exists($error_log) ? htmlspecialchars(get_log_tail($error_log)) : "No error log file found";
                ?>
            </div>
            
            <h3>Debug Log</h3>
            <div class="code">
                <?php 
                    $debug_log = __DIR__ . '/logs/debug.log';
                    echo file_exists($debug_log) ? htmlspecialchars(get_log_tail($debug_log)) : "No debug log file found";
                ?>
            </div>
        </div>
        
        <div class="section">
            <h2>Test Page</h2>
            <p>Test URL: <strong><?php echo htmlspecialchars($test_url); ?></strong></p>
            <p>
                <a href="<?php echo htmlspecialchars($test_url); ?>" class="btn" target="_blank">Open in New Tab</a>
                <a href="debug.php?url=<?php echo urlencode($test_url); ?>" class="btn">Refresh</a>
            </p>
            
            <h3>Test Page Content</h3>
            <div class="code">
                <?php
                    // Try to fetch the content of the test page
                    $test_url_full = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $test_url;
                    echo "Attempting to fetch: $test_url_full\n\n";
                    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $test_url_full);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    
                    $response = curl_exec($ch);
                    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                    $header = substr($response, 0, $header_size);
                    $body = substr($response, $header_size);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    
                    curl_close($ch);
                    
                    echo "HTTP Status Code: $http_code\n\n";
                    echo "Response Headers:\n$header\n\n";
                    echo "Response Body (first 1000 chars):\n" . substr($body, 0, 1000) . (strlen($body) > 1000 ? "..." : "");
                ?>
            </div>
        </div>
        
        <div class="section">
            <h2>PHP Configuration</h2>
            <div class="code">
                <?php 
                    $relevant_settings = [
                        'display_errors', 'log_errors', 'error_log', 'error_reporting',
                        'date.timezone', 'default_charset', 'max_execution_time',
                        'memory_limit', 'upload_max_filesize', 'post_max_size',
                        'session.save_path', 'session.gc_maxlifetime'
                    ];
                    
                    foreach ($relevant_settings as $setting) {
                        echo "$setting = " . ini_get($setting) . "\n";
                    }
                ?>
            </div>
        </div>
        
        <div class="section">
            <h2>Loaded PHP Extensions</h2>
            <div class="code">
                <?php echo implode(", ", get_loaded_extensions()); ?>
            </div>
        </div>
    </div>
</body>
</html>
