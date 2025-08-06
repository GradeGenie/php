# Deployment Instructions for Syllabus Generator with 3-Day Free Trial (PHP Version)

This document provides instructions for deploying the Syllabus Generator application with the 3-day free trial feature to your production environment at app.getgradegenie.com.

## Files to Deploy

### Frontend Files
- `index.html`
- `styles.css`
- `script.js`
- `auth.js`

### Backend Files (PHP)
- `server/api/` directory with all PHP files
- `server/config/` directory with database configuration
- `server/composer.json`

## Deployment Steps

### 1. Frontend Deployment

Deploy the frontend files to your web server at the `/new` path:
```
https://app.getgradegenie.com/new/
```

### 2. Backend Deployment

Deploy the server directory to your production environment at:
```
https://app.getgradegenie.com/new/server/
```

### 3. PHP Dependencies Installation

In the server directory, run:
```
composer install
```

This will install the required PHP packages (Stripe PHP SDK and Firebase JWT).

### 4. Stripe Integration

Before deploying to production, make sure to:

1. Replace the Stripe test keys with your production keys:
   - In all PHP files in the `server/api/` directory: Replace `sk_test_your_stripe_secret_key`
   - In `auth.js`: Replace `pk_test_your_stripe_key`
   - In `server/api/webhook.php`: Replace `whsec_your_webhook_secret`

2. Test the subscription flow in a staging environment before going live

### 5. Database Setup

Ensure your production database has the necessary table structure as specified in your requirements:

```sql
CREATE TABLE users (
  uid INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255),
  password VARCHAR(255),
  join_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_login DATETIME DEFAULT CURRENT_TIMESTAMP,
  stripeID VARCHAR(255),
  active_sub INT,
  subscription_id VARCHAR(255),
  plan_id VARCHAR(255),
  subscription_status VARCHAR(50),
  trial_end_date DATETIME,
  trial_ending TINYINT(1) DEFAULT 0
);
```

### 6. Database Configuration

Update the database connection details in `server/config/database.php` to match your production database:

```php
$host = "your_production_db_host";
$db_name = "your_production_db_name";
$username = "your_production_db_username";
$password = "your_production_db_password";
```

## Testing

After deployment, test the following flows:

1. User registration with the 3-day free trial
2. Login for existing users
3. Trial expiration notification
4. Subscription process after trial ends ($18.99/month)

## Monitoring

Monitor your Stripe dashboard for:
- Successful trial registrations
- Subscription conversions
- Failed payments
