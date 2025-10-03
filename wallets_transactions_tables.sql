-- wallets_transactions_tables.sql
-- MySQL schema for wallets and transactions tables for SecurePay

-- Wallets table: stores each user's current balance
CREATE TABLE IF NOT EXISTS wallets (
    user_id INT PRIMARY KEY,
    balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
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
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Note: For add_fund, sender_id and receiver_id are the same (the user).
-- For transfer, sender_id is the sender, receiver_id is the recipient.
