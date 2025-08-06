<?php
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" href="https://app.getgradegenie.com/assets/apple-touch-icon.png" sizes="180x180" type="image/png">
    <link rel="icon" href="https://app.getgradegenie.com/assets/ggfav.png" sizes="32x32" type="image/png">
    <!-- <link rel="icon" href="https://app.getgradegenie.com/assets/favicon-16x16.png" sizes="16x16" type="image/png"> -->
    <link rel="manifest" href="/site.webmanifest">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Onest:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <script>window.$zoho=window.$zoho || {};$zoho.salesiq=$zoho.salesiq||{ready:function(){}}</script>
    <script id="zsiqscript" src="https://salesiq.zohopublic.com/widget?wc=siqd253e99bcf4402f90ed4a732998ac4f6d43aaaa72afa6b9ca43c8f5c9da0bb40" defer></script>
    <script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "nlkn82kdz8");
</script>
</head>
<body>
    <header>
        <div id="logo">
            <a href="https://app.getgradegenie.com" class="logo-link">
                <img src="https://app.getgradegenie.com/assets/gg.png" alt="Grade Genie Logo" class="logo-image">
            </a>
        </div>
        <div id="right">
            <?php if (isset($_SESSION['user_first_name'])): ?>
                Welcome, <span id="userFirstName"><?php echo htmlspecialchars(explode(" ", $_SESSION['user_first_name'])[0]); ?></span> | 
                <a href="logout.php" class="header-link">Logout</a>
            <?php else: ?>
                <a href="login.php" class="header-link">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <style>
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        #logo .logo-link {
            display: flex;
            align-items: center;
        }

        #logo .logo-image {
            height: 40px; /* Adjust this value as needed */
            width: auto;
        }
    </style>
