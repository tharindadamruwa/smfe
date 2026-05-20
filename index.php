<?php
session_start();
require_once 'components/navbar.php';
require_once 'components/posts_data.php';
require_once 'components/post_card.php';

$posts = getPosts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php renderHead('Home', 'SMFE — Ask math questions, get real answers from the community. Explore Calculus, Algebra, Statistics, and more.') ?>
</head>
<body>

<?php renderNavbar('home'); ?>

<div class="container">

  <!-- Hero welcome strip -->
  <div style="
    background: linear-gradient(135deg, var(--primary), var(--teal));
    border-radius: var(--radius-lg);
    padding: 28px 28px 24px;
    margin-bottom: 28px;
    color: #fff;
    position: relative;
    overflow: hidden;
  ">
    <div style="position:relative;z-index:1">
      <div style="font-family:var(--font-display);font-size:1.5rem;font-weight:800;margin-bottom:6px;">
        Welcome to SMFE 👋
      </div>
      <div style="opacity:.85;font-size:.93rem;max-width:480px;line-height:1.55;">
        The community where math questions get real answers. Ask, answer, and explore together.
      </div>
      <a href="ask.php" class="btn" style="margin-top:16px;background:rgba(255,255,255,.2);color:#fff;border:1.5px solid rgba(255,255,255,.4);backdrop-filter:blur(6px);">
        ✏️ Ask a Question
      </a>
    </div>
    <div style="position:absolute;right:-30px;top:-30px;width:150px;height:150px;border-radius:50%;background:rgba(255,255,255,.08);"></div>
    <div style="position:absolute;right:60px;bottom:-40px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.06);"></div>
  </div>

  <!-- Filter chips -->
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:22px;overflow-x:auto;padding-bottom:4px;">
    <?php foreach(['All','Calculus','Algebra','Statistics','Linear Algebra','Probability','Geometry','Discrete Math','Topology'] as $i => $t): ?>
    <button class="tag-chip <?= $i===0?'selected':'' ?>" onclick="filterPosts(this,'<?= $t ?>')">
      <?= $t ?>
    </button>
    <?php endforeach; ?>
  </div>

  <!-- Section header -->
  <div class="section-header">
    <span class="section-title">Popular Questions</span>
    <span class="section-badge">🔥 Trending</span>
  </div>

  <!-- Posts feed -->
  <div class="posts-feed" id="posts-feed">
    <?php foreach ($posts as $post): ?>
      <?php renderPost($post); ?>
    <?php endforeach; ?>
  </div>

</div>

<?php renderAskBar(); ?>
<?php renderShareModal(); ?>

<script src="js/mathkb.js?v=<?= filemtime('js/mathkb.js') ?>"></script>
<script src="js/main.js?v=<?= filemtime('js/main.js') ?>"></script>
<script>
function filterPosts(btn, tag) {
  document.querySelectorAll('.tag-chip').forEach(c => c.classList.remove('selected'));
  btn.classList.add('selected');
  document.querySelectorAll('.post-card').forEach(card => {
    if (tag === 'All') {
      card.style.display = '';
    } else {
      const cardTag = card.querySelector('.post-tag')?.textContent?.trim();
      card.style.display = (cardTag === tag) ? '' : 'none';
    }
  });
}
</script>
</body>
</html>
