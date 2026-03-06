<?php
$title="Pelanggan";
require_once __DIR__ . "/../../partials/header.php";
require_once __DIR__ . "/../../partials/sidebar.php";
require_once __DIR__ . "/../../partials/topbar.php";
$rows=$conn->query("SELECT * FROM pelanggan ORDER BY PelangganID DESC");
?>
<div class="card shadow-sm"><div class="card-body">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="mb-0">Data Pelanggan</h5>
    <a href="create.php" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> Tambah</a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle datatable">
      <thead><tr><th>ID</th><th>Nama</th><th>No. Telp</th><th>Alamat</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
      <?php while($r=$rows->fetch_assoc()): ?>
        <tr>
          <td><?= e($r['PelangganID']) ?></td>
          <td><?= e($r['Nama_pelanggan']) ?></td>
          <td><?= e($r['Nomortelpon']) ?></td>
          <td><?= e($r['Alamat']) ?></td>
          <td class="text-end">
            <a class="btn btn-outline-dark btn-sm" href="edit.php?id=<?= e($r['PelangganID']) ?>"><i class="bi bi-pencil"></i></a>
            <a class="btn btn-outline-danger btn-sm" onclick="return confirm('Hapus data?')" href="delete.php?id=<?= e($r['PelangganID']) ?>"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div></div>
<?php require_once __DIR__ . "/../../partials/footer.php"; ?>