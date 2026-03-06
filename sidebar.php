<?php
$role = $user['Role'] ?? '';
$dash = "/kasanova/pages/kasir/dashboard.php";
if ($role === 'admin') $dash = "/kasanova/pages/admin/dashboard.php";
if ($role === 'owner') $dash = "/kasanova/pages/owner/dashboard.php";

function active($needle){
  $path = $_SERVER['REQUEST_URI'] ?? '';
  return (strpos($path, $needle) !== false) ? "active" : "";
}
?>
<aside class="sidebar vh-100 position-sticky top-0">
  <div class="p-4">
    <div class="d-flex align-items-center gap-2 mb-3">
      <div class="rounded-4 d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.12);">
        <i class="bi bi-shop text-white fs-4"></i>
      </div>
      <div>
        <div class="fw-bold">KASANOVA</div>
        <div class="text-muted small"><?= e($role) ?> • <?= e($user['Username'] ?? '-') ?></div>
      </div>
    </div>

    <div class="nav nav-pills flex-column gap-1">
      <?php if($role === 'owner'): ?>
        <a class="nav-link <?= active("/pages/owner") ?>" href="<?= $dash ?>"><i class="bi bi-person-gear me-2"></i>Dashboard Owner</a>
        <a class="nav-link <?= active('/pages/laporan') ?>" href="/kasanova/pages/laporan/index.php"><i class="bi bi-graph-up-arrow me-2"></i>Laporan</a>

      <?php else: ?>
        <a class="nav-link <?= ($role==='admin'?active('/pages/admin/dashboard'):active('/pages/kasir/dashboard')) ?>" href="<?= $dash ?>">
          <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </a>

        <?php if($role === 'admin'): ?>
          <a class="nav-link <?= active('/pages/petugas') ?>" href="/kasanova/pages/petugas/index.php"><i class="bi bi-people me-2"></i>Petugas</a>
          <a class="nav-link <?= active('/pages/produk') ?>" href="/kasanova/pages/produk/index.php"><i class="bi bi-box-seam me-2"></i>Produk</a>
        <?php endif; ?>

        <a class="nav-link <?= active('/config/pelanggan') ?>" href="/kasanova/config/pelanggan/index.php"><i class="bi bi-person-badge me-2"></i>Pelanggan</a>
        <a class="nav-link <?= active('/pages/transaksi') ?>" href="/kasanova/pages/transaksi/create.php"><i class="bi bi-receipt me-2"></i>Transaksi</a>
        <a class="nav-link <?= active('/pages/laporan') ?>" href="/kasanova/pages/laporan/index.php"><i class="bi bi-graph-up-arrow me-2"></i>Laporan</a>
      <?php endif; ?>

      <hr class="border-light opacity-25 my-3">
      <a class="nav-link" href="/kasanova/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
    </div>
  </div>
</aside>
