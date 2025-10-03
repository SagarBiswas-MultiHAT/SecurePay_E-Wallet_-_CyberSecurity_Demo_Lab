<?php
// Admin access check
// Redirect to login or dashboard
session_start();
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
} // else, show dashboard as usual
// Admin Dashboard for SecurePay
// Shows user, wallet, and transaction statistics
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_login();

// Helper function to format amounts
function formatAmount($amount)
{
    return number_format((float)$amount, 2, '.', '');
}

// Get total number of users
$userCount = 0;
$sql = "SELECT COUNT(*) AS total FROM users";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $userCount = $row['total'];
}

// Get total wallet balance
$totalBalance = 0;
$sql = "SELECT SUM(balance) AS total FROM wallets";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $totalBalance = $row['total'] ? $row['total'] : 0;
}

// Get number of transactions today
$todayCount = 0;
$today = date('Y-m-d');

// Fix: Use correct column name for transaction date
$sql = "SHOW COLUMNS FROM transactions LIKE 'timestamp'";
$result = $conn->query($sql);
if ($result && $result->num_rows === 0) {
    // If 'timestamp' column does not exist, try 'created_at' or 'date'
    $dateColumn = 'created_at';
    $sql = "SHOW COLUMNS FROM transactions LIKE 'created_at'";
    $result2 = $conn->query($sql);
    if ($result2 && $result2->num_rows === 0) {
        $dateColumn = 'date';
    }
} else {
    $dateColumn = 'timestamp';
}

$sql = "SELECT COUNT(*) AS total FROM transactions WHERE DATE($dateColumn) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $today);
$stmt->execute();
$stmt->bind_result($todayCount);
$stmt->fetch();
$stmt->close();

// Get 10 most recent transactions with sender/receiver names

// Fix: Use correct column names for recent transactions
$recentTransactions = [];
// Detect available columns
$sql = "SHOW COLUMNS FROM transactions";
$result = $conn->query($sql);
$columns = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
}
$amountCol = in_array('amount', $columns) ? 'amount' : (in_array('value', $columns) ? 'value' : '');
$typeCol = in_array('type', $columns) ? 'type' : (in_array('transaction_type', $columns) ? 'transaction_type' : '');
$dateCol = in_array('timestamp', $columns) ? 'timestamp' : (in_array('created_at', $columns) ? 'created_at' : (in_array('date', $columns) ? 'date' : ''));


// Detect correct user name column
$userNameCol = 'full_name';
$sql = "SHOW COLUMNS FROM users LIKE 'full_name'";
$result = $conn->query($sql);
if ($result && $result->num_rows === 0) {
    $userNameCol = 'name';
    $sql = "SHOW COLUMNS FROM users LIKE 'name'";
    $result2 = $conn->query($sql);
    if ($result2 && $result2->num_rows === 0) {
        $userNameCol = 'username';
    }
}

$sql = "SELECT t.id"
    . ($amountCol ? ", t.$amountCol" : "")
    . ($typeCol ? ", t.$typeCol" : "")
    . ($dateCol ? ", t.$dateCol" : "")
    . ", u1.$userNameCol AS sender, u2.$userNameCol AS receiver
        FROM transactions t
        LEFT JOIN users u1 ON t.sender_id = u1.id
        LEFT JOIN users u2 ON t.receiver_id = u2.id
        ORDER BY t." . ($dateCol ? $dateCol : 'id') . " DESC
        LIMIT 10";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentTransactions[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | SecurePay</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');

        :root {
            --color-accent: #00ffea;
            --color-accent2: #3a86ff;
            --color-accent3: #7c3aed;
            --color-card: rgba(255, 255, 255, 0.18);
            --color-input: rgba(255, 255, 255, 0.22);
            --color-text: #e0e7ef;
            --color-placeholder: #b3c2e0;
            --color-border: #00ffea99;
        }

        * {
            box-sizing: border-box;
            font-family: "Inter", sans-serif;
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            color: var(--color-text);
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
            background: linear-gradient(120deg, #3a86ff 0%, #7c3aed 50%, #00ffea 100%);
            background-size: 200% 200%;
            animation: gradientMove 7s ease-in-out infinite;
            filter: blur(8px) brightness(0.8);
        }

        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .container {
            width: 100%;
            max-width: 1500px;
            margin: 60px auto 0 auto;
            padding: 32px 24px;
            background: var(--color-card);
            border-radius: 22px;
            box-shadow: 0 8px 32px 0 #00ffea33, 0 1.5px 4px 0 #3a86ff33;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(18px) saturate(1.3);
            border: 2.5px solid var(--color-border);
        }

        #header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            background: rgba(255, 255, 255, 0.13);
            padding: 18px 32px 18px 32px;
            border-radius: 12px;
            font-size: 1.1rem;
            box-shadow: 0 2px 12px #00ffea22;
        }

        #header h1 {
            margin: 0;
            font-size: 2.1em;
            font-weight: 900;
            letter-spacing: 2px;
            color: #000000cc;
            text-shadow: 0 2px 16px #00ffea, 0 2px 8px #3a86ff;
        }

        #header nav a {
            color: #00ffea;
            font-weight: 700;
            text-decoration: none;
            margin-right: 1.5rem;
            font-size: 1.08em;
            letter-spacing: 1px;
            transition: color 0.2s;
        }

        #header nav a:last-child {
            margin-right: 0;
        }

        #header nav a:hover {
            color: #7c3aed;
            text-decoration: underline;
        }

        .admin-grid {
            display: flex;
            gap: 2rem;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }

        .admin-card {
            border-radius: 18px;
            box-shadow: 0 2px 16px #00ffea22, 0 1.5px 6px #3a86ff33;
            padding: 32px 28px 28px 28px;
            min-width: 220px;
            flex: 1;
            background: linear-gradient(135deg, #3a86ff 0%, #7c3aed 100%);
            color: #fff;
            position: relative;
            overflow: hidden;
            border: 2px solid #00ffea55;
            margin-bottom: 10px;
            transition: box-shadow 0.2s, transform 0.2s;
        }

        .admin-card:hover {
            box-shadow: 0 8px 32px #00ffea44, 0 4px 24px #3a86ff33;
            transform: translateY(-4px) scale(1.03);
        }

        .admin-card h2 {
            margin: 0 0 0.5rem 0;
            font-size: 1.25em;
            color: #00ffea;
            font-weight: 800;
            letter-spacing: 1px;
            text-shadow: 0 2px 8px #3a86ff99;
        }

        .admin-card .stat {
            font-size: 2.2em;
            font-weight: bold;
            color: #fff;
            text-shadow: 0 2px 8px #00ffea99;
        }

        .recent-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            background: rgba(255, 255, 255, 0.13);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px #00ffea22;
        }

        .recent-table th,
        .recent-table td {
            padding: 12px 18px;
            border-bottom: 1.5px solid #3a86ff33;
            text-align: left;
            font-size: 1.05em;
        }

        .recent-table th.timestamp,
        .recent-table td.timestamp {
            text-align: center;
        }

        .recent-table th {
            background: linear-gradient(90deg, #3a86ff 0%, #7c3aed 100%);
            color: #fff;
            font-weight: 800;
            letter-spacing: 1px;
            border-bottom: 2.5px solid #00ffea99;
        }

        .recent-table tr:hover {
            background: #00ffea11;
        }

        @media (max-width: 900px) {
            .admin-grid {
                flex-direction: column;
                gap: 1.2rem;
            }
        }

        @media (max-width: 700px) {
            .container {
                padding: 8px 2vw 8vw 2vw;
                max-width: 99vw;
            }

            #header {
                flex-direction: column;
                gap: 10px;
                padding: 12px 8px 12px 8px;
            }

            .admin-card {
                padding: 18px 10px 18px 10px;
            }

            .recent-table th,
            .recent-table td {
                padding: 8px 6px;
                font-size: 0.98em;
            }
        }

        html {
            zoom: 90%;
        }
    </style>
