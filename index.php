<?php
// index.php - SecurePay project root
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SecurePay E-Wallet & Cybersecurity Demo Lab</title>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-bg: #0a0a0f;
            --color-accent: #00ff41;
            --color-accent2: #00bfff;
            --color-danger: #ff0055;
            --color-card: rgba(10, 20, 20, 0.98);
            --color-text: #e0ffe6;
            --color-placeholder: #00ff41a0;
            --color-border: #00ff41cc;
            --color-btn-bg: #181f1b;
            --color-btn-hover: #00ff4160;
            --color-shadow: 0 0 32px #00ff41cc, 0 0 8px #00bfff99;
            --glitch1: #00ff41;
            --glitch2: #00bfff;
            --glitch3: #ff0055;
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Fira Mono', 'Consolas', 'Menlo', monospace;
            color: var(--color-text);
            background: var(--color-bg);
            overflow: hidden;
        }

        /* Matrix rain effect */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
            pointer-events: none;
            background: repeating-linear-gradient(180deg, #00ff4160 0 2px, transparent 2px 20px);
            opacity: 0.10;
            animation: matrix-fall 2s linear infinite;
        }

        @keyframes matrix-fall {
            0% {
                background-position-y: 0;
            }

            100% {
                background-position-y: 20px;
            }
        }

        /* Animated scanline overlay */
        body::after {
            content: "";
            pointer-events: none;
            position: fixed;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            z-index: 2;
            background: repeating-linear-gradient(180deg, rgba(0, 0, 0, 0.12) 0 2px, transparent 2px 8px);
            mix-blend-mode: overlay;
            animation: scanline 1.2s linear infinite;
        }

        @keyframes scanline {
            0% {
                background-position-y: 0;
            }

            100% {
                background-position-y: 8px;
            }
        }

        .container {
            width: 540px;
            max-width: 96vw;
            margin: 80px auto;
            padding: 36px 32px 32px 32px;
            position: relative;
            border-radius: 16px;
            background: var(--color-card);
            box-shadow: var(--color-shadow);
            border: 2.5px solid var(--color-accent2);
            z-index: 3;
            overflow: hidden;
        }

        .glitch {
            font-family: 'Fira Mono', 'Consolas', 'Menlo', monospace;
            font-size: 2.3em;
            font-weight: bold;
            color: var(--color-accent);
            text-align: center;
            letter-spacing: 2px;
            margin-top: 2px;
            margin-bottom: 20px;
            position: relative;
            text-shadow:
                0 0 8px var(--color-accent),
                2px 0 2px var(--glitch1),
                -2px 0 2px var(--glitch2),
                0 2px 2px var(--glitch3);
            animation: glitch 1.2s infinite linear alternate-reverse;
        }

        @keyframes glitch {
            0% {
                text-shadow: 2px 0 var(--glitch1), -2px 0 var(--glitch2), 0 2px var(--glitch3);
            }

            20% {
                text-shadow: -2px 0 var(--glitch2), 2px 0 var(--glitch1), 0 -2px var(--glitch3);
            }

            40% {
                text-shadow: 2px 2px var(--glitch1), -2px -2px var(--glitch2), 2px -2px var(--glitch3);
            }

            60% {
                text-shadow: -2px 2px var(--glitch2), 2px -2px var(--glitch1), -2px -2px var(--glitch3);
            }

            80% {
                text-shadow: 0 0 8px var(--color-accent);
            }

            100% {
                text-shadow: 2px 0 var(--glitch1), -2px 0 var(--glitch2), 0 2px var(--glitch3);
            }
        }

        .desc {
            margin-bottom: 18px;
            color: #baffc9;
            font-size: 1.08em;
            text-align: center;
            text-shadow: 0 0 4px #00ff41cc;
        }

        .main-btns {
            margin: 24px 0 0 0;
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .main-btns a {
            background: #0a0a0f;
            border: 2px solid var(--color-accent);
            color: var(--color-accent);
            font-family: inherit;
            font-size: 1.18em;
            font-weight: bold;
            padding: 16px 0;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            letter-spacing: 1.5px;
            box-shadow: 0 0 18px #00ff4140, 0 0 2px #00bfff80;
            transition: background 0.18s, color 0.18s, box-shadow 0.18s, border 0.18s;
            position: relative;
            overflow: hidden;
        }

        .main-btns a:hover {
            background: var(--color-accent);
            color: #0a0a0f;
            border-color: var(--color-danger);
            box-shadow: 0 0 32px #ff0055cc, 0 0 12px #00ff41cc;
            text-shadow: 0 0 8px #ff0055cc;
            filter: brightness(1.15) contrast(1.2);
        }

        .main-btns a:active {
            background: var(--color-danger);
            color: #fff;
            border-color: var(--color-accent2);
        }

        ul {
            margin: 0 0 0 18px;
            padding: 0;
            text-align: left;
        }

        li {
            margin-bottom: 8px;
        }

        strong {
            color: var(--color-accent2);
            text-shadow: 0 0 4px #00bfffcc;
        }

        ::-webkit-scrollbar {
            width: 8px;
            background: #222;
        }

        ::-webkit-scrollbar-thumb {
            background: #00ff41cc;
            border-radius: 4px;
        }

        /* Flicker effect for subtitle */
        .subtitle {
            color: var(--color-danger);
            font-size: 1.08em;
            text-align: center;
            margin-bottom: 18px;
            letter-spacing: 1.5px;
            text-shadow: 0 0 8px #ff0055cc, 0 0 2px #00ff41cc;
            animation: flicker 2.2s infinite alternate;
        }

        @keyframes flicker {

            0%,
            19%,
            21%,
            23%,
            25%,
            54%,
            56%,
            100% {
                opacity: 1;
            }

            20%,
            22%,
            24%,
            55% {
                opacity: 0.4;
            }
        }

        html {
            zoom: 85%;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="glitch">SecurePay</div>
        <div class="subtitle">[ access granted : blackHAT mode ]</div>
        <br>
        <div class="desc">
            <p>Choose your destination:</p>
            <ul>
                <li><strong>User Dashboard:</strong> Access your wallet, send/receive funds, and view transactions. Only an admin user can add funds.</li>
                <li><strong>Admin Panel:</strong> Manage users, view logs, and monitor activity (admin login required).</li>
                <li><strong>Cybersecurity Attack Demo Lab:</strong> Explore and learn about common web vulnerabilities and their fixes.</li>
            </ul>
            <br>
        </div>
        <div class="main-btns">
            <a href="auth/login.php">User Login</a>
            <a href="admin/login.php">Admin Login</a>
            <a href="cyberlab/">Cybersecurity Attack Demo Lab</a>
        </div>
    </div>
</body>

</html>