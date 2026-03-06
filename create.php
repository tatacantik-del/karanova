<?php
$title="Tambah Pelanggan";
require_once __DIR__ . "/../../partials/header.php";
require_once __DIR__ . "/../../partials/sidebar.php";
require_once __DIR__ . "/../../partials/topbar.php";
$error="";
if($_SERVER['REQUEST_METHOD']==='POST'){
  $nama=trim($_POST['nama']??'');
  $telp=trim($_POST['telp']??'');
  $alamat=trim($_POST['alamat']??'');
  if($nama==''){ $error="Nama wajib diisi."; }
  else{
    $stmt=$conn->prepare("INSERT INTO pelanggan (Nama_pelanggan,Alamat,Nomortelpon) VALUES (?,?,?)");
    $stmt->bind_param("sss",$nama,$alamat,$telp);
    $stmt->execute();
    header("Location: index.php"); exit;
  }
}
?>
<div class="card shadow-sm"><div class="card-body">
  <?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="row g-3">
    <div class="col-md-6"><label class="form-label">Nama</label><input class="form-control" name="nama" required></div>
    <div class="col-md-6"><label class="form-label">No. Telp</label><input class="form-control" name="telp"></div>
    <div class="col-12"><label class="form-label">Alamat</label><textarea class="form-control" name="alamat" rows="3"></textarea></div>
    <div class="col-12 d-flex gap-2">
      <button class="btn btn-dark"><i class="bi bi-save"></i> Simpan</button>
      <a class="btn btn-outline-secondary" href="index.php">Batal</a>
    </div>
  </form>
</div></div>
<?php require_once __DIR__ . "/../../partials/footer.php"; ?>