</head>

<body>
    <div class="container">
        <div id="header">
            <h1 style="margin-top: 10px;">╰(..:: Admin Dashboard ::..)╯</h1>
            <nav>
                <a href="users.php" style="margin-right:1rem; color:#0077b6; font-weight:bold; text-decoration:none;">Users</a>
                <a href="logout.php" style="color:#d90429; font-weight:bold; text-decoration:none;">Logout</a>
            </nav>
        </div>
        <br>
        <!-- Statistics Grid -->
        <div class="admin-grid">
            <div class="admin-card total-users">
                <h2>Total Users</h2>
                <div class="stat"><?php echo $userCount; ?></div>
            </div>
            <div class="admin-card total-users">
                <h2>Total Wallet Balance</h2>
                <div class="stat">$<?php echo formatAmount($totalBalance); ?></div>
            </div>
            <div class="admin-card total-users">
                <h2>Transactions Today</h2>
                <div class="stat"><?php echo $todayCount; ?></div>
            </div>
        </div>

        <!-- Recent Transactions Table -->
        <h2>Recent Transactions</h2>
        <table class="recent-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Amount ($)</th>
                    <th>Type</th>
                    <th>Sender</th>
                    <th>Receiver</th>
                    <th class="timestamp">Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentTransactions as $tx): ?>
                    <tr>
                        <td><?php echo isset($tx['id']) ? $tx['id'] : '-'; ?></td>
                        <td><?php echo isset($tx['amount']) ? formatAmount($tx['amount']) : (isset($tx['value']) ? formatAmount($tx['value']) : '-'); ?></td>
                        <td><?php
                            if (isset($tx['type']) && !empty($tx['type'])) {
                                echo htmlspecialchars($tx['type']);
                            } elseif (isset($tx['transaction_type']) && !empty($tx['transaction_type'])) {
                                echo htmlspecialchars($tx['transaction_type']);
                            } elseif (
                                ((!isset($tx['receiver']) || $tx['receiver'] == '-' || empty($tx['receiver'])) &&
                                    isset($tx['sender']) && !empty($tx['sender']) &&
                                    isset($tx['amount']) && floatval($tx['amount']) > 0)
                            ) {
                                echo 'withdraw';
                            } else {
                                echo '-';
                            }
                            ?></td>
                        <td><?php echo htmlspecialchars($tx['sender'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($tx['receiver'] ?? '-'); ?></td>
                        <td class="timestamp"><?php
                                                if (isset($tx['timestamp'])) echo date('d-m-Y H:i', strtotime($tx['timestamp']));
                                                elseif (isset($tx['created_at'])) echo date('d-m-Y H:i', strtotime($tx['created_at']));
                                                elseif (isset($tx['date'])) echo date('d-m-Y H:i', strtotime($tx['date']));
                                                else echo '-';
                                                ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recentTransactions)): ?>
                    <tr>
                        <td colspan="6">No transactions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>