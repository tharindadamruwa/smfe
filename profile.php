<?php
session_start();
require_once 'components/navbar.php';
require_once 'components/posts_data.php';
require_once 'components/post_card.php';

// Require login
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}

$sessionUser = $_SESSION['user'];

// Try to load fresh data from DB
$dbUser = null;
$questionCount = 0;
try {
  if (file_exists('db/config.php')) {
    require_once 'db/config.php';
    $db = tryGetDB();
    if ($db) {
      $stmt = $db->prepare('SELECT id, username, email, avatar_letter, bio, created_at FROM users WHERE id = ?');
      $stmt->execute([$sessionUser['id']]);
      $dbUser = $stmt->fetch();

      $qstmt = $db->prepare('SELECT COUNT(*) as cnt FROM posts WHERE user_id = ?');
      $qstmt->execute([$sessionUser['id']]);
      $qrow = $qstmt->fetch();
      $questionCount = (int)($qrow['cnt'] ?? 0);
    }
  }
} catch (Exception $e) {}

// Merge: prefer DB data, fall back to session
$user = [
  'username'      => $dbUser['username']      ?? $sessionUser['username'],
  'email'         => $dbUser['email']         ?? $sessionUser['email'],
  'avatar_letter' => $dbUser['avatar_letter'] ?? $sessionUser['avatar_letter'],
  'bio'           => $dbUser['bio']           ?? ($sessionUser['bio'] ?? ''),
  'joined'        => isset($dbUser['created_at'])
                       ? date('F Y', strtotime($dbUser['created_at']))
                       : 'Recently',
];

// User's own posts
$allPosts = getPosts();
$myPosts  = array_values(array_filter($allPosts, fn($p) => $p['username'] === $user['username']));
$questionCount = $questionCount ?: count($myPosts);

