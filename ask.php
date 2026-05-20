<?php
session_start();
require_once 'db/config.php';
restoreSessionFromCookie();
if (!isset($_SESSION['user'])) {
  header('Location: signup.php');
  exit;
}
require_once 'components/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php renderHead('Ask a Question', 'Ask a math question on SMFE. Get help with Calculus, Algebra, Statistics, Geometry, and more from the community.') ?>
</head>
<body>

<?php renderNavbar('ask'); ?>

<div class="ask-wrapper">

  <div id="ask-success" style="display:none;background:#ecfdf5;border:1.5px solid var(--border);border-radius:var(--radius);padding:16px 20px;margin-bottom:20px;color:var(--primary-dark);align-items:center;gap:12px;">
    <span style="font-size:1.3rem;">✅</span>
    <div>
      <strong>Question posted!</strong><br>
      <span style="font-size:.88rem;">The community will help you soon.</span>
    </div>
    <a href="index.php" class="btn btn-primary" style="margin-left:auto;">Go Home</a>
  </div>
  <div id="ask-error" style="display:none;background:#fef2f2;border:1.5px solid #fecaca;border-radius:var(--radius);padding:12px 16px;margin-bottom:16px;color:#dc2626;font-size:.88rem;"></div>

  <div class="ask-card">

    <div class="ask-card-title">
      <span>✏️</span> Ask the Community
    </div>

    <form id="ask-form" novalidate>

      <!-- ── Dual input: Text + Math ──────────────────────────── -->
      <div class="dual-input-wrap">

        <!-- Text area (plain, no math rendering) -->
        <div class="dual-input-section">
          <label class="dual-input-label">
            <span class="dual-input-icon">💬</span> Your Question <span class="dual-input-sub">(plain text)</span>
          </label>
          <textarea
            class="question-textarea"
            id="question-input"
            name="question"
            placeholder="Describe your math question clearly… e.g. How do I solve this integral?"
            required
          ></textarea>
        </div>

        <div class="dual-input-divider">
          <span>+</span>
        </div>

        <!-- Math area (rendered with KaTeX) -->
        <div class="dual-input-section">
          <label class="dual-input-label">
            <span class="dual-input-icon">🧮</span> Math Expression <span class="dual-input-sub">(optional, renders as math)</span>
          </label>
          <div class="question-area" style="margin-bottom:0;">
            <textarea
              class="question-textarea"
              id="math-input"
              name="math"
              placeholder="Type or insert a math expression… e.g. ∫[0 to π] sin(x) dx"
            ></textarea>
            <button type="button" class="mathkb-trigger" data-target="#math-input" title="Open Math Keyboard">🧮</button>
          </div>
          <!-- KaTeX live preview auto-attached by mathkb.js below math-input -->
        </div>

      </div>

      <!-- Topic tags -->
      <div style="margin-bottom:16px;">
        <label class="form-label">Topic / Tag</label>
        <div class="ask-tag-row" id="tag-row">
          <?php foreach(['Calculus','Algebra','Statistics','Trigonometry','Linear Algebra','Probability','Geometry','Discrete Math','Number Theory','Complex Analysis','Differential Equations','Real Analysis','Abstract Algebra','Topology','Other'] as $t): ?>
          <button type="button" class="tag-chip" onclick="selectTag(this,'<?= $t ?>')"><?= $t ?></button>
          <?php endforeach; ?>
        </div>
        <input type="hidden" name="tag" id="selected-tag" value="" />
      </div>

      <!-- Image upload -->
      <label class="form-label">Attach Images (optional)</label>
      <div class="img-upload-area" id="drop-zone" onclick="document.getElementById('img-input').click()">
        <div class="upload-icon">📷</div>
        <div class="upload-text">
          <strong>Click to upload</strong> or drag &amp; drop<br>
          PNG, JPG, GIF up to 5MB each
        </div>
      </div>
      <input type="file" id="img-input" name="images[]" multiple accept="image/*" />
      <div class="uploaded-previews" id="img-previews"></div>

      <!-- Actions -->
      <div class="ask-actions">
        <a href="index.php" class="btn btn-ghost">Cancel</a>
        <button type="submit" id="ask-submit-btn" class="btn btn-primary" style="padding:11px 28px;">
          Post Question ➜
        </button>
      </div>

    </form>
  </div>

  <div style="background:#fff;border:1.5px solid var(--border-subtle);border-radius:var(--radius);padding:20px 22px;margin-top:18px;">
    <div style="font-family:var(--font-display);font-weight:700;font-size:.95rem;color:var(--text-dark);margin-bottom:12px;">💡 Tips for a great question</div>
    <ul style="list-style:none;display:flex;flex-direction:column;gap:8px;">
      <li style="font-size:.85rem;color:#6b7280;display:flex;gap:8px;"><span style="color:var(--primary);">✓</span> Use the top box for your question in plain words</li>
      <li style="font-size:.85rem;color:#6b7280;display:flex;gap:8px;"><span style="color:var(--primary);">✓</span> Use the Math box (🧮) for equations — they'll render beautifully</li>
      <li style="font-size:.85rem;color:#6b7280;display:flex;gap:8px;"><span style="color:var(--primary);">✓</span> Tag the correct topic for faster answers</li>
      <li style="font-size:.85rem;color:#6b7280;display:flex;gap:8px;"><span style="color:var(--primary);">✓</span> Attach images of handwritten work if needed</li>
    </ul>
  </div>

