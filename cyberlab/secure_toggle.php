<?php
// secure_toggle.php
// Global toggle for Secure/Vulnerable mode in Cyber Lab
// Usage: include this file at the top of each demo page

session_start();

// Set mode via ?mode=secure or ?mode=vulnerable
if (isset($_GET['mode'])) {
    if ($_GET['mode'] === 'secure') {
        $_SESSION['secure_mode'] = 'secure';
    } elseif ($_GET['mode'] === 'vulnerable') {
        $_SESSION['secure_mode'] = 'vulnerable';
    }
    // Optional: redirect to remove mode param from URL
    $url = strtok($_SERVER['REQUEST_URI'], '?');
    header('Location: ' . $url);
    exit();
}

// Helper function to check current mode
function isSecureMode()
{
    return (isset($_SESSION['secure_mode']) && $_SESSION['secure_mode'] === 'secure');
}

// Default to vulnerable if not set
if (!isset($_SESSION['secure_mode'])) {
    $_SESSION['secure_mode'] = 'vulnerable';
}
