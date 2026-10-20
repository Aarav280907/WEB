<?php
/**
 * StudentHub Portal - Shared configuration (used from Practical 9 onwards)
 * All page URLs below are relative to a module folder such as Practical-10/.
 */

// Idle time (seconds) after which a logged-in session expires. 15 minutes by default;
// the SH_TIMEOUT environment variable lets testers shorten it.
define('SESSION_TIMEOUT', (int)(getenv('SH_TIMEOUT') !== false ? getenv('SH_TIMEOUT') : 900));

// "Remember me" cookie lifetime in days
define('REMEMBER_DAYS', 30);

// Where each role lands after login
define('LOGIN_URL',    '../Practical-10/login.php');
define('STUDENT_HOME', '../Practical-10/dashboard.php');
define('ADMIN_HOME',   '../Practical-14/admin_dashboard.php');

// Poster upload limits (Practical 12)
define('POSTER_MAX_BYTES', 2 * 1024 * 1024);   // 2 MB

// Password rule shared by registration (Practical 9) and the JavaScript validator (Practical 5)
define('PASSWORD_REGEX', '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,64}$/');
