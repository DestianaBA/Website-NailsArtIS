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
if (!$koneksi) { //cek apakah sudah terhubung ke database
    die("Gagal terkoneksi ke database!!!");
}

$sukses        = "";
$error         = "";

if (isset($_GET['op'])) {
    $op  = $_GET['op'];
} else {
    $op = "";
}

// Hanya izinkan operasi complete untuk customer
if ($op == 'complete') {
    $id = $_GET['id'];
    // Update status appointment menjadi completed
    $sql1 = "UPDATE appointment SET status = 'completed', completed_at = NOW() WHERE id = '$id'";
    $q1   = mysqli_query($koneksi, $sql1);
    if ($q1) {
        $sukses = "Appointment berhasil diselesaikan!";
    } else {
        $error  = "Gagal menyelesaikan appointment.";
    }
}

// Tambahkan kolom status jika belum ada (untuk backward compatibility)
$check_column = "SHOW COLUMNS FROM appointment LIKE 'status'";
$result = mysqli_query($koneksi, $check_column);
if (mysqli_num_rows($result) == 0) {
    $add_column = "ALTER TABLE appointment ADD COLUMN status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending'";
    mysqli_query($koneksi, $add_column);
    
    $add_completed_at = "ALTER TABLE appointment ADD COLUMN completed_at TIMESTAMP NULL";
    mysqli_query($koneksi, $add_completed_at);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>MY APPOINTMENT</title>
    <script src="https://unpkg.com/feather-icons"></script>
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

        .vl {
            border-left: 3px solid goldenrod;
            height: 90px;
            margin: 0 10px;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-text p {
            margin: 0;
            color: rgb(214, 52, 132);
        }

        .nav-tabs {
            background-color: rgb(214, 52, 132);
            padding: 5px 3px;
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
            bottom: -10px;
            transition: 0.3s;
        }

        .navbar-pilihan li:hover {
            background-color: #821a4e;
            transition: background-color 1s ease;
        }

        .offcanvas {
            background-color: rgb(214, 52, 132);
        }

        .content {
            height: 80vh;
            background-image: url("1.png");
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-size: cover;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .content::after {
            content: " ";
            display: block;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.7);
        }

        .content h2 {
            color: white;
            z-index: 1;
            padding: 20px 25px;
            border: 3px solid white;
            text-decoration: none;
        }

        .content a {
            color: white;
        }

        section {
            padding: 50px 0;
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
            margin-top: 100px;
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
            transition: 0.5;
        }

        footer .navbar-brand img {
            height: 200px;
        }

        footer .brand-text {
            display: flex;
            flex-direction: column;
            font-size: 50px;
            font-family: sans-serif;
        }

        footer.brand-text p {
            margin: 0;
            color: rgb(214, 52, 132);
        }

        footer table {
            text-align: left;
        }

        .text-justify {
            text-align: justify;
        }

        .tombol {
            text-decoration: none;
            border: none;
            height: 2.5em;
            width: 15em;
            border-radius: 0.5em;
            background-color: #D63484;
            color: #fff;
            margin-top: 1em;
            transition: all 0.4s ease-in-out;
        }

        .tombol:hover {
            transform: rotate(4deg);
        }

        .offcanvas-body.bg {
            background-color: rgb(214, 52, 132);
        }

        .offcanvas-header {
            background-color: rgb(214, 52, 132);
        }

        .navbar-toggler:hover {
            background-color: #821a4e;
            transition: background-color 1s ease;
        }

        .td a {
            text-decoration: none;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }

        .status-completed {
            background-color: #28a745;
            color: white;
        }

        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }

        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .info-box h5 {
            color: #007bff;
            margin-bottom: 10px;
        }

        .contact-info {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
        }

        .contact-info h6 {
            color: #856404;
            margin-bottom: 10px;
        }

        .contact-info p {
            margin: 5px 0;
            color: #856404;
        }

        .btn-complete {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }

        .btn-complete:hover {
            background-color: #218838;
            border-color: #1e7e34;
            color: white;
        }

        .btn-disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <nav class="navbar navbar">
        <div class="container-fluid">
            <ul class="nav  d-flex align-items-center navbar-pilihan" style="height: 5em;">
                <li> <a class="navbar-brand">
                        <img src="logo-bgoff.png" alt="Logo" height="1px" class="d-inline-block align-text-top">
                    </a></li>
                <li>
                    <a aria-current="home" href="home2.php">Home</a>
                </li>
                <li>
                    <a href="kami.php">About us</a>
                </li>
                <li>
                    <a href="service.php">Services</a>
                </li>
                <li>
                    <a href="inspirasi.php">Inspiration Art</a>
                </li>
            </ul>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar" aria-label="Toggle navigation">
                <img src="user.png" width="50px">
            </button>
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasDarkNavbar" aria-labelledby="offcanvasDarkNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasDarkNavbarLabel">Welcome, <?= $_SESSION['username'] ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body bg">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="data.php" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <b>My Appointment</b>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-tertiary">
                                <li><a class="dropdown-item" href="promise.php">Add </a></li>
                                <li><a class="dropdown-item" href="tampil.php">Check</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><b>Logout</b></a>
                        </li>
                </div>
            </div>
        </div>
    </nav>

    <center>
        <a href=""><button class="tombol" style="border-radius: 1.5em; height: 4em; width: 50em; margin-top: 4em;">
                <h2>My Appointment</h2>
            </button></a>
    </center>

    <!-- Info Box -->
    <div class="container" style="margin-top: 2em;">
        <div class="info-box">
            <h5><i class="fas fa-info-circle"></i> Informasi Penting</h5>
            <p><strong>Kebijakan Appointment:</strong></p>
            <ul>
                <li>Anda hanya dapat menandai appointment sebagai <strong>"Selesai"</strong> setelah layanan diterima</li>
                <li>Untuk <strong>pembatalan</strong> atau <strong>reschedule</strong> appointment, silakan hubungi layanan customer service kami</li>
                <li>Appointment yang sudah selesai tidak dapat diubah lagi</li>
            </ul>
        </div>
        
        <?php
        if ($error) {
        ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
            </div>
        <?php
        }
        ?>
        <?php
        if ($sukses) {
        ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i> <?= $sukses ?>
            </div>
        <?php
        }
        ?>
    </div>

    <!-- Table -->
    <div class="container" style="margin-top: 2em;">
        <table class="table table-striped">
            <thead>
                <tr class="table-danger">
                    <th scope="col">#</th>
                    <th scope="col">Customer Name</th>
                    <th scope="col">No.HP</th>
                    <th scope="col">Service</th>
                    <th scope="col">Appointment Date</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql2   = "SELECT * FROM appointment ORDER BY 
                          CASE 
                            WHEN status = 'pending' THEN 1 
                            WHEN status = 'completed' THEN 2 
                            WHEN status = 'cancelled' THEN 3 
                            ELSE 4 
                          END, 
                          tanggal DESC";
                $q2     = mysqli_query($koneksi, $sql2);
                $urut   = 1;

                while ($r2 = mysqli_fetch_array($q2)) {
                    $id              = $r2['id'];
                    $nama            = $r2['nama'];
                    $number          = $r2['noHP'];
                    $servis          = $r2['service'];
                    $tanggal         = date("d F Y", strtotime($r2['tanggal']));
                    $status          = isset($r2['status']) ? $r2['status'] : 'pending';
                    $completed_at    = $r2['completed_at'];
                    
                    // Determine status class and text
                    $status_class = '';
                    $status_text = '';
                    switch($status) {
                        case 'completed':
                            $status_class = 'status-completed';
                            $status_text = 'Selesai';
                            break;
                        case 'cancelled':
                            $status_class = 'status-cancelled';
                            $status_text = 'Dibatalkan';
                            break;
                        default:
                            $status_class = 'status-pending';
                            $status_text = 'Menunggu';
                            break;
                    }
                ?>
                    <tr>
                        <th scope="row"><?= $urut++ ?></th>
                        <td><?= $nama ?></td>
                        <td><?= $number ?></td>
                        <td><a href="service.php" style="text-decoration: none; color: black;"><?= $servis ?></a></td>
                        <td><?= $tanggal ?></td>
                        <td>
                            <span class="status-badge <?= $status_class ?>">
                                <?= $status_text ?>
                            </span>
                            <?php if ($status == 'completed' && $completed_at): ?>
                                <br><small class="text-muted">Selesai: <?= date("d/m/Y H:i", strtotime($completed_at)) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($status == 'pending'): ?>
                                <a href="tampil.php?op=complete&id=<?= $id ?>" 
                                   onclick="return confirm('Apakah Anda yakin appointment ini sudah selesai?')"
                                   class="btn btn-complete btn-sm">
                                    <i class="fas fa-check"></i> Selesai
                                </a>
                            <?php elseif ($status == 'completed'): ?>
                                <button class="btn btn-success btn-sm btn-disabled" disabled>
                                    <i class="fas fa-check-circle"></i> Selesai
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm btn-disabled" disabled>
                                    <i class="fas fa-ban"></i> Dibatalkan
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
        
        <center>
            <a href="promise.php"><button class="tombol" style="border-radius: 1.5em; height: 4em; width: 20em; margin-top: 2em; color:#D63484; background-color:pink;">
                    <h2><i class="fas fa-plus"></i> Add Appointment</h2>
                </button></a>
        </center>

        <!-- Contact Information for Reschedule/Cancel -->
        <div class="contact-info">
            <h6><i class="fas fa-phone"></i> Butuh Bantuan untuk Reschedule atau Pembatalan?</h6>
            <p><strong>Hubungi Customer Service:</strong></p>
            <p><i class="fas fa-phone"></i> WhatsApp: <a href="https://wa.me/6281270707067" target="_blank">+62 812-7070-7067</a></p>
            <p><i class="fas fa-envelope"></i> Email: NailsArtIS@gmail.com</p>
            <p><i class="fab fa-instagram"></i> Instagram: <a href="https://www.instagram.com/destyninth_?igsh=MWp4b3c3dHNhMmJ1bQ==" target="_blank">@NailsArtIS</a></p>
            <p><strong>Jam Operasional:</strong> Senin - Minggu, 09:00 - 21:00 WIB</p>
        </div>
    </div>

    <footer class="bg-body-black text-center text-lg-center" style="margin-top: 3em;">
        <!-- Grid container -->
        <div class="container-fluid p-4">
            <!--Grid row-->
            <div class="row">
                <!--Grid column-->
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
                <!--Grid column-->
                <!--Grid column-->
                <div class="col-lg-4 col-md-12 mb-4 mb-md-0">
                    <h5 class="text-uppercase">About Us :</h5>
                    <p class="text-justify">
                        Nails Art Is – tempat di mana kreativitas bertemu dengan kesempurnaan. Di Nails Art Is, kami percaya bahwa kuku bukan hanya pernyataan kecantikan, tetapi juga kanvas untuk mengekspresikan individualitas dan gaya. Misi kami adalah menyediakan layanan nail art berkualitas tinggi yang menggabungkan seni, presisi, dan perawatan untuk menciptakan desain menakjubkan yang disukai oleh pelanggan kami.
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
                        <tr>
                            <td>Instagram</td>
                            <td>: @NailsArtIS</td>
                        </tr>
                        <tr>
                            <td>Tiktok</td>
                            <td>: @NailsArtIS</td>
                        </tr>
                    </table>
                </div>
                <!--Grid column-->
            </div>
            <!--Grid row-->
        </div>
    </footer>

    <script>
        feather.replace();
    </script>
</body>
</html>