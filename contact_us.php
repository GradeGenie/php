<!DOCTYPE html>
<html lang="en">
<head>
<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <title>Contact Us - GradeGenie</title>
    <style>
        :root {
            --primary-color: #4caf50;
            --primary-hover: #45a049;
            --secondary-color: #2196f3;
            --secondary-hover: #1e88e5;
            --tertiary-color: #ff9800;
            --tertiary-hover: #f57c00;
            --bg-color: #f4f4f4;
            --white: #ffffff;
            --text-primary: #333333;
            --text-secondary: #666666;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Albert Sans', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .contact-form {
            background-color: var(--white);
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        select,
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
            font-size: 1rem;
        }

        textarea {
            resize: vertical;
            min-height: 150px;
        }

        .submit-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: var(--primary-hover);
        }

        .error-message {
            color: #ff0000;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .success-message {
            color: #28a745;
            font-size: 1rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <?php
    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $successMessage = '';
    $errorMessage = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Specify the recipient email address
        $to = 'hello@getgradegenie.com';

        // Retrieve form data
        $category = $_POST['category'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $message = $_POST['message'];

        // Validate required fields
        if (empty($category) || empty($name) || empty($email) || empty($phone) || empty($message)) {
            $errorMessage = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Validate email
            $errorMessage = 'Invalid email format.';
        } else {
            // Prepare email content
            $subject = "New Contact Us Message: $category";
            $body = "Category: $category\n";
            $body .= "Name: $name\n";
            $body .= "Email: $email\n";
            $body .= "Phone: $phone\n\n";
            $body .= "Message:\n$message";

            // Set headers
            $headers = "From: $email\r\n";
            $headers .= "Reply-To: $email\r\n";

            // Send email
            if (mail($to, $subject, $body, $headers)) {
                $successMessage = 'Your message has been sent successfully.';
            } else {
                $errorMessage = 'Unable to send email. Please try again.';
            }
        }
    }
    ?>

    <div class="container">
        <h1 class="section-title">Contact Us</h1>
        <?php if ($successMessage): ?>
            <p class="success-message"><?php echo $successMessage; ?></p>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <p class="error-message"><?php echo $errorMessage; ?></p>
        <?php endif; ?>
        <form id="contactForm" class="contact-form" action="" method="POST">
            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="">Select a category</option>
                    <option value="Request Feature">Request Feature</option>
                    <option value="Report Bug">Report Bug</option>
                    <option value="General Inquiry">General Inquiry</option>
                    <option value="Support">Support</option>
                </select>
            </div>
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" required>
                <span id="phoneError" class="error-message"></span>
            </div>
            <div class="form-group">
                <label for="message">Message:</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <button type="submit" class="submit-btn">Send Message</button>
        </form>
    </div>

    <script>
        document.getElementById('contactForm').addEventListener('submit', function(event) {
            const phoneInput = document.getElementById('phone');
            const phoneError = document.getElementById('phoneError');
            const phoneRegex = /^\+?(\d{1,3})?[-.\s]?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/;

            if (!phoneRegex.test(phoneInput.value)) {
                event.preventDefault();
                phoneError.textContent = 'Please enter a valid phone number.';
            } else {
                phoneError.textContent = '';
            }
        });
    </script>
</body>
</html>
