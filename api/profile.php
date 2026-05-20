<?php
/**
 * SMFE — Profile API
 * GET  /api/profile.php              → current user profile data
 * POST /api/profile.php  { "action":"update", "full_name":"...", "bio":"...", "password":"..." }
 */

session_start();
require_once __DIR__ . '/../db/config.php';

header('Content-Type: application/json');

$user = requireAuth();
$db   = getDB();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  $stmt = $db->prepare('SELECT id, username, email, avatar_letter, bio, full_name, created_at FROM users WHERE id = ?');
  $stmt->execute([$user['id']]);
  $row = $stmt->fetch();

  // question count
  $qstmt = $db->prepare('SELECT COUNT(*) as cnt FROM posts WHERE user_id = ?');
  $qstmt->execute([$user['id']]);
  $qrow = $qstmt->fetch();

  jsonResponse(['ok' => true, 'user' => $row, 'question_count' => (int)$qrow['cnt']]);
}

if ($method === 'POST') {
  $raw  = file_get_contents('php://input');
  $data = json_decode($raw, true) ?: $_POST;

  $action = trim($data['action'] ?? 'update');

  if ($action === 'update') {
    $fullName = trim($data['full_name'] ?? '');
    $bio      = trim($data['bio'] ?? '');
    $password = $data['password'] ?? '';

    if ($fullName) {
      $stmt = $db->prepare('UPDATE users SET full_name = ?, bio = ? WHERE id = ?');
      $stmt->execute([$fullName, $bio ?: null, $user['id']]);
    } else {
      $stmt = $db->prepare('UPDATE users SET bio = ? WHERE id = ?');
      $stmt->execute([$bio ?: null, $user['id']]);
    }

    if ($password && strlen($password) >= 6) {
      $hash = password_hash($password, PASSWORD_BCRYPT);
      $stmt = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
      $stmt->execute([$hash, $user['id']]);
    }

    // refresh session bio
    $_SESSION['user']['bio'] = $bio ?: null;

    jsonResponse(['ok' => true]);
  }

  jsonResponse(['ok' => false, 'error' => 'Unknown action.'], 400);
}

jsonResponse(['ok' => false, 'error' => 'Method not allowed.'], 405);
