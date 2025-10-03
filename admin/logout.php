<?php
// Admin Logout for SecurePay
session_start();
// Destroy all session data
session_unset();
session_destroy();
// Redirect to login page
header('Location: login.php');
exit;
