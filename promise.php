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

$nama          = "";
$number        = "";
$servis        = "";
$tanggal       = "";
$sukses        = "";
$error         = "";

// Fungsi untuk mendapatkan tanggal yang sudah penuh (asumsi maksimal 5 appointment per hari)
function getTanggalPenuh($koneksi) {
    $sql = "SELECT tanggal, COUNT(*) as jumlah FROM appointment GROUP BY tanggal HAVING jumlah >= 5";
    $result = mysqli_query($koneksi, $sql);
    $tanggal_penuh = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $tanggal_penuh[] = $row['tanggal'];
    }
    
    return $tanggal_penuh;
}

// Fungsi untuk mendapatkan jumlah appointment per tanggal
function getJumlahAppointment($koneksi) {
    $sql = "SELECT tanggal, COUNT(*) as jumlah FROM appointment GROUP BY tanggal";
    $result = mysqli_query($koneksi, $sql);
    $appointment_count = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $appointment_count[$row['tanggal']] = $row['jumlah'];
    }
    
    return $appointment_count;
}

$tanggal_penuh = getTanggalPenuh($koneksi);
$appointment_count = getJumlahAppointment($koneksi);

if (isset($_GET['op'])) { //untuk edit data
    $op  = $_GET['op'];
} else {
    $op = "";
}

if ($op == 'edit') {
    $id       = $_GET['id']; // Correct variable name
    $sql1           = "SELECT * FROM appointment WHERE id = '$id'";
    $q1             = mysqli_query($koneksi, $sql1);
    $r1             = mysqli_fetch_array($q1);
    $nama               = $r1['nama'];
    $number             = $r1['noHP'];
    $servis             = $r1['service'];
    $tanggal             = $r1['tanggal'];

    if ($nama == '') {
        $error = "Data tidak ditemukan";
    }
}

if (isset($_POST['simpan'])) { //untuk create data
    $nama       = $_POST['nama'];
    $number     = $_POST['number'];
    $servis     = $_POST['servis'];
    $tanggal    = $_POST['tanggal'];

    // Cek apakah tanggal sudah penuh (maksimal 5 appointment per hari)
    $sql_check  = "SELECT COUNT(*) as jumlah FROM appointment WHERE tanggal = '$tanggal'";
    $q_check    = mysqli_query($koneksi, $sql_check);
    $result_check = mysqli_fetch_assoc($q_check);

    if ($result_check['jumlah'] >= 5) {
        $error = "Tanggal sudah penuh. Silakan pilih tanggal lain.";
    } else {
        if ($nama && $tanggal && $servis && $number) {
            if ($op == 'edit') { //untuk update
                $sql1 = "UPDATE appointment SET nama='$nama', noHP='$number', tanggal='$tanggal', service='$servis' WHERE id='$id' ";
                $q1   = mysqli_query($koneksi, $sql1);
                if ($q1) {
                    $sukses = "Data Berhasil Diupdate";
                    echo "<script>window.location.href = 'tampil.php';</script>";
                    exit;
                } else {
                    $error  = "Gagal Memperbarui Data";
                }
            } else { //untuk insert
                $sql1 = "INSERT INTO appointment (nama, tanggal, service, noHP) VALUES ('$nama', '$tanggal', '$servis', '$number')";
                $q1   = mysqli_query($koneksi, $sql1);

                if ($q1) {
                    $sukses = "Berhasil Memasukkan Data Baru";
                    echo "<script>window.location.href = 'tampil.php';</script>";
                    exit;
                } else {
                    $error  = "Gagal Memasukkan Data";
                }
            }  
        } else {
            $error = "Silakan Isikan Semua Data";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            height: 80vh;
            background-image: url("1.png");
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-size: cover;
            position: relative;
            display: flex;
            justify-content: center;
        }

        body::after {
            content: " ";
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.8);
        }

        .popup {
            z-index: 1;
            position: absolute;
            top: 50%;
            left: 50%;
            opacity: 0;
            transform: translate(-50%, -50%) scale(1.25);
            width: 700px;
            padding: 20px 30px;
            margin-top: 70px;
            background: white;
            box-shadow: 2px 2px 5px 5px rgba(0, 0, 0, 0.15);
            transition: top 0ms ease-in-out 200ms,
                opacity 200ms ease-in-out 0ms,
                transform 200ms ease-in-out 0ms;
            max-height: 90vh;
            overflow-y: auto;
        }

        .popup.active {
            top: 50%;
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
            transition: top 0ms ease-in-out 0ms,
                opacity 200ms ease-in-out 0ms,
                transform 200ms ease-in-out 0ms;
        }

        .popup .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 20px;
            height: 20px;
            background: white;
            color: black;
            text-align: center;
            line-height: 15px;
            border: 1px solid black;
            border-radius: 15px;
            cursor: pointer;
        }

        .popup .form-container h3 {
            text-align: center;
            color: black;
            margin: 10px 0px 20px;
            font-size: 25px;
        }

        .login-container {
            display: none;
        }

        .calendar-container {
            display: none;
            margin-top: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            background-color: #f8f9fa;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .calendar-header button {
            background: none;
            border: 1px solid #007bff;
            color: #007bff;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        .calendar-header button:hover {
            background-color: #007bff;
            color: white;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }

        .calendar-day {
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: white;
            cursor: pointer;
            border-radius: 3px;
            position: relative;
        }

        .calendar-day:hover {
            background-color: #e9ecef;
        }

        .calendar-day.selected {
            background-color: #007bff;
            color: white;
        }

        .calendar-day.disabled {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
        }

        .calendar-day.full {
            background-color: #dc3545;
            color: white;
            cursor: not-allowed;
        }

        .calendar-day.partial {
            background-color: #ffc107;
            color: #212529;
        }

        .calendar-day-header {
            font-weight: bold;
            padding: 8px;
            text-align: center;
            background-color: #007bff;
            color: white;
        }

        .appointment-count {
            font-size: 0.7em;
            position: absolute;
            top: 2px;
            right: 2px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .legend {
            display: flex;
            justify-content: space-around;
            margin-top: 10px;
            font-size: 0.8em;
        }

        .legend-item {
            display: flex;
            align-items: center;
        }

        .legend-color {
            width: 15px;
            height: 15px;
            margin-right: 5px;
            border-radius: 3px;
        }

        .date-input-container {
            position: relative;
        }

        .calendar-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2em;
            color: #007bff;
        }
    </style>
