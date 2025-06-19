<?php
session_start();
if (empty($_SESSION['username'])) {
    header("location:login.php?pesan=belum_login");
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
$nama_pegawai = "";
$no_hp = "";
$alamat = "";
$tanggal_masuk = "";
$sukses = "";
$error = "";

if (isset($_GET['op'])) {
    $op = $_GET['op'];
} else {
    $op = "";
}

// DELETE operation - hanya untuk pegawai tidak aktif
if ($op == 'delete') {
    $id = $_GET['id'];
    // Cek dulu apakah pegawai tidak aktif
    $check_sql = "SELECT status FROM pegawai WHERE id = '$id'";
    $check_result = mysqli_query($koneksi, $check_sql);
    $check_data = mysqli_fetch_array($check_result);
    
    if ($check_data && $check_data['status'] == 'Tidak Aktif') {
        $sql1 = "DELETE FROM pegawai WHERE id = '$id'";
        $q1 = mysqli_query($koneksi, $sql1);
        if ($q1) {
            $sukses = "Data pegawai berhasil dihapus";
        } else {
            $error = "Gagal menghapus data pegawai";
        }
    } else {
        $error = "Hanya pegawai yang tidak aktif yang dapat dihapus!";
    }
}

// EDIT operation - Load data for editing
if ($op == 'edit') {
    $id = $_GET['id'];
    $sql1 = "SELECT * FROM pegawai WHERE id = '$id'";
    $q1 = mysqli_query($koneksi, $sql1);
    $r1 = mysqli_fetch_array($q1);
    
    if ($r1) {
        $nama_pegawai = $r1['nama_pegawai'];
        $no_hp = $r1['no_hp'];
        $alamat = $r1['alamat'];
        $tanggal_masuk = $r1['tanggal_masuk'];
    } else {
        $error = "Data pegawai tidak ditemukan";
    }
}

// UPDATE STATUS operation - toggle antara Aktif dan Tidak Aktif
if ($op == 'toggle_status') {
    $id = $_GET['id'];
    $current_status = $_GET['current_status'];
    $new_status = ($current_status == 'Aktif') ? 'Tidak Aktif' : 'Aktif';
    
    $sql1 = "UPDATE pegawai SET status='$new_status' WHERE id='$id'";
    $q1 = mysqli_query($koneksi, $sql1);
    if ($q1) {
        $sukses = "Status pegawai berhasil diubah menjadi $new_status";
    } else {
        $error = "Gagal mengubah status pegawai";
    }
}

// CREATE & UPDATE operations
if (isset($_POST['simpan'])) {
    $nama_pegawai = trim($_POST['nama_pegawai']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    $tanggal_masuk = $_POST['tanggal_masuk'];

    if ($nama_pegawai && $no_hp && $tanggal_masuk) {
        if ($op == 'edit') {
            // UPDATE operation
            $id = $_GET['id'];
            $sql1 = "UPDATE pegawai SET 
                    nama_pegawai='$nama_pegawai', 
                    no_hp='$no_hp', 
                    alamat='$alamat', 
                    tanggal_masuk='$tanggal_masuk'
                    WHERE id='$id'";
            $q1 = mysqli_query($koneksi, $sql1);
            if ($q1) {
                $sukses = "Data pegawai berhasil diupdate";
                // Reset form
                $nama_pegawai = $no_hp = $alamat = $tanggal_masuk = "";
            } else {
                $error = "Gagal memperbarui data pegawai";
            }
        } else {
            // CREATE operation - semua pegawai baru otomatis Nail Artist dan Aktif
            $sql1 = "INSERT INTO pegawai (nama_pegawai, jabatan, no_hp, alamat, tanggal_masuk, status) 
                    VALUES ('$nama_pegawai', 'Nail Artist', '$no_hp', '$alamat', '$tanggal_masuk', 'Aktif')";
            $q1 = mysqli_query($koneksi, $sql1);
            if ($q1) {
                $sukses = "Pegawai Nail Artist baru berhasil ditambahkan";
                // Reset form
                $nama_pegawai = $no_hp = $alamat = $tanggal_masuk = "";
            } else {
                $error = "Gagal menambahkan data pegawai";
            }
        }
    } else {
        $error = "Silakan lengkapi data Nama, No. HP, dan Tanggal Masuk";
    }
}

// Create table if not exists
$create_table = "CREATE TABLE IF NOT EXISTS pegawai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_pegawai VARCHAR(100) NOT NULL,
    jabatan VARCHAR(50) NOT NULL DEFAULT 'Nail Artist',
    no_hp VARCHAR(15) NOT NULL,
    alamat TEXT,
    tanggal_masuk DATE NOT NULL,
    status ENUM('Aktif', 'Tidak Aktif') NOT NULL DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($koneksi, $create_table);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <title>MANAJEMEN PEGAWAI - NailsArtIS</title>
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

        .form-section {
            background: linear-gradient(135deg, #fff, #ffeef8);
            border: 2px solid #D63484;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #D63484, #821a4e);
        }

        .form-title {
            color: #D63484;
            font-weight: bold;
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .form-title i {
            margin-right: 10px;
            font-size: 1.8rem;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #D63484;
            box-shadow: 0 0 0 0.2rem rgba(214, 52, 132, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #D63484, #821a4e);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: bold;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(214, 52, 132, 0.3);
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            border: none;
            border-radius: 20px;
            padding: 8px 15px;
            transition: all 0.3s ease;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            border-radius: 20px;
            padding: 8px 15px;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            border-radius: 20px;
            padding: 8px 15px;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            border: none;
            border-radius: 20px;
            padding: 8px 15px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.4);
        }

        .table-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .table-title {
            color: #D63484;
            font-weight: bold;
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .table-title i {
            margin-right: 10px;
            font-size: 1.8rem;
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .table thead th {
            background: linear-gradient(135deg, #D63484, #821a4e);
            color: white;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            padding: 15px;
            text-align: center;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
            text-align: center;
        }

        .table tbody tr:hover {
            background-color: #ffeef8;
            transition: all 0.3s ease;
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .status-aktif {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .status-tidak-aktif {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .jabatan-badge {
            background: linear-gradient(135deg, #D63484, #821a4e);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
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

        .stats-card {
            background: linear-gradient(135deg, #fff, #ffeef8);
            border: 2px solid #D63484;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(214, 52, 132, 0.2);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #D63484;
            margin: 0;
        }

        .stats-label {
            color: #666;
            font-weight: 500;
            margin-top: 5px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .no-data i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
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
            
            .form-section {
                padding: 15px;
            }
            
            .table-responsive {
                font-size: 0.85rem;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- Navigation -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <nav class="navbar navbar">
        <div class="container-fluid">
            <ul class="nav  d-flex align-items-center navbar-pilihan" style="height: 5em;">
                <li> <a class="navbar-brand">
                        <img src="logo-bgoff.png" alt="Logo" height="1px" class="d-inline-block align-text-top">
                    </a></li>
                <li>
                    <a aria-current="home" href="home_owner.php">Home</a>
                </li>
                <li>
                    <a href="pegawai.php">Pegawai</a>
                </li>
                <li>
                    <a href="laporan.php">Laporan</a>
                </li>
                <li>
                    <a href="inspirasi_owner.php">Katalog</a>
                </li>
            </ul>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar" aria-label="Toggle navigation">
                <img src="user.png" width="50px">
            </button>
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasDarkNavbar" aria-labelledby="offcanvasDarkNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasDarkNavbarLabel">Welcome, BOSS <?= $_SESSION['username'] ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body bg">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link" href="kataloginput.php">
                                <b>Add Katalog</b>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="home_owner.php">
                                <b>Beranda</b>
                            </a>
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
            <h1><i class="fas fa-users me-3"></i>MANAJEMEN NAIL ARTIST</h1>
            <p class="mb-0">Kelola Data Nail Artist NailsArtIS</p>
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
        <div class="row mb-4">
            <?php
            $sql_stats = "SELECT 
                COUNT(*) as total_pegawai,
                COUNT(CASE WHEN status = 'Aktif' THEN 1 END) as pegawai_aktif,
                COUNT(CASE WHEN status = 'Tidak Aktif' THEN 1 END) as pegawai_tidak_aktif
                FROM pegawai";
            $q_stats = mysqli_query($koneksi, $sql_stats);
            $stats = mysqli_fetch_array($q_stats);
            if (!$stats) {
                $stats = ['total_pegawai' => 0, 'pegawai_aktif' => 0, 'pegawai_tidak_aktif' => 0];
            }
            ?>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['total_pegawai'] ?></div>
                    <div class="stats-label">Total Nail Artist</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['pegawai_aktif'] ?></div>
                    <div class="stats-label">Sedang Bekerja</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['pegawai_tidak_aktif'] ?></div>
                    <div class="stats-label">Tidak Aktif</div>
                </div>
            </div>
        </div>

        <div class="main-container">
            <!-- Form Section -->
            <div class="form-section">
                <div class="form-title">
                    <i class="fas fa-user-plus"></i>
                    <?= ($op == 'edit') ? 'Edit Data Nail Artist' : 'Tambah Nail Artist Baru' ?>
                </div>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama_pegawai" class="form-label fw-bold">
                                <i class="fas fa-user me-1"></i>Nama Lengkap *
                            </label>
                            <input type="text" class="form-control" id="nama_pegawai" name="nama_pegawai" 
                                   value="<?= htmlspecialchars($nama_pegawai) ?>" placeholder="Masukkan nama lengkap" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="no_hp" class="form-label fw-bold">
                                <i class="fas fa-phone me-1"></i>No. HP/WhatsApp *
                            </label>
                            <input type="text" class="form-control" id="no_hp" name="no_hp" 
                                   value="<?= htmlspecialchars($no_hp) ?>" placeholder="08xxxxxxxxxx" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_masuk" class="form-label fw-bold">
                                <i class="fas fa-calendar me-1"></i>Tanggal Masuk Kerja *
                            </label>
                            <input type="date" class="form-control" id="tanggal_masuk" name="tanggal_masuk" 
                                   value="<?= $tanggal_masuk ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-briefcase me-1"></i>Jabatan
                            </label>
                            <input type="text" class="form-control" value="Nail Artist" readonly style="background-color: #f8f9fa;">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="alamat" class="form-label fw-bold">
                            <i class="fas fa-map-marker-alt me-1"></i>Alamat Lengkap
                        </label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" 
                                  placeholder="Masukkan alamat lengkap (opsional)"><?= htmlspecialchars($alamat) ?></textarea>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" name="simpan" class="btn btn-primary me-2">
                            <i class="fas fa-save me-1"></i>
                            <?= ($op == 'edit') ? 'Update Data' : 'Simpan Data' ?>
                        </button>
                        
                        <?php if ($op == 'edit') { ?>
                            <a href="pegawai.php" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                        <?php } ?>
                    </div>
                </form>
            </div>

<table class="table">
            <thead>
                <tr class="table-danger">
                    <th scope="col">#</th>
                    <th scope="col">Nama Pegawai</th>
                    <th scope="col">No HP</th>
                    <th scope="col">Alamat</th>
                    <th scope="col">Tgl Masuk</th>
                    <th scope="col">Status</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
    <?php
    $sql2 = "SELECT * FROM pegawai ORDER BY status ASC, nama_pegawai ASC";
    $q2 = mysqli_query($koneksi, $sql2);
    $urut = 1;

    if (mysqli_num_rows($q2) > 0) {
        while ($r2 = mysqli_fetch_array($q2)) {
            $id = $r2['id'];
            $nama_pegawai = $r2['nama_pegawai'];
            $no_hp = $r2['no_hp'];
            $alamat = $r2['alamat'];
            $tanggal_masuk = date("d M Y", strtotime($r2['tanggal_masuk']));
            $status = $r2['status'];
    ?>
            <tr>
                <td><strong><?= $urut++ ?></strong></td>
                <td>
                    <div class="fw-bold"><?= htmlspecialchars($nama_pegawai) ?></div>
                    <small class="jabatan-badge">Nail Artist</small>
                </td>
                <td>
                    <a href="https://wa.me/<?= $no_hp ?>" class="text-decoration-none" target="_blank">
                        <i class="fab fa-whatsapp text-success me-1"></i><?= $no_hp ?>
                    </a>
                </td>
                <td><?= htmlspecialchars($alamat ?: 'Tidak diisi') ?></td>
                <td><?= $tanggal_masuk ?></td>
                <td>
                    <span class="status-badge <?= ($status == 'Aktif') ? 'status-aktif' : 'status-tidak-aktif' ?>">
                        <?= ($status == 'Aktif') ? 'Bekerja' : 'Tidak Aktif' ?>
                    </span>
                </td>
                <td>
                    <a href="?op=edit&id=<?= $id ?>" class="btn btn-warning btn-sm">
                        <img src="edit.png" width="20px" alt="Edit">
                    </a>
                    <?php if ($status == 'Tidak Aktif') { ?>
                        <a href="?op=delete&id=<?= $id ?>" onclick="return confirm('Yakin ingin menghapus pegawai ini?')" class="btn btn-danger btn-sm">
                            <img src="accept.png" width="20px" alt="Delete">
                        </a>
                    <?php } ?>
                    <a href="?op=toggle_status&id=<?= $id ?>&current_status=<?= $status ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </td>
            </tr>
    <?php
        }
    } else {
    ?>
        <tr>
            <td colspan="7" class="text-center">Belum ada data pegawai</td>
        </tr>

 </div>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
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