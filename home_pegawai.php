<?php
session_start();
if (empty($_SESSION['username'])) {
    header("location:login.php?pesan=belum_login");
  if ($_SESSION['username'] === 'Destiana') {
        header("Location: home_owner.php");
    } elseif (strpos($lower_username, 'pegawai') !== false) {
        header("Location: home_pegawai.php");
    } else {
        header("Location: home2.php");
    }
}


$host       = "localhost";
$user       = "root";
$pass       = "";
$db         = "nailartis";

$koneksi    = mysqli_connect($host, $user, $pass, $db);
if (!$koneksi) {
    die("Gagal terkoneksi ke database!!!");
}

// Initialize variables
$sukses = "";
$error = "";
$id = $_SESSION['username']; // Assuming pegawai_id is stored in session

// Tandai appointment sebagai selesai
if (isset($_POST['mark_complete'])) {
    $appointment_id = $_POST['appointment_id'];
    
    // Update status appointment menjadi 'completed'
    $sql_complete = "UPDATE appointment SET status = 'completed', completed_at = NOW() 
                     WHERE id = '$appointment_id'";
    
    $q_complete = mysqli_query($koneksi, $sql_complete);
    if ($q_complete) {
        $sukses = "Appointment berhasil ditandai sebagai selesai!";
    } else {
        $error = "Gagal menandai appointment sebagai selesai!";
    }
}

// Batalkan status selesai (untuk koreksi jika diperlukan)
if (isset($_POST['mark_pending'])) {
    $appointment_id = $_POST['appointment_id'];
    
    $sql_pending = "UPDATE appointment SET status = 'pending', completed_at = NULL 
                    WHERE id = '$appointment_id'";
    
    $q_pending = mysqli_query($koneksi, $sql_pending);
    if ($q_pending) {
        $sukses = "Status appointment berhasil dikembalikan ke pending!";
    } else {
        $error = "Gagal mengubah status appointment!";
    }
}

