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
  <?php renderHead('Log In', 'Log in to SMFE — the math education community where students and educators share and solve problems together.') ?>
  <style>
    body { padding: 0; }
    .auth-wrapper { padding-top: 0; }
  </style>
</head>
<body class="no-askbar">

<div class="auth-wrapper">
  <div class="auth-card">

    <div class="auth-logo">
      SMFE
      <span class="made-by-tharinda" style="display:block;font-size:.65rem;letter-spacing:.5px;">Made by Tharinda</span>
    </div>
    <div class="auth-sub">Social Media For Education</div>

    <div class="auth-title">Welcome back 👋</div>

    <div id="auth-error" style="display:none;background:#fef2f2;border:1.5px solid #fecaca;border-radius:var(--radius-sm);padding:10px 14px;color:#dc2626;font-size:.88rem;margin-bottom:16px;"></div>

    <form id="login-form" novalidate>

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
            placeholder="••••••••"
            required
            autocomplete="current-password"
            style="padding-right:44px"
          />
          <button type="button" id="toggle-pw" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1rem;" title="Show/hide password">👁</button>
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
        <a href="signup.php" style="font-size:.82rem;color:var(--primary);font-weight:600;">Create a new account instead</a>
      </div>

      <button type="submit" class="btn btn-primary" id="login-btn" style="width:100%;justify-content:center;padding:12px;">
        Log in
      </button>

    </form>

    <div class="divider">or continue with</div>

    <div style="display:flex;gap:10px;">
      <button class="btn btn-outline" style="flex:1;justify-content:center;" onclick="showToast('Google login coming soon','🔜')">
        🌐 Google
      </button>
      <button class="btn btn-outline" style="flex:1;justify-content:center;" onclick="showToast('GitHub login coming soon','🔜')">
        🐙 GitHub
      </button>
    </div>

    <div class="auth-link">
      Don't have an account? <a href="signup.php">Sign up free</a>
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

document.getElementById('login-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn   = document.getElementById('login-btn');
  const errEl = document.getElementById('auth-error');
  const email = document.getElementById('email').value.trim();
  const pass  = document.getElementById('password').value;

  errEl.style.display = 'none';
  btn.textContent = 'Logging in…';
  btn.disabled = true;

  try {
    const res  = await fetch('api/auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'login', email, password: pass }),
    });
    const data = await res.json();
    if (data.ok) {
      window.location.href = data.redirect || 'index.php';
    } else {
      errEl.textContent = '⚠️ ' + data.error;
      errEl.style.display = 'block';
      btn.textContent = 'Log in';
      btn.disabled = false;
    }
  } catch (err) {
    errEl.textContent = '⚠️ Network error. Please try again.';
    errEl.style.display = 'block';
    btn.textContent = 'Log in';
    btn.disabled = false;
  }
});
</script>
</body>
</html>
