<?php
// config/auth.php
if (session_status() === PHP_SESSION_NONE) session_start();

function require_login(){
  if (empty($_SESSION['user'])) {
    header("Location: /kasanova/login.php");
    exit;
  }
}

function require_role(array $roles){
  require_login();
  $role = $_SESSION['user']['Role'] ?? '';
  if (!in_array($role, $roles)) {
    http_response_code(403);
    echo "<h3>Akses ditolak (403)</h3>";
    exit;
  }
}

function current_user(){ return $_SESSION['user'] ?? null; }
?>