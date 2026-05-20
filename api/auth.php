<?php
/**
 * SMFE — Auth API
 * Handles: login, signup, logout
 * Called via AJAX (POST with JSON body or form data)
 *
 * Actions:
 *   POST /api/auth.php  { "action":"login",  "email":"...", "password":"..." }
 *   POST /api/auth.php  { "action":"signup", "username":"...", "email":"...", "password":"..." }
 *   POST /api/auth.php  { "action":"logout" }
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

$raw    = file_get_contents('php://input');
$data   = json_decode($raw, true);
if (!$data) {
  $data = $_POST;
}

$action = trim($data['action'] ?? '');

switch ($action) {

  // ── LOGIN ──────────────────────────────────────────────────
  case 'login': {
    $email    = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (!$email || !$password) {
      jsonResponse(['ok' => false, 'error' => 'Email and password are required.']);
    }

    $db   = getDB();
    $stmt = $db->prepare('SELECT id, username, email, password_hash, avatar_letter, bio FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
      jsonResponse(['ok' => false, 'error' => 'Invalid email or password.']);
    }

    $_SESSION['user'] = [
      'id'           => $user['id'],
      'username'     => $user['username'],
      'email'        => $user['email'],
      'avatar_letter'=> $user['avatar_letter'],
      'bio'          => $user['bio'],
    ];

    // Set 30-day remember_me cookie
    $rmToken = bin2hex(random_bytes(32));
    $rmHash  = hash('sha256', $rmToken);
    $db->exec("CREATE TABLE IF NOT EXISTS remember_tokens (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL,
      token_hash VARCHAR(64) NOT NULL,
      expires_at DATETIME NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    $db->prepare("INSERT INTO remember_tokens (user_id, token_hash, expires_at)
      VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))")->execute([$user['id'], $rmHash]);
    setcookie('remember_me', $rmToken, [
      'expires'  => time() + 86400 * 30,
      'path'     => '/',
      'httponly' => true,
      'samesite' => 'Lax',
    ]);

    jsonResponse(['ok' => true, 'redirect' => '../index.php', 'user' => $_SESSION['user']]);
  }

  // ── SIGNUP ─────────────────────────────────────────────────
  case 'signup': {
    $username = trim($data['username'] ?? '');
    $email    = trim($data['email']    ?? '');
    $password = $data['password']      ?? '';

    if (strlen($username) < 3) {
      jsonResponse(['ok' => false, 'error' => 'Username must be at least 3 characters.']);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      jsonResponse(['ok' => false, 'error' => 'Please enter a valid email address.']);
    }
    if (strlen($password) < 6) {
      jsonResponse(['ok' => false, 'error' => 'Password must be at least 6 characters.']);
    }

    $db = getDB();

    // Check uniqueness
    $chk = $db->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
    $chk->execute([$email, $username]);
    if ($chk->fetch()) {
      jsonResponse(['ok' => false, 'error' => 'Email or username already in use.']);
    }

    $hash   = password_hash($password, PASSWORD_BCRYPT);
    $letter = strtoupper(substr($username, 0, 1));

    $ins = $db->prepare(
      'INSERT INTO users (username, email, password_hash, avatar_letter) VALUES (?, ?, ?, ?)'
    );
    $ins->execute([$username, $email, $hash, $letter]);
    $userId = (int)$db->lastInsertId();

    $_SESSION['user'] = [
      'id'           => $userId,
      'username'     => $username,
      'email'        => $email,
      'avatar_letter'=> $letter,
      'bio'          => null,
    ];

    // Set 30-day remember_me cookie
    $rmToken = bin2hex(random_bytes(32));
    $rmHash  = hash('sha256', $rmToken);
    $db->exec("CREATE TABLE IF NOT EXISTS remember_tokens (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL,
      token_hash VARCHAR(64) NOT NULL,
      expires_at DATETIME NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    $db->prepare("INSERT INTO remember_tokens (user_id, token_hash, expires_at)
      VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))")->execute([$userId, $rmHash]);
    setcookie('remember_me', $rmToken, [
      'expires'  => time() + 86400 * 30,
      'path'     => '/',
      'httponly' => true,
      'samesite' => 'Lax',
    ]);

    jsonResponse(['ok' => true, 'redirect' => '../index.php', 'user' => $_SESSION['user']]);
  }

  // ── LOGOUT ─────────────────────────────────────────────────
  case 'logout': {
    // Clear remember_me cookie if present
    if (!empty($_COOKIE['remember_me'])) {
      $pdo = tryGetDB();
      if ($pdo) {
        $h = hash('sha256', $_COOKIE['remember_me']);
        $pdo->prepare('DELETE FROM remember_tokens WHERE token_hash = ?')->execute([$h]);
      }
      setcookie('remember_me', '', time() - 3600, '/', '', false, true);
    }
    $_SESSION = [];
    session_destroy();
    jsonResponse(['ok' => true, 'redirect' => '../login.php']);
  }

  default:
    jsonResponse(['ok' => false, 'error' => 'Unknown action.'], 400);
}
