<?php
// dashboard/index.php
// Main dashboard for SecurePay user
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get current balance
$stmt = $conn->prepare('SELECT balance FROM wallets WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($balance);
$stmt->fetch();
$stmt->close();
$balance = $balance ?? 0.00;

// Get last 5 transactions
$sql = "SELECT t.*, su.username AS sender_name, ru.username AS receiver_name
        FROM transactions t
        LEFT JOIN users su ON t.sender_id = su.id
        LEFT JOIN users ru ON t.receiver_id = ru.id
        WHERE t.sender_id = ? OR t.receiver_id = ?
        ORDER BY t.created_at DESC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$transactions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get top 3 frequent recipients
$sql = "SELECT u.username, COUNT(*) as count
        FROM transactions t
        JOIN users u ON t.receiver_id = u.id
        WHERE t.sender_id = ? AND t.transaction_type = 'transfer'
        GROUP BY t.receiver_id
        ORDER BY count DESC
        LIMIT 3";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$recipients = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - SecurePay</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --color-primary: #3a86ff;
            --color-secondary: #7c3aed;
            --color-accent: #06b6d4;
            --color-bg: linear-gradient(135deg, #e0e7ff 0%, #4586ff 100%);
            --color-card: #fff;
            --color-section: #f7fafd;
            --color-table-header: #eaf2ff;
            --color-table-border: #d0e2ff;
            --color-table-row: #f7fafd;
            --color-danger: #b00020;
            --color-shadow: 0 8px 32px 0 rgba(60, 80, 120, 0.13), 0 1.5px 4px 0 rgba(60, 80, 120, 0.09);
            --color-shadow-soft: 0 2px 12px #3a86ff18;
        }

        body {
            background: var(--color-bg);
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
            color: #1a2233;
            margin: 0;
            min-height: 100vh;
            letter-spacing: 0.01em;
        }

        .hero {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 32px;
            padding-bottom: 18px;
            border-bottom: 1.5px solid #eaf2ff;
        }

        .hero-logo {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: linear-gradient(135deg, #3a86ff 0%, #7c3aed 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--color-shadow-soft);
            border: 1.5px solid #eaf2ff;
        }

        .hero-logo img {
            width: 44px;
            height: 44px;
            border-radius: 10px;
        }

        .hero-title {
            font-size: 2.1em;
            font-weight: 700;
            color: #2a3b6e;
            letter-spacing: 1px;
            text-shadow: 0 2px 8px #3a86ff0a;
        }

        .container {
            max-width: 1600px;
            width: 100%;
            margin: 40px auto 0 auto;
            background: rgba(255,255,255,0.85);
            border-radius: 22px;
            box-shadow: var(--color-shadow);
            padding: 48px 44px 36px 44px;
        }

        .balance-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(90deg, #3a86ff 0%, #7c3aed 100%);
            color: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 18px #3a86ff22;
            padding: 36px 0 28px 0;
            margin-bottom: 36px;
            border: 1.5px solid #eaf2ff;
        }

        .balance-label {
            font-size: 1.1em;
            opacity: 0.85;
            margin-bottom: 8px;
        }

        .balance {
            font-size: 2.5em;
            font-weight: 800;
            letter-spacing: 2px;
            color: #fff;
        }

        .dashboard-links {
            margin: 24px 0 16px 0;
            display: flex;
            gap: 18px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .dashboard-links a {
            background: linear-gradient(90deg, #3a86ff 0%, #7c3aed 100%);
            color: #fff;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.08em;
            text-decoration: none;
            box-shadow: 0 2px 8px #3a86ff22;
            transition: background 0.18s, box-shadow 0.18s, color 0.18s, transform 0.13s;
            border: none;
            position: relative;
        }

        .dashboard-links a:hover {
            background: linear-gradient(90deg, #7c3aed 0%, #3a86ff 100%);
            color: #fff;
            box-shadow: 0 6px 24px #3a86ff33;
            transform: translateY(-2px) scale(1.04);
            z-index: 1;
        }

        .dashboard-links a[style*="color:#b00020"] {
            background: #fff0f0;
            color: var(--color-danger) !important;
            border: 1.5px solid var(--color-danger);
            box-shadow: none;
        }

        .dashboard-links a[style*="color:#b00020"]:hover {
            background: linear-gradient(90deg, var(--color-danger) 0%, #ff6a6a 100%);
            color: #fff;
        }

        .section {
            margin-bottom: 36px;
            background: var(--color-section);
            border-radius: 16px;
            box-shadow: 0 2px 10px #3a86ff0a;
            padding: 32px 32px 22px 32px;
            border: 1.5px solid #eaf2ff;
        }

        .section h3 {
            margin-top: 0;
            color: var(--color-primary);
            font-size: 1.22em;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .recipients-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .recipients-list li {
            margin-bottom: 8px;
            font-size: 1.08em;
            padding-left: 8px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px #3a86ff0a;
        }

        th,
        td {
            padding: 14px 12px;
            font-size: 1.06em;
        }

        th {
            background: var(--color-table-header);
            color: #2a3b6e;
            font-weight: 700;
            border-bottom: 2px solid var(--color-table-border);
            letter-spacing: 0.5px;
        }

        tr:nth-child(even) td {
            background: var(--color-table-row);
        }

        tr:last-child td {
            border-bottom: none;
        }

        td {
            border-bottom: 1px solid #f0f4fa;
        }

        @media (max-width: 900px) {
            .container {
                padding: 12px 2vw;
            }

            .section {
                padding: 12px 2vw 10px 2vw;
            }

            th,
            td {
                padding: 8px 4px;
                font-size: 0.98em;
            }
        }

        html {
            zoom: 80%;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="hero">
            <div class="hero-logo">
                <img src="../imgs/SecurePAY.png" alt="SecurePay Logo" onerror="this.style.display='none'">
            </div>
            <div class="hero-title">Welcome, <?php echo htmlspecialchars($username); ?>!</div>
        </div>
        <div class="balance-card">
            <div class="balance-label">Current Balance</div>
            <div class="balance">-/<?php echo number_format($balance, 2); ?></div>
        </div>
        <div class="dashboard-links">
            <?php if (!empty($_SESSION['is_admin'])): ?>
                <a href="../add_funds.php">Add Funds</a>
            <?php else: ?>
                <a href="../withdraw.php">Withdraw</a>
            <?php endif; ?>
            <a href="../send_money.php">Send Money</a>
            <a href="../history.php">View History</a>
            <a href="../auth/logout.php" style="color:#b00020;">Logout</a>
        </div>
        <div class="section">
            <h3>Last 5 Transactions</h3>
            <table>
                <tr>
                    <th style="text-align:left;">Date</th>
                    <th style="text-align:left;">Amount</th>
                    <th style="text-align:center;">To/From</th>
                    <th style="text-align:right;">Type</th>
                </tr>
                <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td style="padding-left:5px;"><?php echo htmlspecialchars($t['created_at']); ?></td>
                        <td>
                            <?php
                            // Add_fund: always (+)
                            if ($t['transaction_type'] === 'add_fund') {
                                echo '(+) -/' . number_format($t['amount'], 2);
                            }
                            // Withdraw: always (-)
                            elseif ($t['transaction_type'] === 'withdraw') {
                                echo '(-) -/' . number_format($t['amount'], 2);
                            }
                            // Transfer: sent (-), received (+)
                            elseif ($t['transaction_type'] === 'transfer') {
                                if ($t['sender_id'] == $user_id) {
                                    echo '(-) -/' . number_format($t['amount'], 2);
                                } else {
                                    echo '(+) -/' . number_format($t['amount'], 2);
                                }
                            }
                            // Fallback
                            else {
                                // If To/From is '-' and type is Withdraw, show (-) -/amount
                                if ($t['transaction_type'] === 'withdraw' || (empty($t['receiver_name']) && $t['sender_id'] == $user_id)) {
                                    echo '(-) -/' . number_format($t['amount'], 2);
                                } else {
                                    echo '-/' . number_format($t['amount'], 2);
                                }
                            }
                            ?>
                        </td>
                        <td style="text-align:center;">
                            <?php
                            if ($t['transaction_type'] === 'add_fund') {
                                echo 'Self';
                            } elseif ($t['transaction_type'] === 'withdraw' || (empty($t['receiver_name']) && $t['sender_id'] == $user_id)) {
                                echo '<span style="display:block;text-align:center;">-</span>';
                            } elseif ($t['sender_id'] == $user_id) {
                                echo 'To: ' . htmlspecialchars($t['receiver_name']);
                            } else {
                                echo 'From: ' . htmlspecialchars($t['sender_name']);
                            }
                            ?>
                        </td>
                        <td style="text-align:right;">
                            <?php
                            if (!empty($t['transaction_type'])) {
                                echo ucfirst($t['transaction_type']);
                            } elseif (is_null($t['receiver_id'])) {
                                echo 'Withdraw';
                            } else {
                                echo 'Unknown';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">No transactions found.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
        <div class="section">
            <h3>Top 3 Frequent Recipients</h3>
            <ul class="recipients-list">
                <?php foreach ($recipients as $r): ?>
                    <li><?php echo htmlspecialchars($r['username']); ?> (<?php echo $r['count']; ?> transfers)</li>
                <?php endforeach; ?>
                <?php if (empty($recipients)): ?>
                    <li>No recipients yet.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>

</html>