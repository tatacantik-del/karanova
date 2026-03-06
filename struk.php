<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/auth.php";
require_login();

$id=(int)($_GET['id']??0);
$stmt=$conn->prepare("SELECT p.*, pl.Nama_pelanggan, u.Nama_user AS Nama_kasir
  FROM penjualan p
  LEFT JOIN pelanggan pl ON pl.PelangganID=p.PelangganID
  LEFT JOIN user u ON u.UserID=p.UserID
  WHERE p.PenjualanID=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$jual=$stmt->get_result()->fetch_assoc();
if(!$jual) die("Transaksi tidak ditemukan.");

$items=$conn->prepare("SELECT d.JumlahProduk, d.Subtotal, pr.Nama_produk, pr.Harga
  FROM detailpenjualan d
  LEFT JOIN produk pr ON pr.ProdukID=d.ProdukID
  WHERE d.PenjualanID=?
  ORDER BY d.DetailID ASC");
$items->bind_param("i",$id);
$items->execute();
$items=$items->get_result();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Struk #<?= e($id) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body{ background:#f6f7fb; }
    .receipt{ width:360px; }
    @media print{
      body{ background:#fff; }
      .no-print{ display:none !important; }
      .receipt{ width:100%; }
    }
  </style>
</head>
<body class="py-4">
  <div class="container d-flex justify-content-center">
    <div class="receipt card shadow-sm">
      <div class="card-body">
        <div class="text-center mb-3">
          <div class="fw-bold fs-5">RESTO KAMU</div>
          <div class="text-muted small">Jl. cak eman No. 123 • 08xx-xxxx-xxxx</div>
          <hr>
          <div class="small">
            <div>No: <b>#<?= e($jual['PenjualanID']) ?></b></div>
            <div>Tanggal: <?= e($jual['Tanggal_penjualan']) ?></div>
            <div>Kasir: <?= e($jual['Nama_kasir'] ?? '-') ?></div>
            <div>Pelanggan: <?= e($jual['Nama_pelanggan'] ?? 'Umum') ?></div>
            <div>Metode: <?= e($jual['MetodePembayaran'] ?? '-') ?></div>
          </div>
        </div>

        <table class="table table-sm">
          <thead><tr><th>Item</th><th class="text-end">Qty</th><th class="text-end">Sub</th></tr></thead>
          <tbody>
            <?php while($it=$items->fetch_assoc()): ?>
            <tr>
              <td>
                <div class="fw-semibold"><?= e($it['Nama_produk']) ?></div>
                <div class="text-muted small">Rp <?= number_format((int)$it['Harga'],0,',','.') ?></div>
              </td>
              <td class="text-end"><?= e($it['JumlahProduk']) ?></td>
              <td class="text-end">Rp <?= number_format((int)$it['Subtotal'],0,',','.') ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>

        <hr>
        <div class="d-flex justify-content-between"><div class="text-muted">Total</div><div class="fw-bold">Rp <?= number_format((int)$jual['Total_harga'],0,',','.') ?></div></div>
        <div class="d-flex justify-content-between"><div class="text-muted">Bayar</div><div>Rp <?= number_format((int)$jual['Bayar'],0,',','.') ?></div></div>
        <div class="d-flex justify-content-between"><div class="text-muted">Kembalian</div><div>Rp <?= number_format((int)$jual['Kembalian'],0,',','.') ?></div></div>

        <div class="text-center mt-4">
          <div class="small text-muted">Terima kasih 🙏</div>
          <div class="small text-muted">Silakan datang kembali!</div>
        </div>

        <div class="d-grid gap-2 mt-3 no-print">
          <button class="btn btn-dark" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
          <a class="btn btn-outline-secondary" href="/kasanova/pages/transaksi/create.php">Transaksi Baru</a>
          <a class="btn btn-outline-secondary" href="/kasanova/pages/dashboard.php">Kembali Dashboard</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>