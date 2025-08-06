# Syllabus Generator with 3-Day Free Trial

A professional web application that allows educators to generate comprehensive course syllabi with a subscription model that includes a 3-day free trial.

## Grading Made Effortless. Feedback Students Love.
Save hours every week with AI-powered grading that's fast, fair, and transparent.

## Features Overview

### Core Features
- **Professional Syllabus Generation**: Create detailed, customizable syllabi for educational courses
- **PDF Download**: Generate and download syllabi as professional PDF documents
- **Responsive Design**: Works seamlessly on desktop and mobile devices

### Authentication & Subscription System
- **User Registration**: Create an account with name, email, and password
- **3-Day Free Trial**: Automatic access to premium features upon registration
- **Stripe Integration**: Secure payment processing for subscriptions
- **JWT Authentication**: Secure token-based authentication

## Pricing Plans

### Educator Plan
- **Monthly**: $18.99 / seat / month
- **Annual**: $16.99 / seat / month, billed yearly
- **Ideal for**: Individual teachers who want to save time and give better feedback

**Features include:**
- Grade hundreds of papers in seconds with One-Click Bulk Grading
- Final score auto-calculated from weighted subscores (with clear rationale to avoid disputes)
- Evidence-based feedback highlighting strengths, weaknesses, and improvement areas
- Inline AI comments directly on student submissions
- GradeGenie creates rubrics, syllabi, and assignment briefs for you
- Email grade reports to students or export as PDF

### Institution Plan
- **Monthly**: $19.99 / seat / month (min 3 seats)
- **Annual**: $17.99 / seat / month (min 3 seats), billed yearly
- **Ideal for**: Teams and schools who need collaboration and shared grading

**Everything in Educator Plan, plus:**
- Pre-built rubrics and feedback templates for consistent grading
- Collaborate with your team on assignments
- Role-based access for teachers, TAs, and assistants
- Track grading progress with a team dashboard
- Centralized billing for simplified payments

### Enterprise Plan
- **Custom Pricing** (min 3 seats)
- **Ideal for**: Universities and institutions needing advanced tools and support

**Everything in Institution Plan, plus:**
- Plagiarism detection and originality checks
- Student Self-Check Portal for pre-submission feedback
- Support for advanced assignment types and formats
- Custom LMS integrations for seamless workflows
- Advanced analytics and reporting for educators and admins
- Dedicated relationship manager and priority support
- Volume-based pricing and centralized admin controls

## Directory Structure

### Frontend Files
Located in `/var/www/html/new/`:

- **index.html**: Main application interface and syllabus form
- **styles.css**: Styling for the application and authentication modals
- **script.js**: Core application functionality for syllabus generation
- **auth.js**: Authentication and subscription management

### Backend API
Located in `/var/www/html/new/api/`:

- **register-with-trial.php**: Handles user registration with 3-day free trial
- **syllabus-login.php**: Authenticates users and provides JWT tokens
- **syllabus-verify-token.php**: Validates JWT tokens for protected routes
- **syllabus-subscribe.php**: Processes subscription payments via Stripe
- **syllabus-webhook.php**: Handles Stripe webhook events for subscription management

## Technical Implementation

### Authentication Flow
1. User registers with email/password and credit card information
2. System creates a Stripe customer and sets a 3-day trial period
3. JWT token is generated for authenticated access
4. After 3 days, the system prompts for subscription payment

### Database Schema
The application uses MySQL with the following key tables:
- **users**: Stores user information, authentication details, and subscription status
  - Fields: id, name, email, password (hashed), stripeID, trial_end_date, trial_ending, subscription_status

### API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/register-with-trial.php` | POST | Register new user with 3-day trial |
| `/api/syllabus-login.php` | POST | Authenticate user and return JWT |
| `/api/syllabus-verify-token.php` | POST | Verify JWT token validity |
| `/api/syllabus-subscribe.php` | POST | Process subscription payment |
| `/api/syllabus-webhook.php` | POST | Handle Stripe webhook events |

### Dependencies
- **Stripe PHP SDK**: Payment processing
- **Firebase JWT**: Token generation and validation
- **MySQL**: Database storage
- **PHP 7.4+**: Server-side processing

## User Experience

### Registration & Trial
1. User clicks "Sign Up" button
2. Enters name, email, password, and credit card details
3. Gets immediate access to premium features for 3 days
4. No charge during trial period

### Trial Expiration
1. User receives notification when trial is ending
2. Credit card is automatically charged for the first month
3. Subscription continues monthly until cancelled

### Subscription Management
1. Users can manage their subscription through their account
2. Cancel anytime through the application interface

## Security Features
- Passwords are securely hashed using PHP's `password_hash()`
- JWT tokens for secure authentication
- HTTPS for all communications
- Stripe for PCI-compliant payment processing

## Access URL
The application is accessible at:
```
https://app.getgradegenie.com/new/
```

## Future Enhancements
- Email notifications for trial expiration
- Additional subscription tiers
- Bulk discount options for educational institutions
