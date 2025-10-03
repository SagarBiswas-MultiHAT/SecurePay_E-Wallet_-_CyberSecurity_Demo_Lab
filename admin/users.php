<?php
// Admin access check
session_start();
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}
// Admin User Management for SecurePay
// Allows admin to view, reset password, block/unblock, and delete users
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_login();

// Helper: Format amounts
function formatAmount($amount)
{
    return number_format((float)$amount, 2, '.', '');
}

// Helper: Generate random password
function generatePassword($length = 8)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $pass = '';
    for ($i = 0; $i < $length; $i++) {
        $pass .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $pass;
}

// Alert message
$alert = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Reset Password
    if (isset($_POST['reset_password'])) {
        $user_id = intval($_POST['user_id']);
        // Generate and hash new password
        $new_pass = generatePassword();
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->bind_param('si', $hash, $user_id);
        if ($stmt->execute()) {
            $alert = '<div class="alert success" style="text-align:center;">Password reset! New password: <b style="color:#d90429;">' . htmlspecialchars($new_pass) . '</b></div>';
        } else {
            $alert = '<div class="alert error">Failed to reset password.</div>';
        }
        $stmt->close();
    }
    // Block/Unblock User
    if (isset($_POST['toggle_block'])) {
        $user_id = intval($_POST['user_id']);
        $is_admin = intval($_POST['is_admin']);
        $is_blocked = intval($_POST['is_blocked']);
        if ($is_admin) {
            $alert = '<div class="alert error">Cannot block/unblock admin accounts.</div>';
        } else {
            $new_state = $is_blocked ? 0 : 1;
            $stmt = $conn->prepare('UPDATE users SET is_blocked = ? WHERE id = ?');
            $stmt->bind_param('ii', $new_state, $user_id);
            if ($stmt->execute()) {
                $alert = '<div class="alert success">User ' . ($new_state ? 'blocked' : 'unblocked') . ' successfully.</div>';
            } else {
                $alert = '<div class="alert error">Failed to update block status.</div>';
            }
            $stmt->close();
        }
    }
    // Delete User
    if (isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        $is_admin = intval($_POST['is_admin']);
        if ($is_admin) {
            $alert = '<div class="alert error">Cannot delete admin accounts.</div>';
        } else {
            // Delete transactions
            $stmt = $conn->prepare('DELETE FROM transactions WHERE sender_id = ? OR receiver_id = ?');
            $stmt->bind_param('ii', $user_id, $user_id);
            $stmt->execute();
            $stmt->close();
            // Delete wallet
            $stmt = $conn->prepare('DELETE FROM wallets WHERE user_id = ?');
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->close();
            // Delete user
            $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
            $stmt->bind_param('i', $user_id);
            if ($stmt->execute()) {
                $alert = '<div class="alert success">User deleted successfully.</div>';
            } else {
                $alert = '<div class="alert error">Failed to delete user.</div>';
            }
            $stmt->close();
        }
    }
}

// Fetch all users with wallet balances

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

// Detect if is_blocked column exists
$hasBlocked = true;
$sql = "SHOW COLUMNS FROM users LIKE 'is_blocked'";
$result = $conn->query($sql);
if ($result && $result->num_rows === 0) {
    $hasBlocked = false;
}

$sql = "SELECT u.id, u.$userNameCol, u.email, u.is_admin" . ($hasBlocked ? ", u.is_blocked" : "") . ", w.balance
        FROM users u
        LEFT JOIN wallets w ON u.id = w.user_id
        ORDER BY u.id ASC";
