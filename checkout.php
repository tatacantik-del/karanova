<?php
require_once __DIR__ . "/config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
  header("Location: /kasanova/order.php");
  exit;
}

// Load cart products
$ids = implode(",", array_map("intval", array_keys($cart)));
$res = $conn->query("SELECT ProdukID, Nama_produk, Harga, Stok FROM produk WHERE ProdukID IN ($ids)");
$items = [];
$total = 0;
while ($p = $res->fetch_assoc()) {
  $pid = (int)$p['ProdukID'];
  $qty = (int)($cart[$pid] ?? 0);
  if ($qty <= 0) continue;
  $harga = (int)$p['Harga'];
  $sub = $harga * $qty;
  $total += $sub;
  $items[] = [
    'ProdukID'=>$pid,
    'Nama_produk'=>$p['Nama_produk'],
    'Harga'=>$harga,
    'Stok'=>(int)$p['Stok'],
    'Qty'=>$qty,
    'Subtotal'=>$sub
  ];
}

$qrisImg = "/kasanova/assets/qris.jpg";

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $namaPelanggan = trim($_POST['nama_pelanggan'] ?? '');

  // ✅ hanya CASH & QRIS
  $metode = $_POST['metode'] ?? 'CASH';
  if(!in_array($metode, ['CASH','QRIS'], true)) $metode = 'CASH';

  $bayar  = (int)($_POST['bayar'] ?? 0);

  if ($total <= 0) $error = "Keranjang kosong.";
  else {
    foreach ($items as $it) {
      if ($it['Qty'] > $it['Stok']) {
        $error = "Stok tidak cukup untuk: " . $it['Nama_produk'];
        break;
      }
    }
  }

  // ✅ QRIS: bayar otomatis = total
  if(!$error && $metode === 'QRIS'){
    $bayar = $total;
  }

  if (!$error && $bayar < $total) {
    $error = "Uang bayar kurang. Total Rp " . number_format($total,0,',','.');
  }

  if (!$error) {
    // cari/insert pelanggan
    $pelangganID = 0;
    if ($namaPelanggan !== '') {
      $namaPelanggan = preg_replace('/\s+/', ' ', $namaPelanggan);

      $stmt = $conn->prepare("SELECT PelangganID FROM pelanggan WHERE Nama_pelanggan=? LIMIT 1");
      $stmt->bind_param("s", $namaPelanggan);
      $stmt->execute();
      $r = $stmt->get_result();

      if ($row = $r->fetch_assoc()) {
        $pelangganID = (int)$row['PelangganID'];
      } else {
        $stmt = $conn->prepare("INSERT INTO pelanggan (Nama_pelanggan) VALUES (?)");
        $stmt->bind_param("s", $namaPelanggan);
        $stmt->execute();
        $pelangganID = (int)$conn->insert_id;
      }
    }

    $kembalian = $bayar - $total; // QRIS => 0
    $tanggal = date('Y-m-d');

    // kalau ada user login -> simpan UserID, kalau customer biasa -> NULL
    $userID = $_SESSION['user']['UserID'] ?? NULL;

    $conn->begin_transaction();
    try {
      $stmt = $conn->prepare("INSERT INTO penjualan (Tanggal_penjualan, Total_harga, PelangganID, UserID, MetodePembayaran, Bayar, Kembalian)
                              VALUES (?,?,?,?,?,?,?)");
      $stmt->bind_param("siiisii", $tanggal, $total, $pelangganID, $userID, $metode, $bayar, $kembalian);
      $stmt->execute();
      $penjualanID = (int)$conn->insert_id;

      foreach ($items as $it) {
        $pid = (int)$it['ProdukID'];
        $qty = (int)$it['Qty'];
        $sub = (int)$it['Subtotal'];

        $stmt = $conn->prepare("INSERT INTO detailpenjualan (PenjualanID, ProdukID, UserID, JumlahProduk, Subtotal)
                                VALUES (?,?,?, ?,?)");
        $stmt->bind_param("iiiii", $penjualanID, $pid, $userID, $qty, $sub);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE produk SET Stok=Stok-? WHERE ProdukID=?");
        $stmt->bind_param("ii", $qty, $pid);
        $stmt->execute();
      }

      $conn->commit();

      // clear cart
      $_SESSION['cart'] = [];

      header("Location: /kasanova/receipt.php?id=" . $penjualanID);
      exit;

    } catch (Exception $e) {
      $conn->rollback();
      $error = "Gagal simpan transaksi: " . $e->getMessage();
    }
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Checkout - Kasanova</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/kasanova/assets/css/neo3d.css">
  <style>.qris-img{ max-width: 240px; border-radius: 14px; }</style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container py-2">
    <a class="navbar-brand fw-bold" href="/kasanova/"><i class="bi bi-shop me-1"></i> Kasanova</a>
    <div class="ms-auto d-flex gap-2">
      <a class="btn btn-outline-secondary" href="/kasanova/order.php"><i class="bi bi-arrow-left"></i> Keranjang</a>
      <a class="btn btn-dark" href="/kasanova/login.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    </div>
  </div>
</nav>

<main class="container py-4">
  <div class="d-flex align-items-end justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <div class="text-muted small">Checkout</div>
      <h3 class="fw-bold mb-0">Pembayaran</h3>
    </div>
  </div>

  <?php if($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
  <?php endif; ?>

  <div class="row g-3">
    <div class="col-12 col-lg-7">
      <div class="card">
        <div class="card-body">
          <h5 class="mb-3 fw-bold">Ringkasan Pesanan</h5>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead><tr><th>Menu</th><th class="text-end">Qty</th><th class="text-end">Sub</th></tr></thead>
              <tbody>
              <?php foreach($items as $it): ?>
                <tr>
                  <td>
                    <div class="fw-semibold"><?= e($it['Nama_produk']) ?></div>
                    <div class="text-muted small">Rp <?= number_format((int)$it['Harga'],0,',','.') ?> • Stok: <?= e($it['Stok']) ?></div>
                  </td>
                  <td class="text-end"><?= e($it['Qty']) ?></td>
                  <td class="text-end fw-bold">Rp <?= number_format((int)$it['Subtotal'],0,',','.') ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="2" class="text-end">Total</th>
                  <th class="text-end">Rp <?= number_format((int)$total,0,',','.') ?></th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5">
      <div class="card">
        <div class="card-body">
          <h5 class="mb-3 fw-bold">Detail Pembayaran</h5>

          <form method="post">
            <div class="mb-3">
              <label class="form-label">Nama Pelanggan (opsional)</label>
              <input class="form-control" name="nama_pelanggan" placeholder="misal: Budi">
            </div>

            <div class="mb-3">
              <label class="form-label">Metode Pembayaran</label>
              <select class="form-select" name="metode" id="metode">
                <option value="CASH">CASH</option>
                <option value="QRIS">QRIS</option>
              </select>
              <div class="form-text">Metode dibatasi hanya CASH & QRIS.</div>
            </div>

            <div class="mb-3" id="qrisBox" style="display:none;">
              <div class="alert alert-info">
                <div class="fw-semibold mb-2">Scan QRIS</div>
                <img class="qris-img shadow-sm" src="<?= e($qrisImg) ?>" alt="QRIS">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Uang Bayar</label>
              <input type="number" min="0" class="form-control" name="bayar" id="bayar" value="<?= e($total) ?>" required>
              <div class="form-text">CASH: harus >= total. QRIS: otomatis = total.</div>
            </div>

            <button class="btn btn-dark w-100"><i class="bi bi-check2-circle me-1"></i> Bayar & Cetak Struk</button>
          </form>

        </div>
      </div>
    </div>
  </div>
</main>

<script>
const metode = document.getElementById('metode');
const bayar   = document.getElementById('bayar');
const qrisBox = document.getElementById('qrisBox');
const total   = <?= json_encode((int)$total) ?>;

function sync(){
  const m = metode.value;
  if(m === 'QRIS'){
    bayar.value = total;
    bayar.setAttribute('readonly','readonly');
    qrisBox.style.display = 'block';
  } else {
    bayar.removeAttribute('readonly');
    qrisBox.style.display = 'none';
  }
}
metode.addEventListener('change', sync);
sync();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