</div>

<script src="js/mathkb.js?v=<?= filemtime('js/mathkb.js') ?>"></script>
<script src="js/main.js?v=<?= filemtime('js/main.js') ?>"></script>
<script>
function selectTag(btn, tag) {
  document.querySelectorAll('#tag-row .tag-chip').forEach(c => c.classList.remove('selected'));
  btn.classList.add('selected');
  document.getElementById('selected-tag').value = tag;
}

const imgInput   = document.getElementById('img-input');
const previewBox = document.getElementById('img-previews');
const dropZone   = document.getElementById('drop-zone');
let selectedFiles = [];

function addFiles(files) {
  Array.from(files).forEach(file => {
    if (!file.type.startsWith('image/')) return;
    selectedFiles.push(file);
    const reader = new FileReader();
    reader.onload = (e) => {
      const div = document.createElement('div');
      div.className = 'preview-item';
      const idx = selectedFiles.length - 1;
      div.innerHTML = `
        <img src="${e.target.result}" alt="preview" />
        <button type="button" class="preview-remove" data-idx="${idx}" title="Remove">✕</button>
      `;
      div.querySelector('.preview-remove').addEventListener('click', function() {
        selectedFiles.splice(+this.dataset.idx, 1);
        div.remove();
      });
      previewBox.appendChild(div);
    };
    reader.readAsDataURL(file);
  });
}

imgInput.addEventListener('change', () => addFiles(imgInput.files));
dropZone.addEventListener('dragover',  (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
dropZone.addEventListener('drop', (e) => {
  e.preventDefault();
  dropZone.classList.remove('dragover');
  addFiles(e.dataTransfer.files);
});

document.getElementById('ask-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const body = document.getElementById('question-input').value.trim();
  const math = document.getElementById('math-input').value.trim();
  const tag  = document.getElementById('selected-tag').value;
  const btn  = document.getElementById('ask-submit-btn');
  const errEl = document.getElementById('ask-error');

  errEl.style.display = 'none';

  if (!body) {
    showToast('Please enter your question first.', '⚠️');
    document.getElementById('question-input').focus();
    return;
  }

  btn.textContent = 'Posting…';
  btn.disabled = true;

  try {
    // Use FormData so image files can be sent along with text fields
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('body', body);
    if (math) formData.append('math', math);
    formData.append('tag', tag || 'Other');
    selectedFiles.forEach(file => formData.append('images[]', file));

    const res  = await fetch('api/posts.php', {
      method: 'POST',
      body: formData,
    });
    const data = await res.json();
    if (data.ok) {
      const successEl = document.getElementById('ask-success');
      successEl.style.display = 'flex';
      document.getElementById('ask-form').style.display = 'none';
    } else {
      errEl.textContent = '⚠️ ' + (data.error || 'Failed to post.');
      errEl.style.display = 'block';
      btn.textContent = 'Post Question ➜';
      btn.disabled = false;
    }
  } catch (err) {
    errEl.textContent = '⚠️ Network error. Please try again.';
    errEl.style.display = 'block';
    btn.textContent = 'Post Question ➜';
    btn.disabled = false;
  }
});
</script>
</body>
</html>
