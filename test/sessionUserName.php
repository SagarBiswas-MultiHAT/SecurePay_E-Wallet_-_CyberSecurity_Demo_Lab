<?php
session_start();
// echo password_hash('hackthebox', PASSWORD_DEFAULT); 

echo 'Logged in as: ' . ($_SESSION['username'] ?? 'Not logged in');
