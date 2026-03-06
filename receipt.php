<?php
require_once __DIR__ . "/config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$id = (int)($_GET['id'] ?? 0);

/*
  Join ke tabel user supaya bisa tampil "Kasir: Nama_user"
  Note: penjualan.UserID boleh NULL (kalau customer checkout tanpa login)
*/
$stmt = $conn->prepare("SELECT p.*, pl.Nama_pelanggan, u.Nama_user AS Nama_kasir
  FROM penjualan p
  LEFT JOIN pelanggan pl ON pl.PelangganID = p.PelangganID
  LEFT JOIN user u ON u.UserID = p.UserID
  WHERE p.PenjualanID = ?");
$stmt->bind_param("i",$id);
$stmt->execute();
$jual = $stmt->get_result()->fetch_assoc();
if(!$jual){ die('Transaksi tidak ditemukan.'); }

$items = $conn->prepare("SELECT d.JumlahProduk, d.Subtotal, pr.Nama_produk, pr.Harga
  FROM detailpenjualan d
  LEFT JOIN produk pr ON pr.ProdukID = d.ProdukID
  WHERE d.PenjualanID = ?
  ORDER BY d.DetailID ASC");
$items->bind_param("i",$id);
$items->execute();
$items = $items->get_result();

$kasirName = trim((string)($jual['Nama_kasir'] ?? ''));
if ($kasirName === '') $kasirName = 'Customer';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Struk #<?= e($id) ?> - Kasanova</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/kasanova/assets/css/neo3d.css">
  <style>
    .receipt { width: 380px; position:relative; z-index:2; }
    .stamp {
      position:absolute; top:16px; right:16px;
      border:3px dashed rgba(34,197,94,.95);
      color: rgba(34,197,94,.95);
      padding:6px 10px;
      transform: rotate(12deg);
      font-weight:950;
      border-radius:12px;
      opacity:.95;
      letter-spacing: 1px;
    }
    .toggle{
      position: fixed;
      top: 12px;
      right: 12px;
      z-index: 9999;
      border-radius: 999px;
      padding: 8px 10px;
    }
    body.light{
      background: radial-gradient(900px 520px at 18% 12%, rgba(139,92,246,.16), transparent 60%),
                  radial-gradient(900px 520px at 85% 25%, rgba(34,211,238,.12), transparent 62%),
                  linear-gradient(180deg, #f8fafc, #eef2ff);
      color:#0b1220;
    }
    body.light .card{ background:#ffffff !important; color:#0b1220 !important; border-color: rgba(2,6,23,.12) !important; }
    body.light .text-muted{ color: rgba(2,6,23,.60) !important; }
    body.light .table{ color:#0b1220 !important; }
    body.light .btn-outline-secondary{ color:#0b1220 !important; border-color: rgba(2,6,23,.18) !important; }
    @media print {
      body{ background:#fff !important; color:#000 !important; }
      .no-print{ display:none !important; }
      .receipt{ width:100%; }
    }
  </style>
</head>
<body class="py-4">
  <button class="btn btn-outline-secondary toggle no-print" id="toggleMode" title="Toggle mode">
    <i class="bi bi-sun"></i>
  </button>

  <div class="container d-flex justify-content-center">
    <div class="receipt card shadow-sm">
      <div class="card-body">
        <div class="stamp">PAID</div>

        <div class="text-center mb-3">
          <div class="fw-bold fs-5">KASANOVA</div>
          <div class="text-muted small">Struk Pembayaran • Terima kasih 🙏</div>
          <hr>
          <div class="small">
            <div>No: <b>#<?= e($jual['PenjualanID']) ?></b></div>
            <div>Tanggal: <?= e($jual['Tanggal_penjualan']) ?></div>
            <div>Kasir: <?= e($kasirName) ?></div>
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
          <div class="small text-muted">Simpan struk ini ya.</div>
        </div>

        <div class="d-grid gap-2 mt-3 no-print">
          <button class="btn btn-dark" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
          <a class="btn btn-outline-secondary" href="/kasanova/">Kembali ke Menu</a>
        </div>
      </div>
    </div>
  </div>

<script>
const btn = document.getElementById('toggleMode');
btn.addEventListener('click', () => {
  document.body.classList.toggle('light');
  btn.innerHTML = document.body.classList.contains('light') ? '<i class="bi bi-moon-stars"></i>' : '<i class="bi bi-sun"></i>';
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
