# StudentHub Portal (ITUE203 - Web Development Frameworks)

A semester-long student portal: static front end (Practicals 1-6) plus a PHP/MySQL back end (Practicals 7-15).

## Requirements
PHP 8.1+ (mysqli, pdo_mysql, fileinfo, mbstring), MySQL 8 / MariaDB 10.4+, Apache (XAMPP is fine) or `php -S`.

## Installation
1. Copy this folder to your web root (e.g. `htdocs/studenthub`).
2. Create the database: `cd Practical-15 && ./reset_db.sh root` (or import `Practical-15/studenthub_full.sql`).
3. Check credentials in `Practical-8/db.php` (defaults: host 127.0.0.1, user root, empty password, db `studenthub`; override with env vars `SH_DB_*`).
4. Make `Practical-12/uploads/` and `Practical-7/` writable by the web server.
5. Open `Practical-10/login.php`. Quick test server: `php -S 127.0.0.1:8080 -t .`

## Demo logins
| Role | Login | Password |
|---|---|---|
| Admin | admin@charusat.edu.in | Admin@123 |
| Student | 25dce072@charusat.edu.in (or 25DCE072) | Student@123 |

## Structure
| Folder | Practical |
|---|---|
| `Html folder/`, `for_css/`, `javascript/` | P1-P5 static pages, CSS, JS (P5 validation: `javascript/validation.js`) |
| `Practical-6/` | JSON data for the Fetch API pages |
| `Practical-7/` | PHP form processing, CSV/JSON storage |
| `Practical-8/` | schema, seed, ER diagram, `db.php`, `db_pdo.php`, stored procedures |
| `Practical-9/` | secure registration (`register.php`, `users.sql`) |
| `Practical-10/` | login, logout, dashboards, sessions, remember-me |
| `Practical-11/` | student CRUD, search, filter, CSV export |
| `Practical-12/` | event CRUD, poster upload, public gallery |
| `Practical-13/` | JSON API (`api/`) and AJAX student manager |
| `Practical-14/` | admin dashboard, users, audit log |
| `Practical-15/` | full SQL dump, reset script, Apache vhost, test report |
| `includes/` | shared config, helpers, auth, layout, validators, upload |
| `for_css/extra/PRACTICAL-N.docx` | lab documents |
| `screenshots/` | screenshots used in the documents |

## Testing
See `Practical-15/TEST_REPORT.md`. Run `php -l` on any file for a syntax check.
## Security notes
Prepared statements everywhere, `password_hash`, CSRF tokens, escaped output, HttpOnly/SameSite cookies, session timeout, upload validation. Use HTTPS and a limited DB user in production.
