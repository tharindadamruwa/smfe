<?php
/**
 * SMFE — Likes API
 *
 * POST /api/likes.php  { "post_id": 5 }
 *   Toggles like for the logged-in user. Returns new count & liked state.
 */

session_start();
require_once __DIR__ . '/../db/config.php';

// Always return JSON even on uncaught exceptions
set_exception_handler(function(Throwable $e) {
  http_response_code(500);
  header('Content-Type: application/json');
  echo json_encode(['ok' => false, 'error' => 'Server error: ' . $e->getMessage()]);
  exit;
});

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  jsonResponse(['ok' => false, 'error' => 'POST required.'], 405);
}

$user   = requireAuth();
$raw    = file_get_contents('php://input');
$data   = json_decode($raw, true) ?: $_POST;
$postId = (int)($data['post_id'] ?? 0);

if (!$postId) {
  jsonResponse(['ok' => false, 'error' => 'post_id required.']);
}

$db = getDB();

// Check if already liked
$chk = $db->prepare('SELECT id FROM likes WHERE post_id = ? AND user_id = ?');
$chk->execute([$postId, $user['id']]);
$existing = $chk->fetch();

if ($existing) {
  // Unlike
  $db->prepare('DELETE FROM likes WHERE post_id = ? AND user_id = ?')
     ->execute([$postId, $user['id']]);
  $liked = false;
} else {
  // Like
  $db->prepare('INSERT INTO likes (post_id, user_id) VALUES (?, ?)')
     ->execute([$postId, $user['id']]);
  $liked = true;
}

// Return new count
$cnt = $db->prepare('SELECT COUNT(*) AS n FROM likes WHERE post_id = ?');
$cnt->execute([$postId]);
$count = (int)$cnt->fetch()['n'];

jsonResponse(['ok' => true, 'liked' => $liked, 'count' => $count]);
