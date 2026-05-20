/**
 * SMFE Main JS
 */

// Current logged-in username (set via navbar data attribute)
const CURRENT_USER = document.getElementById('navbar')?.dataset.username || '';

// ─── NAVBAR TOGGLE ─────────────────────────────────────────────────────────────
const navToggle = document.getElementById('nav-toggle');
const navLinks  = document.getElementById('nav-links');

if (navToggle && navLinks) {
  navToggle.addEventListener('click', () => {
    navToggle.classList.toggle('open');
    navLinks.classList.toggle('open');
  });
  document.addEventListener('click', (e) => {
    if (!navToggle.contains(e.target) && !navLinks.contains(e.target)) {
      navToggle.classList.remove('open');
      navLinks.classList.remove('open');
    }
  });
}

// ─── TOAST ──────────────────────────────────────────────────────────────────────
function showToast(msg, icon='✓') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.innerHTML = `<span>${icon}</span><span>${msg}</span>`;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 3200);
}

// ─── SEARCH ─────────────────────────────────────────────────────────────────────
(function initSearch() {
  const input    = document.getElementById('navbar-search-input');
  const dropdown = document.getElementById('search-dropdown');
  if (!input) return;

  let debounce;

  input.addEventListener('input', () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => doSearch(input.value.trim()), 180);
  });

  input.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') { input.value = ''; doSearch(''); input.blur(); }
  });

  document.addEventListener('click', (e) => {
    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.remove('open');
    }
  });

  function doSearch(q) {
    const cards = document.querySelectorAll('.post-card');
    if (!cards.length) { dropdown.classList.remove('open'); return; }

    if (!q) {
      cards.forEach(c => c.style.display = '');
      hideEmpty();
      dropdown.classList.remove('open');
      return;
    }

    const byUser  = q.startsWith('@');
    const term    = byUser ? q.slice(1).toLowerCase() : q.toLowerCase();
    let   visible = 0;

    cards.forEach(card => {
      let match = false;
      if (byUser) {
        const uEl = card.querySelector('.post-username');
        const un  = (uEl?.textContent || '').replace('@','').toLowerCase();
        match = un.includes(term);
      } else {
        const tagEl  = card.querySelector('.post-tag');
        const bodyEl = card.querySelector('.post-body');
        const tag    = (tagEl?.textContent  || '').toLowerCase();
        const body   = (bodyEl?.textContent || '').toLowerCase();
        match = tag.includes(term) || body.includes(term);
      }
      card.style.display = match ? '' : 'none';
      if (match) visible++;
    });

    // Show/hide empty message
    showEmpty(visible === 0, byUser ? `No posts by @${term}` : `No posts matching "${q}"`);

    // Dropdown summary
    dropdown.innerHTML = visible
      ? `<div class="search-dd-info">${visible} result${visible>1?'s':''} ${byUser?'by @'+term:'for "'+q+'"'} — scroll to see</div>`
      : `<div class="search-dd-info search-dd-empty">No results found</div>`;
    dropdown.classList.add('open');
  }

  function showEmpty(show, msg) {
    let el = document.getElementById('search-empty-state');
    if (!el) {
      el = document.createElement('div');
      el.id = 'search-empty-state';
      el.className = 'empty-state';
      el.style.display = 'none';
      const feed = document.querySelector('.posts-feed') || document.querySelector('.feed-section');
      if (feed) feed.appendChild(el);
    }
    el.innerHTML = `<div class="empty-icon">🔍</div><div class="empty-text">${msg}</div>`;
    el.style.display = show ? 'block' : 'none';
  }

  function hideEmpty() {
    const el = document.getElementById('search-empty-state');
    if (el) el.style.display = 'none';
  }
})();

