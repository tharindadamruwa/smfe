<?php
/**
 * SMFE Post Card Component
 * renderPost($post) — renders one post card HTML
 */

function renderPost($post) {
  $id        = (int)$post['id'];
  $username  = htmlspecialchars($post['username']);
  $letter    = htmlspecialchars($post['avatar_letter']);
  $tag       = htmlspecialchars($post['tag']);
  $body      = htmlspecialchars($post['body']);
  $math      = htmlspecialchars($post['math'] ?? '');
  $imagePath = htmlspecialchars($post['image_path'] ?? '');
  $likes     = (int)($post['likes'] ?? 0);
  $comments  = $post['comments_data'] ?? [];
  $totalCmts = (int)($post['comments_total'] ?? $post['comment_count'] ?? count($comments));
  $shownCmts = count($comments);
  $remaining = max(0, $totalCmts - $shownCmts);
  $userLiked = !empty($post['user_liked']);

  // Is the current logged-in user the author of this post?
  $sessionUser = $_SESSION['user']['username'] ?? '';
  $isAuthor    = ($sessionUser !== '' && $sessionUser === ($post['username'] ?? ''));
?>
<article class="post-card" id="post-<?= $id ?>">

  <!-- Header -->
  <div class="post-header">
    <div class="post-avatar"><?= $letter ?></div>
    <div class="post-meta">
      <span class="post-username">@<?= $username ?></span>
    </div>
    <span class="post-tag"><?= $tag ?></span>
    <?php if ($isAuthor): ?>
    <button class="post-delete-btn" data-post-id="<?= $id ?>" title="Delete this question" aria-label="Delete">
      🗑️
    </button>
    <?php endif; ?>
  </div>

  <!-- Body text (plain, no math rendering) -->
  <?php if ($body): ?>
  <div class="post-body"><?= $body ?></div>
  <?php endif; ?>

  <!-- Math expression (rendered by KaTeX) -->
  <?php if ($math): ?>
  <div class="post-math" data-raw="<?= $math ?>"><?= $math ?></div>
  <?php endif; ?>

  <!-- Attached image -->
  <?php if ($imagePath): ?>
  <div class="post-image-wrap">
    <img src="<?= $imagePath ?>" alt="Post image" class="post-image" loading="lazy" />
  </div>
  <?php endif; ?>

  <!-- Actions -->
  <div class="post-actions">
    <button class="action-btn like-btn <?= $userLiked ? 'liked' : '' ?>" title="Like">
      <span class="action-icon"><?= $userLiked ? '♥' : '♡' ?></span>
      <span class="action-count"><?= $likes ?></span>
    </button>
    <button class="action-btn comment-btn" title="Comments">
      <span class="action-icon">💬</span>
      <span class="action-count"><?= $totalCmts ?></span>
    </button>
    <button class="action-btn share-btn" title="Share">
      <span class="action-icon">↗</span>
      <span>Share</span>
    </button>
    <span class="action-spacer"></span>
    <button class="action-btn" title="Save" onclick="showToast('Saved!','🔖')">
      <span class="action-icon">🔖</span>
    </button>
  </div>

  <!-- Comments -->
  <div class="comments-section">
    <div class="comments-list">
      <?php foreach ($comments as $cmt):
        $cmtMath     = htmlspecialchars($cmt['math'] ?? '');
        $cmtUsername = htmlspecialchars($cmt['username'] ?? $cmt['user'] ?? '');
        $cmtId       = (int)($cmt['id'] ?? 0);
        $cmtIsAuthor = ($sessionUser !== '' && $sessionUser === ($cmt['username'] ?? $cmt['user'] ?? ''));
      ?>
      <div class="comment-item" data-comment-id="<?= $cmtId ?>" data-comment-username="<?= $cmtUsername ?>">
        <div class="comment-avatar"><?= strtoupper(substr($cmtUsername ?: 'U', 0, 2)) ?></div>
        <div class="comment-bubble">
          <div class="comment-user-row">
            <span class="comment-user">@<?= $cmtUsername ?></span>
            <?php if ($cmtIsAuthor && $cmtId): ?>
            <span class="comment-actions">
              <button class="comment-edit-btn"   title="Edit comment">✏️</button>
              <button class="comment-delete-btn" title="Delete comment">🗑️</button>
            </span>
            <?php endif; ?>
          </div>
          <?php if (!empty($cmt['text'])): ?>
          <div class="comment-text"><?= htmlspecialchars($cmt['text']) ?></div>
          <?php endif; ?>
          <?php if ($cmtMath): ?>
          <div class="comment-math" data-raw="<?= $cmtMath ?>"><?= $cmtMath ?></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($remaining > 0): ?>
    <button class="show-more-comments"
            data-post-id="<?= $id ?>"
            data-loaded="<?= $shownCmts ?>"
            data-total="<?= $totalCmts ?>">
      ↓ Show <?= $remaining ?> more comment<?= $remaining !== 1 ? 's' : '' ?>
    </button>
    <?php endif; ?>

    <!-- Dual comment input: text + math -->
    <div class="comment-inputs">
      <div class="comment-text-row">
        <input type="text"
               class="comment-text-input"
               placeholder="Write a comment…"
               id="comment-text-<?= $id ?>" />
      </div>
      <div class="comment-math-row">
        <input type="text"
               class="comment-math-input"
               placeholder="Math expression (optional)…"
               id="comment-math-<?= $id ?>" />
        <button class="mathkb-trigger comment-kb-btn" data-target="#comment-math-<?= $id ?>" title="Math keyboard">🧮</button>
      </div>
      <div class="comment-math-preview math-preview-box" id="comment-math-preview-<?= $id ?>" data-math-preview-for="comment-math-<?= $id ?>" style="display:none;"></div>
      <div class="comment-send-row">
        <button class="comment-send">Send ➤</button>
      </div>
    </div>
  </div>

</article>
<?php
}
?>
