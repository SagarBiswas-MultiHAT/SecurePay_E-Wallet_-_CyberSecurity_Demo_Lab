# SecurePay E‑Wallet + Cybersecurity Demo Lab

<p align="center">
  <img src="./imgs/SecurePAY.png" alt="SecurePay Logo" width="180"/>
</p>

SecurePay is a hands‑on PHP/MySQL E‑Wallet application paired with a small Cybersecurity Demo Lab. It’s built for learning: follow real wallet flows (auth, balance, transfers, history, admin) and then jump into a toggleable lab to see common web attacks and their mitigations side‑by‑side.

— No frameworks, just readable PHP/HTML/CSS/JS.

---

## Table of contents

- Overview
- Features
- Screenshots / Media
- Project structure
- Tech stack
- Prerequisites
- Quickstart (Windows + XAMPP)
- Database setup
- Usage guide (User + Admin)
- Cybersecurity Demo Lab
- Configuration notes
- Troubleshooting
- Contributing
- License
- Author

---

## Overview

SecurePay simulates a small E‑Wallet where users can register/login, see a dashboard with balance and transactions, send money to others, and (policy for this demo) only admins can add funds while non‑admins can withdraw. The Cyber Lab lets you flip between Vulnerable and Secure modes to observe exploits and fixes.

The code is intentionally simple and commented for students and instructors.

---

## Features

User

- Register, login, logout (passwords hashed; sessions)
- Dashboard: balance, last 5 transactions, top 3 recipients
- Send money (CSRF token on form)
- Transaction history with filters (type, date range)
- Withdraw (non‑admin)

Admin

- Admin login with lockout on repeated failures
- Dashboard stats: total users, total wallet balance, transactions today
- User management: reset password (random), block/unblock, delete

Security in the app

- Prepared statements for DB access; server-side input sanitization
- password_hash/password_verify for credentials
- Login lockout (3 failed attempts → 3 minutes)
- Session inactivity timeout (5 minutes) and route protection via `require_login()`
- CSRF token for transfers

Cybersecurity Demo Lab

- Global toggle: Vulnerable vs Secure
- XSS demo: raw vs escaped rendering
- Session hijacking walkthrough (optional Node receiver)

---

## Screenshots / Media

- Logo above; more photos: https://www.facebook.com/share/p/1LtAYUGgND/
- Feel free to add screenshots/GIFs of the dashboard, transfer flow, admin panel, and cyber lab here.

---

## Project structure

```
SecurePay_E-Wallet_&_Cybersecurity_Demo_Lab/
├─ index.php                      # Landing page (User/Admin/Cyber Lab)
├─ add_funds.php                  # Admin-only self top-up
├─ send_money.php                 # Transfer between users (CSRF-protected)
├─ withdraw.php                   # Non-admin withdrawal
├─ history.php                    # Transaction history with filters
├─ setup.sql                      # One-shot DB setup (drop/create + seed)
├─ users_table.sql                # Users table schema
├─ wallets_transactions_tables.sql# Wallet + transactions schema
├─ add_login_lockout.sql          # Migration for failed_attempts/lock_until
├─ make_sagar_admin.sql           # Mark specific email as admin
├─ accounts.txt                   # Local demo accounts (for testing)
│
├─ includes/
│  ├─ db_connect.php              # MySQLi connection (edit creds)
│  └─ functions.php               # sanitize_input, require_login, etc.
│
├─ auth/
│  ├─ login.php                   # User login (with lockout)
│  ├─ register.php                # User sign-up
│  └─ logout.php
│
├─ dashboard/
│  └─ index.php                   # Balance, recents, shortcuts
│
├─ admin/
│  ├─ login.php                   # Admin login (with lockout)
│  ├─ index.php                   # Admin dashboard (stats + recents)
│  ├─ users.php                   # User management
│  └─ logout.php
│
├─ cyberlab/
│  ├─ index.php                   # Lab index + global mode toggle
│  ├─ secure_toggle.php           # Stores mode in session
│  ├─ xss_demo.php                # XSS: vulnerable vs secure rendering
│  └─ for_stole_data/
│     ├─ server.js                # Minimal Node endpoint to collect data
│     ├─ session_hijacking.php    # Small helper for simulation
│     ├─ session_hijacking.md     # Lab guide
│     └─ XSS_Payloads.md          # Educational payloads
│
├─ assets/
│  ├─ css/
│  │  ├─ style.css
│  │  └─ adminStyle.css
│  └─ js/
│     └─ validate.js
│
└─ imgs/
   ├─ SecurePAY.png
   └─ send-money.png
```

---

## Tech stack

- PHP 8.x (works with 7.4+)
- MySQL 5.7+/8.x (InnoDB)
- HTML/CSS, tiny vanilla JS
- Optional: Node.js 16+ (for the session hijack receiver)

---

## Prerequisites

- Windows with XAMPP (Apache + MySQL), or any LAMP/WAMP equivalent
- phpMyAdmin (or MySQL CLI) to import SQL
- Node.js only if you want to run the lab’s receiver (`server.js`)

