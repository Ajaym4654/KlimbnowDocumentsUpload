Final Candidate Upload Portal - Fixes for Status:0
---------------------------------------------------
What's improved:
- CORS headers added so AJAX POST works when testing across origins.
- OPTIONS preflight handled.
- Increased PHP runtime and upload limits via ini_set (subject to host limits).
- Better JS error messages (network error, timeout, detailed server response).
- upload.php now returns more detailed SMTP debug info if sending fails.
- Max file size increased to 50MB per file. You can lower it in upload.php.

Deployment checklist:
1. Upload all files to your PHP host (public_html).
2. Ensure uploads/ is writable (chmod 755 or 775).
3. Ensure your host allows outbound TCP to smtp.gmail.com:587.
4. If testing locally via file:// open the site via a local server (XAMPP, WAMP, or Live Server) — do NOT open index.html as a file in browser or you'll get CORS/network issues (Status: 0).
5. If you still see "Status: 0", open developer tools -> Network tab -> inspect the request and error. Paste the exact server response here and I'll diagnose.

Security note:
- SMTP app password is stored in upload.php for convenience. For production, move it to a protected config file or environment variable.
- Consider scanning uploads for malware in production.

Download the ZIP in the provided link from the assistant.
