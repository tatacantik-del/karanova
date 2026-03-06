<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_login();
$user = current_user();
$role = $user['Role'] ?? '';

$uri = $_SERVER['REQUEST_URI'] ?? '';

// Redirect dashboard umum ke dashboard per-role (biar kasir tidak melihat ringkasan Produk dari dashboard umum)
if (strpos($uri, '/kasanova/pages/dashboard.php') !== false) {
  if ($role === 'admin') {
    header('Location: /kasanova/pages/admin/dashboard.php');
    exit;
  }
  if ($role === 'owner') {
    header('Location: /kasanova/pages/owner/dashboard.php');
    exit;
  }
  header('Location: /kasanova/pages/kasir/dashboard.php');
  exit;
}

// OWNER hanya boleh akses halaman Owner + Laporan
if ($role === 'owner') {
  $allowOwner = (
    strpos($uri, '/kasanova/pages/owner/') !== false ||
    strpos($uri, '/kasanova/pages/laporan/') !== false
  );
  if (!$allowOwner) {
    header('Location: /kasanova/pages/laporan/index.php');
    exit;
  }
}

// Kasir: tidak boleh akses halaman Produk (route tetap ada, tapi dibatasi aksesnya)
if ($role === 'kasir') {
  if (strpos($uri, '/kasanova/pages/produk/') !== false) {
    header('Location: /kasanova/pages/kasir/dashboard.php');
    exit;
  }
}

$title = $title ?? "Dashboard";
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e($title) ?> - Kasanova</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

  <!-- Global theme -->
  <link rel="stylesheet" href="/kasanova/assets/css/neo3d.css">
</head>
<body>
<div class="d-flex">
