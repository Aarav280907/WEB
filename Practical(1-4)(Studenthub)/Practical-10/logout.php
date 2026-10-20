<?php
/**
 * Practical 10 - Logout: removes the session, the session cookie and any remember-me token.
 */
require_once __DIR__ . '/../includes/auth.php';

start_session();
logout_user($conn);
redirect('login.php?loggedout=1');
