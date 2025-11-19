<?php
// kos/detail.php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Pastikan path ke db.php benar (naik satu folder)
require_once __DIR__ . '/../db.php';

$baseUrl = '/NgekosAja.id/';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo "ID kos tidak valid."; exit;
}

// 1. Ambil data Kos + Owner
$stmt = $pdo->prepare("SELECT k.*, u.fullname AS owner_name, u.phone AS owner_phone, u.email AS owner_email FROM kos k JOIN users u ON u.id = k.owner_id WHERE k.id = ? LIMIT 1");
$stmt->execute([$id]);
$kos = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kos) { 
    echo "Kos tidak ditemukan."; 
    $pdo = null; exit; 
}

// 2. Ambil Kamar
$stmt = $pdo->prepare("SELECT * FROM kamar WHERE kos_id = ? ORDER BY id ASC");
$stmt->execute([$id]);
$kamars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Ambil Gambar
$stmt = $pdo->prepare("SELECT filename FROM kos_images WHERE kos_id = ? ORDER BY id ASC");
$stmt->execute([$id]);
$images = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Cek Login & Role
$is_logged_in = !empty($_SESSION['user_id']);
$current_role = $_SESSION['role'] ?? null;

// Logika Tombol Kembali
if ($is_logged_in && $current_role === 'pemilik' && $_SESSION['user_id'] == $kos['owner_id']) {
    $backUrl = $baseUrl . 'dashboard_owner.php';
} else {
    $backUrl = $baseUrl . 'index.php'; // Kembali ke pencarian utama
}

