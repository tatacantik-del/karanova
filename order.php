<?php
require_once __DIR__ . "/config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$error = "";

// Handle add/remove/update via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $items = $_POST['items'] ?? [];
  $cart = [];

  foreach ($items as $pid => $qty) {
    $pid = (int)$pid;
    $qty = (int)$qty;
    if ($qty > 0) $cart[$pid] = $qty;
  }

  // ✅ Validasi stok server-side: tidak boleh pesan melebihi stok, stok 0 tidak boleh dipesan
  if (!empty($cart)) {
    $ids = implode(",", array_map("intval", array_keys($cart)));
    $rs = $conn->query("SELECT ProdukID, Stok FROM produk WHERE ProdukID IN ($ids)");
    $stokMap = [];
    while ($r = $rs->fetch_assoc()) {
      $stokMap[(int)$r['ProdukID']] = (int)$r['Stok'];
    }

    foreach ($cart as $pid => $qty) {
      $stok = (int)($stokMap[(int)$pid] ?? 0);

      if ($stok <= 0) {
        unset($cart[$pid]);
        $error = "Ada menu yang HABIS (stok 0) dan otomatis dihapus dari keranjang.";
        continue;
      }
      if ($qty > $stok) {
        $cart[$pid] = $stok; // clamp ke stok maksimal
        $error = "Qty melebihi stok. Beberapa item otomatis disesuaikan ke stok tersedia.";
      }
    }
  }

  $_SESSION['cart'] = $cart;

  if (isset($_POST['checkout']) && !$error) {
    header("Location: /kasanova/checkout.php");
    exit;
  }
}

// Get products
$produk = $conn->query("SELECT ProdukID, Nama_produk, Harga, Stok FROM produk ORDER BY Nama_produk ASC");

// Compute totals for current cart
$total = 0;
if (!empty($_SESSION['cart'])) {
  $ids = implode(",", array_map("intval", array_keys($_SESSION['cart'])));
  $res = $conn->query("SELECT ProdukID, Harga FROM produk WHERE ProdukID IN ($ids)");
  $hargaMap = [];
  while ($r = $res->fetch_assoc()) {
    $hargaMap[(int)$r['ProdukID']] = (int)$r['Harga'];
  }
  foreach ($_SESSION['cart'] as $pid => $qty) {
    $total += ($hargaMap[(int)$pid] ?? 0) * (int)$qty;
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pesan - Kasanova</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <!-- Tema utama (kalau kamu pakai theme file) -->
  <link rel="stylesheet" href="/kasanova/assets/css/neo3d.css">

  <style>
    .table thead th{ white-space: nowrap; }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/kasanova/"><i class="bi bi-shop me-1"></i> Kasanova</a>
    <div class="ms-auto d-flex gap-2">
      <a class="btn btn-outline-dark" href="/kasanova/login.php"><i class="bi bi-speedometer2 me-1"></i> Login Dashboard</a>
    </div>
  </div>
</nav>

<main class="container py-4">
  <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-3">
    <div>
      <div class="text-muted small">Tanpa login</div>
      <h3 class="mb-0 fw-bold">Pilih Menu</h3>
    </div>
    <a class="btn btn-outline-secondary" href="/kasanova/"><i class="bi bi-arrow-left"></i> Kembali</a>
  </div>

  <?php if($error): ?>
    <div class="alert alert-warning border-0 shadow-sm"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="row g-3">
      <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr>
                    <th>Menu</th>
                    <th class="text-end">Harga</th>
                    <th class="text-end">Stok</th>
                    <th class="text-end" style="width:150px;">Qty</th>
                  </tr>
                </thead>
                <tbody>
                <?php while($p = $produk->fetch_assoc()): ?>
                  <?php
                    $pid  = (int)$p['ProdukID'];
                    $qty  = (int)($_SESSION['cart'][$pid] ?? 0);
                    $stok = (int)$p['Stok'];
                  ?>
                  <tr>
                    <td>
                      <div class="fw-semibold"><?= e($p['Nama_produk']) ?></div>
                      <div class="text-muted small">ID: <?= e($pid) ?></div>
                    </td>
                    <td class="text-end">Rp <?= number_format((int)$p['Harga'],0,',','.') ?></td>
                    <td class="text-end">
                      <?php if($stok <= 0): ?>
                        <span class="badge text-bg-danger">HABIS</span>
                      <?php else: ?>
                        <?= e($stok) ?>
                      <?php endif; ?>
                    </td>
                    <td class="text-end">
                      <input type="number"
                        class="form-control text-end"
                        name="items[<?= e($pid) ?>]"
                        min="0"
                        max="<?= e($stok) ?>"
                        value="<?= e($stok<=0 ? 0 : $qty) ?>"
                        <?= ($stok<=0 ? 'disabled' : '') ?>>
                    </td>
                  </tr>
                <?php endwhile; ?>
                </tbody>
              </table>
            </div>
            <div class="text-muted small">Tip: isi qty menu yang ingin kamu pesan. Qty 0 = tidak masuk keranjang.</div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="mb-2">Ringkasan</h5>
            <div class="d-flex justify-content-between">
              <div class="text-muted">Total</div>
              <div class="fw-bold">Rp <?= number_format((int)$total,0,',','.') ?></div>
            </div>
            <hr>
            <div class="d-grid gap-2">
              <button class="btn btn-outline-dark" type="submit" name="update">
                <i class="bi bi-arrow-repeat me-1"></i> Update Keranjang
              </button>
              <button class="btn btn-dark" type="submit" name="checkout" <?= ($total<=0?'disabled':'') ?>>
                <i class="bi bi-credit-card me-1"></i> Lanjut Checkout
              </button>
              <a class="btn btn-outline-secondary" href="/kasanova/login.php">
                <i class="bi bi-person-lock me-1"></i> Login (Admin/Kasir/Owner)
              </a>
            </div>
          </div>
        </div>

        <div class="alert alert-info mt-3 mb-0">
          <div class="fw-semibold">Catatan</div>
          <div class="small">Customer bisa pesan tanpa login. Dashboard hanya untuk Admin/Kasir/Owner.</div>
        </div>
      </div>
    </div>
  </form>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>