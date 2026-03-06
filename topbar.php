<main class="flex-grow-1">
  <div class="p-4 p-md-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
      <div>
        <div class="text-muted small">Kasir Restoran</div>
        <h2 class="mb-0"><?= e($title) ?></h2>
      </div>
      <div class="d-flex align-items-center gap-2">
        <div class="glass px-3 py-2 rounded-4">
          <i class="bi bi-person-circle me-1"></i>
          <span class="fw-semibold"><?= e($user['Username'] ?? '-') ?></span>
        </div>
        <a class="btn btn-outline-dark" href="/kasanova/logout.php"><i class="bi bi-box-arrow-right"></i></a>
      </div>
    </div>
