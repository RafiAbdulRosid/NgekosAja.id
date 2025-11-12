<?php
// login.php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity'] ?? ''); // email atau username
    $password = $_POST['password'] ?? '';

    if ($identity === '' || $password === '') {
        $error = "Lengkapi email/username dan password.";
    } else {
        // ambil user berdasarkan email atau username
        $stmt = $pdo->prepare("SELECT id, username, fullname, email, password_hash, role FROM users WHERE email = ? OR username = ? LIMIT 1");
        $stmt->execute([$identity, $identity]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // login sukses: set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];

            // redirect sesuai role
            if ($user['role'] === 'pemilik') {
                header('Location: dashboard_owner.php');
                exit;
            } else {
                header('Location: dashboard_user.php');
                exit;
            }
        } else {
            $error = "Email/username atau password salah.";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container my-5" style="max-width:420px;">
  <div class="card p-4">
    <h3 class="mb-1" style="color:#00BFA6;">Masuk ke NgekosAja.id</h3>
    <p class="text-muted mb-3">Masuk sebagai pemilik atau pencari untuk melanjutkan.</p>

    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="post" autocomplete="off">
      <div class="mb-2">
        <label class="form-label">Email atau Username</label>
        <input class="form-control" name="identity" value="<?= htmlspecialchars($_POST['identity'] ?? '') ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <input class="form-control" name="password" type="password" required>
      </div>

      <button class="btn btn-primary w-100" type="submit">Masuk</button>
    </form>

    <p class="mt-3 text-center">Belum punya akun? <a href="register.php">Daftar</a></p>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
