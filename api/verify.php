<?php

// do the opposite of password_verify function and give me the encrypted password for 12345
// echo password_hash('12345', PASSWORD_DEFAULT);

$host = 'localhost';
$username = 'root';
$password = 'JustWing1t';
$database = 'grady';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
echo 'Connected successfully';
$conn->close();
?>
