<?php
/**
 * SMFE — Database Configuration
 *
 * Fill in your phpMyAdmin / MySQL credentials below.
 * These are used by all api/*.php endpoints.
 */

define('DB_HOST', 'sql212.infinityfree.com');
define('DB_NAME', 'if0_41970871_smfe');
define('DB_USER', 'if0_41970871');       // change to your MySQL username
define('DB_PASS', 'Kf9bfJqzGVGd8jm');           // change to your MySQL password
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a PDO connection (singleton), or null if the DB is not reachable.
 * Never exits — safe to call from any page.
 */
function tryGetDB(): ?PDO {
  static $pdo = null;
  static $tried = false;
  if ($tried) return $pdo;
  $tried = true;
  $dsn = 'mysql:host=' . DB_HOST
       . ';dbname=' . DB_NAME
       . ';charset=' . DB_CHARSET;
  try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
  } catch (PDOException $e) {
    $pdo = null;
  }
  return $pdo;
}

/**
 * Returns a PDO connection for API endpoints.
 * Exits with a JSON error response if the DB is not available.
 */
function getDB(): PDO {
  $pdo = tryGetDB();
  if ($pdo === null) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Database connection failed.']);
    exit;
  }
  return $pdo;
}

/**
 * Helper: send a JSON response and exit.
 */
function jsonResponse(array $data, int $code = 200): void {
  http_response_code($code);
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}

/**
 * Helper: require a logged-in session, else abort with 401.
 */
function requireAuth(): array {
  if (empty($_SESSION['user'])) {
    jsonResponse(['ok' => false, 'error' => 'Not authenticated.'], 401);
  }
  return $_SESSION['user'];
}

/**
 * Restore session from the remember_me cookie (30-day login).
 * Call this on every page load after session_start().
 * Safe to call even when no cookie is present.
 */
function restoreSessionFromCookie(): void {
  if (!empty($_SESSION['user'])) return;   // already logged in
  $token = $_COOKIE['remember_me'] ?? '';
  if (!$token) return;

  try {
    $pdo = tryGetDB();
    if (!$pdo) return;

    // Ensure table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS remember_tokens (
      id         INT AUTO_INCREMENT PRIMARY KEY,
      user_id    INT NOT NULL,
      token_hash VARCHAR(64) NOT NULL,
      expires_at DATETIME NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $hash = hash('sha256', $token);
    $stmt = $pdo->prepare("
      SELECT rt.user_id, u.username, u.email, u.avatar_letter, u.bio
      FROM remember_tokens rt
      JOIN users u ON u.id = rt.user_id
      WHERE rt.token_hash = ? AND rt.expires_at > NOW()
      LIMIT 1
    ");
    $stmt->execute([$hash]);
    $row = $stmt->fetch();

    if ($row) {
      $_SESSION['user'] = [
        'id'           => $row['user_id'],
        'username'     => $row['username'],
        'email'        => $row['email'],
        'avatar_letter'=> $row['avatar_letter'],
        'bio'          => $row['bio'],
      ];
    }
  } catch (Throwable $e) {
    // Never crash a page over a missing/broken remember_me token
  }
}
