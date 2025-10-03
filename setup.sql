-- setup.sql
-- One-shot setup for SecurePay E-Wallet demo on MySQL (XAMPP)
-- Creates database, tables, and marks Sagar as admin.

-- Safety: drop and recreate database (optional). Comment DROP if you want to keep existing data.
DROP DATABASE IF EXISTS securepay_db;
CREATE DATABASE IF NOT EXISTS securepay_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE securepay_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    is_blocked TINYINT(1) DEFAULT 0,
    failed_attempts INT NOT NULL DEFAULT 0,
    lock_until DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Wallets table: stores each user's current balance
CREATE TABLE IF NOT EXISTS wallets (
    user_id INT PRIMARY KEY,
    balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT fk_wallets_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Transactions table: logs all wallet transactions
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    receiver_id INT,
    amount DECIMAL(12,2) NOT NULL,
    transaction_type ENUM('add_fund', 'transfer') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tx_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_tx_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Optional: seed an example user for Sagar if not present, then mark as admin
-- Insert Sagar if email not present
INSERT INTO users (username, email, password, is_admin)
SELECT 'Sagar Biswas', 'sagar@outlook.com', '$2y$10$exampleplaceholderhashxxxxxxxxxxxxxxx', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'sagar@outlook.com');

-- Ensure Sagar is admin (idempotent)
UPDATE users SET is_admin = 1 WHERE email = 'sagar@outlook.com';

-- Ensure corresponding wallet rows exist for all users
INSERT INTO wallets (user_id, balance)
SELECT u.id, 0.00
FROM users u
LEFT JOIN wallets w ON w.user_id = u.id
WHERE w.user_id IS NULL;