// ─── LIKE BUTTON (AJAX) ─────────────────────────────────────────────────────────
document.addEventListener('click', async (e) => {
  const likeBtn = e.target.closest('.like-btn');
  if (!likeBtn) return;

  const postCard = likeBtn.closest('.post-card');
  const postId   = postCard ? postCard.id.replace('post-', '') : null;
  const countEl  = likeBtn.querySelector('.action-count');
  const iconEl   = likeBtn.querySelector('.action-icon');

  const wasLiked = likeBtn.classList.contains('liked');
  likeBtn.classList.toggle('liked');
  if (iconEl)  iconEl.textContent  = wasLiked ? '♡' : '♥';
  if (countEl) {
    const n = parseInt(countEl.textContent) || 0;
    countEl.textContent = wasLiked ? Math.max(0, n - 1) : n + 1;
  }

  if (!postId) return;

  try {
    const res  = await fetch('api/likes.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ post_id: parseInt(postId) }),
    });
    const data = await res.json();
    if (data.ok) {
      if (countEl) countEl.textContent = data.count;
      likeBtn.classList.toggle('liked', data.liked);
      if (iconEl) iconEl.textContent = data.liked ? '♥' : '♡';
    } else if (data.error === 'Not authenticated.') {
      likeBtn.classList.toggle('liked', wasLiked);
      if (iconEl)  iconEl.textContent  = wasLiked ? '♥' : '♡';
      if (countEl) {
        const n = parseInt(countEl.textContent) || 0;
        countEl.textContent = wasLiked ? n + 1 : Math.max(0, n - 1);
      }
      showToast('Please log in to like posts.', '🔒');
    }
  } catch (_) {}
});

// ─── COMMENT TOGGLE ──────────────────────────────────────────────────────────────
document.addEventListener('click', (e) => {
  const commentBtn = e.target.closest('.comment-btn');
  if (!commentBtn) return;
  const postCard = commentBtn.closest('.post-card');
  if (!postCard) return;
  const section = postCard.querySelector('.comments-section');
  if (!section) return;
  section.classList.toggle('open');
  if (section.classList.contains('open')) {
    const inp = section.querySelector('.comment-text-input');
    if (inp) inp.focus();
  }
});

// Comment math preview is handled by mathkb.js (uses data-math-preview-for attribute)

// ─── ADD COMMENT (AJAX) ─────────────────────────────────────────────────────────
document.addEventListener('click', async (e) => {
  const sendBtn = e.target.closest('.comment-send');
  if (!sendBtn) return;

  const inputs  = sendBtn.closest('.comment-inputs');
  if (!inputs) return;
  const textInp = inputs.querySelector('.comment-text-input');
  const mathInp = inputs.querySelector('.comment-math-input');
  const text    = textInp ? textInp.value.trim() : '';
  const math    = mathInp ? mathInp.value.trim() : '';

  if (!text && !math) { showToast('Write something first.', '⚠️'); return; }

  const section  = sendBtn.closest('.comments-section');
  const postCard = section.closest('.post-card');
  const postId   = postCard ? parseInt(postCard.id.replace('post-', '')) : null;

  // Optimistic add (no buttons yet — we need the server comment ID first)
  const list = section.querySelector('.comments-list');
  const div  = document.createElement('div');
  div.className = 'comment-item';
  div.innerHTML = `
    <div class="comment-avatar">Me</div>
    <div class="comment-bubble">
      <div class="comment-user-row"><span class="comment-user">You</span></div>
      ${text ? `<div class="comment-text">${escHtml(text)}</div>` : ''}
      ${math ? `<div class="comment-math" data-raw="${escHtml(math)}">${escHtml(math)}</div>` : ''}
    </div>`;
  list.appendChild(div);

  // Render math in the new comment optimistically
  const mathEl = div.querySelector('.comment-math');
  if (mathEl) renderMathEl(mathEl, math);

  // Clear inputs and hide previews
  if (textInp) textInp.value = '';
  if (mathInp) {
    mathInp.value = '';
    mathInp.dispatchEvent(new Event('input', { bubbles: true }));
  }

  // Update comment count badge
  const cntEl = postCard?.querySelector('.comment-btn .action-count');
  if (cntEl) cntEl.textContent = (parseInt(cntEl.textContent) || 0) + 1;

  if (!postId) return;

  try {
    const res  = await fetch('api/comments.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'add', post_id: postId, text, math }),
    });
    const data = await res.json();
    if (!data.ok && data.error === 'Not authenticated.') {
      div.remove();
      if (cntEl) cntEl.textContent = Math.max(0, (parseInt(cntEl.textContent) || 0) - 1);
      showToast('Please log in to comment.', '🔒');
    } else if (data.ok) {
      // Update avatar/username and attach comment ID + action buttons
      div.dataset.commentId       = data.comment_id;
      div.dataset.commentUsername = data.username;
      div.querySelector('.comment-user').textContent = '@' + data.username;
      div.querySelector('.comment-avatar').textContent =
        (data.avatar_letter || data.username.substring(0,2)).toUpperCase().substring(0,2);
      if (data.comment_id) {
        const userRow = div.querySelector('.comment-user-row');
        if (userRow) {
          const actions = document.createElement('span');
          actions.className = 'comment-actions';
          actions.innerHTML = `<button class="comment-edit-btn" title="Edit comment">✏️</button>
            <button class="comment-delete-btn" title="Delete comment">🗑️</button>`;
          userRow.appendChild(actions);
        }
      }
    }
  } catch (_) {}
});

