
# Book Request Management System

This is a PHP-MySQL-based Book Request Management System built as part of the Web Engineering (6th Semester) course project. It offers role-based access control (User, Admin, Super Admin) and integration with the Google Books API.

---

## Features

### User Panel
- Register/Login with username, email, and password
- View and request books by:
  - Selecting book title and author from Google Books API (FormData submission)
  - Choosing from categories: App Development, Mobile Development, AI
  - Uploading optional file (multipart/form-data)
- Dashboard showing:
  - All book requests and statuses
  - Notifications (e.g., "Your request for PHP book is now completed.")
  - Option to cancel pending requests

### Admin Panel
- Login with admin credentials
- View-only dashboard showing:
  - Total users
  - Total book requests
  - Requests in progress and completed

### Super Admin Panel
- Manage all requests and users
  - View/Edit/Delete book requests
  - Add/Delete Admins
  - Reset user passwords
  - Remove users

---

## API Integration

- Google Books API: `https://www.googleapis.com/books/v1/volumes?q=web+development`
- Users are restricted to **5 API requests per 24 hours**.

---

## Folder Structure 

```
book-store/
├── admin_dashboard.php
├── dashboard.php
├── db_connect.php
├── fetch_books.php
├── index.php
├── register.php
├── request_book.php
├── super_admin.php
├── css/
│   ├── background_image.jpg
│   ├── signupstyle.css
│   ├── style.css
│   └── superadminstyle.css
├── uploads/
│   └── index.txt

```

---

## Non-Functional Requirements

- **Security**: SQL Injection protection
- **Maintainability**: Modular structure using `require` and `include`
- **Reliability**: Error reporting suppressed for notices/warnings

---

