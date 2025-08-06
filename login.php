<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include 'header.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <style type="text/css">
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
        }
        .split-container {
            display: flex;
            min-height: 100vh;
        }
        .split-left {
            flex: 1;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-end;
            padding: 0 5vw 0 0;
            border-right: 1px solid #e6e6e6;
        }
        .split-left-content {
            max-width: 420px;
            margin-right: 40px;
        }
        .split-left .logo-row {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
            display: none;
        }
        .split-left .logo-row img {
            height: 32px;
            margin-right: 10px;
        }
        .split-left h1 {
            font-size: 2.3rem;
            margin-bottom: 18px;
            color: #222;
            font-weight: 700;
        }
        .split-left .subtitle {
            color: #444;
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
        .split-left ul {
            list-style: none;
            padding: 0;
            margin: 0 0 30px 0;
        }
        .split-left ul li {
            display: flex;
            align-items: center;
            margin-bottom: 14px;
            font-size: 1rem;
            color: #333;
        }
        .split-left ul li .check {
            color: #2ecc71;
            margin-right: 10px;
            font-size: 1.2em;
        }
        .testimonial {
            background: #f8fafc;
            border-radius: 8px;
            padding: 18px 20px;
            margin-top: 20px;
            font-size: 1.01rem;
            color: #222;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }
        .testimonial .author {
            display: block;
            margin-top: 10px;
            color: #555;
            font-size: 0.97rem;
        }
        .split-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            background: #f5f7fa;
            padding-left: 5vw;
        }
        .login-card {
            background: #fff;
            padding: 40px 32px 32px 32px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.07);
            width: 100%;
            max-width: 360px;
        }
        .login-card h2 {
            margin-bottom: 20px;
            font-size: 1.5rem;
            color: #222;
            font-weight: 600;
        }
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .login-form label {
            font-size: 1rem;
            color: #222;
            margin-bottom: 4px;
            font-weight: 500;
        }
        .login-form input[type="text"],
        .login-form input[type="password"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            width: 100%;
        }
        .login-btn {
            background-color: #19A37E;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s;
        }
        .login-btn:hover {
            background-color: #218838;
        }
        .form-footer {
            margin-top: 18px;
            text-align: center;
            font-size: 0.97rem;
            color: #666;
        }
        .form-footer a {
            color: #19A37E;
            text-decoration: none;
        }
        .form-footer a:hover {
            text-decoration: underline;
        }
        .forgot-link {
            float: right;
            font-size: 0.97rem;
            color: #19A37E;
            text-decoration: none;
        }
        .error-message {
            color: #d32f2f;
            font-size: 0.97rem;
            margin-bottom: 10px;
        }
        @media (max-width: 900px) {
            .split-container {
                flex-direction: column;
            }
            .split-left, .split-right {
                flex: none;
                width: 100%;
                max-width: 100vw;
                padding: 0;
                border-right: none;
            }
            .split-left {
                align-items: center;
                border-bottom: 1px solid #eee;
                padding-bottom: 30px;
            }
            .split-left-content {
                margin: 0;
            }
            .split-right {
                justify-content: center;
                padding: 30px 0;
            }
        }
    </style>
</head>
<body>
    <div class="split-container">
        <!-- Left Side: Welcome/Info -->
        <div class="split-left">
            <div class="split-left-content">
                <div class="logo-row">
                    <!-- <img src="https://app.getgradegenie.com/assets/gradegenie-logo.png" alt="GradeGenie Logo"> -->
                    <!-- <span style="font-size:1.3rem;font-weight:700;color:#28a745;">GradeGenie</span> -->
                </div>
                <h1>Welcome back to GradeGenie</h1>
                <div class="subtitle">The all-in-one AI copilot built for educators that saves teachers 5+ hours every week.</div>
                <ul>
                    <li><span class="check">&#10003;</span> Grade assignments in minutes, not hours</li>
                    <li><span class="check">&#10003;</span> Instantly create detailed feedback, rubrics, and syllabus</li>
                    <li><span class="check">&#10003;</span> Trusted by 10,000+ educators across 500+ schools</li>
                </ul>
                <div class="testimonial">
                    "I didn’t expect to rely on GradeGenie as much as I do now. It’s taken over the tedious parts and saved me hours each week. I can finally leave school without a pile of work waiting at home."
                    <span class="author">— Sarah Johnson, High School English Teacher</span>
                </div>

            </div>
        </div>
        <!-- Right Side: Login Form -->
        <div class="split-right">
            <div class="login-card">
                <h2>Log in to your account</h2>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>
                <form class="login-form" action="api/auth.php" method="post">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" placeholder="m@example.com" required>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <label for="password" style="margin-bottom:0;">Password</label>
                        <a href="mailto:hello@getgradegenie.com" class="forgot-link">Forgot password?</a>
                    </div>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <button type="submit" class="login-btn">Log In</button>
                </form>
                <div class="form-footer">
                    Don't have an account? <a href="signup.php">Sign up for free</a>
                </div>
                <div style="margin-top:10px;text-align:center;color:#bbb;font-size:0.95em;">
                    <span style="font-size:0.9em;">Secure login</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
