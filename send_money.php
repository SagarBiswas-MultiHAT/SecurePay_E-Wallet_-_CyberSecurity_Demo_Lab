<?php
// send_money.php
// Allows a user to send money to another user securely (with CSRF protection)
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];
$success = $error = '';

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Fetch all other users for dropdown
$stmt = $conn->prepare('SELECT id, username FROM users WHERE id != ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get sender's current balance
$stmt = $conn->prepare('SELECT balance FROM wallets WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($balance);
$stmt->fetch();
$stmt->close();
$balance = $balance ?? 0.00;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid CSRF token.';
    } else {
        // Sanitize and validate inputs
        $receiver_id = intval($_POST['receiver_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        if ($receiver_id === $user_id) {
            $error = 'You cannot send money to yourself.';
        } elseif ($amount <= 0) {
            $error = 'Enter a valid amount.';
        } elseif ($amount > $balance) {
            $error = 'Insufficient balance.';
        } else {
            // Start transaction for atomicity
            $conn->begin_transaction();
            try {
                // Deduct from sender
                $stmt1 = $conn->prepare('UPDATE wallets SET balance = balance - ? WHERE user_id = ?');
                $stmt1->bind_param('di', $amount, $user_id);
                $stmt1->execute();
                // Add to receiver
                $stmt2 = $conn->prepare('INSERT INTO wallets (user_id, balance) VALUES (?, ?) ON DUPLICATE KEY UPDATE balance = balance + VALUES(balance)');
                $stmt2->bind_param('id', $receiver_id, $amount);
                $stmt2->execute();
                // Insert transaction
                $stmt3 = $conn->prepare('INSERT INTO transactions (sender_id, receiver_id, amount, transaction_type) VALUES (?, ?, ?, "transfer")');
                $stmt3->bind_param('iid', $user_id, $receiver_id, $amount);
                $stmt3->execute();
                $conn->commit();
                $success = 'Money sent successfully!';
            } catch (Exception $e) {
                $conn->rollback();
                $error = 'Transaction failed. Please try again.';
            }
            $stmt1->close();
            $stmt2->close();
            $stmt3->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Send Money - SecurePay</title>
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(120deg, #6366f1 0%, #a5b4fc 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: rgba(255, 255, 255, 0.85);
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.18), 0 2px 8px rgba(0, 0, 0, 0.09);
            border-radius: 22px;
            max-width: 410px;
            width: 98vw;
            padding: 44px 32px 32px 32px;
            margin: 40px auto;
            text-align: center;
            position: relative;
            backdrop-filter: blur(7px);
            border: 1.5px solid rgba(99, 102, 241, 0.13);
            animation: fadeInUp 0.7s cubic-bezier(.23, 1.01, .32, 1) both;
        }

        .container::before {
            content: '';
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #6366f1 60%, #a5b4fc 100%);
            border-radius: 50%;
            filter: blur(18px);
            opacity: 0.18;
            z-index: 0;
        }

        .container img.logo {
            width: 50px;
            height: 50px;
            margin-bottom: 14px;
            filter: drop-shadow(0 2px 8px rgba(99, 102, 241, 0.10));
        }

        h2 {
            color: #3730a3;
            margin-bottom: 12px !important;
            margin-top: 0px !important;
            font-weight: 900;
            letter-spacing: 1.5px;
            font-size: 2rem;
            background: linear-gradient(90deg, #6366f1 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 25px;
        }

        label {
            display: block;
            margin: 22px 0 7px 2px;
            color: #3730a3;
            font-size: 16px;
            text-align: left;
            font-weight: 600;
        }

        select,
        input[type="number"] {
            width: 100%;
            padding: 13px 14px;
            border: 1.7px solid #c7d2fe;
            border-radius: 9px;
            font-size: 16px;
            margin-bottom: 12px;
            background: #f1f5ff;
            transition: border 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }

        select:focus,
        input[type="number"]:focus {
            border-color: #6366f1;
            outline: none;
            box-shadow: 0 0 0 2px #6366f155;
            background: #fff;
        }

        button[type="submit"] {
            width: 100%;
            background: linear-gradient(90deg, #6366f1 0%, #3b82f6 100%);
            color: #fff;
            border: none;
            border-radius: 9px;
            padding: 15px 0;
            font-size: 18px;
            font-weight: 800;
            margin-top: 24px;
            cursor: pointer;
            box-shadow: 0 2px 12px rgba(99, 102, 241, 0.13);
            transition: background 0.2s, transform 0.13s, box-shadow 0.13s;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        button[type="submit"]:hover {
            background: linear-gradient(90deg, #4f46e5 0%, #2563eb 100%);
            transform: translateY(-2px) scale(1.025);
            box-shadow: 0 4px 18px rgba(99, 102, 241, 0.18);
        }

        .alert {
            padding: 15px 22px;
            border-radius: 8px;
            margin-bottom: 22px;
            font-size: 16px;
            text-align: left;
            box-shadow: 0 1px 8px rgba(0, 0, 0, 0.07);
            border-left: 6px solid;
            animation: fadeIn 0.5s;
            font-weight: 600;
        }

        .alert.success {
            background: #e0f7e9;
            color: #15803d;
            border-color: #34d399;
        }

        .alert.error {
            background: #fef2f2;
            color: #b91c1c;
            border-color: #f87171;
        }

        p.balance {
            font-size: 17px;
            color: #3730a3;
            margin-bottom: 10px;
            font-weight: 500;
        }

        p.balance strong {
            color: #2563eb;
            font-weight: 800;
        }

        a {
            color: #6366f1;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: color 0.15s;
        }

        a:hover {
            text-decoration: underline;
            color: #3b82f6;
        }

        @media (max-width: 520px) {
            .container {
                max-width: 99vw;
                padding: 10vw 2vw 8vw 2vw;
            }

            h2 {
                font-size: 1.2rem;
            }

            .container img.logo {
                width: 60px;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <img src="imgs/send-money.png" alt="SecurePay Logo" class="logo" onerror="this.style.display='none'">
        <h2>Send Money</h2>
        <p class="balance">Your Balance: <strong>-/<?php echo number_format($balance, 2); ?></strong></p>
        <?php if ($success): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" action="" autocomplete="off">
            <label for="receiver_id">Recipient</label>
            <select name="receiver_id" id="receiver_id" required>
                <option value="">-- Select User --</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['username']); ?></option>
                <?php endforeach; ?>
            </select>
            <label for="amount">Amount (-/)</label>
            <input type="number" name="amount" id="amount" min="1" step="0.01" required>
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <button type="submit">Send Money</button>
        </form>
        <p style="margin-top: 18px;"><a href="dashboard/index.php">&larr; Back to Dashboard</a></p>
    </div>
</body>

</html>