// Get pegawai info
$sql_pegawai = "SELECT nama_pegawai FROM pegawai WHERE id = '$id'";
$q_pegawai = mysqli_query($koneksi, $sql_pegawai);
$pegawai_data = mysqli_fetch_array($q_pegawai);
$nama_pegawai = $pegawai_data['nama_pegawai'] ?? 'Pegawai';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <title>JADWAL APPOINTMENT - NailsArtIS</title>
    <style>
        body {
            background: linear-gradient(135deg, #ffeef8, #f8f9fa);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color: black;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            padding-top: 0;
            margin-top: 0;
            margin-left: 40%;
        }

        .navbar-brand img {
            height: 100px;
        }

        .navbar-pilihan {
            gap: 0.5em;
            text-decoration: none;
        }

        .navbar-pilihan li {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-right: 2em;
            border-radius: 0.5em;
        }

        .navbar-pilihan li a {
            text-decoration: none;
            margin-left: 2em;
            color: #fff;
            transition: 0.3s;
        }

        .navbar-pilihan li:hover {
            background-color: #821a4e;
            transition: background-color 1s ease;
        }

        .offcanvas {
            background-color: rgb(214, 52, 132);
        }

        .page-header {
            background: linear-gradient(135deg, #D63484, #821a4e);
            color: white;
            padding: 40px 0;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .main-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .appointment-card {
            background: linear-gradient(135deg, #fff, #ffeef8);
            border: 2px solid #D63484;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .appointment-card.completed {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-color: #28a745;
            opacity: 0.8;
        }

        .appointment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(214, 52, 132, 0.2);
        }

        .appointment-card.completed:hover {
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.2);
        }

        .appointment-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #D63484, #821a4e);
        }

        .appointment-card.completed::before {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .appointment-title {
            color: #D63484;
            font-weight: bold;
            font-size: 1.3rem;
            margin: 0;
        }

        .appointment-card.completed .appointment-title {
            color: #28a745;
        }

        .appointment-id {
            background: linear-gradient(135deg, #D63484, #821a4e);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .appointment-card.completed .appointment-id {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .appointment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            padding: 10px;
            background: rgba(214, 52, 132, 0.1);
            border-radius: 10px;
        }

        .appointment-card.completed .detail-item {
            background: rgba(40, 167, 69, 0.1);
        }

        .detail-item i {
            color: #D63484;
            font-size: 1.2rem;
            margin-right: 10px;
        }

        .appointment-card.completed .detail-item i {
            color: #28a745;
        }

        .detail-content {
            flex: 1;
        }

        .detail-label {
            font-size: 0.85rem;
            color: #666;
            margin: 0;
        }

        .detail-value {
            font-weight: bold;
            color: #333;
            margin: 0;
        }

        .btn-primary {
            background: linear-gradient(135deg, #D63484, #821a4e);
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: bold;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(214, 52, 132, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            border-radius: 20px;
            padding: 8px 15px;
            color: white;
            font-size: 0.85rem;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            border: none;
            border-radius: 20px;
            padding: 8px 15px;
            color: white;
            font-size: 0.85rem;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
            color: white;
        }

        .btn-detail {
            background: linear-gradient(135deg, #6f42c1, #5a32a3);
            border: none;
            border-radius: 20px;
            padding: 8px 15px;
            color: white;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .btn-detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(111, 66, 193, 0.3);
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stats-card {
            background: linear-gradient(135deg, #fff, #ffeef8);
            border: 2px solid #D63484;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(214, 52, 132, 0.2);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #D63484;
            margin: 0;
        }

        .stats-label {
            color: #666;
            font-weight: 500;
            margin-top: 5px;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .no-appointments {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-appointments i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .time-badge {
            background: rgba(214, 52, 132, 0.1);
            color: #D63484;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .today-badge {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-left: 10px;
        }

        .completed-badge {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-left: 10px;
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .modal-header {
            background: linear-gradient(135deg, #D63484, #821a4e);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 20px 30px;
        }

        .modal-title {
            font-weight: bold;
            font-size: 1.3rem;
        }

        .modal-body {
            padding: 30px;
        }

        .detail-row {
            display: flex;
            align-items: flex-start;
            padding: 15px 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #D63484, #821a4e);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .detail-info h6 {
            color: #D63484;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .detail-info p {
            margin: 0;
            color: #666;
        }

        footer {
            background-color: black;
            color: white;
            margin-top: 50px;
        }

        .sosmed {
            display: flex;
            justify-content: center;
            color: black;
        }

        .sosmed a {
            text-decoration: none;
            padding: 5px;
            background-color: palevioletred;
            margin: 10px;
            border-radius: 50%;
        }

        .sosmed a i {
            font-size: 2em sans-serif;
            color: black;
            opacity: 0.9;
        }

        .sosmed a:hover {
            background-color: white;
            transition: 0.5s;
        }

        @media (max-width: 768px) {
            .main-container {
                margin: 10px;
                padding: 15px;
            }
            
            .appointment-card {
                padding: 15px;
            }
            
            .appointment-details {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                justify-content: center;
            }
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- Navigation -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <nav class="navbar navbar">
        <div class="container-fluid">
            <ul class="nav d-flex align-items-center navbar-pilihan" style="height: 5em;">
                <li>
                    <a class="navbar-brand">
                        <img src="logo-bgoff.png" alt="Logo" height="1px" class="d-inline-block align-text-top">
                    </a>
                </li>
            </ul>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDarkNavbar">
                <img src="user.png" width="50px">
            </button>
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasDarkNavbar">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title">Welcome, <?= htmlspecialchars($nama_pegawai) ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link" href="profile_pegawai.php"><b>Profile</b></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="home_pegawai.php"><b>Dashboard</b></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><b>Logout</b></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-calendar-check me-3"></i>JADWAL APPOINTMENT</h1>
            <p class="mb-0">Kelola appointment yang akan dikerjakan</p>
        </div>
    </div>

    <div class="container">
        <!-- Alert Messages -->
        <?php if ($sukses) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= $sukses ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <?php if ($error) { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <!-- Statistics Cards -->
        <?php
        $today = date('Y-m-d');
        $sql_stats = "SELECT 
            COUNT(*) as total_appointments,
            COUNT(CASE WHEN tanggal = '$today' AND (status IS NULL OR status != 'completed') THEN 1 END) as today_appointments,
            COUNT(CASE WHEN tanggal > '$today' AND (status IS NULL OR status != 'completed') THEN 1 END) as upcoming_appointments,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_appointments
            FROM appointment";
        $q_stats = mysqli_query($koneksi, $sql_stats);
        $stats = mysqli_fetch_array($q_stats);
        if (!$stats) {
            $stats = ['total_appointments' => 0, 'today_appointments' => 0, 'upcoming_appointments' => 0, 'completed_appointments' => 0];
        }
        ?>
        
        <div class="stats-container">
            <div class="stats-card">
                <div class="stats-number"><?= $stats['total_appointments'] ?></div>
                <div class="stats-label">Total Appointment</div>
            </div>
            <div class="stats-card">
                <div class="stats-number"><?= $stats['today_appointments'] ?></div>
                <div class="stats-label">Hari Ini</div>
            </div>
            <div class="stats-card">
                <div class="stats-number"><?= $stats['upcoming_appointments'] ?></div>
                <div class="stats-label">Mendatang</div>
            </div>
            <div class="stats-card">
                <div class="stats-number"><?= $stats['completed_appointments'] ?></div>
                <div class="stats-label">Selesai</div>
            </div>
        </div>

        <div class="main-container">
            <!-- Appointments List -->
            <?php
            $sql_appointments = "SELECT *, 
                               CASE 
                                   WHEN status = 'completed' THEN 1
                                   WHEN tanggal = '$today' THEN 2
                                   WHEN tanggal > '$today' THEN 3
                                   ELSE 4
                               END as sort_order
                               FROM appointment 
                               ORDER BY sort_order ASC, tanggal ASC, id ASC";
            $q_appointments = mysqli_query($koneksi, $sql_appointments);

            if (mysqli_num_rows($q_appointments) > 0) {
                while ($appointment = mysqli_fetch_array($q_appointments)) {
                    $appointment_date = date("d M Y", strtotime($appointment['tanggal']));
                    $is_today = ($appointment['tanggal'] == $today);
                    $is_past = ($appointment['tanggal'] < $today);
                    $is_upcoming = ($appointment['tanggal'] > $today);
                    $is_completed = ($appointment['status'] == 'completed');
                    $completed_at = $appointment['completed_at'] ? date("d M Y H:i", strtotime($appointment['completed_at'])) : null;
            ?>
                <div class="appointment-card <?= $is_completed ? 'completed' : '' ?>">
                    <div class="appointment-header">
                        <h5 class="appointment-title">
                            <i class="fas fa-user me-2"></i><?= htmlspecialchars($appointment['nama']) ?>
                            <?php if ($is_completed) { ?>
                                <i class="fas fa-check-circle ms-2" style="color: #28a745;"></i>
                            <?php } ?>
                        </h5>
                        <div class="d-flex gap-2 align-items-center">
                            <button class="btn btn-detail btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal<?= $appointment['id'] ?>">
                                <i class="fas fa-info-circle me-1"></i>Detail
                            </button>
                            <div class="appointment-id">ID #<?= $appointment['id'] ?></div>
                        </div>
                    </div>

                    <div class="appointment-details">
                        <div class="detail-item">
                            <i class="fas fa-phone"></i>
                            <div class="detail-content">
                                <p class="detail-label">No. Telepon</p>
                                <p class="detail-value">
                                    <a href="https://wa.me/<?= $appointment['noHP'] ?>" target="_blank" class="text-decoration-none">
                                        <?= $appointment['noHP'] ?>
                                    </a>
                                </p>
                            </div>
                        </div>

                        <div class="detail-item">
                            <i class="fas fa-spa"></i>
                            <div class="detail-content">
                                <p class="detail-label">Jenis Layanan</p>
                                <p class="detail-value"><?= htmlspecialchars($appointment['service']) ?></p>
                            </div>
                        </div>

                        <div class="detail-item">
                            <i class="fas fa-calendar-alt"></i>
                            <div class="detail-content">
                                <p class="detail-label">Tanggal Appointment</p>
                                <p class="detail-value">
                                    <?= $appointment_date ?>
                                    <?php if ($is_completed) { ?>
                                        <span class="completed-badge">SELESAI</span>
                                    <?php } elseif ($is_today) { ?>
                                        <span class="today-badge">HARI INI</span>
                                    <?php } elseif ($is_past) { ?>
                                        <span class="badge bg-secondary ms-2">LEWAT</span>
                                    <?php } elseif ($is_upcoming) { ?>
                                        <span class="badge bg-info ms-2">MENDATANG</span>
                                    <?php } ?>
                                </p>
                            </div>
                        </div>

                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <div class="detail-content">
                                <p class="detail-label">Status</p>
                                <p class="detail-value">
                                    <?php if ($is_completed) { ?>
                                        <span class="badge bg-success">Selesai</span>
                                        <?php if ($completed_at) { ?>
                                            <small class="text-muted d-block">Diselesaikan: <?= $completed_at ?></small>
                                        <?php } ?>
                                    <?php } elseif ($is_today) { ?>
                                        <span class="badge bg-warning">Hari Ini</span>
                                    <?php } else { ?>
                                        <span class="badge bg-primary">Terjadwal</span>
                                    <?php } ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <?php if (!$is_completed) { ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin appointment ini sudah selesai dikerjakan?')">
                                <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
                                <button type="submit" name="mark_complete" class="btn btn-success btn-sm">
                                    <i class="fas fa-check me-1"></i>Tandai Selesai
                                </button>
                            </form>
                        <?php } else { ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status kembali ke pending?')">
                                <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
                                <button type="submit" name="mark_pending" class="btn btn-warning btn-sm">
                                    <i class="fas fa-undo me-1"></i>Kembalikan ke Pending
                                </button>
                            </form>
                        <?php } ?>
                        
                        <a href="https://wa.me/<?= $appointment['noHP'] ?>" target="_blank" class="btn btn-success btn-sm">
                            <i class="fab fa-whatsapp me-1"></i>Hubungi Customer
                        </a>
                    </div>
                </div>

                <!-- Detail Modal -->
                <div class="modal fade" id="detailModal<?= $appointment['id'] ?>" tabindex="-1" aria-labelledby="detailModalLabel<?= $appointment['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailModalLabel<?= $appointment['id'] ?>">
                                    <i class="fas fa-info-circle me-2"></i>Detail Appointment #<?= $appointment['id'] ?>
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="detail-info">
                                        <h6>Nama Customer</h6>
                                        <p><?= htmlspecialchars($appointment['nama']) ?></p>
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div class="detail-info">
                                        <h6>Nomor Telepon</h6>
                                        <p>
                                            <a href="https://wa.me/<?= $appointment['noHP'] ?>" target="_blank" class="text-decoration-none">
                                                <?= $appointment['noHP'] ?>
                                            </a>
                                        </p>
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <i class="fas fa-spa"></i>
                                    </div>
                                    <div class="detail-info">
                                        <h6>Jenis Layanan</h6>
                                        <p><?= htmlspecialchars($appointment['service']) ?></p>
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="detail-info">
                                        <h6>Tanggal Appointment</h6>
                                        <p><?= $appointment_date ?></p>
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="detail-info">
                                        <h6>Status Appointment</h6>
                                        <p>
                                            <?php if ($is_past) { ?>
                                                <span class="badge bg-success">Appointment Selesai</span>
                                            <?php } elseif ($is_today) { ?>
                                                <span class="badge bg-warning">Appointment Hari Ini</span>
                                            <?php } else { ?>
                                                <span class="badge bg-primary">Appointment Terjadwal</span>
                                            <?php } ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                <?php if (!$is_past) { ?>
                                <a href="https://wa.me/<?= $appointment['noHP'] ?>" target="_blank" class="btn btn-success">
                                    <i class="fab fa-whatsapp me-1"></i>Hubungi Customer
                                </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php 
                }
            } else { 
            ?>
                <div class="no-appointments">
                    <i class="fas fa-calendar-times"></i>
                    <h4>Belum Ada Appointment</h4>
                    <p>Tidak ada appointment yang terdaftar saat ini.</p>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-body-black text-center text-lg-center mt-5 no-print">
        <div class="container-fluid p-4">
            <div class="row">
                <div class="col-lg-4 col-md-12 mb-4 mb-md-0">
                    <div class="container-fluid">
                        <a class="navbar-brand" href="home.php">
                            <img src="logo-bgoff.png" alt="Logo" class="d-inline-block align-text-top">
                        </a>
                        <div class="brand-text">
                            <p>NailsArtIS</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 mb-4 mb-md-0">
                    <h5 class="text-uppercase">Contact Us :</h5>
                    <div class="sosmed">
                        <a href="https://www.instagram.com/destyninth_?igsh=MWp4b3c3dHNhMmJ1bQ=="><i class="fa-brands fa-instagram"></i></a>
                        <a href="https://wa.me/6281270707067"><i class="fa-brands fa-whatsapp"></i></a>
                        <a href="https://www.tiktok.com/@karmalogy/video/7372133019739524357?_t=8mcH8OZoEBF&_r=1"><i class="fa-brands fa-tiktok"></i></a>
                    </div>
                    <p>&copy; 2024 NailArtIS</p>
                </div>
                <div class="col-lg-4 col-md-12 mb-4 mb-md-0">
                    <h5 class="text-uppercase">About Us :</h5>
                    <p class="text-justify">
                        Nails Art Is – tempat di mana kreativitas bertemu dengan kesempurnaan. Di Nails Art Is, kami percaya bahwa kuku bukan hanya pernyataan kecantikan, tetapi juga kanvas untuk mengekspresikan individualitas dan gaya.
                    </p>
                    <table>
                        <tr>
                            <td>Alamat</td>
                            <td>: Babarsari, Depok, Kab. Sleman, D.I. Yogyakarta</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>: NailsArtIS@gmail.com</td>
                        </tr>
                        <tr>
                            <td>No. Hp</td>
                            <td>: +6281270707067</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>