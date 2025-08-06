<?php
session_start();

// Redirect if not coming from signup
if (!isset($_SESSION['trial_end'])) {
    header('Location: index.php');
    exit;
}

// Immediately redirect to index.php
header('Location: index.php');
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to GradeGenie - Your Trial Has Started</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
            font-family: 'Albert Sans', sans-serif;
            color: #333;
        }
        .container {
            max-width: 800px;
            width: 100%;
            margin: 50px auto;
            padding: 30px;
            border-radius: 12px;
            background-color: #fff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-icon {
            font-size: 80px;
            color: #16a085;
            margin-bottom: 20px;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .trial-info {
            background-color: #e8f8f5;
            border-radius: 8px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
            border-left: 4px solid #16a085;
        }
        .trial-info h2 {
            color: #16a085;
            margin-top: 0;
        }
        .btn {
            display: inline-block;
            background-color: #16a085;
            color: #fff;
            padding: 14px 28px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background-color: #138a72;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .features {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin: 40px 0;
        }
        .feature {
            flex-basis: 30%;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .feature i {
            font-size: 30px;
            color: #16a085;
            margin-bottom: 10px;
        }
        .feature h3 {
            margin: 10px 0;
            color: #2c3e50;
        }
        @media (max-width: 768px) {
            .feature {
                flex-basis: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Welcome to GradeGenie, <?php echo htmlspecialchars($user_name); ?>!</h1>
        <p>Your account has been created successfully.</p>
        
        <div class="trial-info">
            <h2>Your 3-Day Free Trial Has Started</h2>
            <p>You now have full access to all GradeGenie features for the next 3 days. Your subscription will automatically begin after your trial period ends.</p>
            <p><strong>Trial End Date:</strong> <?php echo htmlspecialchars($formatted_date); ?></p>
            <p><i class="fas fa-info-circle"></i> You won't be charged until your trial period ends. You can cancel anytime during your trial period.</p>
        </div>
        
        <div class="features">
            <div class="feature">
                <i class="fas fa-bolt"></i>
                <h3>Grade Faster</h3>
                <p>Grade hundreds of papers in seconds with One-Click Bulk Grading</p>
            </div>
            <div class="feature">
                <i class="fas fa-chart-line"></i>
                <h3>Better Feedback</h3>
                <p>Evidence-based feedback highlighting strengths and weaknesses</p>
            </div>
            <div class="feature">
                <i class="fas fa-clock"></i>
                <h3>Save Time</h3>
                <p>Reclaim 10+ hours per week with automated grading assistance</p>
            </div>
        </div>
        
        <a href="index.php" class="btn">Go to Dashboard <i class="fas fa-arrow-right"></i></a>
        
        <script>
            // Automatically redirect to index.php after 5 seconds
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 5000);
        </script>
    </div>
</body>
</html>
