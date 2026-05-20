<?php
/**
 * SMFE — Comments API
 *
 * GET  /api/comments.php?post_id=5   → list all comments for a post
 * POST /api/comments.php  { "action":"add",    "post_id":5, "text":"...", "math":"..." }
 * POST /api/comments.php  { "action":"delete", "comment_id":12 }
 */

session_start();
require_once __DIR__ . '/../db/config.php';

header('Content-Type: application/json');

// Always return JSON even on uncaught exceptions
set_exception_handler(function(Throwable $e) {
  http_response_code(500);
  header('Content-Type: application/json');
  echo json_encode(['ok' => false, 'error' => 'Server error: ' . $e->getMessage()]);
  exit;
});

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  $postId = (int)($_GET['post_id'] ?? 0);
  if (!$postId) jsonResponse(['ok' => false, 'error' => 'post_id required.']);

  $db   = getDB();
  $stmt = $db->prepare("
    SELECT c.id, c.text, c.math, c.created_at, u.username, u.avatar_letter
    FROM comments c
    JOIN users u ON u.id = c.user_id
    WHERE c.post_id = ?
    ORDER BY c.created_at ASC
  ");
  $stmt->execute([$postId]);
  jsonResponse(['ok' => true, 'comments' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
  $user   = requireAuth();
  $raw    = file_get_contents('php://input');
  $data   = json_decode($raw, true) ?: $_POST;
  $action = trim($data['action'] ?? '');

  switch ($action) {

    case 'add': {
      $postId = (int)($data['post_id'] ?? 0);
      $text   = trim($data['text'] ?? '');
      $math   = trim($data['math'] ?? '');

      if (!$postId) jsonResponse(['ok' => false, 'error' => 'post_id required.']);
      if (!$text && !$math) jsonResponse(['ok' => false, 'error' => 'Comment cannot be empty.']);

      $db   = getDB();

      // Try to insert with math column; fall back if column doesn't exist yet
      try {
        $stmt = $db->prepare(
          'INSERT INTO comments (post_id, user_id, text, math) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$postId, $user['id'], $text ?: null, $math ?: null]);
      } catch (PDOException $e) {
        // math column might not exist yet — insert without it
        $combined = $text . ($math ? ' | ' . $math : '');
        $stmt = $db->prepare(
          'INSERT INTO comments (post_id, user_id, text) VALUES (?, ?, ?)'
        );
        $stmt->execute([$postId, $user['id'], $combined]);
      }
      $id = (int)$db->lastInsertId();

      jsonResponse([
        'ok'           => true,
        'comment_id'   => $id,
        'username'     => $user['username'],
        'avatar_letter'=> $user['avatar_letter'],
        'text'         => $text,
        'math'         => $math,
      ]);
    }

    case 'delete': {
      $commentId = (int)($data['comment_id'] ?? 0);
      if (!$commentId) jsonResponse(['ok' => false, 'error' => 'comment_id required.']);

      $db   = getDB();
      $stmt = $db->prepare('DELETE FROM comments WHERE id = ? AND user_id = ?');
      $stmt->execute([$commentId, $user['id']]);

      jsonResponse(['ok' => true]);
    }

    case 'update': {
      $commentId = (int)($data['comment_id'] ?? 0);
      $text      = trim($data['text'] ?? '');
      $math      = trim($data['math'] ?? '');

      if (!$commentId) jsonResponse(['ok' => false, 'error' => 'comment_id required.']);
      if (!$text && !$math) jsonResponse(['ok' => false, 'error' => 'Comment cannot be empty.']);

      $db = getDB();
      try {
        $stmt = $db->prepare('UPDATE comments SET text = ?, math = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$text ?: null, $math ?: null, $commentId, $user['id']]);
      } catch (PDOException $e) {
        // math column might not exist — update text only
        $stmt = $db->prepare('UPDATE comments SET text = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$text ?: '', $commentId, $user['id']]);
      }

      jsonResponse(['ok' => true, 'text' => $text, 'math' => $math]);
    }

    default:
      jsonResponse(['ok' => false, 'error' => 'Unknown action.'], 400);
  }
}

jsonResponse(['ok' => false, 'error' => 'Method not allowed.'], 405);
