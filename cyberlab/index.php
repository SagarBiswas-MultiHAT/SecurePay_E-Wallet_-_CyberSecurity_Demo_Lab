<?php
// cyberlab/index.php
// Cybersecurity Demo Lab overview with global secure/vulnerable toggle
include 'secure_toggle.php';

// Helper for mode label
function modeLabel()
{
    if (isSecureMode()) {
        return '<span style="color:#007e33;font-weight:bold;background:#e0ffe6;padding:2px 10px;border-radius:4px;">Secure Mode</span>';
    } else {
        return '<span style="color:#b00020;font-weight:bold;background:#ffe0e0;padding:2px 10px;border-radius:4px;">Vulnerable Mode</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Cybersecurity Attack Demo Lab - SecurePay</title>

    <style>
        :root {
            --color-bg: #10151a;
            --color-accent: #00ff41;
            --color-accent2: #00bfff;
            --color-card: rgba(20, 30, 30, 0.98);
            --color-text: #e0ffe6;
            --color-placeholder: #00ff41a0;
            --color-border: #00ff41cc;
            --color-btn-bg: #181f1b;
            --color-btn-hover: #00ff4160;
            --color-shadow: 0 0 24px #00ff41cc, 0 0 4px #00bfff99;
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

        /* Matrix animation */
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
            opacity: 0.12;
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

        .container {
            width: 540px;
            max-width: 96vw;
            margin: 80px auto;
            padding: 36px 32px 32px 32px;
            position: relative;
            border-radius: 12px;
            background: var(--color-card);
            box-shadow: var(--color-shadow);
            border: 2px solid var(--color-border);
            z-index: 1;
        }

        .mode-toggle {
            margin-top: 10px;
            position: absolute;
            top: 18px;
            right: 24px;
            font-size: 1.1em;
            z-index: 2;
        }

        .mode-toggle a {
            color: var(--color-accent2);
            background: var(--color-btn-bg);
            border: 1px solid var(--color-accent2);
            border-radius: 4px;
            padding: 4px 14px;
            margin-left: 10px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 0 8px #00bfff80;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        }

        .mode-toggle a:hover {
            background: var(--color-btn-hover);
            color: var(--color-accent);
            box-shadow: 0 0 16px #00ff41cc;
        }

        .lab-btns {
            margin: 12px 0 0 0;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .lab-btns a {
            background: var(--color-btn-bg);
            border: 1.5px solid var(--color-accent);
            color: var(--color-accent);
            font-family: inherit;
            font-size: 1.1em;
            font-weight: bold;
            padding: 14px 0;
            border-radius: 6px;
            text-align: center;
            text-decoration: none;
            letter-spacing: 1px;
            box-shadow: 0 0 12px #00ff4140;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        }

        .lab-btns a:hover {
            background: var(--color-accent);
            color: #10151a;
            box-shadow: 0 0 24px #00ff41cc;
        }

        .desc {
            margin-bottom: 18px;
            color: #baffc9;
            font-size: 1.08em;
        }

        h1 {
            margin-top: 40px;
            color: var(--color-accent);
            font-family: inherit;
            font-size: 2.1em;
            text-shadow: 0 0 8px #00ff41cc;
            margin-bottom: 32px;
            letter-spacing: 2px;
            text-align: center;
        }

        ul {
            margin: 0 0 0 18px;
            padding: 0;
        }

        li {
            margin-bottom: 8px;
        }

        strong {
            color: var(--color-accent2);
        }

        /* Custom scrollbars */
        ::-webkit-scrollbar {
            width: 8px;
            background: #222;
        }

        ::-webkit-scrollbar-thumb {
            background: #00ff41cc;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 style="text-decoration: underline; margin-bottom: 32px;">Cybersecurity Attack <br>Demo Lab</h1>

        <div class="mode-toggle">
            <?php echo modeLabel(); ?>
            <?php if (isSecureMode()): ?>
                <a href="?mode=vulnerable" style="margin-left:12px;">Switch to Vulnerable</a>
            <?php else: ?>
                <a href="?mode=secure" style="margin-left:12px;">Switch to Secure</a>
            <?php endif; ?>
        </div>
        <div class="desc">
            <p>This lab demonstrates common web vulnerabilities and their fixes. Toggle between <strong>Vulnerable</strong> and <strong>Secure</strong> modes globally.</p>
            <ul>
                <li><strong>XSS (Cross-Site Scripting):</strong> Inject scripts via user input. <a href="xss_demo.php">Try XSS Demo</a></li>

            </ul>
        </div>
        <div class="lab-btns">
            <a href="xss_demo.php">XSS Demo</a>
        </div>
    </div>
</body>

</html>