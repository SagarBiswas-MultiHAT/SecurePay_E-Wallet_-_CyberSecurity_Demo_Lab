<?php
// Admin Login for SecurePay
require_once '../includes/db_connect.php';
session_start();

$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    // Find admin user by email
    $sql = "SELECT id, password, is_admin, failed_attempts, lock_until FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($id, $hash, $is_admin, $failed_attempts, $lock_until);
    if ($stmt->fetch()) {
        // Clear expired locks
        if (!empty($lock_until) && strtotime($lock_until) <= time()) {
            $clear = $conn->prepare('UPDATE users SET failed_attempts = 0, lock_until = NULL WHERE id = ?');
            $clear->bind_param('i', $id);
            $clear->execute();
            $clear->close();
            $failed_attempts = 0;
            $lock_until = null;
        }

        // Enforce lock if active
        if (!empty($lock_until) && strtotime($lock_until) > time()) {
            $remaining = max(0, strtotime($lock_until) - time());
            $minutes = floor($remaining / 60);
            $seconds = $remaining % 60;
            $alert = '<div class="alert error">Too many failed attempts. Try again in ' . ($minutes > 0 ? $minutes . 'm ' : '') . $seconds . 's.</div>';
        } elseif (!$is_admin) {
            $alert = '<div class="alert error">Invalid credentials or not an admin.</div>';
        } elseif (password_verify($password, $hash)) {
            // Reset counters on success
            $reset = $conn->prepare('UPDATE users SET failed_attempts = 0, lock_until = NULL WHERE id = ?');
            $reset->bind_param('i', $id);
            $reset->execute();
            $reset->close();

            $_SESSION['user_id'] = $id;
            $_SESSION['is_admin'] = true;
            header('Location: index.php');
            exit;
        } else {
            // increment attempts and maybe lock
            $failed_attempts = (int)$failed_attempts + 1;
            if ($failed_attempts >= 3) {
                $lock_until_dt = date('Y-m-d H:i:s', time() + 3 * 60);
                $upd = $conn->prepare('UPDATE users SET failed_attempts = ?, lock_until = ? WHERE id = ?');
                $upd->bind_param('isi', $failed_attempts, $lock_until_dt, $id);
                $upd->execute();
                $upd->close();
                $alert = '<div class="alert error">Incorrect password. Admin access locked for 3 minutes.</div>';
            } else {
                $upd = $conn->prepare('UPDATE users SET failed_attempts = ? WHERE id = ?');
                $upd->bind_param('ii', $failed_attempts, $id);
                $upd->execute();
                $upd->close();
                $remaining_attempts = 3 - $failed_attempts;
                $alert = '<div class="alert error">Incorrect password. Attempts left: ' . $remaining_attempts . '.</div>';
            }
        }
    } else {
        $alert = '<div class="alert error">Admin account not found.</div>';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Login | SecurePay</title>
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
            align-items: center;
            justify-content: center;
            color: var(--color-text);
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            position: relative;
            overflow: hidden;
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
            max-width: 600px;
            margin: 60px auto 0 auto;
            padding: 0 0 32px 0;
            background: var(--color-card);
            border-radius: 22px;
            box-shadow: 0 8px 32px 0 #00ffea33, 0 1.5px 4px 0 #3a86ff33;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(18px) saturate(1.3);
            border: 2.5px solid var(--color-border);
        }

        .logo {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 12px;
        }

        .logo img {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            box-shadow: 0 2px 12px #00ffea44, 0 1.5px 6px #3a86ff33;
            margin-top: 45px;
            border: 1.5px solid #00ffea66;
            transition: box-shadow 0.3s, border 0.3s;
        }

        .login-title {
            margin-top: 30px;
            font-size: 30px;
            text-align: center;
            color: #00000099;
            font-weight: 900;
            margin-bottom: 0;
            letter-spacing: 2px;
            text-shadow: 0 2px 16px #00ffea, 0 2px 8px #3a86ff;
        }

        label {
            display: block;
            margin-top: 8px;
            color: var(--color-accent);
            font-weight: 700;
            font-size: 1.09em;
            letter-spacing: 1px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 16px 18px;
            margin-top: 8px;
            border-radius: 14px;
            font-size: 1.13em;
            background: var(--color-input);
            border: 2px solid var(--color-border);
            color: #fff;
            transition: border 0.2s, box-shadow 0.2s, background 0.2s;
            box-shadow: 0 1.5px 8px #00ffea22;
        }

        input[type="text"]::placeholder,
        input[type="email"]::placeholder,
        input[type="password"]::placeholder {
            color: var(--color-placeholder);
            font-size: 1em;
            opacity: 0.8;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--color-accent);
            background: rgba(0, 255, 234, 0.08);
            box-shadow: 0 0 0 3px #00ffea55;
        }

        button {
            width: 100%;
            margin-top: 32px;
            padding: 15px;
            background: linear-gradient(90deg, #00ffea 0%, #3a86ff 50%, #7c3aed 100%);
            color: #fff;
            border: none;
            border-radius: 14px;
            font-size: 1.18em;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 0 24px #00ffea, 0 2px 12px #3a86ff99;
            letter-spacing: 1px;
            text-shadow: 0 2px 8px #00ffea99;
            transition: background 0.2s, box-shadow 0.2s, color 0.2s, transform 0.1s;
        }

        button:hover {
            background: linear-gradient(90deg, #7c3aed 0%, #00ffea 100%);
            color: #fff;
            box-shadow: 0 0 48px #00ffea, 0 4px 24px #3a86ffcc;
            transform: translateY(-2px) scale(1.03);
        }

        .alert.error {
            padding: 12px;
            margin: 18px 32px 0 32px;
            border-radius: 8px;
            font-size: 1em;
            background: #ffe0e0cc;
            color: #b00020;
            border: 1.5px solid #ffb3b3;
            box-shadow: 0 1.5px 8px #ffb3b344;
        }

        a {
            color: #000;
            text-decoration: none;
            font-weight: 500;
        }

        a:hover {
            text-decoration: underline;
        }

        html {
            zoom: 90%;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">
            <img src="../imgs/SecurePAY.png" alt="SecurePay Logo" onerror="this.style.display='none'">
        </div>
        <div class="login-title">Admin Login</div>
        <?php if ($alert) echo $alert; ?>
        <form method="post" style="padding:32px 32px 0 32px;">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required autofocus placeholder="Enter your email">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required placeholder="Enter your password">
            <button type="submit">Login</button>
        </form>
    </div>
</body>

</html>