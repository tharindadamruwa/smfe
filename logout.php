<?php
session_start();
require_once 'db/config.php';

// Clear 30-day remember_me cookie from DB + browser
if (!empty($_COOKIE['remember_me'])) {
  $pdo = tryGetDB();
  if ($pdo) {
    $hash = hash('sha256', $_COOKIE['remember_me']);
    $pdo->prepare('DELETE FROM remember_tokens WHERE token_hash = ?')->execute([$hash]);
  }
  setcookie('remember_me', '', time() - 3600, '/', '', false, true);
}

session_destroy();
header('Location: index.php');
exit;
?>
