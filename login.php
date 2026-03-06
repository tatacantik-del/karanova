<?php
require_once __DIR__ . "/config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

// Jika sudah login, lempar sesuai role
if (!empty($_SESSION['user'])) {
  $role = $_SESSION['user']['Role'] ?? '';
  if ($role === 'admin') {
    header("Location: /kasanova/pages/admin/dashboard.php");
  } elseif ($role === 'owner') {
    header("Location: /kasanova/pages/owner/dashboard.php");
  } else {
    header("Location: /kasanova/pages/kasir/dashboard.php");
  }
  exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  $stmt = $conn->prepare("SELECT UserID, Nama_user, Username, Password, Role FROM user WHERE Username=? LIMIT 1");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();

  // NOTE: tetap sama (plaintext) mengikuti project
  if ($row && $password === $row['Password'])  {
    $_SESSION['user'] = $row;

    $role = $row['Role'] ?? '';
    if ($role === 'admin') {
      header("Location: /kasanova/pages/admin/dashboard.php");
    } elseif ($role === 'owner') {
      header("Location: /kasanova/pages/owner/dashboard.php");
    } else {
      header("Location: /kasanova/pages/kasir/dashboard.php");
    }
    exit;
  } else {
    $error = "Username / password salah.";
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login - Kasanova</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/kasanova/assets/css/neo3d.css">

  <style>
    .auth{ min-height:100vh; display:flex; align-items:center; }

    /* ✅ FIX: icon header login biar gak blank */
    .brandMark{
      width:72px;
      height:72px;
      border-radius:20px;
      display:flex;
      align-items:center;
      justify-content:center;

      /* ganti dari putih polos -> gradient biar premium */
      background: linear-gradient(135deg, rgba(124,58,237,.95), rgba(56,189,248,.60));
      border: 1px solid rgba(255,255,255,.18);
      box-shadow: 0 18px 40px rgba(0,0,0,.35), inset 0 1px 0 rgba(255,255,255,.25);
      flex: 0 0 auto;
    }
    .brandMark i{
      font-size: 32px !important;
      color: #ffffff !important; /* ini yang bikin icon pasti keliatan */
      opacity: 1 !important;
      line-height: 1 !important;
      display: inline-block !important;
    }
  </style>
</head>
<body>

  <div class="container auth py-4">
    <div class="row w-100 justify-content-center">
      <div class="col-12 col-md-8 col-lg-5">
        <div class="card">
          <div class="card-body p-4 p-md-5">

            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="brandMark"><i class="bi bi-shield-lock"></i></div>
              <div>
                <div class="fw-bold fs-4">Login Dashboard</div>
                <div class="text-muted">Admin • Kasir • Owner</div>
              </div>
            </div>

            <?php if ($error): ?>
              <div class="alert alert-danger mb-3"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" class="mt-2">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input class="form-control" name="username" autocomplete="username" required />
              </div>

              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" autocomplete="current-password" required />
              </div>

              <button class="btn btn-dark w-100">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
              </button>
            </form>

            <div class="d-flex gap-2 mt-3">
              <a class="btn btn-outline-secondary w-100" href="/kasanova/">
                <i class="bi bi-arrow-left me-1"></i> Menu
              </a>
              <a class="btn btn-outline-secondary w-100" href="/kasanova/order.php">
                <i class="bi bi-bag me-1"></i> Pesan
              </a>
            </div>

            <div class="text-muted small mt-3">
              Default: admin/admin123 • kasir/kasir123 • owner/owner123
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>