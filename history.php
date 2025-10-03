<?php
// history.php
// Shows the user's last 10 transactions with filter options
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];
$type_filter = $_GET['type'] ?? '';
$date_from = $_GET['from'] ?? '';
$date_to = $_GET['to'] ?? '';

// Build filter query
$where = '(sender_id = ? OR receiver_id = ?)';
$params = [$user_id, $user_id];
$types = 'ii';
if ($type_filter && in_array($type_filter, ['add_fund', 'transfer'])) {
    $where .= ' AND transaction_type = ?';
    $params[] = $type_filter;
    $types .= 's';
}
if ($date_from && $date_to) {
    $where .= ' AND DATE(created_at) BETWEEN ? AND ?';
    $params[] = $date_from;
    $params[] = $date_to;
    $types .= 'ss';
}

// Prepare query with JOINs to get usernames
$sql = "SELECT t.*, su.username AS sender_name, ru.username AS receiver_name
        FROM transactions t
        LEFT JOIN users su ON t.sender_id = su.id
        LEFT JOIN users ru ON t.receiver_id = ru.id
        WHERE $where
        ORDER BY t.created_at DESC
        LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$transactions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Transaction History - SecurePay</title>
    <!-- <link rel="stylesheet" href="assets/css/style.css"> -->
    <style>
        body {
            background: linear-gradient(120deg, #6366f1 0%, #a5b4fc 100%);
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .history-wrapper {
            max-width: 1300px;
            margin: 40px auto 0 auto;
            background: rgba(255,255,255,0.85);
            border-radius: 18px;
            box-shadow: 0 8px 32px 0 rgba(30, 64, 175, 0.13);
            padding: 36px 32px 32px 32px;
            position: relative;
        }

        h2 {
            text-align: center;
            margin-top: 10px;
            font-size: 2.1rem;
            color: #1e3a8a;
            letter-spacing: 1px;
            margin-bottom: 40px;
            font-weight: 700;
            text-shadow: 0 2px 8px #c7d2fe;
            font-size: 30px;
        }

        .container {
            width: 100%;
        }

        form {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            background: #e0e7ff;
            padding: 16px 18px;
            border-radius: 10px;
            box-shadow: 0 2px 8px 0 rgba(30, 64, 175, 0.07);
        }

        form label {
            font-weight: 500;
            color: #1e40af;
            margin-right: 6px;
        }

        form select,
        form input[type="date"] {
            padding: 7px 12px;
            border: 1px solid #a5b4fc;
            border-radius: 6px;
            background: #f1f5ff;
            font-size: 1rem;
            color: #1e3a8a;
            outline: none;
            transition: border 0.2s;
        }

        form select:focus,
        form input[type="date"]:focus {
            border: 1.5px solid #2563eb;
        }

        form button {
            background: linear-gradient(90deg, #2563eb 0%, #38bdf8 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 22px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px 0 rgba(37, 99, 235, 0.10);
            transition: background 0.2s, box-shadow 0.2s;
        }

        form button:hover {
            background: linear-gradient(90deg, #1e3a8a 0%, #0ea5e9 100%);
            box-shadow: 0 4px 16px 0 rgba(37, 99, 235, 0.18);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            background: #f1f5ff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px 0 rgba(30, 64, 175, 0.07);
        }

        th,
        td {
            padding: 14px 12px;
        }

        th {
            background: #2563eb;
            color: #fff;
            font-size: 1.05rem;
            font-weight: 600;
            border-bottom: 2px solid #60a5fa;
        }

        tr {
            transition: background 0.18s;
        }

        tr:nth-child(even) {
            background: #e0e7ff;
        }

        tr:hover {
            background: #bae6fd;
        }

        td {
            color: #1e3a8a;
            font-size: 1.01rem;
        }

        td[style*="text-align:center"] {
            font-weight: 500;
            color: #0ea5e9;
        }

        td[style*="text-align:right"] {
            font-weight: 600;
            color: #2563eb;
        }

        a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        a:hover {
            color: #0ea5e9;
            text-decoration: underline;
        }

        @media (max-width: 800px) {
            .history-wrapper {
                padding: 18px 4vw 18px 4vw;
            }

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            th {
                display: none;
            }

            tr {
                margin-bottom: 18px;
                background: #e0e7ff !important;
                box-shadow: 0 2px 8px 0 rgba(30, 64, 175, 0.07);
                border-radius: 8px;
                padding: 10px 0;
            }

            td {
                padding: 10px 16px;
                text-align: left !important;
                position: relative;
            }

            td:before {
                content: attr(data-label);
                font-weight: 600;
                color: #2563eb;
                display: block;
                margin-bottom: 4px;
            }
        }
    </style>
</head>

<body>
    <div class="history-wrapper">
        <h2>╰(..:: Transaction History ::..)╯</h2>
        <div class="container">
            <form method="get" action="" style="margin-bottom:16px;">
                <label>Type:</label>
                <select name="type">
                    <option value="">All</option>
                    <option value="add_fund" <?php if ($type_filter === 'add_fund') echo 'selected'; ?>>Add Fund</option>
                    <option value="transfer" <?php if ($type_filter === 'transfer') echo 'selected'; ?>>Transfer</option>
                </select>
                <label>Date From:</label>
                <input type="date" name="from" value="<?php echo htmlspecialchars($date_from); ?>">
                <label>Date To:</label>
                <input type="date" name="to" value="<?php echo htmlspecialchars($date_to); ?>">
                <button type="submit">Filter</button>
            </form>
            <table style="width:100%;border-collapse:collapse;">
                <tr style="background:#f0f4fa;">
                    <th style="text-align:left;">Date</th>
                    <th style="text-align:left;">Amount</th>
                    <th style="text-align:center;">To/From</th>
                    <th style="text-align:right;">Type</th>
                </tr>
                <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['created_at']); ?></td>
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
                                echo '-/' . number_format($t['amount'], 2);
                            }
                            ?>
                        <td style="text-align:center;">
                            <?php
                            if ($t['transaction_type'] === 'add_fund') {
                                echo 'Self';
                            } elseif ($t['sender_id'] == $user_id) {
                                echo 'To: ' . htmlspecialchars($t['receiver_name']);
                            } else {
                                echo 'From: ' . htmlspecialchars($t['sender_name']);
                            }
                            ?>
                        </td>
                        <td style="text-align:right;"><?php echo ucfirst($t['transaction_type']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">No transactions found.</td>
                    </tr>
                <?php endif; ?>
            </table>
            <p><a href="dashboard/index.php">Back to Dashboard</a></p>
        </div>
</body>

</html>