function escHtml(s) {
  return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

/**
 * Build inner HTML for a .comment-item.
 * Shows edit/delete buttons when the comment belongs to the current user.
 */
function buildCommentHTML(cmt) {
  const username   = cmt.username || cmt.user || 'user';
  const avatarText = (cmt.avatar_letter || username.substring(0, 2)).toUpperCase().substring(0, 2);
  const commentId  = cmt.id || cmt.comment_id || 0;
  const isOwn      = CURRENT_USER && CURRENT_USER === username;
  const actions    = (isOwn && commentId)
    ? `<span class="comment-actions">
         <button class="comment-edit-btn"   title="Edit comment">✏️</button>
         <button class="comment-delete-btn" title="Delete comment">🗑️</button>
       </span>`
    : '';
  return `
    <div class="comment-avatar">${escHtml(avatarText)}</div>
    <div class="comment-bubble">
      <div class="comment-user-row">
        <span class="comment-user">@${escHtml(username)}</span>${actions}
      </div>
      ${cmt.text ? `<div class="comment-text">${escHtml(cmt.text)}</div>` : ''}
      ${cmt.math ? `<div class="comment-math" data-raw="${escHtml(cmt.math)}">${escHtml(cmt.math)}</div>` : ''}
    </div>`;
}

// ─── DELETE POST ─────────────────────────────────────────────────────────────
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.post-delete-btn');
  if (!btn) return;

  const postId  = parseInt(btn.dataset.postId);
  const postCard = btn.closest('.post-card');

  if (!confirm('Delete this question? This cannot be undone.')) return;

  btn.disabled = true;
  btn.textContent = '…';

  try {
    const res  = await fetch('api/posts.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete', post_id: postId }),
    });
    const data = await res.json();
    if (data.ok) {
      // Animate card out then remove from DOM
      postCard.style.transition = 'opacity .3s ease, transform .3s ease';
      postCard.style.opacity    = '0';
      postCard.style.transform  = 'scale(.97)';
      setTimeout(() => postCard.remove(), 320);
      showToast('Question deleted.', '🗑️');
    } else {
      showToast(data.error || 'Could not delete.', '⚠️');
      btn.disabled = false;
      btn.textContent = '🗑️';
    }
  } catch (_) {
    showToast('Network error. Try again.', '⚠️');
    btn.disabled = false;
    btn.textContent = '🗑️';
  }
});

// ─── COMMENT DELETE ──────────────────────────────────────────────────────────
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.comment-delete-btn');
  if (!btn) return;

  const item      = btn.closest('.comment-item');
  const commentId = parseInt(item?.dataset.commentId);
  if (!commentId) return;

  if (!confirm('Delete this comment?')) return;

  btn.disabled = true;

  try {
    const res  = await fetch('api/comments.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete', comment_id: commentId }),
    });
    const data = await res.json();
    if (data.ok) {
      const postCard = item.closest('.post-card');
      item.style.transition = 'opacity .2s ease';
      item.style.opacity    = '0';
      setTimeout(() => {
        const cntEl = postCard?.querySelector('.comment-btn .action-count');
        if (cntEl) cntEl.textContent = Math.max(0, (parseInt(cntEl.textContent) || 0) - 1);
        item.remove();
      }, 220);
      showToast('Comment deleted.', '🗑️');
    } else {
      showToast(data.error || 'Could not delete.', '⚠️');
      btn.disabled = false;
    }
  } catch (_) {
    showToast('Network error.', '⚠️');
    btn.disabled = false;
  }
});

