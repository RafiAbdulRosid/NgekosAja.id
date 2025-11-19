<?php
// kos/booking.php
session_start();
require_once __DIR__ . '/../db.php';

// 1. Cek Login & Role
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pencari') {
    // Jika bukan pencari, tendang ke login
    header('Location: ../login.php');
    exit;
}

// 2. Proses Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id   = $_SESSION['user_id'];
    $kos_id    = (int)$_POST['kos_id'];
    $kamar_id  = (int)$_POST['kamar_id'];
    $start_date = $_POST['start_date'];

    // Validasi sederhana
    if ($kos_id <= 0 || $kamar_id <= 0 || empty($start_date)) {
        echo "<script>alert('Data tidak lengkap!'); window.history.back();</script>";
        exit;
    }

    // 3. Cek apakah sudah pernah mengajukan kamar INI dan statusnya masih pending?
    // Agar tidak spam tombol booking berkali-kali
    $check = $pdo->prepare("SELECT id FROM bookings WHERE user_id = ? AND kamar_id = ? AND status = 'pending'");
    $check->execute([$user_id, $kamar_id]);
    if ($check->fetch()) {
        echo "<script>alert('Anda sudah mengajukan sewa untuk kamar ini. Tunggu konfirmasi pemilik.'); window.location='../dashboard_user.php';</script>";
        exit;
    }

    // 4. Simpan ke Database
    try {
        $sql = "INSERT INTO bookings (user_id, kos_id, kamar_id, start_date, status) VALUES (?, ?, ?, ?, 'pending')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $kos_id, $kamar_id, $start_date]);

        // Update status kamar jadi 'booked' (Opsional: atau biarkan tetap kosong sampai disetujui pemilik)
        // Untuk saat ini kita biarkan status kamar tetap 'kosong' sampai pemilik menyetujui (approve).

        // Redirect Sukses
        echo "<script>
            alert('Pengajuan berhasil dikirim! Silakan cek dashboard untuk status.');
            window.location = '../dashboard_user.php';
        </script>";
        exit;

    } catch (PDOException $e) {
        echo "Terjadi kesalahan: " . $e->getMessage();
        exit;
    }
} else {
    // Jika akses langsung ke file ini tanpa POST
    header('Location: ../index.php');
}