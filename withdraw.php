<?php
// withdraw.php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount'] ?? 0);
    if ($amount <= 0) {
        $errors[] = 'Please enter a valid withdrawal amount.';
    } else {
        // Get current balance
        $stmt = $conn->prepare('SELECT balance FROM wallets WHERE user_id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($balance);
        $stmt->fetch();
        $stmt->close();
        if ($amount > $balance) {
            $errors[] = 'Insufficient balance.';
        } else {
            // Deduct amount and record transaction
            $stmt = $conn->prepare('UPDATE wallets SET balance = balance - ? WHERE user_id = ?');
            $stmt->bind_param('di', $amount, $user_id);
            $stmt->execute();
            $stmt->close();
            $stmt = $conn->prepare('INSERT INTO transactions (sender_id, receiver_id, amount, transaction_type) VALUES (?, NULL, ?, "withdraw")');
            $stmt->bind_param('id', $user_id, $amount);
            $stmt->execute();
            $stmt->close();
            $success = 'Withdrawal successful!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Withdraw - SecurePay</title>

    <style>
        :root {
            --color-accent: #7c3aed;
            --color-accent2: #06b6d4;
            --color-accent3: #3a86ff;
            --color-card: #fff;
            --color-input: #f3f0ff;
            --color-text: #1a2233;
            --color-placeholder: #a3a3c2;
            --color-border: #d1d5db;
            --color-gradient: linear-gradient(135deg, #e0e7ff 0%, #f0fdfa 100%);
        }

        body {
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--color-gradient);
            color: var(--color-text);
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
        }

        .container {
            width: 100%;
            max-width: 520px;
            margin: 150px auto 0 auto;
            padding: 0 0 32px 0;
            background: var(--color-card);
            border-radius: 22px;
            box-shadow: 0 8px 32px 0 rgba(124, 58, 237, 0.10), 0 1.5px 4px 0 rgba(6, 182, 212, 0.08);
            position: relative;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(90deg, var(--color-accent) 0%, var(--color-accent2) 100%);
            color: #fff;
            padding: 32px 32px 18px 32px;
            border-radius: 0 0 60% 60%/0 0 18% 18%;
            text-align: center;
            box-shadow: 0 2px 12px #7c3aed22;
        }

        .card-header h2 {
            margin: 0;
            font-size: 2em;
            font-weight: 800;
            letter-spacing: 1px;
            text-shadow: 0 2px 8px #3a86ff33;
        }

        form {
            padding: 32px 32px 0 32px;
        }

        label {
            display: block;
            margin-top: 18px;
            color: var(--color-accent);
            font-weight: 600;
            font-size: 1.08em;
        }

        input[type="number"] {
            width: 92%;
            padding: 16px 18px;
            margin-top: 7px;
            border-radius: 12px;
            font-size: 1.13em;
            background: var(--color-input);
            border: 1.5px solid var(--color-border);
            transition: border 0.2s, box-shadow 0.2s;
        }

        input[type="number"]::placeholder {
            color: var(--color-placeholder);
            font-size: 1em;
        }

        input[type="number"]:focus {
            outline: none;
            border-color: var(--color-accent2);
            box-shadow: 0 0 0 3px #06b6d422;
        }

        button {
            width: 100%;
            margin-top: 32px;
            padding: 15px;
            background: linear-gradient(90deg, var(--color-accent3) 0%, var(--color-accent2) 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1.18em;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 2px 12px #3a86ff22;
            transition: background 0.2s, box-shadow 0.2s, color 0.2s, transform 0.1s;
        }

        button:hover {
            background: linear-gradient(90deg, var(--color-accent2) 0%, var(--color-accent3) 100%);
            color: #fff;
            box-shadow: 0 4px 24px #3a86ff33;
            transform: translateY(-2px) scale(1.03);
        }

        .alert.error {
            padding: 12px;
            margin: 18px 32px 0 32px;
            border-radius: 8px;
            font-size: 1em;
            background: #ffe0e0;
            color: #b00020;
            border: 1.5px solid #ffb3b3;
        }

        .alert.success {
            padding: 12px;
            margin: 18px 32px 0 32px;
            border-radius: 8px;
            font-size: 1em;
            background: #e0fff4;
            color: #007e33;
            border: 1.5px solid #7fffd4;
        }

        a {
            color: var(--color-accent3);
            text-decoration: none;
            font-weight: 500;
        }

        a:hover {
            text-decoration: underline;
        }

        .back-link {
            text-align: center;
            margin-top: 24px;
            font-size: 1em;
        }

        @media (max-width: 600px) {
            .container {
                padding: 0 0 12px 0;
                max-width: 98vw;
            }

            .card-header,
            form {
                padding-left: 6vw;
                padding-right: 6vw;
            }

            .alert.error,
            .alert.success {
                margin-left: 6vw;
                margin-right: 6vw;
            }

            .card-header h2 {
                font-size: 1.3em;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card-header">
            <h2>Withdraw Funds</h2>
        </div>
        <?php if ($errors): ?>
            <div class="alert error">
                <?php foreach ($errors as $e) echo '<p>' . $e . '</p>'; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert success">
                <p><?php echo $success; ?></p>
            </div>
        <?php endif; ?>
        <form method="post" action="" autocomplete="off">
            <label for="amount">Amount</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0" required placeholder="Enter amount to withdraw">
            <button type="submit">Withdraw</button>
        </form>
        <div class="back-link">
            <a href="dashboard/index.php">&larr; Back to Dashboard</a>
        </div>
    </div>
</body>

</html>