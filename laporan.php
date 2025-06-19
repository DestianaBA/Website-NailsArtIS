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

// Filter variables
$filter_tanggal_dari = isset($_GET['tanggal_dari']) ? $_GET['tanggal_dari'] : '';
$filter_tanggal_sampai = isset($_GET['tanggal_sampai']) ? $_GET['tanggal_sampai'] : '';
$filter_service = isset($_GET['service']) ? $_GET['service'] : '';
$filter_nama = isset($_GET['nama']) ? $_GET['nama'] : '';

// Build SQL query with filters
$sql_where = "WHERE 1=1";
if (!empty($filter_tanggal_dari)) {
    $sql_where .= " AND tanggal >= '$filter_tanggal_dari'";
}
if (!empty($filter_tanggal_sampai)) {
    $sql_where .= " AND tanggal <= '$filter_tanggal_sampai'";
}
if (!empty($filter_service)) {
    $sql_where .= " AND service LIKE '%$filter_service%'";
}
if (!empty($filter_nama)) {
    $sql_where .= " AND nama LIKE '%$filter_nama%'";
}

// Get services for dropdown
$sql_services = "SELECT DISTINCT service FROM appointment ORDER BY service";
$q_services = mysqli_query($koneksi, $sql_services);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <title>LAPORAN TRANSAKSI - NailsArtIS</title>
    <style>
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
        
        .filter-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .report-header {
            background: linear-gradient(135deg, #D63484, #821a4e);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .stats-card {
            background: linear-gradient(135deg, #fff, #f8f9fa);
            border: none;
            border-radius: 15px;
            padding: 20px;
            margin: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #D63484;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        
        .btn-filter {
            background: linear-gradient(135deg, #D63484, #821a4e);
            border: none;
            color: white;
            border-radius: 25px;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }
        
        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(214, 52, 132, 0.3);
            color: white;
        }
        
        .btn-print {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            color: white;
            border-radius: 25px;
            padding: 10px 25px;
            transition: all 0.3s ease;
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
            color: white;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead th {
            background: linear-gradient(135deg, #D63484, #821a4e);
            color: white;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            padding: 15px;
        }
        
        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .service-badge {
            background: linear-gradient(135deg, #D63484, #821a4e);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .date-badge {
            background-color: #e9ecef;
            color: #495057;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
        }
        
        footer {
            background-color: black;
            color: white;
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
        
        @media print {
            .no-print {
                display: none !important;
            }
            .navbar, footer {
                display: none !important;
            }
            .container {
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
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
                            <a class="nav-link" href="kataloginput.php">
                                <b>Add Katalog</b>
                            </a>
                            <a class="nav-link" href="home_owner.php">
                                <b>Beranda</b>
                            </a>
                            </ul>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><b>Logout</b></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Report Header -->
        <div class="report-header">
            <h1><i class="fas fa-chart-line me-3"></i>LAPORAN TRANSAKSI</h1>
            <p class="mb-0">NailsArtIS - Appointment Report</p>
        </div>

        <!-- Filter Section -->
        <div class="filter-section no-print">
            <h5><i class="fas fa-filter me-2"></i>Filter Laporan</h5>
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-3">
                        <label for="tanggal_dari" class="form-label">Tanggal Dari:</label>
                        <input type="date" class="form-control" id="tanggal_dari" name="tanggal_dari" value="<?= $filter_tanggal_dari ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="tanggal_sampai" class="form-label">Tanggal Sampai:</label>
                        <input type="date" class="form-control" id="tanggal_sampai" name="tanggal_sampai" value="<?= $filter_tanggal_sampai ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="service" class="form-label">Service:</label>
                        <select class="form-control" id="service" name="service">
                            <option value="">Semua Service</option>
                            <?php while ($service = mysqli_fetch_array($q_services)) { ?>
                                <option value="<?= $service['service'] ?>" <?= ($filter_service == $service['service']) ? 'selected' : '' ?>>
                                    <?= $service['service'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="nama" class="form-label">Nama Customer:</label>
                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Cari nama..." value="<?= $filter_nama ?>">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-filter me-2">
                            <i class="fas fa-search me-1"></i>Filter Data
                        </button>
                        <a href="?" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-refresh me-1"></i>Reset Filter
                        </a>
                        <button type="button" class="btn btn-print" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Print Laporan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <?php
        // Get statistics
        $sql_stats = "SELECT 
                        COUNT(*) as total_appointment,
                        COUNT(DISTINCT nama) as total_customer,
                        COUNT(DISTINCT service) as total_service
                      FROM appointment $sql_where";
        $q_stats = mysqli_query($koneksi, $sql_stats);
        $stats = mysqli_fetch_array($q_stats);
        ?>

        <!-- Statistics Cards -->
        <div class="row no-print">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['total_appointment'] ?></div>
                    <div class="text-muted">Total Appointment</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['total_customer'] ?></div>
                    <div class="text-muted">Total Customer</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['total_service'] ?></div>
                    <div class="text-muted">Jenis Service</div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-table me-2"></i>Data Transaksi</h5>
                <small class="text-muted">
                    Menampilkan <?= $stats['total_appointment'] ?> data
                    <?php if (!empty($filter_tanggal_dari) || !empty($filter_tanggal_sampai)) { ?>
                        <?php if (!empty($filter_tanggal_dari)) echo "dari " . date('d/m/Y', strtotime($filter_tanggal_dari)); ?>
                        <?php if (!empty($filter_tanggal_sampai)) echo "sampai " . date('d/m/Y', strtotime($filter_tanggal_sampai)); ?>
                    <?php } ?>
                </small>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="20%">Customer Name</th>
                            <th width="15%">No. HP</th>
                            <th width="25%">Service</th>
                            <th width="20%">Tanggal Appointment</th>
                            <th width="15%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql2 = "SELECT * FROM appointment $sql_where ORDER BY tanggal DESC, id DESC";
                        $q2 = mysqli_query($koneksi, $sql2);
                        $urut = 1;

                        if (mysqli_num_rows($q2) > 0) {
                            while ($r2 = mysqli_fetch_array($q2)) {
                                $id = $r2['id'];
                                $nama = $r2['nama'];
                                $number = $r2['noHP'];
                                $servis = $r2['service'];
                                $tanggal = $r2['tanggal'];
                                $tanggal_formatted = date("d F Y", strtotime($tanggal));
                                
                                // Determine status based on date
                                $today = date('Y-m-d');
                                if ($tanggal < $today) {
                                    $status = '<span class="badge bg-success">Selesai</span>';
                                } elseif ($tanggal == $today) {
                                    $status = '<span class="badge bg-warning">Hari Ini</span>';
                                } else {
                                    $status = '<span class="badge bg-primary">Terjadwal</span>';
                                }
                        ?>
                            <tr>
                                <td><strong><?= $urut++ ?></strong></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($nama) ?></div>
                                </td>
                                <td>
                                    <a href="https://wa.me/<?= $number ?>" class="text-decoration-none" target="_blank">
                                        <i class="fab fa-whatsapp text-success me-1"></i><?= $number ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="service-badge"><?= htmlspecialchars($servis) ?></span>
                                </td>
                                <td>
                                    <span class="date-badge"><?= $tanggal_formatted ?></span>
                                </td>
                                <td><?= $status ?></td>
                            </tr>
                        <?php
                            }
                        } else {
                        ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <div class="text-muted">Tidak ada data yang ditemukan</div>
                                    <small>Coba ubah filter atau tambah appointment baru</small>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary by Service -->
        <?php if (mysqli_num_rows($q2) > 0) { ?>
        <div class="table-container">
            <h5><i class="fas fa-chart-pie me-2"></i>Ringkasan Per Service</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th width="20%">Jumlah Appointment</th>
                            <th width="20%">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql_summary = "SELECT service, COUNT(*) as jumlah FROM appointment $sql_where GROUP BY service ORDER BY jumlah DESC";
                        $q_summary = mysqli_query($koneksi, $sql_summary);
                        
                        while ($summary = mysqli_fetch_array($q_summary)) {
                            $percentage = ($summary['jumlah'] / $stats['total_appointment']) * 100;
                        ?>
                            <tr>
                                <td><span class="service-badge"><?= htmlspecialchars($summary['service']) ?></span></td>
                                <td><strong><?= $summary['jumlah'] ?></strong> appointment</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" style="width: <?= $percentage ?>%; background: linear-gradient(135deg, #D63484, #821a4e);">
                                            <?= number_format($percentage, 1) ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php } ?>
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