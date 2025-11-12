<?php
// dashboard_owner.php
session_start();
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'pemilik') {
    header('Location: login.php');
    exit;
}
require_once 'db.php';
include 'includes/header.php';
?>
<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Dashboard Pemilik</h3>
    <div>
      <span class="me-3">Halo, <?= htmlspecialchars($_SESSION['fullname'] ?: $_SESSION['username']) ?></span>
      <a class="btn btn-outline-secondary" href="logout.php">Logout</a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-md-4">
      <div class="card p-3">
        <h5>Kelola Kos</h5>
        <p><a class="btn btn-primary" href="kos/add.php">Tambah Kos Baru</a></p>
      </div>
    </div>
    <div class="col-md-8">
      <div class="card p-3">
        <h5>Daftar Kos Milik Anda</h5>
        <p>(Di sini nanti tampil daftar kos yang dimiliki pemilik dan tombol edit/hapus)</p>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
