<?php
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user ID from session if available
$user_id = $_SESSION['user_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <div id="overlay"></div>
    <div class="modal" id="registrationModal">
        <span class="closeModal" onclick="closeRegistrationModal();">&times;</span>
        <div class="modal-content">
            <h2>Create a new GradeGenie account</h2>
            <p>Create your account to start using GradeGenie.</p>
            <form id="registrationForm">
                <input type="text" id="firstName" name="firstName" placeholder="First Name" required><br>
                <input type="text" id="lastName" name="lastName" placeholder="Last Name" required><br>
                <input type="email" id="email" name="email" placeholder="Email Address" required><br>
                <input type="password" id="password" name="password" placeholder="Password" required><br>
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required><br>
                <button type="submit" class="formCTA">Create an Account</button>
            </form>
            <p>Already have an account? <a href="login.php">Log In</a></p>
            <p>By signing up you agree to GradeGenie's <a href="https://www.getgradegenie.com/terms-conditions">Terms & Conditions</a> and <a href="https://www.getgradegenie.com/privacy-policy">Privacy Policy</a>.</p>
        </div>
    </div>
</body>
<script>
    $(document).ready(function() {
        // Show the registration modal on page load if not logged in
        <?php if (!isset($_SESSION['user_id'])): ?>
            showRegistrationModal();
        <?php endif; ?>
        
        // Handle registration form submission
        $('#registrationForm').submit(function(event) {
            event.preventDefault();
            var formData = {
                firstName: $('#firstName').val(),
                lastName: $('#lastName').val(),
                email: $('#email').val(),
                password: $('#password').val(),
                confirmPassword: $('#confirmPassword').val()
            };
            $.ajax({
                type: 'POST',
                url: 'api/register.php',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        alert('Registration successful. Please log in.');
                        window.location.href = 'login.php';
                    } else {
                        alert('Failed to register: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while registering.');
                }
            });
        });
    });

    function showRegistrationModal() {
        $('#overlay').show();
        $('#registrationModal').show();
    }

    function closeRegistrationModal() {
        $('#overlay').hide();
        $('#registrationModal').hide();
    }
</script>
<style>
    body {
        font-family: 'Albert Sans', sans-serif;
    }

    #overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .modal {
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px #5d5d5d;
        width: 400px;
        max-width: 90%;
        z-index: 2000;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .modal-content {
        text-align: center;
    }

    .modal-content h2 {
        margin-bottom: 10px;
    }

    .modal-content p {
        margin-bottom: 20px;
    }

    .modal-content form input {
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

    .closeModal {
        float: right;
        cursor: pointer;
    }
</style>
</html>
