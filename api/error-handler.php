<?php
/**
 * Error Handler
 * 
 * This file provides centralized error handling for the GradeGenie application.
 * It logs errors to files and displays user-friendly error messages.
 */

// Define constants
define('LOG_DIR', dirname(__DIR__) . '/logs');

// Create logs directory if it doesn't exist
if (!file_exists(LOG_DIR)) {
    mkdir(LOG_DIR, 0777, true);
}

// Set error reporting
ini_set('display_errors', 0); // Don't show errors to users
ini_set('log_errors', 1); // Log errors
error_reporting(E_ALL); // Report all errors

/**
 * Custom error handler
 */
function custom_error_handler($errno, $errstr, $errfile, $errline) {
    // Log error to file
    $error_message = date('Y-m-d H:i:s') . " - Error: [$errno] $errstr in $errfile on line $errline";
    error_log($error_message . "\n", 3, LOG_DIR . '/php_errors.log');
    
    // For fatal errors, display a user-friendly message
    if (in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        show_error_page("We're experiencing technical difficulties", 
                       "Our team has been notified. Please try again later or contact support if the problem persists.");
        exit(1);
    }
    
    // Return false to allow PHP's internal error handler to continue
    return false;
}

/**
 * Custom exception handler
 */
function custom_exception_handler($exception) {
    // Log exception to file
    $error_message = date('Y-m-d H:i:s') . " - Exception: " . $exception->getMessage() . 
                    " in " . $exception->getFile() . " on line " . $exception->getLine() . 
                    "\nStack trace: " . $exception->getTraceAsString();
    error_log($error_message . "\n", 3, LOG_DIR . '/php_exceptions.log');
    
    // Display user-friendly error message
    show_error_page("We're experiencing technical difficulties", 
                   "Our team has been notified. Please try again later or contact support if the problem persists.");
    exit(1);
}

/**
 * Handler for fatal errors
 */
function fatal_error_handler() {
    $error = error_get_last();
    
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Log fatal error
        $error_message = date('Y-m-d H:i:s') . " - Fatal Error: [{$error['type']}] {$error['message']} in {$error['file']} on line {$error['line']}";
        error_log($error_message . "\n", 3, LOG_DIR . '/php_fatal_errors.log');
        
        // Display user-friendly error message
        show_error_page("We're experiencing technical difficulties", 
                       "Our team has been notified. Please try again later or contact support if the problem persists.");
    }
}

/**
 * Display a user-friendly error page
 */
function show_error_page($title, $message, $status_code = 500) {
    // Set HTTP response code
    http_response_code($status_code);
    
    // Check if headers have already been sent
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    
    // Display error page
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - GradeGenie</title>
    <style>
        body {
            font-family: "Albert Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .error-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .error-icon {
            font-size: 60px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .error-details {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: left;
            font-size: 14px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1>' . htmlspecialchars($title) . '</h1>
        <p>' . htmlspecialchars($message) . '</p>
        <a href="/" class="btn">Return to Home</a>
        
        <div class="error-details">
            <p>Error ID: ' . uniqid() . '</p>
            <p>Time: ' . date('Y-m-d H:i:s') . '</p>
        </div>
    </div>
</body>
</html>';
    
    // Stop execution
    exit;
}

/**
 * Log a debug message
 */
function log_debug($message, $context = []) {
    $logFile = LOG_DIR . '/debug.log';
    
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message,
        'context' => $context
    ];
    
    $logLine = json_encode($logData) . "\n";
    file_put_contents($logFile, $logLine, FILE_APPEND);
}

/**
 * Log an error message
 */
function log_error($type, $message, $context = []) {
    $logFile = LOG_DIR . '/error.log';
    
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'message' => $message,
        'context' => $context
    ];
    
    $logLine = json_encode($logData) . "\n";
    file_put_contents($logFile, $logLine, FILE_APPEND);
}

// Set custom error and exception handlers
set_error_handler('custom_error_handler');
set_exception_handler('custom_exception_handler');
register_shutdown_function('fatal_error_handler');
