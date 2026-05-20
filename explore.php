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
  <?php renderHead('Explore', 'Explore math questions on SMFE. Browse topics like Calculus, Algebra, Statistics, Geometry, Probability, and more.') ?>
</head>
<body>

<?php renderNavbar('explore'); ?>

<div class="container">

  <!-- Search bar -->
  <div style="position:relative;margin-bottom:24px;">
    <span style="position:absolute;left:16px;top:50%;transform:translateY(-50%);font-size:1.1rem;">🔍</span>
    <input
      type="text"
      id="explore-search"
      class="form-control"
      placeholder="Search questions, topics, users…"
      style="padding-left:46px;border-radius:99px;font-size:1rem;padding-top:13px;padding-bottom:13px;"
      oninput="liveSearch(this.value)"
    />
  </div>

  <!-- Topics grid -->
  <div class="section-header">
    <span class="section-title">Browse Topics</span>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-bottom:30px;">
    <?php
    $topics = [
      ['Calculus','∫','#10b981'],
      ['Algebra','x²','#0d9488'],
      ['Statistics','σ','#06b6d4'],
      ['Trigonometry','sin','#10b981'],
      ['Linear Algebra','[A]','#0d9488'],
      ['Probability','P','#06b6d4'],
      ['Geometry','△','#10b981'],
      ['Discrete Math','⟹','#0d9488'],
      ['Number Theory','ℕ','#06b6d4'],
      ['Topology','∞','#10b981'],
    ];
    foreach ($topics as [$name, $sym, $col]):
    ?>
    <button onclick="filterByTopic('<?= $name ?>')" style="background:linear-gradient(135deg,<?= $col ?>18,<?= $col ?>08);border:1.5px solid <?= $col ?>40;border-radius:var(--radius);padding:18px 14px;cursor:pointer;text-align:left;transition:var(--transition);"
      onmouseover="this.style.borderColor='<?= $col ?>'"
      onmouseout="this.style.borderColor='<?= $col ?>40'">
      <div style="font-size:1.5rem;font-weight:800;color:<?= $col ?>;font-family:'Courier New',monospace;margin-bottom:6px;"><?= $sym ?></div>
      <div style="font-size:.85rem;font-weight:600;color:var(--text-dark);"><?= $name ?></div>
    </button>
    <?php endforeach; ?>
  </div>

  <!-- Results -->
  <div class="section-header">
    <span class="section-title" id="explore-section-title">All Questions</span>
    <span class="section-badge" id="explore-count"><?= count($posts) ?> questions</span>
  </div>

  <div class="posts-feed" id="explore-feed">
    <?php foreach ($posts as $post): renderPost($post); endforeach; ?>
  </div>

  <div class="empty-state" id="explore-empty" style="display:none;">
    <div class="empty-icon">🔍</div>
    <div class="empty-text">No questions found. Try a different search.</div>
  </div>

</div>

<?php renderAskBar(); ?>
<?php renderShareModal(); ?>

<script src="js/mathkb.js?v=<?= filemtime('js/mathkb.js') ?>"></script>
<script src="js/main.js?v=<?= filemtime('js/main.js') ?>"></script>
<script>
function filterByTopic(topic) {
  document.getElementById('explore-section-title').textContent = topic;
  const cards = document.querySelectorAll('#explore-feed .post-card');
  let shown = 0;
  cards.forEach(card => {
    const tag = card.querySelector('.post-tag')?.textContent?.trim();
    if (tag === topic) { card.style.display = ''; shown++; }
    else card.style.display = 'none';
  });
  document.getElementById('explore-count').textContent = shown + ' questions';
  document.getElementById('explore-empty').style.display = shown ? 'none' : 'block';
  window.scrollTo({ top: document.getElementById('explore-feed').offsetTop - 80, behavior: 'smooth' });
}

function liveSearch(q) {
  q = q.toLowerCase().trim();
  const cards = document.querySelectorAll('#explore-feed .post-card');
  let shown = 0;
  cards.forEach(card => {
    const text = card.textContent.toLowerCase();
    if (!q || text.includes(q)) { card.style.display = ''; shown++; }
    else card.style.display = 'none';
  });
  document.getElementById('explore-section-title').textContent = q ? 'Results for "' + q + '"' : 'All Questions';
  document.getElementById('explore-count').textContent = shown + ' questions';
  document.getElementById('explore-empty').style.display = shown ? 'none' : 'block';
}
</script>
</body>
</html>
