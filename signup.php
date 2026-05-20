<?php
session_start();
require_once 'components/navbar.php';
if (isset($_SESSION['user'])) {
  header('Location: index.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php renderHead('Sign Up', 'Join SMFE for free — the math education community where students and educators ask questions and share solutions.') ?>
  <style>
    body { padding: 0; }
  </style>
</head>
<body class="no-askbar">

<div class="auth-wrapper">
  <div class="auth-card">

    <div class="auth-logo">
      SMFE
      <span class="made-by-tharinda" style="display:block;font-size:.65rem;letter-spacing:.5px;">Made by Tharinda</span>
    </div>
    <div class="auth-sub">Join the math community</div>

    <div class="auth-title">Create account ✨</div>

    <div id="auth-error"   style="display:none;background:#fef2f2;border:1.5px solid #fecaca;border-radius:var(--radius-sm);padding:10px 14px;color:#dc2626;font-size:.88rem;margin-bottom:16px;"></div>
    <div id="auth-success" style="display:none;background:#ecfdf5;border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:10px 14px;color:var(--primary-dark);font-size:.88rem;margin-bottom:16px;"></div>

    <form id="signup-form" novalidate>

      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input
          class="form-control"
          type="text"
          id="username"
          name="username"
          placeholder="e.g. MathWizard99"
          required
          autocomplete="username"
        />
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Email address</label>
        <input
          class="form-control"
          type="email"
          id="email"
          name="email"
          placeholder="you@example.com"
          required
          autocomplete="email"
        />
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div style="position:relative;">
          <input
            class="form-control"
            type="password"
            id="password"
            name="password"
            placeholder="Minimum 6 characters"
            required
            autocomplete="new-password"
            style="padding-right:44px"
          />
          <button type="button" id="toggle-pw" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1rem;" title="Show/hide">👁</button>
        </div>
        <div id="pw-strength-bar" style="height:4px;border-radius:99px;background:var(--border-subtle);margin-top:6px;overflow:hidden;">
          <div id="pw-strength-fill" style="height:100%;width:0;border-radius:99px;transition:all .3s;background:var(--primary);"></div>
        </div>
        <div id="pw-strength-label" style="font-size:.72rem;color:#6b7280;margin-top:3px;"></div>
      </div>

      <div class="form-group">
        <label class="form-label" for="confirm_password">Confirm Password</label>
        <input
          class="form-control"
          type="password"
          id="confirm_password"
          name="confirm_password"
          placeholder="Re-enter password"
          required
          autocomplete="new-password"
        />
      </div>

      <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:20px;">
        <input type="checkbox" id="terms" name="terms" required style="margin-top:3px;accent-color:var(--primary);width:16px;height:16px;flex-shrink:0;" />
        <label for="terms" style="font-size:.83rem;color:#6b7280;line-height:1.5;cursor:pointer;">
          I agree to the <a href="terms.php" style="color:var(--primary);font-weight:600;">Terms of Service</a> and
          <a href="privacy.php" style="color:var(--primary);font-weight:600;">Privacy Policy</a>
        </label>
      </div>

      <button type="submit" class="btn btn-primary" id="signup-btn" style="width:100%;justify-content:center;padding:12px;">
        Create Account
      </button>

    </form>

    <div class="auth-link" style="margin-top:16px;">
      Already have an account? <a href="login.php">Log in</a>
    </div>

    <div class="auth-link" style="margin-top:10px;">
      <a href="index.php" style="color:var(--teal);font-size:.82rem;">← Go to Main Page</a>
    </div>

  </div>
</div>

<script src="js/mathkb.js?v=<?= filemtime('js/mathkb.js') ?>"></script>
<script src="js/main.js?v=<?= filemtime('js/main.js') ?>"></script>
<script>
document.getElementById('toggle-pw')?.addEventListener('click', function() {
  const pw = document.getElementById('password');
  pw.type = pw.type === 'password' ? 'text' : 'password';
  this.textContent = pw.type === 'password' ? '👁' : '🙈';
});

document.getElementById('password')?.addEventListener('input', function() {
  const val  = this.value;
  const fill = document.getElementById('pw-strength-fill');
  const lbl  = document.getElementById('pw-strength-label');
  let score  = 0;
  if (val.length >= 6)       score++;
  if (val.length >= 10)      score++;
  if (/[A-Z]/.test(val))     score++;
  if (/[0-9]/.test(val))     score++;
  if (/[^a-zA-Z0-9]/.test(val)) score++;
  const pct    = (score / 5) * 100;
  const colors = ['#ef4444','#f97316','#eab308','#22c55e','#10b981'];
  const labels = ['Very weak','Weak','Fair','Strong','Very strong'];
  fill.style.width      = pct + '%';
  fill.style.background = colors[score - 1] || '#e5e7eb';
  lbl.textContent       = val.length ? (labels[score - 1] || '') : '';
});

document.getElementById('signup-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn      = document.getElementById('signup-btn');
  const errEl    = document.getElementById('auth-error');
  const succEl   = document.getElementById('auth-success');
  const username = document.getElementById('username').value.trim();
  const email    = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;
  const confirm  = document.getElementById('confirm_password').value;
  const terms    = document.getElementById('terms').checked;

  errEl.style.display  = 'none';
  succEl.style.display = 'none';

  if (password !== confirm) {
    errEl.textContent = '⚠️ Passwords do not match.';
    errEl.style.display = 'block';
    return;
  }
  if (!terms) {
    errEl.textContent = '⚠️ Please agree to the Terms of Service.';
    errEl.style.display = 'block';
    return;
  }

  btn.textContent = 'Creating account…';
  btn.disabled = true;

  try {
    const res  = await fetch('api/auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'signup', username, email, password }),
    });
    const data = await res.json();
    if (data.ok) {
      window.location.href = data.redirect || 'index.php';
    } else {
      errEl.textContent = '⚠️ ' + data.error;
      errEl.style.display = 'block';
      btn.textContent = 'Create Account';
      btn.disabled = false;
    }
  } catch (err) {
    errEl.textContent = '⚠️ Network error. Please try again.';
    errEl.style.display = 'block';
    btn.textContent = 'Create Account';
    btn.disabled = false;
  }
});
</script>
</body>
</html>