// Liked posts (from DB if available, else demo slice)
$likedPosts = array_slice($allPosts, 0, 4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php renderHead('Profile — @' . $user['username']) ?>
  <style>body { padding-bottom: 24px; }</style>
</head>
<body class="no-askbar">

<?php renderNavbar('profile'); ?>

<!-- Profile hero -->
<div class="profile-hero">
  <div class="profile-avatar-wrap">
    <div class="profile-avatar"><?= htmlspecialchars($user['avatar_letter']) ?></div>
  </div>
  <div class="profile-name">@<?= htmlspecialchars($user['username']) ?></div>
  <div class="profile-handle" style="font-size:.85rem;opacity:.65;margin-top:-6px;"><?= htmlspecialchars($user['email']) ?></div>
  <?php if ($user['bio']): ?>
  <div style="max-width:360px;margin:10px auto 0;font-size:.88rem;opacity:.8;line-height:1.55;">
    <?= htmlspecialchars($user['bio']) ?>
  </div>
  <?php endif; ?>
  <div class="profile-stats" style="margin-top:18px;">
    <div class="stat-item">
      <div class="stat-num"><?= $questionCount ?></div>
      <div class="stat-label">Questions</div>
    </div>
    <div class="stat-item">
      <div class="stat-num"><?= htmlspecialchars($user['joined']) ?></div>
      <div class="stat-label">Joined</div>
    </div>
  </div>
</div>

<!-- Profile content -->
<div class="profile-content">

  <!-- Tabs -->
  <div class="profile-tabs">
    <div class="tabs-nav">
      <button class="tab-btn active" data-tab="my-questions">❓ My Questions</button>
      <button class="tab-btn" data-tab="liked">♥ Liked</button>
      <button class="tab-btn" data-tab="settings">⚙️ Settings</button>
    </div>

    <!-- My Questions -->
    <div class="tab-content active" id="tab-my-questions">
      <div class="tab-section-title">❓ My Questions <span class="tab-section-count"><?= $questionCount ?></span></div>
      <?php if (empty($myPosts)): ?>
      <div class="empty-state">
        <div class="empty-icon">❓</div>
        <div class="empty-text">You haven't asked any questions yet.</div>
        <a href="ask.php" class="btn btn-primary" style="margin-top:14px;">Ask your first question</a>
      </div>
      <?php else: ?>
      <div class="posts-feed">
        <?php foreach ($myPosts as $post): renderPost($post); endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Liked Posts -->
    <div class="tab-content" id="tab-liked">
      <div class="tab-section-title">♥ Liked Posts</div>
      <?php if (empty($likedPosts)): ?>
      <div class="empty-state">
        <div class="empty-icon">♡</div>
        <div class="empty-text">No liked posts yet.</div>
      </div>
      <?php else: ?>
      <div class="liked-grid">
        <?php foreach ($likedPosts as $lp): ?>
        <a href="index.php#post-<?= (int)$lp['id'] ?>" class="liked-card" style="display:block;">
          <div class="liked-card-user">
            <div style="width:20px;height:20px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--teal));display:flex;align-items:center;justify-content:center;color:#fff;font-size:.6rem;font-weight:700;flex-shrink:0;">
              <?= htmlspecialchars($lp['avatar_letter']) ?>
            </div>
            @<?= htmlspecialchars($lp['username']) ?>
            <span class="badge badge-green" style="margin-left:auto;"><?= htmlspecialchars($lp['tag']) ?></span>
          </div>
          <div class="liked-card-text"><?= htmlspecialchars($lp['body']) ?></div>
          <?php if (!empty($lp['math'])): ?>
          <div class="liked-card-math post-math" data-raw="<?= htmlspecialchars($lp['math']) ?>"><?= htmlspecialchars($lp['math']) ?></div>
          <?php endif; ?>
          <div style="display:flex;align-items:center;gap:10px;margin-top:10px;font-size:.75rem;color:#6b7280;">
            <span>♥ <?= (int)$lp['likes'] ?></span>
            <span>💬 <?= (int)($lp['comment_count'] ?? $lp['comments'] ?? 0) ?></span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Settings -->
    <div class="tab-content" id="tab-settings">
      <div style="max-width:440px;">

        <div id="settings-success" style="display:none;background:#ecfdf5;border:1.5px solid var(--border);border-radius:var(--radius);padding:12px 16px;margin-bottom:16px;color:var(--primary-dark);font-size:.88rem;">✅ Changes saved!</div>
        <div id="settings-error" style="display:none;background:#fef2f2;border:1.5px solid #fecaca;border-radius:var(--radius);padding:12px 16px;margin-bottom:16px;color:#dc2626;font-size:.88rem;"></div>

        <div class="form-group">
          <label class="form-label">Username</label>
          <input class="form-control" type="text" value="@<?= htmlspecialchars($user['username']) ?>" disabled style="opacity:.6;" />
        </div>

        <div class="form-group">
          <label class="form-label">Email</label>
          <input class="form-control" type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:.6;" />
        </div>

        <div class="form-group">
          <label class="form-label">Bio</label>
          <div class="question-area" style="margin-bottom:0;">
            <textarea class="question-textarea" id="bio-input" style="min-height:80px;"><?= htmlspecialchars($user['bio']) ?></textarea>
            <button type="button" class="mathkb-trigger" data-target="#bio-input" title="Math Keyboard" style="bottom:8px;right:8px;">🧮</button>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">New Password <span style="opacity:.5;font-size:.8rem;">(leave blank to keep current)</span></label>
          <input class="form-control" type="password" id="new-password" placeholder="Min. 6 characters" />
        </div>

        <div style="display:flex;gap:10px;margin-top:8px;">
          <button class="btn btn-primary" id="save-settings-btn">Save Changes</button>
          <a href="logout.php" class="btn btn-outline" style="color:#ef4444;border-color:#ef4444;">Log out</a>
        </div>

      </div>
    </div>

  </div>
</div>

<?php renderShareModal(); ?>

<script src="js/mathkb.js?v=<?= filemtime('js/mathkb.js') ?>"></script>
<script src="js/main.js?v=<?= filemtime('js/main.js') ?>"></script>
<script>
// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const target = btn.dataset.tab;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-' + target).classList.add('active');
  });
});

// Save settings
document.getElementById('save-settings-btn').addEventListener('click', async () => {
  const bio      = document.getElementById('bio-input').value.trim();
  const password = document.getElementById('new-password').value;
  const successEl = document.getElementById('settings-success');
  const errorEl   = document.getElementById('settings-error');

  successEl.style.display = 'none';
  errorEl.style.display   = 'none';

  try {
    const res  = await fetch('api/profile.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'update', bio, password }),
    });
    const data = await res.json();
    if (data.ok) {
      successEl.style.display = 'block';
      document.getElementById('new-password').value = '';
    } else {
      errorEl.textContent = '⚠️ ' + (data.error || 'Save failed.');
      errorEl.style.display = 'block';
    }
  } catch (_) {
    errorEl.textContent = '⚠️ Network error. Are you connected to the database?';
    errorEl.style.display = 'block';
  }
});
</script>
</body>
</html>