// ─── COMMENT EDIT ─────────────────────────────────────────────────────────────
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.comment-edit-btn');
  if (!btn) return;

  const item      = btn.closest('.comment-item');
  const bubble    = item?.querySelector('.comment-bubble');
  if (!bubble || bubble.dataset.editing) return;
  bubble.dataset.editing = '1';

  const commentId = parseInt(item.dataset.commentId);
  const textEl    = bubble.querySelector('.comment-text');
  const mathEl    = bubble.querySelector('.comment-math');
  const oldText   = textEl?.textContent?.trim() || '';
  const oldMath   = mathEl?.dataset.raw?.trim()  || '';

  if (textEl) textEl.style.display = 'none';
  if (mathEl) mathEl.style.display = 'none';

  const editor = document.createElement('div');
  editor.className = 'comment-inline-editor';
  editor.innerHTML = `
    <input class="comment-edit-text" type="text" value="${escHtml(oldText)}" placeholder="Comment text…" />
    <input class="comment-edit-math" type="text" value="${escHtml(oldMath)}" placeholder="Math expression (optional)…" />
    <div class="comment-edit-actions">
      <button class="comment-edit-save">Save</button>
      <button class="comment-edit-cancel">Cancel</button>
    </div>`;
  bubble.appendChild(editor);
  editor.querySelector('.comment-edit-text').focus();

  editor.querySelector('.comment-edit-cancel').addEventListener('click', () => {
    if (textEl) textEl.style.display = '';
    if (mathEl) mathEl.style.display = '';
    editor.remove();
    delete bubble.dataset.editing;
  });

  editor.querySelector('.comment-edit-save').addEventListener('click', async () => {
    const newText  = editor.querySelector('.comment-edit-text').value.trim();
    const newMath  = editor.querySelector('.comment-edit-math').value.trim();
    if (!newText && !newMath) { showToast('Comment cannot be empty.', '⚠️'); return; }

    const saveBtn  = editor.querySelector('.comment-edit-save');
    saveBtn.disabled    = true;
    saveBtn.textContent = '…';

    try {
      const res  = await fetch('api/comments.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update', comment_id: commentId, text: newText, math: newMath }),
      });
      const data = await res.json();
      if (data.ok) {
        if (textEl) { textEl.textContent = newText; textEl.style.display = newText ? '' : 'none'; }
        if (mathEl) {
          if (newMath) {
            mathEl.dataset.raw = newMath;
            delete mathEl.dataset.katexRendered;
            mathEl.textContent = newMath;
            mathEl.style.display = '';
            renderMathEl(mathEl, newMath);
          } else {
            mathEl.style.display = 'none';
          }
        }
        editor.remove();
        delete bubble.dataset.editing;
        showToast('Comment updated.', '✏️');
      } else {
        showToast(data.error || 'Could not update.', '⚠️');
        saveBtn.disabled    = false;
        saveBtn.textContent = 'Save';
      }
    } catch (_) {
      showToast('Network error.', '⚠️');
      saveBtn.disabled    = false;
      saveBtn.textContent = 'Save';
    }
  });
});

// ─── SHOW MORE COMMENTS ───────────────────────────────────────────────────────
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.show-more-comments');
  if (!btn || btn.dataset.loading) return;

  const postId  = parseInt(btn.dataset.postId);
  const loaded  = parseInt(btn.dataset.loaded);
  const total   = parseInt(btn.dataset.total);
  const section = btn.closest('.comments-section');
  const list    = section.querySelector('.comments-list');

  btn.dataset.loading = '1';
  btn.textContent = '…Loading';
  btn.disabled = true;

  try {
    const res  = await fetch(`api/comments.php?post_id=${postId}`);
    const data = await res.json();
    if (!data.ok) throw new Error(data.error || 'Failed');

    const newComments = data.comments.slice(loaded);
    newComments.forEach(cmt => {
      const div = document.createElement('div');
      div.className = 'comment-item comment-item--new';
      div.dataset.commentId       = cmt.id || '';
      div.dataset.commentUsername = cmt.username || '';
      div.innerHTML = buildCommentHTML(cmt);
      list.appendChild(div);
      const mathEl = div.querySelector('.comment-math');
      if (mathEl) renderMathEl(mathEl, cmt.math);
    });

    // Update button: if everything is now loaded, replace with a "show less" toggle
    const nowLoaded = loaded + newComments.length;
    if (nowLoaded >= total) {
      btn.outerHTML = `<button class="show-less-comments show-more-comments-toggle">↑ Show less</button>`;
      // Wire the new show-less button
    } else {
      const left = total - nowLoaded;
      btn.textContent = `↓ Show ${left} more comment${left !== 1 ? 's' : ''}`;
      btn.dataset.loaded = nowLoaded;
      delete btn.dataset.loading;
      btn.disabled = false;
    }
  } catch (_) {
    btn.textContent = '↓ Show more comments';
    delete btn.dataset.loading;
    btn.disabled = false;
  }
});

