<?php
// session_start();
// if (empty($_SESSION['username'])) {
//     header("location:login.php?pesan=belum_login");
// }

$host       = "localhost";
$user       = "root";
$pass       = "";
$db         = "nailartis";

$koneksi    = mysqli_connect($host, $user, $pass, $db);
if (!$koneksi) { //cek apakah sudah terhubung ke database
    die("Gagal terkoneksi ke database!!!");
}

$id_katalog     = "";
$nama_katalog   = "";
$harga          = "";
$gambar         = "";
$sukses         = "";
$error          = "";

if (isset($_GET['op'])) { //untuk edit data
    $op  = $_GET['op'];
} else {
    $op = "";
}

if ($op == 'edit') {
    $id             = $_GET['id'];
    $sql1           = "SELECT * FROM katalog WHERE id = '$id'";
    $q1             = mysqli_query($koneksi, $sql1);
    $r1             = mysqli_fetch_array($q1);
    $id_katalog     = $r1['id_katalog'];
    $nama_katalog   = $r1['nama_katalog'];
    $harga          = $r1['harga'];
    $gambar         = $r1['gambar'];

    if ($nama_katalog == '') {
        $error = "Data tidak ditemukan";
    }
}

if (isset($_POST['simpan'])) { //untuk create data
    $id_katalog     = $_POST['id_katalog'];
    $nama_katalog   = $_POST['nama_katalog'];
    $harga          = $_POST['harga'];
    
    // Cek apakah ID_Katalog sudah digunakan (untuk insert baru)
    if ($op != 'edit') {
        $sql_check  = "SELECT * FROM katalog WHERE id_katalog = '$id_katalog'";
        $q_check    = mysqli_query($koneksi, $sql_check);
        
        if (mysqli_num_rows($q_check) > 0) {
            $error = "ID Katalog sudah digunakan. Silakan gunakan ID yang lain.";
        }
    }
    
    // Handle file upload
    $gambar_name = "";
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION));
        $new_filename = $id_katalog . '_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Check if image file is actual image
        $check = getimagesize($_FILES["gambar"]["tmp_name"]);
        if($check !== false) {
            // Check file size (limit to 5MB)
            if ($_FILES["gambar"]["size"] <= 5000000) {
                // Allow certain file formats
                if($file_extension == "jpg" || $file_extension == "png" || $file_extension == "jpeg" || $file_extension == "gif") {
                    if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                        $gambar_name = $new_filename;
                    } else {
                        $error = "Gagal mengupload gambar.";
                    }
                } else {
                    $error = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
                }
            } else {
                $error = "File terlalu besar. Maksimal 5MB.";
            }
        } else {
            $error = "File bukan gambar.";
        }
    }

    if (!$error) {
        if ($id_katalog && $nama_katalog && $harga) {
            if ($op == 'edit') { //untuk update
                $sql1 = "UPDATE katalog SET id_katalog='$id_katalog', nama_katalog='$nama_katalog', harga='$harga'";
                if ($gambar_name) {
                    $sql1 .= ", gambar='$gambar_name'";
                }
                $sql1 .= " WHERE id='$id'";
                $q1   = mysqli_query($koneksi, $sql1);
                if ($q1) {
                    $sukses = "Data Katalog Berhasil Diupdate";
                    echo "<script>window.location.href = 'tampil_katalog.php';</script>";
                    exit;
                } else {
                    $error  = "Gagal Memperbarui Data";
                }
            } else { //untuk insert
                if (!$gambar_name) {
                    $error = "Gambar harus diupload";
                } else {
                    $sql1 = "INSERT INTO katalog (id_katalog, nama_katalog, harga, gambar) VALUES ('$id_katalog', '$nama_katalog', '$harga', '$gambar_name')";
                    $q1   = mysqli_query($koneksi, $sql1);

                    if ($q1) {
                        $sukses = "Berhasil Menambahkan Data Katalog Baru";
                        echo "<script>window.location.href = 'tampil_katalog.php';</script>";
                        exit;
                    } else {
                        $error  = "Gagal Memasukkan Data";
                    }
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
    <title>Add Katalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<style>
    body {
        height: 100vh;
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
        width: 550px;
        padding: 20px 30px;
        margin-top: 70px;
        background: white;
        box-shadow: 2px 2px 5px 5px rgba(0, 0, 0, 0.15);
        border-radius: 10px;
        max-height: 90vh;
        overflow-y: auto;
        transition: top 0ms ease-in-out 200ms,
            opacity 200ms ease-in-out 0ms,
            transform 200ms ease-in-out 0ms;
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
        width: 25px;
        height: 25px;
        background: #ff4757;
        color: white;
        text-align: center;
        line-height: 23px;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-weight: bold;
        font-size: 16px;
    }

    .popup .close-btn:hover {
        background: #ff3742;
    }

    .popup .form-container h3 {
        text-align: center;
        color: #2c3e50;
        margin: 10px 0px 20px;
        font-size: 28px;
        font-weight: bold;
    }

    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }

    .form-control, .form-select {
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 10px 15px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #e91e63;
        box-shadow: 0 0 0 0.2rem rgba(233, 30, 99, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, #e91e63, #f06292);
        border: none;
        padding: 12px 30px;
        font-weight: bold;
        border-radius: 25px;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #c2185b, #e91e63);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(233, 30, 99, 0.4);
    }

    .btn-secondary {
        background: #6c757d;
        border: none;
        padding: 12px 25px;
        font-weight: bold;
        border-radius: 25px;
        font-size: 14px;
    }

    .preview-image {
        max-width: 100%;
        max-height: 200px;
        margin-top: 10px;
        border: 2px solid #ddd;
        border-radius: 8px;
        object-fit: cover;
    }

    .upload-area {
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .upload-area:hover {
        border-color: #e91e63;
        background-color: #fce4ec;
    }

    .upload-area.dragover {
        border-color: #e91e63;
        background-color: #fce4ec;
    }

    .upload-icon {
        font-size: 48px;
        color: #ccc;
        margin-bottom: 10px;
    }

    .input-group-text {
        background-color: #e91e63;
        color: white;
        border: none;
        font-weight: bold;
    }
</style>

<body>
    <div class="popup active" id="popup">
        <div class="close-btn" onclick="closePopup()">&times;</div>
        <div class="form-container">
            <form method="POST" action="" enctype="multipart/form-data">
                <h3>ADD KATALOG</h3>
                <div class="card-body">
                <?php
                if ($error) {
                ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $error ?>
                    </div>
                <?php
                }
                ?>
                <?php
                if ($sukses) {
                ?>
                    <div class="alert alert-success" role="alert">
                        <?= $sukses ?>
                    </div>
                <?php
                }
                ?>
                
                <div class="mb-3">
                    <label for="id_katalog" class="form-label"><b>ID Katalog</b></label>
                    <input type="text" class="form-control" id="id_katalog" name="id_katalog" value="<?= $id_katalog ?>" required placeholder="Masukkan ID Katalog (contoh: KTL001)" <?= ($op == 'edit') ? 'readonly' : '' ?>>
                </div>
                
                <div class="mb-3">
                    <label for="nama_katalog" class="form-label"><b>Nama Katalog</b></label>
                    <input type="text" class="form-control" id="nama_katalog" name="nama_katalog" value="<?= $nama_katalog ?>" required placeholder="Masukkan nama katalog">
                </div>
                
                <div class="mb-3">
                    <label for="harga" class="form-label"><b>Harga</b></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" id="harga" name="harga" value="<?= $harga ?>" required placeholder="0" min="0">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="gambar" class="form-label"><b>Gambar</b></label>
                    <div class="upload-area" onclick="document.getElementById('gambar').click()">
                        <div class="upload-icon">+</div>
                        <p class="mb-0"><strong>Klik untuk upload gambar</strong></p>
                        <small class="text-muted">atau drag & drop file disini</small>
                        <br>
                        <small class="text-muted">Format: JPG, JPEG, PNG, GIF (Max: 5MB)</small>
                    </div>
                    <input type="file" class="form-control d-none" id="gambar" name="gambar" accept="image/*" onchange="previewImage(event)" <?= ($op != 'edit') ? 'required' : '' ?>>
                    
                    <?php if ($gambar && $op == 'edit'): ?>
                        <div class="mt-3">
                            <p class="mb-2"><strong>Gambar saat ini:</strong></p>
                            <img src="uploads/<?= $gambar ?>" alt="Current Image" class="preview-image">
                        </div>
                    <?php endif; ?>
                    <div id="imagePreview"></div>
                </div>
                
                <div class="mb-3 text-center">
                    <input type="submit" name="simpan" value="<?= ($op == 'edit') ? 'UPDATE KATALOG' : 'TAMBAH KATALOG' ?>" class="btn btn-primary me-2" />
                    <a href="insirasi.php" class="btn btn-secondary">KEMBALI</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        function closePopup() {
            window.location.href = 'home_owner.php';
        }

        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('imagePreview');
            const uploadArea = document.querySelector('.upload-area');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <div class="mt-3">
                            <p class="mb-2"><strong>Preview gambar:</strong></p>
                            <img src="${e.target.result}" alt="Preview" class="preview-image">
                        </div>
                    `;
                    uploadArea.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
                uploadArea.style.display = 'block';
            }
        }

        // Drag and drop functionality
        const uploadArea = document.querySelector('.upload-area');
        const fileInput = document.getElementById('gambar');

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                previewImage({ target: { files: files } });
            }
        });

        // Format input harga dengan thousand separator
        document.getElementById('harga').addEventListener('input', function(e) {
            let value = e.target.value;
            // Remove non-numeric characters
            value = value.replace(/[^\d]/g, '');
            // Add thousand separator for display
            this.value = value;
        });

        // Auto generate ID Katalog
        document.getElementById('nama_katalog').addEventListener('input', function(e) {
            const namaKatalog = e.target.value;
            const idKatalogField = document.getElementById('id_katalog');
            
            if (namaKatalog && !idKatalogField.value && !idKatalogField.readOnly) {
                // Generate ID from first 3 characters + random number
                const prefix = namaKatalog.substring(0, 3).toUpperCase();
                const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                idKatalogField.value = 'KTL' + prefix + randomNum;
            }
        });
    </script>
</body>

</html>