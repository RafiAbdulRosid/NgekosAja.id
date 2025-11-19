<?php
// dashboard_user.php
session_start();
require_once __DIR__ . '/db.php';

// Cek apakah user login dan role-nya 'pencari'
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pencari') {
    header('Location: login.php');
    exit;
}

$fullname = $_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Pencari';
$userId = $_SESSION['user_id'];
$baseUrl = '/NgekosAja.id/'; 

// --- LOGIKA MENGAMBIL DATA BOOKING ---
// Kita join tabel bookings dengan kos dan kamar untuk dapat nama & harga
$sql = "
    SELECT b.*, k.name AS kos_name, k.city, k.type, km.name AS kamar_name, km.price
    FROM bookings b
    JOIN kos k ON b.kos_id = k.id
    JOIN kamar km ON b.kamar_id = km.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
// -------------------------------------
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard Pencari - NgekosAja.id</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Nunito+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root{
            --primary:#00BFA6;
            --secondary:#FF8A65;
            --light:#FFFFFF;
            --bg-light:#F4F8F9;
            --dark:#263238;
            --text-muted:#607D8B;
            --success:#2ecc71;
            --warning:#f1c40f;
            --danger:#e74c3c;
        }
        *{box-sizing:border-box}
        body{font-family:'Nunito Sans',sans-serif;margin:0;background:var(--bg-light);color:var(--dark)}
        
        .wrap{max-width:1000px;margin:0 auto;padding:0 20px}
        
        /* Header */
        header{background:var(--light);box-shadow:0 2px 6px rgba(0,0,0,0.05);padding:15px 0;margin-bottom:30px;}
        .navbar{display:flex;justify-content:space-between;align-items:center;}
        .logo{font-family:'Poppins',sans-serif;font-weight:700;font-size:22px;color:var(--primary);text-decoration:none}
        
        /* Buttons */
        .btn{padding:10px 18px;border-radius:10px;text-decoration:none;font-weight:600;cursor:pointer;transition:all .2s;border:none;display:inline-block;}
        .btn-primary{background:var(--secondary);color:#fff;}
        .btn-primary:hover{background:#FF7043;transform:translateY(-2px);}
        .btn-outline{background:transparent;border:1px solid #ddd;color:var(--text-muted);}
        .btn-outline:hover{border-color:var(--dark);color:var(--dark);}
        
        /* Cards */
        .card{background:var(--light);border-radius:15px;padding:25px;box-shadow:0 4px 15px rgba(0,0,0,0.05);margin-bottom:20px;}
        .card h3{margin-top:0;font-family:'Poppins',sans-serif;color:var(--dark);}
        
        /* Welcome Box */
        .welcome-box{
            background: linear-gradient(135deg, var(--primary), #009688);
            color: #fff; border-radius: 15px; padding: 30px; margin-bottom: 30px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 10px 20px rgba(0, 191, 166, 0.2);
        }
        .welcome-text h2{margin:0 0 5px; font-family:'Poppins',sans-serif;}

        /* Empty State */
        .empty-state{text-align:center;padding:40px 20px;}
        .empty-state img{width:100px;margin-bottom:20px;opacity:0.8;}
        .empty-state p{color:var(--text-muted);margin-bottom:20px;}

        /* LIST BOOKING STYLE */
        .booking-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 12px;
            margin-bottom: 15px;
            background: #fff;
            transition: transform 0.2s;
        }
        .booking-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-color: var(--primary);
        }
        .b-info h4 { margin: 0 0 5px; font-family: 'Poppins', sans-serif; font-size: 18px; }
        .b-meta { color: var(--text-muted); font-size: 14px; }
        .b-date { margin-top: 8px; font-weight: 600; color: var(--dark); font-size: 14px; }
        
        /* Status Badges */
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge.pending { background: #FFF3E0; color: #F57C00; border: 1px solid #FFE0B2; }
        .badge.approved { background: #E8F5E9; color: #2E7D32; border: 1px solid #C8E6C9; }
        .badge.rejected { background: #FFEBEE; color: #C62828; border: 1px solid #FFCDD2; }

        @media (max-width: 600px) {
            .booking-item { flex-direction: column; align-items: flex-start; gap: 15px; }
            .welcome-box { flex-direction: column; text-align: center; gap: 15px; }
            .navbar { flex-direction: column; gap: 15px; }
        }
    </style>
</head>
<body>

    <header>
        <div class="wrap navbar">
            <a href="<?= $baseUrl ?>index.php" class="logo">NgekosAja.id</a>
            <div style="display:flex; align-items:center; gap:15px;">
                <span style="display:none; @media(min-width:600px){display:inline;}">
                    Halo, <b><?= htmlspecialchars($fullname) ?></b>
                </span>
                <a href="<?= $baseUrl ?>index.php" class="btn btn-primary">🔍 Cari Kos</a>
                <a href="logout.php" class="btn btn-outline">Logout</a>
            </div>
        </div>
    </header>

    <div class="wrap">
        <div class="welcome-box">
            <div class="welcome-text">
                <h2>Selamat Datang, <?= htmlspecialchars(explode(' ', $fullname)[0]) ?>! 👋</h2>
                <p>Pantau status pengajuan sewa kosmu di sini.</p>
            </div>
        </div>

        <div class="card">
            <h3>🏠 Status Pengajuan Sewa</h3>
            
            <?php if (count($bookings) > 0): ?>
                <div style="margin-top: 20px;">
                    <?php foreach($bookings as $b): ?>
                        <div class="booking-item">
                            <div class="b-info">
                                <h4><?= htmlspecialchars($b['kos_name']) ?></h4>
                                <div class="b-meta">
                                    <?= htmlspecialchars($b['kamar_name']) ?> • 
                                    <?= htmlspecialchars($b['city']) ?> • 
                                    Rp <?= number_format($b['price'], 0, ',', '.') ?>/bln
                                </div>
                                <div class="b-date">
                                    📅 Mulai: <?= date('d M Y', strtotime($b['start_date'])) ?>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <?php 
                                    // Logika Badge Warna
                                    $statusClass = 'pending';
                                    $statusText = 'Menunggu Konfirmasi';
                                    
                                    if ($b['status'] === 'approved') {
                                        $statusClass = 'approved';
                                        $statusText = '✅ Disetujui';
                                    } elseif ($b['status'] === 'rejected') {
                                        $statusClass = 'rejected';
                                        $statusText = '❌ Ditolak';
                                    }
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                
                                <?php if ($b['status'] === 'approved'): ?>
                                    <div style="margin-top: 8px; font-size: 12px; color: var(--success);">
                                        Silakan hubungi pemilik untuk pembayaran.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <div class="empty-state">
                    <div style="font-size:50px;">📂</div> 
                    <h4>Belum ada pengajuan sewa</h4>
                    <p>Kamu belum mengajukan sewa di kos manapun. Yuk mulai cari!</p>
                    <a href="<?= $baseUrl ?>index.php" class="btn btn-primary">Mulai Mencari</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="text-align:center; padding:30px; color:var(--text-muted); font-size:14px;">
        &copy; <?= date('Y') ?> NgekosAja.id — Dashboard Pencari
    </div>

</body>
</html>