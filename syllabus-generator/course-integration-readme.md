# Syllabus Generator Course Integration

This document explains how to integrate the Syllabus Generator with your existing course system, allowing users to create, edit, save, and manage syllabi directly from course pages.

## Features

- **Course Integration**: Link syllabi directly to courses in your existing database
- **Multiple Syllabi Per Course**: Create and manage multiple syllabi for each course
- **Server-Side Storage**: Save syllabi to your database for access across devices
- **Edit & Delete**: Full CRUD operations for syllabus management
- **Modal Interface**: Clean UI integration with your existing course pages

## Implementation

### 1. Database Setup

First, create the syllabi table in your database:

```sql
CREATE TABLE syllabi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    title VARCHAR(512) NOT NULL,
    content LONGTEXT NOT NULL,
    form_data JSON NOT NULL,
    created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(cid) ON DELETE CASCADE
);
```

### 2. API Endpoints

Three PHP files handle all syllabus operations:

- **syllabus-save.php**: Creates or updates syllabi
- **syllabus-get.php**: Retrieves syllabi (single or by course)
- **syllabus-delete.php**: Deletes syllabi

These files are already configured to:
- Verify user authentication
- Check course ownership
- Handle JSON data storage and retrieval

### 3. Frontend Integration

To add the Syllabus Generator to your course pages:

1. Include the required JavaScript and CSS files:

```html
<link rel="stylesheet" href="/syllabus-generator/course-integration-styles.css">
<script src="/syllabus-generator/course-integration.js"></script>
```

2. Initialize the integration on your course page:

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize syllabus generator with course ID and name
    SyllabusIntegration.initSyllabusGenerator(courseId, courseName);
});
```

### 4. Syllabus Generator Modifications

The syllabus generator has been modified to:
- Accept course ID and name parameters
- Save syllabi to the server instead of localStorage
- Load syllabi from the server for editing
- Communicate with the parent window when operations complete

## Usage Flow

1. User navigates to a course page
2. User clicks "Create Syllabus" button
3. Syllabus Generator opens in a modal
4. User creates or edits a syllabus
5. Syllabus is saved to the database
6. User can view, edit, or delete syllabi from the course page

## Security Considerations

- All API endpoints verify user authentication via JWT
- Course ownership is verified before any syllabus operations
- Form data is sanitized before storage
- Cross-site scripting (XSS) protections are in place

## Customization

You can customize the integration by:
- Modifying the CSS to match your site's design
- Changing the modal behavior or layout
- Adding additional fields to the syllabi table
- Extending the API endpoints with additional functionality
