<?php
echo "poopies";
exit();
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT name, email FROM users WHERE uid = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($name, $email);
$stmt->fetch();
$stmt->close();
$conn->close();

list($first_name, $last_name) = explode(' ', $name, 2);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile0 | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <div id="mainContent">
        <h2>Profile</h2>
        <p>Update your profile information below.</p>
        <form id="profileForm">
            <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($first_name); ?>" placeholder="First Name" required><br>
            <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($last_name); ?>" placeholder="Last Name" required><br>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Email Address" disabled><br>
            <input type="password" id="password" name="password" placeholder="New Password"><br>
            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm New Password"><br>
            <button type="submit" class="formCTA">Update Profile</button>
        </form>
    </div>
</body>
<script>
    $(document).ready(function() {
        // Handle profile form submission
        $('#profileForm').submit(function(event) {
            event.preventDefault();
            var formData = {
                firstName: $('#firstName').val(),
                lastName: $('#lastName').val(),
                password: $('#password').val(),
                confirmPassword: $('#confirmPassword').val()
            };
            $.ajax({
                type: 'POST',
                url: 'api/update_profile.php',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        alert('Profile updated successfully.');
                    } else {
                        alert('Failed to update profile: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while updating the profile.');
                }
            });
        });
    });
</script>
<style>
    body {
        font-family: 'Albert Sans', sans-serif;
    }

    #mainContent {
        padding: 20px;
    }

    h2 {
        margin-bottom: 10px;
    }

    form input {
        width: 100%;
        padding: 10px;
        margin: 5px 0;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    .formCTA {
        background: #28a745;
        text-align: center;
        padding: 10px 5px;
        color: #fff;
        font-weight: bold;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        display: inline-block;
        margin: 10px 5px;
        border: none;
    }
</style>
</html>
