<?php
// dashboard_user.php
session_start();
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'pencari') {
    header('Location: login.php');
    exit;
}
require_once 'db.php';
include 'includes/header.php';
?>
<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Dashboard Pencari</h3>
    <div>
      <span class="me-3">Halo, <?= htmlspecialchars($_SESSION['fullname'] ?: $_SESSION['username']) ?></span>
      <a class="btn btn-outline-secondary" href="logout.php">Logout</a>
    </div>
  </div>

  <div class="card p-3">
    <h5>Favorit & Pengajuan</h5>
    <p>Di sini nanti muncul kos yang disimpan, status pengajuan booking, dan review.</p>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
