<?php
// xss_demo.php
// XSS Demo: Shows both vulnerable and secure comment forms
// Uses global secure_toggle.php for mode
include 'secure_toggle.php';

// Use session to store comments for demo (not persistent)
$comments = $_SESSION['xss_comments'] ?? [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = $_POST['comment'] ?? '';
    if (trim($comment) === '') {
        $message = 'Comment cannot be empty!';
    } else {
        $comments[] = $comment;
        $_SESSION['xss_comments'] = $comments;
        $message = 'Comment posted!';
    }
}

// Helper for output (vulnerable vs secure)
function show_comment($c)
{
    if (isSecureMode()) {
        // Secure: escape HTML
        return htmlspecialchars($c);
    } else {
        // Vulnerable: raw output
        return $c;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>XSS Demo - Cyber Lab</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
            overflow-x: hidden;
        }

        /* Matrix rain animation */
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
            opacity: 0.13;
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
            width: 900px;
            margin: 50px auto;
            padding: 36px 32px 32px 32px;
            position: relative;
            border-radius: 12px;
            background: var(--color-card);
            box-shadow: var(--color-shadow);
            border: 2px solid var(--color-border);
            z-index: 1;
        }
        html{
            zoom: 80%;
        }

        .toggle-btns {
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .toggle-btns a {
            margin-right: 10px;
            color: var(--color-accent2);
            background: var(--color-btn-bg);
            border: 1px solid var(--color-accent2);
            border-radius: 4px;
            padding: 4px 14px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 0 8px #00bfff80;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        }

        .toggle-btns a:hover {
            background: var(--color-btn-hover);
            color: var(--color-accent);
            box-shadow: 0 0 16px #00ff41cc;
        }

        .xss-note {
            background: #1a1f1a;
            border: 1.5px solid var(--color-accent2);
            color: var(--color-accent2);
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 4px;
            font-size: 1.08em;
            box-shadow: 0 0 12px #00bfff40;
        }

        .xss-note code {
            color: var(--color-accent);
            background: #181f1b;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 1em;
        }

        .alert {
            margin: 12px 0 18px 0;
            padding: 10px 16px;
            border-radius: 4px;
            font-weight: bold;
            font-family: inherit;
            box-shadow: 0 0 8px #00ff4140;
        }

        .alert.success {
            background: #0f2c1a;
            color: var(--color-accent);
            border: 1.5px solid var(--color-accent);
        }

        .alert.error {
            background: #2c0f1a;
            color: #ff4f4f;
            border: 1.5px solid #ff4f4f;
        }

        form {
            margin-bottom: 18px;
        }

        label {
            font-size: 1.08em;
            color: var(--color-accent2);
            font-family: inherit;
        }

        textarea {
            width: 100%;
            background: #181f1b;
            color: var(--color-accent);
            border: 1.5px solid var(--color-accent2);
            border-radius: 6px;
            font-family: inherit;
            font-size: 1.08em;
            padding: 10px;
            margin-bottom: 10px;
            box-shadow: 0 0 8px #00bfff40;
            resize: vertical;
        }

        textarea::placeholder {
            color: var(--color-placeholder);
        }

        button[type="submit"] {
            background: var(--color-btn-bg);
            color: var(--color-accent);
            border: 1.5px solid var(--color-accent);
            font-family: inherit;
            font-size: 1.1em;
            font-weight: bold;
            padding: 10px 32px;
            border-radius: 6px;
            letter-spacing: 1px;
            box-shadow: 0 0 12px #00ff4140;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background: var(--color-accent);
            color: #10151a;
            box-shadow: 0 0 24px #00ff41cc;
        }

        h2 {
            color: var(--color-accent);
            font-family: inherit;
            font-size: 2em;
            text-shadow: 0 0 8px #00ff41cc, 0 0 2px #00bfff99;
            margin-bottom: 18px;
            letter-spacing: 2px;
            text-align: center;
            animation: flicker 2.5s infinite alternate;
        }

        @keyframes flicker {

            0%,
            100% {
                opacity: 1;
                text-shadow: 0 0 8px #00ff41cc, 0 0 2px #00bfff99;
            }

            10% {
                opacity: 0.85;
            }

            20% {
                opacity: 0.95;
            }

            30% {
                opacity: 0.7;
                text-shadow: 0 0 16px #00ff41cc, 0 0 8px #00bfff99;
            }

            40% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }

            60% {
                opacity: 1;
            }

            70% {
                opacity: 0.9;
            }

            80% {
                opacity: 1;
            }

            90% {
                opacity: 0.95;
            }
        }

        h3 {
            color: var(--color-accent2);
            font-family: inherit;
            font-size: 1.3em;
            margin-top: 28px;
            margin-bottom: 10px;
            text-shadow: 0 0 6px #00bfff99;
        }

        .comment-box {
            margin-bottom: 12px;
            background: #181f1b;
            color: var(--color-accent);
            border-left: 3px solid var(--color-accent2);
            border-radius: 4px;
            padding: 10px 14px;
            font-family: inherit;
            font-size: 1.08em;
            box-shadow: 0 0 8px #00bfff20;
            word-break: break-word;
        }

        .comment-box:last-child {
            margin-bottom: 0;
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

        /* Terminal-style footer */
        .footer-terminal {
            margin-top: 32px;
            background: #0a0f0a;
            color: #00ff41;
            font-family: inherit;
            font-size: 1.05em;
            border-radius: 0 0 12px 12px;
            border-top: 1.5px solid #00ff41cc;
            box-shadow: 0 0 12px #00ff4140;
            padding: 10px 18px 8px 18px;
            text-shadow: 0 0 4px #00ff41cc;
            letter-spacing: 1px;
            user-select: text;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>XSS Demo: Cross-Site Scripting</h2>
        <br>
        <div class="toggle-btns">
            <div>
                <?php if (isSecureMode()): ?>
                    <span style="color:#00ff41;font-weight:bold;background:#0f2c1a;padding:2px 10px;border-radius:4px;box-shadow:0 0 8px #00ff41cc;">Secure Mode: ON</span>
                    <a href="?mode=vulnerable">Switch to Vulnerable</a>
                <?php else: ?>
                    <span style="color:#ff4f4f;font-weight:bold;background:#2c0f1a;padding:2px 10px;border-radius:4px;box-shadow:0 0 8px #ff4f4fcc;">Vulnerable Mode: ON</span>
                    <a href="?mode=secure">Switch to Secure</a>
                <?php endif; ?>
            </div>
            <a href="index.php">Back to Lab Index</a>
        </div>
        <div class="xss-note">
            <strong>Try posting a comment like:</strong> <code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code><br>
            <span style="color:#ff4f4f;">Vulnerable mode will pop an alert. Secure mode will show the code as text.</span>
        </div>
        <?php if ($message): ?>
            <div class="alert <?php echo isSecureMode() ? 'success' : 'error'; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="post" action="" autocomplete="off">
            <label>Post a Comment:</label>
            <textarea name="comment" rows="3" placeholder="Type your hack here..."></textarea>
            <button type="submit">Submit</button>
        </form>
        <h3>Comments:</h3>
        <?php if (empty($comments)): ?>
            <div class="comment-box">No comments yet.</div>
        <?php else: ?>
            <?php foreach ($comments as $c): ?>
                <div class="comment-box">
                    <?php echo show_comment($c); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <div class="footer-terminal">
            <span style="color:#00bfff;">user@cyberlab</span>:<span style="color:#00ff41;">~</span>$ <span style="color:#e0ffe6;"># XSS Demo for Web Security — <b>Try to break it!</b></span>
        </div>
    </div>
</body>

</html>