// ─── SHOW LESS COMMENTS ───────────────────────────────────────────────────────
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.show-less-comments');
  if (!btn) return;

  const section = btn.closest('.comments-section');
  const list    = section.querySelector('.comments-list');
  const items   = list.querySelectorAll('.comment-item');
  const postCard = section.closest('.post-card');
  const total   = parseInt(postCard?.querySelector('.comment-btn .action-count')?.textContent) || items.length;

  // Remove all beyond first 3
  items.forEach((item, i) => { if (i >= 3) item.remove(); });

  const left = total - 3;
  if (left > 0) {
    btn.outerHTML = `<button class="show-more-comments show-more-comments-toggle"
      data-post-id="${postCard?.id.replace('post-','')}"
      data-loaded="3"
      data-total="${total}">↓ Show ${left} more comment${left !== 1 ? 's' : ''}</button>`;
  } else {
    btn.remove();
  }
});

// ─── SHARE MODAL ──────────────────────────────────────────────────────────────
const shareModal = document.getElementById('share-modal');

document.addEventListener('click', (e) => {
  const shareBtn = e.target.closest('.share-btn');
  if (!shareBtn || !shareModal) return;
  shareModal.classList.add('open');
});

if (shareModal) {
  shareModal.addEventListener('click', (e) => {
    if (e.target === shareModal) shareModal.classList.remove('open');
  });
  const closeBtn = shareModal.querySelector('.modal-close');
  if (closeBtn) closeBtn.addEventListener('click', () => shareModal.classList.remove('open'));

  const copyBtn = shareModal.querySelector('.copy-link');
  if (copyBtn) {
    copyBtn.addEventListener('click', () => {
      navigator.clipboard?.writeText(window.location.href).catch(()=>{});
      showToast('Link copied!', '🔗');
      shareModal.classList.remove('open');
    });
  }
}

// ─── ASK BAR REDIRECT ─────────────────────────────────────────────────────────
const askBarInput = document.getElementById('ask-bar-input');
if (askBarInput) {
  askBarInput.addEventListener('click', () => {
    window.location.href = 'ask.php';
  });
}

// ─── POST CARD STAGGER ────────────────────────────────────────────────────────
document.querySelectorAll('.post-card').forEach((card, i) => {
  card.style.animationDelay = `${i * 0.06}s`;
});

// ─── MATH RENDERING ───────────────────────────────────────────────────────────

/**
 * Render a single element's text as KaTeX math (display mode).
 */
function renderMathEl(el, rawText) {
  if (typeof katex === 'undefined') return;
  const src = rawText || el.dataset.raw || el.textContent || '';
  if (!src.trim()) return;
  try {
    const latex = typeof MathKB !== 'undefined' ? MathKB.textToLatex(src) : src;
    katex.render(latex, el, {
      throwOnError: false,
      displayMode: true,
      output: 'html',
      trust: false,
    });
    el.dataset.katexRendered = '1';
  } catch (_) {}
}

/**
 * Render all .post-math and .comment-math elements that haven't been rendered yet.
 */
function renderAllMath() {
  if (typeof katex === 'undefined') return;
  document.querySelectorAll('.post-math:not([data-katex-rendered]), .comment-math:not([data-katex-rendered])').forEach(el => {
    renderMathEl(el);
  });
}

// Wait for KaTeX to be ready
function onKaTeXReady(callback) {
  if (typeof katex !== 'undefined') {
    callback();
    return;
  }
  // KaTeX loaded with onload attribute in renderHead()
  window._onKaTeXLoad = () => {
    callback();
    // Also re-call after a short delay in case new elements appear
    setTimeout(callback, 200);
  };
  // Fallback polling (in case onload didn't fire)
  let tries = 0;
  const poll = setInterval(() => {
    if (typeof katex !== 'undefined') {
      clearInterval(poll);
      callback();
    }
    if (++tries > 50) clearInterval(poll);
  }, 100);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => onKaTeXReady(renderAllMath));
} else {
  onKaTeXReady(renderAllMath);
}
