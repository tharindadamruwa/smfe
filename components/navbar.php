<?php
/**
 * SMFE Navbar Component
 */

require_once __DIR__ . '/../db/config.php';

// Restore 30-day login session from cookie on every page
if (session_status() === PHP_SESSION_ACTIVE) {
  try { restoreSessionFromCookie(); } catch (Throwable $e) { /* never crash a page */ }
}

function renderNavbar($current = 'home') {
  $isLoggedIn = isset($_SESSION['user']);
?>
<nav class="navbar" id="navbar" data-username="<?= htmlspecialchars($_SESSION['user']['username'] ?? '') ?>">
  <a href="index.php" class="navbar-brand">
    SMFE
    <span class="made-by-tharinda">Made by Tharinda</span>
  </a>

  <div class="navbar-search" id="navbar-search-wrap">
    <span class="search-icon">🔍</span>
    <input type="text" id="navbar-search-input" placeholder="Search @user or topic…" autocomplete="off" />
    <div class="search-dropdown" id="search-dropdown"></div>
  </div>

  <button class="nav-toggle" id="nav-toggle" aria-label="Toggle menu">
    <span></span><span></span><span></span>
  </button>

  <div class="navbar-links" id="nav-links">
    <a href="index.php"   class="nav-link <?= $current==='home'    ? 'active':'' ?>">🏠 Home</a>
    <a href="ask.php"     class="nav-link <?= $current==='ask'     ? 'active':'' ?>">❓ Ask</a>

    <?php if ($isLoggedIn): ?>
      <a href="profile.php" class="nav-link <?= $current==='profile' ? 'active':'' ?>">👤 Profile</a>
      <a href="logout.php"  class="btn btn-outline">Log out</a>
    <?php else: ?>
      <a href="login.php"   class="btn btn-outline <?= $current==='login'  ? 'active':'' ?>">Log in</a>
      <a href="signup.php"  class="btn btn-primary <?= $current==='signup' ? 'active':'' ?>">Sign up</a>
    <?php endif; ?>
  </div>
</nav>
<?php
}

function renderAskBar() {
?>
<div class="ask-bar">
  <div class="ask-bar-input" id="ask-bar-input">
    <span class="ask-icon">✏️</span>
    <span>Have a math question? Ask the community…</span>
  </div>
  <a href="ask.php" class="btn btn-primary" style="flex-shrink:0">Ask&nbsp;➜</a>
</div>
<?php
}

function renderShareModal() {
?>
<div class="modal-overlay" id="share-modal">
  <div class="modal-box">
    <div class="modal-title">📤 Share this question</div>
    <div class="share-link">
      <span id="share-url"><?= htmlspecialchars('https://smfe.app/q/example-question') ?></span>
      <button class="copy-link">Copy</button>
    </div>
    <div class="share-socials">
      <button class="share-soc-btn">WhatsApp</button>
      <button class="share-soc-btn">Telegram</button>
      <button class="share-soc-btn">Twitter / X</button>
      <button class="share-soc-btn">Copy Link</button>
    </div>
    <button class="modal-close">Close</button>
  </div>
</div>
<?php
}

function renderHead($title = 'SMFE', $description = '') {
  $defaultDesc = 'SMFE — Social Media For Education. Ask math questions, share solutions, and explore topics like Calculus, Algebra, Statistics, and more.';
  $metaDesc = htmlspecialchars($description ?: $defaultDesc);
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?= $metaDesc ?>">
<meta name="robots" content="index, follow">
<meta name="theme-color" content="#10b981">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= htmlspecialchars($title) ?> — SMFE">
<meta property="og:description" content="<?= $metaDesc ?>">
<meta property="og:site_name" content="SMFE">
<title><?= htmlspecialchars($title) ?> — SMFE</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
<!-- KaTeX for math rendering -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.10/dist/katex.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.10/dist/katex.min.js"
        onload="window._katexLoaded=true; if(window._onKaTeXLoad) window._onKaTeXLoad();"></script>
<?php $v = filemtime(__DIR__ . '/../style/main.css'); ?>
<link rel="stylesheet" href="style/main.css?v=<?= $v ?>">
<?php $vj = filemtime(__DIR__ . '/../js/mathkb.js'); $vm = filemtime(__DIR__ . '/../js/main.js'); ?>
<?php
}
?>