$result = $conn->query($sql);
$users = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Management | SecurePay Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&family=Montserrat:wght@700;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-bg: #f8fafc;
            --color-primary: #2563eb;
            --color-danger: #e11d48;
            --color-success: #059669;
            --color-warning: #f59e42;
            --color-table-header: #f1f5f9;
            --color-table-row: #fff;
            --color-table-row-alt: #f8fafc;
            --color-border: #e5e7eb;
            --color-text: #22223b;
            --color-muted: #64748b;
            --radius: 12px;
            --transition: 0.18s cubic-bezier(.4, 0, .2, 1);
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            color: var(--color-text);
            background: var(--color-bg);
        }

        .logo-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 10px;
        }

        .logo-img {
            display: block;
            margin: 0 auto;
            max-width: 40px;
            margin-top: 18px;
            margin-bottom: 8px;
            filter: drop-shadow(0 2px 8px #2563eb22);
            transition: transform 0.18s var(--transition);
        }

        .logo-img:hover {
            transform: scale(1.08) rotate(-4deg);
        }

        .brand-title {
            text-align: center;
            font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
            font-size: 2rem;
            font-weight: 800;
            color: var(--color-primary);
            letter-spacing: 0.04em;
            margin-bottom: 0.2rem;
            margin-top: 0;
            user-select: none;
        }

        .container {

            max-width: 1300px;
            margin: 36px auto 0 auto;
            padding: 28px 24px 18px 24px;
            background: #fff;
            border-radius: var(--radius);
            box-shadow: 0 2px 16px 0 rgba(34, 60, 80, 0.08);
            border: 1px solid var(--color-border);
        }

        .user-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 28px;
            background: #fff;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(34, 60, 80, 0.04);
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
        }

        .user-table th {
            padding: 0.9rem 1.1rem;
            background: var(--color-table-header);
            color: #22223b;
            font-size: 1.04rem;
            font-weight: 700;
            border-bottom: 2px solid var(--color-border);
            text-align: left;
            letter-spacing: 0.01em;
        }

        .user-table td {
            padding: 0.7rem 1.1rem;
            background: var(--color-table-row);
            border-bottom: 1px solid var(--color-border);
            font-size: 1.01rem;
            color: #22223b;
        }

        .user-table tr:nth-child(even) td {
            background: var(--color-table-row-alt);
        }

        .user-table tr:hover td {
            background: #e0e7ef;
            color: var(--color-primary);
            cursor: pointer;
        }

        .action-btn {
            padding: 0.38rem 1rem;
            margin-right: 0.2rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.97rem;
            font-weight: 600;
            background: #f1f5f9;
            color: var(--color-primary);
            transition: background var(--transition), color var(--transition), box-shadow var(--transition), transform var(--transition);
            outline: none;
        }

        .reset-btn {
            background: #2563eb;
            color: #fff;
        }

        .reset-btn:hover {
            background: #1746a2;
            color: #fff;
            transform: translateY(-1px) scale(1.04);
        }

        .block-btn {
            background: #f59e42;
            color: #fff;
        }

        .block-btn:hover {
            background: #d97706;
            color: #fff;
            transform: translateY(-1px) scale(1.04);
        }

        .delete-btn {
            background: #e11d48;
            color: #fff;
        }

        .delete-btn:hover {
            background: #991b32;
            color: #fff;
            transform: translateY(-1px) scale(1.04);
        }

        .alert {
            margin: 18px 0 0 0;
            padding: 0.9rem 1.3rem;
            border-radius: 8px;
            font-size: 1.04rem;
            font-weight: 500;
            box-shadow: 0 1px 4px rgba(34, 60, 80, 0.07);
            letter-spacing: 0.01em;
            border: 1px solid var(--color-border);
            background: #f8fafc;
        }

        .alert.success {
            background: #e7f9ef;
            color: #059669;
            border-left: 5px solid #059669;
        }

        .alert.error {
            background: #fbe7ea;
            color: #e11d48;
            border-left: 5px solid #e11d48;
        }

        nav a {
            color: var(--color-primary);
            font-weight: 700;
            text-decoration: none;
            margin-right: 1.1rem;
            transition: color var(--transition);
            border-bottom: 2px solid transparent;
            padding-bottom: 2px;
        }

        nav a:last-child {
            margin-right: 0;
        }

        nav a:hover {
            color: #1746a2;
            border-bottom: 2px solid var(--color-primary);
        }

        h1 {
            font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            color: var(--color-primary);
            margin-bottom: 0.5rem;
        }

        @media (max-width: 900px) {
            .container {
                padding: 10px 2vw;
            }

            .user-table th,
            .user-table td {
                padding: 0.5rem 0.3rem;
                font-size: 0.93rem;
            }

            h1 {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 600px) {
            .container {
                padding: 4px 1vw;
            }

            .user-table th,
            .user-table td {
                padding: 0.3rem 0.1rem;
                font-size: 0.89rem;
            }

            h1 {
                font-size: 1rem;
            }
        }
    </style>
    <script>
        // Confirm before deleting user
        function confirmDelete() {
            return confirm('Are you sure you want to delete this user? This action cannot be undone.');
        }
    </script>
</head>

<body>
    <div class="logo-wrap">
        <img src="../imgs/SecurePAY.png" alt="SecurePay Logo" class="logo-img">
        <div class="brand-title">SecurePay</div>
    </div>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; background: #f0f4fa; border-radius: 8px; padding-right: 10px; font-size: 0.8rem;">
            <h1>╰(..:: User Management ::..)╯</h1>
            <nav style="padding-right: 12px;">
                <a href="index.php" style="margin-right:1rem; color:#0077b6; font-weight:bold; text-decoration:none;">Dashboard</a>
                <a href="logout.php" style="color:#d90429; font-weight:bold; text-decoration:none;">Logout</a>
            </nav>
        </div>
        <?php if ($alert) echo $alert; ?>
        <table class="user-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Wallet Balance ($)</th>
                    <th>Admin?</th>
                    <th>Blocked?</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo isset($user['id']) ? $user['id'] : '-'; ?></td>
                        <td><?php
                            if (isset($user['full_name'])) echo htmlspecialchars($user['full_name']);
                            elseif (isset($user['name'])) echo htmlspecialchars($user['name']);
                            elseif (isset($user['username'])) echo htmlspecialchars($user['username']);
                            else echo '-';
                            ?></td>
                        <td><?php echo isset($user['email']) ? htmlspecialchars($user['email']) : '-'; ?></td>
                        <td><?php echo isset($user['balance']) ? formatAmount($user['balance']) : '0.00'; ?></td>
                        <td><?php echo isset($user['is_admin']) && $user['is_admin'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo isset($user['is_blocked']) ? ($user['is_blocked'] ? 'Yes' : 'No') : '-'; ?></td>
                        <td style="text-align:center; padding: 0.5rem;">
                            <!-- Reset Password Button -->
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo isset($user['id']) ? $user['id'] : ''; ?>">
                                <button type="submit" name="reset_password" class="action-btn reset-btn">Reset Password</button>
                            </form>
                            <!-- Block/Unblock Button -->
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo isset($user['id']) ? $user['id'] : ''; ?>">
                                <input type="hidden" name="is_admin" value="<?php echo isset($user['is_admin']) ? $user['is_admin'] : 0; ?>">
                                <input type="hidden" name="is_blocked" value="<?php echo isset($user['is_blocked']) ? $user['is_blocked'] : 0; ?>">
                                <button type="submit" name="toggle_block" class="action-btn block-btn">
                                    <?php echo isset($user['is_blocked']) ? ($user['is_blocked'] ? 'Unblock' : 'Block') : 'Block'; ?>
                                </button>
                            </form>
                            <!-- Delete Button -->
                            <form method="post" style="display:inline;" onsubmit="return confirmDelete();">
                                <input type="hidden" name="user_id" value="<?php echo isset($user['id']) ? $user['id'] : ''; ?>">
                                <input type="hidden" name="is_admin" value="<?php echo isset($user['is_admin']) ? $user['is_admin'] : 0; ?>">
                                <button type="submit" name="delete_user" class="action-btn delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>