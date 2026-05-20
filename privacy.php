<?php
session_start();
require_once 'components/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php renderHead('Privacy Policy') ?>
  <meta name="description" content="SMFE Privacy Policy — how we collect, use, and protect your data on the SMFE math education platform.">
  <meta name="robots" content="index, follow">
</head>
<body class="no-askbar">

<?php renderNavbar(''); ?>

<div class="container" style="max-width:760px;padding-top:36px;padding-bottom:60px;">

  <h1 style="font-family:var(--font-display);font-size:1.8rem;font-weight:800;margin-bottom:6px;">Privacy Policy</h1>
  <p style="color:var(--text-muted);font-size:.88rem;margin-bottom:36px;">Last updated: May 2025</p>

  <section style="margin-bottom:28px;">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:10px;">1. What We Collect</h2>
    <p style="line-height:1.7;color:var(--text-dark);">When you create an account, we collect your username and email address. When you post questions or comments, that content is stored on our servers. We do not collect payment information or sensitive personal data.</p>
  </section>

  <section style="margin-bottom:28px;">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:10px;">2. How We Use Your Data</h2>
    <p style="line-height:1.7;color:var(--text-dark);">Your data is used solely to operate the SMFE platform — to display your posts, authenticate your account, and let other community members interact with your questions. We do not sell or share your data with third parties for marketing purposes.</p>
  </section>

  <section style="margin-bottom:28px;">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:10px;">3. Cookies</h2>
    <p style="line-height:1.7;color:var(--text-dark);">We use a session cookie to keep you logged in, and an optional "remember me" cookie that lasts 30 days if you choose to stay signed in. These cookies are strictly necessary for the platform to function and contain no tracking data.</p>
  </section>

  <section style="margin-bottom:28px;">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:10px;">4. Third-Party Services</h2>
    <p style="line-height:1.7;color:var(--text-dark);">SMFE loads fonts from Google Fonts and the KaTeX math rendering library from jsDelivr CDN. These services may log your IP address as part of normal CDN operation. Please review their respective privacy policies for details.</p>
  </section>

  <section style="margin-bottom:28px;">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:10px;">5. Data Security</h2>
    <p style="line-height:1.7;color:var(--text-dark);">Passwords are stored as secure bcrypt hashes — we never store plain-text passwords. We take reasonable precautions to protect your data, but no system is 100% secure. Please use a strong, unique password.</p>
  </section>

  <section style="margin-bottom:28px;">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:10px;">6. Your Rights</h2>
    <p style="line-height:1.7;color:var(--text-dark);">You can delete your posts at any time from the platform. If you wish to have your account and all associated data removed, please contact us through the community page.</p>
  </section>

  <section style="margin-bottom:28px;">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:10px;">7. Changes to This Policy</h2>
    <p style="line-height:1.7;color:var(--text-dark);">We may update this policy as the platform evolves. We will note the "Last updated" date at the top of this page when changes are made.</p>
  </section>

  <div style="margin-top:36px;">
    <a href="index.php" style="color:var(--primary);font-weight:600;">← Back to SMFE</a>
    &nbsp;&nbsp;·&nbsp;&nbsp;
    <a href="terms.php" style="color:var(--primary);font-weight:600;">Terms of Service →</a>
  </div>

</div>

</body>
</html>
