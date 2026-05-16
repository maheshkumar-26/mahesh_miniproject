# Employee Compensation Insights

A full-featured PHP web application for managing employee payroll, salary analytics, and compensation transparency.

---

## Features

- **Admin Panel** — Manage employees, departments, payroll, and reports
- **Employee Portal** — View salary breakdowns, payroll history, compensation insights, and submit feedback
- **Interactive Charts** — Chart.js powered bar, line, and doughnut charts
- **Payslip Generation** — Printable payslips for each payroll period
- **CSV Export** — Export payroll data to CSV
- **Notifications** — In-app notifications for payroll events
- **Secure Auth** — CSRF protection, bcrypt passwords, session management
- **Responsive UI** — Bootstrap 5, works on mobile and desktop

---

## Requirements

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite (XAMPP / WAMP / Laragon recommended)
- Web browser (Chrome, Firefox, Edge)

---

## Installation

### 1. Clone / Copy the Project

Place the project folder inside your web server root:

```
C:\xampp\htdocs\employee-compensation-insights\
```

### 2. Import the Database

1. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Click **New** → create database named `employee_compensation_insights`
3. Select the database → click **Import**
4. Choose `database/schema.sql` → click **Go**

### 3. Configure Database Connection

Open `config/db.php` and update if needed:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // your MySQL password
define('DB_NAME', 'employee_compensation_insights');
define('BASE_URL', 'http://localhost/employee-compensation-insights/');
```

### 4. Set Permissions

Make sure the `uploads/` folder is writable:

```bash
chmod 755 uploads/
```

On Windows (XAMPP), this is usually not needed.

### 5. Open in Browser

```
http://localhost/employee-compensation-insights/
```

---

## Default Login Credentials

> Password for all accounts: **`password`**

| Role     | Email              | Password |
|----------|--------------------|----------|
| Admin    | admin@eci.com      | password |
| Employee | rahul@eci.com      | password |
| Employee | priya@eci.com      | password |
| Employee | amit@eci.com       | password |
| Employee | sneha@eci.com      | password |
| Employee | vikram@eci.com     | password |

---

## Folder Structure

```
employee-compensation-insights/
├── admin/
│   ├── dashboard.php          # Admin dashboard with charts
│   ├── employees.php          # Employee list with search/filter
│   ├── add_employee.php       # Add new employee
│   ├── edit_employee.php      # Edit employee details
│   ├── departments.php        # Manage departments
│   ├── payroll.php            # Payroll list and management
│   ├── add_payroll.php        # Generate new payroll
│   ├── reports.php            # Analytics and CSV export
│   └── profile.php            # Admin profile settings
├── employee/
│   ├── dashboard.php          # Employee dashboard
│   ├── salary_breakdown.php   # Detailed salary breakdown with bars
│   ├── payroll_history.php    # All payslips + printable view
│   ├── insights.php           # Compensation analytics
│   ├── feedback.php           # Satisfaction index / star ratings
│   └── profile.php            # Employee profile settings
├── auth/
│   ├── login.php              # Login page
│   ├── logout.php             # Logout handler
│   └── forgot_password.php    # Password reset request
├── config/
│   ├── config.php             # App configuration
│   └── db.php                 # Database connection (MySQLi)
├── includes/
│   ├── header.php             # HTML head + Bootstrap CDN
│   ├── footer.php             # Bootstrap JS + Chart.js + main.js
│   ├── topbar.php             # Top navigation bar
│   ├── admin_sidebar.php      # Admin sidebar navigation
│   ├── employee_sidebar.php   # Employee sidebar navigation
│   ├── auth_helper.php        # Auth functions
│   └── helpers.php            # Utility functions
├── assets/
│   ├── css/style.css          # Full custom CSS
│   ├── js/main.js             # JavaScript (charts, interactions)
│   └── images/                # Static images (default-avatar.png)
├── database/
│   └── schema.sql             # Full DB schema + sample data
├── uploads/                   # Employee profile images
└── index.php                  # Landing page
```

---

## Tech Stack

| Layer      | Technology                        |
|------------|-----------------------------------|
| Backend    | PHP 8 (procedural + OOP DB class) |
| Database   | MySQL / MariaDB                   |
| Frontend   | Bootstrap 5.3, Bootstrap Icons    |
| Charts     | Chart.js 4.4                      |
| Fonts      | Google Fonts (Inter)              |
| Auth       | PHP Sessions + bcrypt             |

---

## Security Features

- CSRF tokens on all forms
- Prepared statements (no SQL injection)
- `password_hash()` / `password_verify()` for passwords
- `htmlspecialchars()` output sanitization
- Session regeneration on login
- Role-based access control (admin / employee)

---

## Screenshots

> Run the project locally and log in with the demo credentials to explore all features.

---

## License

This project is built as a mini project for educational purposes.
