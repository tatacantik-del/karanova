<?php
$title="Dashboard Admin";
require_once __DIR__ . "/../../partials/header.php";
require_once __DIR__ . "/../../partials/sidebar.php";
require_once __DIR__ . "/../../partials/topbar.php";

function rupiah($n){ return "Rp ".number_format((int)$n,0,',','.'); }

$today = date('Y-m-d');
$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-d');

$q1 = $conn->prepare("SELECT COALESCE(SUM(Total_harga),0) AS total, COUNT(*) AS cnt FROM penjualan WHERE Tanggal_penjualan=?");
$q1->bind_param("s",$today); $q1->execute(); $kpiToday=$q1->get_result()->fetch_assoc();

$q2 = $conn->prepare("SELECT COALESCE(SUM(Total_harga),0) AS total, COUNT(*) AS cnt FROM penjualan WHERE Tanggal_penjualan BETWEEN ? AND ?");
$q2->bind_param("ss",$monthStart,$monthEnd); $q2->execute(); $kpiMonth=$q2->get_result()->fetch_assoc();

$produkCnt = (int)($conn->query("SELECT COUNT(*) c FROM produk")->fetch_assoc()['c'] ?? 0);
$pelangganCnt = (int)($conn->query("SELECT COUNT(*) c FROM pelanggan")->fetch_assoc()['c'] ?? 0);

$stokTipis = $conn->query("SELECT Nama_produk, Stok FROM produk WHERE Stok <= 5 ORDER BY Stok ASC LIMIT 6");

$best = $conn->query("
  SELECT pr.Nama_produk, SUM(dp.JumlahProduk) AS qty
  FROM detailpenjualan dp
  JOIN produk pr ON pr.ProdukID = dp.ProdukID
  GROUP BY dp.ProdukID, pr.Nama_produk
  ORDER BY qty DESC
  LIMIT 5
");

$last = $conn->query("
  SELECT p.PenjualanID, p.Tanggal_penjualan, p.Total_harga
  FROM penjualan p
  ORDER BY p.PenjualanID DESC
  LIMIT 8
");

$labels=[]; $values=[];
for($i=6;$i>=0;$i--){
  $d = date('Y-m-d', strtotime("-$i day"));
  $labels[] = $d;
  $st = $conn->prepare("SELECT COALESCE(SUM(Total_harga),0) AS total FROM penjualan WHERE Tanggal_penjualan=?");
  $st->bind_param("s",$d); $st->execute();
  $values[] = (int)($st->get_result()->fetch_assoc()['total'] ?? 0);
}
?>
<div class="neo-shell">

  <div class="neo-card tilt p-4 mb-3">
    <div class="lift">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <div class="neo-title fs-4">Dashboard Admin</div>
          <div class="neo-muted">Statistik lengkap + kontrol sistem</div>
        </div>
        <div class="neo-chip"><i class="bi bi-stars"></i> 3D Glass Neon</div>
      </div>

      <div class="neo-kpi mt-3">
        <div class="k">
          <div class="lbl">Pendapatan Hari Ini</div>
          <div class="val"><?= rupiah($kpiToday['total'] ?? 0) ?></div>
          <div class="neo-muted small"><?= (int)($kpiToday['cnt'] ?? 0) ?> transaksi</div>
        </div>
        <div class="k">
          <div class="lbl">Pendapatan Bulan Ini</div>
          <div class="val"><?= rupiah($kpiMonth['total'] ?? 0) ?></div>
          <div class="neo-muted small"><?= (int)($kpiMonth['cnt'] ?? 0) ?> transaksi</div>
        </div>
        <div class="k">
          <div class="lbl">Total Produk</div>
          <div class="val"><?= $produkCnt ?></div>
          <div class="neo-muted small">pantau stok</div>
        </div>
        <div class="k">
          <div class="lbl">Total Pelanggan</div>
          <div class="val"><?= $pelangganCnt ?></div>
          <div class="neo-muted small">database pelanggan</div>
        </div>
      </div>

      <div class="mt-3 d-flex gap-2 flex-wrap">
        <a class="btn neo-btn neo-btn-primary" href="/kasanova/pages/transaksi/create.php"><i class="bi bi-receipt"></i> Buat Transaksi</a>
        <a class="btn neo-btn" href="/kasanova/pages/laporan/index.php"><i class="bi bi-graph-up"></i> Laporan</a>
        <a class="btn neo-btn" href="/kasanova/pages/produk/index.php"><i class="bi bi-box-seam"></i> Produk</a>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-7">
      <div class="neo-card tilt p-4 h-100">
        <div class="lift">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="neo-title">Grafik 7 Hari Terakhir</div>
            <div class="neo-muted small">pendapatan per hari</div>
          </div>
          <canvas id="chart7" height="120"></canvas>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5">
      <div class="neo-card tilt p-4 h-100">
        <div class="lift">
          <div class="neo-title mb-2">Stok Menipis</div>
          <div class="table-responsive">
            <table class="table neo-table table-sm align-middle">
              <thead><tr><th>Produk</th><th class="text-end">Stok</th></tr></thead>
              <tbody>
              <?php if($stokTipis && $stokTipis->num_rows): while($s=$stokTipis->fetch_assoc()): ?>
                <tr>
                  <td><?= e($s['Nama_produk']) ?></td>
                  <td class="text-end fw-bold"><?= (int)$s['Stok'] ?></td>
                </tr>
              <?php endwhile; else: ?>
                <tr><td colspan="2" class="neo-muted">Aman ✅</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="neo-card tilt p-4 h-100">
        <div class="lift">
          <div class="neo-title mb-2">Best Seller Top 5</div>
          <div class="table-responsive">
            <table class="table neo-table table-sm align-middle">
              <thead><tr><th>Menu</th><th class="text-end">Terjual</th></tr></thead>
              <tbody>
              <?php if($best && $best->num_rows): while($b=$best->fetch_assoc()): ?>
                <tr>
                  <td><?= e($b['Nama_produk']) ?></td>
                  <td class="text-end fw-bold"><?= (int)$b['qty'] ?> pcs</td>
                </tr>
              <?php endwhile; else: ?>
                <tr><td colspan="2" class="neo-muted">Belum ada data.</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="neo-card tilt p-4 h-100">
        <div class="lift">
          <div class="neo-title mb-2">Transaksi Terakhir</div>
          <div class="table-responsive">
            <table class="table neo-table table-sm align-middle">
              <thead><tr><th>ID</th><th>Tanggal</th><th class="text-end">Total</th></tr></thead>
              <tbody>
              <?php if($last && $last->num_rows): while($t=$last->fetch_assoc()): ?>
                <tr>
                  <td>#<?= e($t['PenjualanID']) ?></td>
                  <td><?= e($t['Tanggal_penjualan']) ?></td>
                  <td class="text-end fw-bold"><?= rupiah($t['Total_harga']) ?></td>
                </tr>
              <?php endwhile; else: ?>
                <tr><td colspan="3" class="neo-muted">Belum ada transaksi.</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels7 = <?= json_encode($labels) ?>;
const values7 = <?= json_encode($values) ?>;
new Chart(document.getElementById('chart7'), {
  type: 'line',
  data: { labels: labels7, datasets: [{ label: 'Pendapatan', data: values7, tension: .35 }] },
  options: { plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true } } }
});
</script>

<?php require_once __DIR__ . "/../../partials/footer.php"; ?>