$pdo = null;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Detail - <?= htmlspecialchars($kos['name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Nunito+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root{
            --primary:#00BFA6;
            --secondary:#FF8A65;
            --light:#FFFFFF;
            --bg-light:#F4F8F9;
            --dark:#263238;
            --text-muted:#607D8B;
        }
        *{box-sizing:border-box}
        body{font-family:'Nunito Sans',sans-serif;margin:0;background:var(--bg-light);color:var(--dark)}
        
        .wrap{max-width:1100px;margin:20px auto;padding:0 20px}
        
        /* Header & Nav */
        .topbar{display:flex;justify-content:space-between;align-items:center;padding:15px 0; margin-bottom:10px;}
        .logo{font-family:'Poppins',sans-serif;font-weight:700;font-size:22px;color:var(--primary);text-decoration:none}
        .btn-link{color:var(--primary);text-decoration:none;font-weight:600;}
        
        /* Layout Utama */
        .main-content{display:grid; grid-template-columns: 1fr 340px; gap:25px;}
        @media(max-width:900px){ .main-content{grid-template-columns:1fr;} }

        /* Kartu Putih */
        .card{background:var(--light); border-radius:15px; box-shadow:0 5px 20px rgba(0,0,0,0.05); padding:20px; overflow:hidden;}
        
        /* Gallery */
        .gallery img#mainImg{width:100%; height:400px; object-fit:cover; border-radius:12px; margin-bottom:10px;}
        .thumbs{display:flex; gap:10px; overflow-x:auto;}
        .thumbs img{width:80px; height:60px; object-fit:cover; border-radius:8px; cursor:pointer; opacity:0.7; transition:0.3s;}
        .thumbs img.active, .thumbs img:hover{opacity:1; border:2px solid var(--primary);}

        /* Info Kos */
        .info h1{font-family:'Poppins',sans-serif; margin:0 0 5px; font-size:28px;}
        .meta{color:var(--text-muted); margin-bottom:15px; font-size:15px;}
        .price{font-size:24px; font-weight:700; color:#388E3C; margin-bottom:15px;}
        .desc{line-height:1.6; color:#555;}

        /* Daftar Kamar */
        .kamar-list{margin-top:25px;}
        .kamar-item{
            display:flex; justify-content:space-between; align-items:center;
            padding:15px; border:1px solid #eee; border-radius:10px; margin-bottom:10px;
            background:#fff; transition:transform 0.2s;
        }
        .kamar-item:hover{transform:translateY(-3px); box-shadow:0 5px 15px rgba(0,0,0,0.05);}
        .kamar-info .k-name{font-weight:700; font-size:16px;}
        .kamar-info .k-price{color:var(--text-muted); font-size:14px;}
        
        /* Badges & Buttons */
        .badge{padding:5px 10px; border-radius:6px; font-size:12px; font-weight:700;}
        .badge.kosong{background:#E8F5E9; color:#2E7D32;}
        .badge.penuh{background:#FFEBEE; color:#C62828;}

        .btn{
            padding:10px 18px; border-radius:8px; border:none; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block;
            transition:0.2s;
        }
        .btn-primary{background:var(--secondary); color:#fff;}
        .btn-primary:hover{background:#FF7043; transform:translateY(-2px);}
        .btn-login{background:#eee; color:#555;}

        /* MODAL STYLE (Yang diperbaiki) */
        .modal {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            display: flex; justify-content: center; align-items: center; z-index: 1000;
            visibility: hidden; opacity: 0; transition: visibility 0s, opacity 0.3s;
        }
        .modal.show { visibility: visible; opacity: 1; }
        .modal-box {
            background: var(--light); border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 90%; max-width: 420px; transform: translateY(-20px); transition: transform 0.3s ease-out;
            padding:0;
        }
        .modal.show .modal-box { transform: translateY(0); }
        .modal-header{ padding:20px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; }
        .modal-body{ padding:25px; }
        .modal-footer{ padding:15px 20px; background:#f9f9f9; border-radius:0 0 15px 15px; text-align:right; }
        
        .form-control-modal{ width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:16px; }
        .close-btn{ background:none; border:none; font-size:24px; cursor:pointer; color:#999; }
    </style>
</head>
<body>

<div class="wrap">
    <div class="topbar">
        <a href="<?= $baseUrl ?>index.php" class="logo">NgekosAja.id</a>
        <div>
            <?php if($is_logged_in): ?>
                Hai, <?= htmlspecialchars($_SESSION['fullname'] ?? 'User') ?>
            <?php else: ?>
                <a href="<?= $baseUrl ?>login.php" class="btn-link">Login</a>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-bottom:15px;">
        <a href="<?= htmlspecialchars($backUrl) ?>" class="btn-link">← Kembali</a>
    </div>

    <div class="main-content">
        <div class="left-col">
            <div class="card gallery">
                <?php
                    if (!empty($images)) {
                        $mainSrc = $baseUrl . ltrim($images[0], '/');
                    } else {
                        $mainSrc = "https://picsum.photos/seed/kos{$id}/800/500";
                    }
                ?>
                <img id="mainImg" src="<?= htmlspecialchars($mainSrc) ?>" alt="Foto Utama">
                <div class="thumbs">
                    <?php if($images): foreach($images as $i=>$img): $src=$baseUrl.ltrim($img,'/'); ?>
                        <img src="<?= $src ?>" onclick="changeImg('<?= $src ?>')" class="<?= $i==0?'active':'' ?>">
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <div class="card info" style="margin-top:20px;">
                <h1><?= htmlspecialchars($kos['name']) ?></h1>
                <div class="meta">
                    <?= htmlspecialchars($kos['city']) ?> • <?= htmlspecialchars($kos['type']) ?>
                </div>
                <div class="price">
                    Rp <?= number_format($kos['price'], 0, ',', '.') ?> / bulan
                </div>
                <hr style="border:0; border-top:1px solid #eee; margin:15px 0;">
                <h4>Deskripsi</h4>
                <div class="desc"><?= nl2br(htmlspecialchars($kos['description'])) ?></div>
            </div>

            <div class="card kamar-list">
                <h3 style="margin-top:0;">Pilih Kamar</h3>
                
                <?php if($kamars): foreach($kamars as $k): ?>
                    <div class="kamar-item">
                        <div class="kamar-info">
                            <div class="k-name"><?= htmlspecialchars($k['name']) ?></div>
                            <div class="k-price">Rp <?= number_format($k['price'],0,',','.') ?></div>
                        </div>
                        <div class="kamar-action">
                            <?php if($k['status'] == 'kosong'): ?>
                                <span class="badge kosong" style="margin-right:10px;">Tersedia</span>
                                
                                <?php if($is_logged_in && $current_role === 'pencari'): ?>
                                    <button class="btn btn-primary" onclick="openBooking(<?= $k['id'] ?>, '<?= htmlspecialchars($k['name'], ENT_QUOTES) ?>')">
                                        Ajukan Sewa
                                    </button>
                                <?php elseif(!$is_logged_in): ?>
                                    <a href="<?= $baseUrl ?>login.php" class="btn btn-login">Login utk Sewa</a>
                                <?php endif; ?>

                            <?php else: ?>
                                <span class="badge penuh">Penuh / Terisi</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <p>Belum ada data kamar.</p>
                <?php endif; ?>
            </div>
        </div>

        <aside class="right-col">
            <div class="card">
                <h4 style="margin-top:0;">Pemilik Kos</h4>
                <div style="font-weight:700; margin-bottom:5px;"><?= htmlspecialchars($kos['owner_name']) ?></div>
                <div style="font-size:14px; color:#666;">Hubungi via aplikasi untuk detail lebih lanjut.</div>
            </div>

            <div class="card" style="margin-top:20px;">
                <h4 style="margin-top:0;">Lokasi</h4>
                <?php $addr = rawurlencode($kos['address'] ?? $kos['city'] ?? ''); ?>
                <iframe src="https://maps.google.com/maps?q=<?= $addr ?>&output=embed" style="width:100%;height:200px;border:0;border-radius:8px;"></iframe>
            </div>
        </aside>
    </div>
</div>

<div id="modalBook" class="modal">
    <div class="modal-box">
        <div class="modal-header">
            <h4 style="margin:0;">Ajukan Sewa</h4>
            <button class="close-btn" onclick="closeBooking()">&times;</button>
        </div>
        <form method="post" action="<?= $baseUrl ?>kos/booking.php">
            <div class="modal-body">
                <p style="margin-top:0;">Kamar: <strong id="bkRoomName" style="color:var(--primary)"></strong></p>
                
                <input type="hidden" name="kos_id" value="<?= $id ?>">
                <input type="hidden" name="kamar_id" id="bkKamarId" value="">
                
                <label style="display:block; margin-bottom:8px; font-weight:600;">Mulai Sewa Tanggal:</label>
                <input type="date" name="start_date" required min="<?= date('Y-m-d') ?>" class="form-control-modal">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" style="background:transparent; color:#666;" onclick="closeBooking()">Batal</button>
                <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Ganti Gambar Gallery
    function changeImg(src) {
        document.getElementById('mainImg').src = src;
    }

    // Buka Modal Booking
    function openBooking(kamarId, kamarName) {
        document.getElementById('bkKamarId').value = kamarId;
        document.getElementById('bkRoomName').textContent = kamarName;
        document.getElementById('modalBook').classList.add('show');
    }

    // Tutup Modal
    function closeBooking() {
        document.getElementById('modalBook').classList.remove('show');
    }
    
    // Tutup jika klik di luar box
    window.onclick = function(event) {
        var modal = document.getElementById('modalBook');
        if (event.target == modal) {
            closeBooking();
        }
    }
</script>

</body>
</html>