</head>

<body>
    <div class="popup active" id="popup">
        <div class="close-btn" onclick="closePopup()">&times;</div>
        <div class="form-container">
            <form method="POST" action="">
                <h3>APPOINTMENT</h3>
                <div class="card-body">
                <?php
                if ($error) {
                ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $error ?>
                    </div>
                <?php
                    header("refresh:3;url=promise.php");
                }
                ?>
                <?php
                if ($sukses) {
                ?>
                    <div class="alert alert-success" role="alert">
                        <?= $sukses ?>
                    </div>
                <?php
                    header("refresh:3;url=tampil.php");
                }
                ?>
                <div class="mb-3">
                    <label for="nama" class="form-label"><b>Nama</b></label>
                    <input type="text" class="form-control" id="nama" name="nama" value="<?= $nama ?>" required placeholder="Enter your name">
                </div>
                <div class="mb-3">
                    <label for="number" class="form-label"><b>No. HP</b></label>
                    <input type="text" class="form-control" id="number" name="number" value="<?= $number ?>" required placeholder="Enter your number">
                </div>
                <div class="mb-3">
                    <label for="servis" class="form-label"><b>Service</b></label>
                    <select class="form-select" name="servis" aria-label="Default select example">
                        <option value="Manicure" <?= ($servis == "Manicure") ? "selected" : "" ?>>Manicure</option>
                        <option value="Nails Art" <?= ($servis == "Nails Art") ? "selected" : "" ?>>Nails Art</option>
                        <option value="Manicure + Nails Art" <?= ($servis == "Manicure + Nails Art") ? "selected" : "" ?>>Manicure + Nails Art</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="tanggal" class="col-form-label"><b>Tanggal</b></label>
                    <div class="date-input-container">
                        <input type="date" id="tanggal" name="tanggal" value="<?= $tanggal ?>" min="<?= date('Y-m-d') ?>" class="form-control" readonly>
                        <button type="button" class="calendar-toggle" onclick="toggleCalendar()">📅</button>
                    </div>
                    
                    <div class="calendar-container" id="calendarContainer">
                        <div class="calendar-header">
                            <button type="button" id="prevMonth">&lt;</button>
                            <h5 id="currentMonth"></h5>
                            <button type="button" id="nextMonth">&gt;</button>
                        </div>
                        <div class="calendar-grid" id="calendarGrid">
                            <!-- Calendar will be generated here -->
                        </div>
                        <div class="legend">
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #28a745;"></div>
                                <span>Tersedia</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #ffc107;"></div>
                                <span>Terbatas</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #dc3545;"></div>
                                <span>Penuh</span>
                            </div>
                        </div>
                    </div>
                </div>
        
                <div class="mb-3 text-center">
                    <input type="submit" name="simpan" value="BUAT APPOINTMENT" class="btn btn-primary" />
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Data appointment dari PHP
        const tanggalPenuh = <?= json_encode($tanggal_penuh) ?>;
        const appointmentCount = <?= json_encode($appointment_count) ?>;
        
        let currentDate = new Date();
        let selectedDate = null;

        function closePopup() {
            window.location.href = 'home2.php';
        }

        function toggleCalendar() {
            const calendar = document.getElementById('calendarContainer');
            calendar.style.display = calendar.style.display === 'none' ? 'block' : 'none';
            if (calendar.style.display === 'block') {
                generateCalendar();
            }
        }

        function generateCalendar() {
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();

            document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;

            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = new Date();

            const calendarGrid = document.getElementById('calendarGrid');
            calendarGrid.innerHTML = '';

            // Header hari
            dayNames.forEach(day => {
                const dayHeader = document.createElement('div');
                dayHeader.className = 'calendar-day-header';
                dayHeader.textContent = day;
                calendarGrid.appendChild(dayHeader);
            });

            // Tanggal kosong di awal bulan
            for (let i = 0; i < firstDay; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'calendar-day disabled';
                calendarGrid.appendChild(emptyDay);
            }

            // Tanggal dalam bulan
            for (let day = 1; day <= daysInMonth; day++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                dayElement.textContent = day;

                const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dateObj = new Date(year, month, day);

                // Cek apakah tanggal sudah lewat
                if (dateObj < today.setHours(0, 0, 0, 0)) {
                    dayElement.classList.add('disabled');
                } else {
                    const count = appointmentCount[dateString] || 0;
                    
                    if (count >= 5) {
                        dayElement.classList.add('full');
                        dayElement.title = `Penuh (${count}/5 appointment)`;
                    } else if (count >= 3) {
                        dayElement.classList.add('partial');
                        dayElement.title = `Terbatas (${count}/5 appointment)`;
                    } else {
                        dayElement.style.backgroundColor = '#28a745';
                        dayElement.style.color = 'white';
                        dayElement.title = `Tersedia (${count}/5 appointment)`;
                    }

                    // Tambahkan counter jika ada appointment
                    if (count > 0) {
                        const counter = document.createElement('div');
                        counter.className = 'appointment-count';
                        counter.textContent = count;
                        dayElement.appendChild(counter);
                    }

                    // Event listener untuk memilih tanggal
                    if (count < 5) {
                        dayElement.addEventListener('click', function() {
                            selectDate(dateString, dayElement);
                        });
                    }
                }

                calendarGrid.appendChild(dayElement);
            }
        }

        function selectDate(dateString, element) {
            // Hapus selection sebelumnya
            document.querySelectorAll('.calendar-day.selected').forEach(day => {
                day.classList.remove('selected');
            });

            // Pilih tanggal baru
            element.classList.add('selected');
            selectedDate = dateString;
            
            // Update input
            document.getElementById('tanggal').value = dateString;
            
            // Tutup kalender
            document.getElementById('calendarContainer').style.display = 'none';
        }

        // Event listeners untuk navigasi bulan
        document.getElementById('prevMonth').addEventListener('click', function() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            generateCalendar();
        });

        document.getElementById('nextMonth').addEventListener('click', function() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            generateCalendar();
        });

        // Tutup kalender jika klik di luar
        document.addEventListener('click', function(event) {
            const calendar = document.getElementById('calendarContainer');
            const toggle = document.querySelector('.calendar-toggle');
            const dateInput = document.getElementById('tanggal');
            
            if (!calendar.contains(event.target) && 
                !toggle.contains(event.target) && 
                !dateInput.contains(event.target)) {
                calendar.style.display = 'none';
            }
        });

        // Initialize calendar saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            generateCalendar();
        });
    </script>
</body>

</html>