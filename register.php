<?php
// register.php
session_start();
require_once 'db.php'; // pastikan path benar

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = ($_POST['role'] === 'pemilik') ? 'pemilik' : 'pencari';

    // Validasi sederhana
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email tidak valid.";
    if (strlen($password) < 6) $errors[] = "Password minimal 6 karakter.";
    if ($username === '') $errors[] = "Username wajib diisi.";

    if (empty($errors)) {
        // cek email/username sudah terpakai
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $errors[] = "Email atau username sudah terdaftar.";
        } else {
            // hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (username, fullname, email, phone, password_hash, role) VALUES (?, ?, ?, ?, ?, ?)");
            try {
                $ins->execute([$username, $fullname, $email, $phone, $hash, $role]);
                $success = "Registrasi berhasil. Silakan login.";
                // optional: redirect to login after 2s
                // header('Location: login.php'); exit;
            } catch (PDOException $e) {
                $errors[] = "Gagal menyimpan data: " . $e->getMessage();
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container my-5" style="max-width:520px;">
  <div class="card p-4">
    <h3 class="mb-2" style="color:#00BFA6;">Daftar Akun</h3>
    <p class="text-muted mb-3">Buat akun pemilik kos atau pencari (mahasiswa).</p>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <a class="btn btn-primary" href="login.php">Login sekarang</a>
    <?php else: ?>
      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" novalidate>
        <div class="mb-2">
          <label class="form-label">Username</label>
          <input class="form-control" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>

        <div class="mb-2">
          <label class="form-label">Nama lengkap</label>
          <input class="form-control" name="fullname" value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
        </div>

        <div class="mb-2">
          <label class="form-label">Email</label>
          <input class="form-control" name="email" type="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>

        <div class="mb-2">
          <label class="form-label">No. HP (opsional)</label>
          <input class="form-control" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>

        <div class="mb-2">
          <label class="form-label">Password</label>
          <input class="form-control" name="password" type="password" required>
          <div class="form-text">Minimal 6 karakter.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Daftar sebagai</label>
          <select name="role" class="form-select">
            <option value="pencari" <?= (($_POST['role'] ?? '') === 'pencari') ? 'selected' : '' ?>>Pencari / Mahasiswa</option>
            <option value="pemilik" <?= (($_POST['role'] ?? '') === 'pemilik') ? 'selected' : '' ?>>Pemilik Kos</option>
          </select>
        </div>

        <button class="btn btn-primary w-100" type="submit">Daftar</button>
      </form>

      <p class="mt-3 text-center">Sudah punya akun? <a href="login.php">Masuk</a></p>
    <?php endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
