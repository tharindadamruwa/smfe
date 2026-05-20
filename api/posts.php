<?php
/**
 * SMFE — Posts API
 *
 * GET  /api/posts.php?page=1&tag=Calculus   → list posts (paginated, optionally filtered)
 * POST /api/posts.php  { "action":"create", "body":"...", "math":"...", "tag":"..." }
 * POST /api/posts.php  { "action":"delete", "post_id":5 }
 */

session_start();
// Always return JSON even on uncaught exceptions
set_exception_handler(function(Throwable $e) {
  http_response_code(500);
  header('Content-Type: application/json');
  echo json_encode(['ok' => false, 'error' => 'Server error: ' . $e->getMessage()]);
  exit;
});
require_once __DIR__ . '/../db/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// ── GET: list posts ────────────────────────────────────────────────────────────
if ($method === 'GET') {
  $db      = getDB();
  $page    = max(1, (int)($_GET['page'] ?? 1));
  $limit   = 20;
  $offset  = ($page - 1) * $limit;
  $tag     = trim($_GET['tag'] ?? '');
  $userId  = $_SESSION['user']['id'] ?? 0;

  $where  = $tag && $tag !== 'All' ? 'WHERE p.tag = ?' : '';
  $params = $tag && $tag !== 'All' ? [$tag] : [];

  $sql = "
    SELECT
      p.id, p.body, p.math, p.tag,
      p.image_path, p.created_at,
      u.username, u.avatar_letter,
      (SELECT COUNT(*) FROM likes   l WHERE l.post_id = p.id) AS likes,
      (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
      " . ($userId ? "(SELECT COUNT(*) FROM likes lme WHERE lme.post_id = p.id AND lme.user_id = {$userId}) AS user_liked" : "0 AS user_liked") . "
    FROM posts p
    JOIN users u ON u.id = p.user_id
    {$where}
    ORDER BY p.created_at DESC
    LIMIT {$limit} OFFSET {$offset}
  ";

  $stmt = $db->prepare($sql);
  $stmt->execute($params);
  $posts = $stmt->fetchAll();

  // Attach comments (up to 3 per post for preview)
  $postIds = array_column($posts, 'id');
  $comments = [];
  if ($postIds) {
    $in   = implode(',', array_map('intval', $postIds));
    $cstmt = $db->query("
      SELECT c.post_id, c.text, u.username
      FROM comments c JOIN users u ON u.id = c.user_id
      WHERE c.post_id IN ({$in})
      ORDER BY c.created_at ASC
    ");
    foreach ($cstmt->fetchAll() as $row) {
      $comments[$row['post_id']][] = $row;
    }
  }

  foreach ($posts as &$p) {
    $p['comments_data'] = array_slice($comments[$p['id']] ?? [], 0, 3);
    $p['user_liked']    = (bool)$p['user_liked'];

    // Human-readable time
    $diff = time() - strtotime($p['created_at']);
    if ($diff < 60)         $p['time'] = 'just now';
    elseif ($diff < 3600)   $p['time'] = floor($diff/60) . ' min ago';
    elseif ($diff < 86400)  $p['time'] = floor($diff/3600) . ' hr ago';
    else                    $p['time'] = floor($diff/86400) . ' days ago';
  }
  unset($p);

  jsonResponse(['ok' => true, 'posts' => $posts, 'page' => $page]);
}

// ── POST: create / delete ──────────────────────────────────────────────────────
if ($method === 'POST') {
  $user = requireAuth();

  // Support both JSON body and multipart/form-data (needed for file uploads)
  $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
  if (strpos($contentType, 'application/json') !== false) {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: [];
  } else {
    $data = $_POST;
  }
  $action = trim($data['action'] ?? '');

  switch ($action) {

    case 'create': {
      $body = trim($data['body'] ?? '');
      $math = trim($data['math'] ?? '');
      $tag  = trim($data['tag']  ?? 'Other');

      if (strlen($body) < 5) {
        jsonResponse(['ok' => false, 'error' => 'Question is too short.']);
      }

      // Handle image upload
      $imagePath = null;
      if (!empty($_FILES['images']['name'][0])) {
        $file = [
          'name'     => $_FILES['images']['name'][0],
          'tmp_name' => $_FILES['images']['tmp_name'][0],
          'size'     => $_FILES['images']['size'][0],
          'type'     => $_FILES['images']['type'][0],
          'error'    => $_FILES['images']['error'][0],
        ];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (
          $file['error'] === UPLOAD_ERR_OK &&
          in_array($file['type'], $allowed) &&
          $file['size'] <= 5 * 1024 * 1024
        ) {
          $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
          $filename = 'img_' . uniqid('', true) . '.' . $ext;
          $uploadDir = __DIR__ . '/../img/uploads/';
          if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
          }
          if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $imagePath = 'img/uploads/' . $filename;
          }
        }
      }

      $db   = getDB();
      $stmt = $db->prepare(
        'INSERT INTO posts (user_id, body, math, tag, image_path) VALUES (?, ?, ?, ?, ?)'
      );
      $stmt->execute([$user['id'], $body, $math ?: null, $tag, $imagePath]);
      $id = (int)$db->lastInsertId();

      jsonResponse(['ok' => true, 'post_id' => $id, 'image_path' => $imagePath]);
    }

    case 'delete': {
      $postId = (int)($data['post_id'] ?? 0);
      if (!$postId) jsonResponse(['ok' => false, 'error' => 'post_id required.']);

      $db   = getDB();
      $stmt = $db->prepare('DELETE FROM posts WHERE id = ? AND user_id = ?');
      $stmt->execute([$postId, $user['id']]);

      jsonResponse(['ok' => true]);
    }

    default:
      jsonResponse(['ok' => false, 'error' => 'Unknown action.'], 400);
  }
}

jsonResponse(['ok' => false, 'error' => 'Method not allowed.'], 405);
