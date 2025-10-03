<?php
// functions.php
// Common reusable functions for input validation and session management

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to sanitize user input
function sanitize_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to check if user is logged in
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

// Function to redirect to login if not logged in
function require_login()
{
    // Inactivity timeout: 5 minutes
    $timeout = 300; // seconds
    // Detect if current script is in admin folder
    $isAdmin = strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false;
    $loginPath = $isAdmin
        ? '/Web_Tech_Project/SecurePay_E-Wallet_&_Cybersecurity_Demo_Lab/admin/login.php'
        : '/Web_Tech_Project/SecurePay_E-Wallet_&_Cybersecurity_Demo_Lab/auth/login.php';
    if (!is_logged_in()) {
        header('Location: ' . $loginPath);
        exit();
    }
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        session_unset();
        session_destroy();
        header('Location: ' . $loginPath . '?timeout=1');
        exit();
    }
    $_SESSION['last_activity'] = time();
}