---

## Quickstart (Windows + XAMPP)

1. Copy the folder under htdocs

- Example: `C:\\xampp\\htdocs\\Web_Tech_Project\\SecurePay_E-Wallet_&_Cybersecurity_Demo_Lab`

2. Create the database

- Recommended: import `setup.sql` via phpMyAdmin (creates `securepay_db` and tables)
- Alternatively: import `users_table.sql` and `wallets_transactions_tables.sql`, then `add_login_lockout.sql`

3. Configure DB connection

- Edit `includes/db_connect.php` (defaults: host `localhost`, user `root`, pass `''`, db `securepay_db`)

4. Start Apache and MySQL in XAMPP

5. Open the app

- Adjust base path to where you placed the folder:
  - http://localhost/Web*Tech_Project/SecurePay_E-Wallet*&\_Cybersecurity_Demo_Lab/

Optional: start the lab receiver

- In `cyberlab/for_stole_data/` run `node server.js` (listens on http://localhost:3000)

---

## Database setup

One-shot (recommended)

- `setup.sql` drops/creates `securepay_db`, creates all tables, and seeds an admin if not present

Manual

- `users_table.sql` → `users`
- `wallets_transactions_tables.sql` → `wallets`, `transactions`
- `add_login_lockout.sql` → adds `failed_attempts`, `lock_until`
- `make_sagar_admin.sql` → marks a specific email as admin

Schema summary

- users: id, username, email (unique), password (hash), is_admin, is_blocked, failed_attempts, lock_until, created_at
- wallets: user_id (PK, FK), balance DECIMAL(12,2)
- transactions: id, sender_id, receiver_id, amount, transaction_type ENUM('add_fund','transfer','withdraw'), created_at

---

## Usage guide

User

- Register at `auth/register.php`, login at `auth/login.php`
- Dashboard shows balance, last 5 transactions, top recipients
- Send money via `send_money.php` (CSRF token enforced)
- View filters in `history.php`
- Withdraw with `withdraw.php` (non‑admin)

Admin

- Login: `admin/login.php`
- Dashboard: `admin/index.php` (stats + recent transactions)
- Manage users: `admin/users.php` (reset password, block/unblock, delete)
- Demo policy: admins see "Add Funds" while users see "Withdraw"

Demo accounts

- See `accounts.txt` for local testing entries (e.g., `eng.sagar.aiub@gmail.com` / `admin123` as provided for the demo)

Notable security behaviors

- 3 failed logins → lock for 3 minutes (with countdown feedback)
- 5 minutes of inactivity → session timeout via `require_login()`
- Transfers require a valid per‑session CSRF token
- Inputs sanitized and all DB calls use prepared statements

---

## Cybersecurity Demo Lab

Entry point

- `cyberlab/index.php` — switch between Vulnerable and Secure modes (stored in session via `secure_toggle.php`)

XSS demo (`cyberlab/xss_demo.php`)

- Vulnerable: comments are rendered raw (script executes)
- Secure: comments are escaped (`htmlspecialchars`)
- Try: `<script>alert('XSS')</script>` (education only)

Session hijacking walkthrough

- Guide: `cyberlab/for_stole_data/session_hijacking.md`
- Receiver: `cyberlab/for_stole_data/server.js` (Node on :3000)
- Helper: `cyberlab/for_stole_data/session_hijacking.php`

Ethics notice

- These materials are for learning on systems you own/have permission to test. Do not attack real systems.

---

## Configuration notes

Base paths

- `includes/functions.php` contains `require_login()` which redirects to different login pages depending on whether the script path contains `/admin/`
- Paths are coded for `Web_Tech_Project/SecurePay_E-Wallet_&_Cybersecurity_Demo_Lab`
- If you move the folder, update those redirects or configure an Apache Alias to keep URLs stable

Sessions

- PHP sessions must be enabled; default XAMPP configuration usually works
- Session timeout is 5 minutes of inactivity per `require_login()`

---

## Troubleshooting

- 404 Not Found → Verify folder path under `htdocs` and URL
- DB connection fails → Check credentials in `includes/db_connect.php` and ensure `securepay_db` exists
- Unknown column errors → Run `setup.sql` or the correct migration scripts
- Image/CSS not visible → Confirm files exist under `imgs/` and `assets/`
- CSRF/token errors on transfer → Reload the page to refresh the token

Windows/XAMPP notes

- If imports time out in phpMyAdmin, import tables individually instead of `setup.sql`
- If `node` isn’t recognized, install from https://nodejs.org and re-open the terminal

---

## Contributing

- Fork → branch → PR
- Keep commits focused and explain changes (educational value matters here)
- New lab demos should honor the global toggle via `isSecureMode()` and clearly show vuln vs fix

---

## License

MIT

---

## Author

- GitHub Profile: https://github.com/SagarBiswas-MultiHAT
- Project: SecurePay E‑Wallet + Cybersecurity Demo Lab

If this repo helped you learn, please star it and share feedback. Happy (ethical) hacking!
