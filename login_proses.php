<?php
session_start();
$query = new mysqli('localhost', 'root', '', 'nailartis');

$username = $_POST['username'];
$password = $_POST['password'];

$data = mysqli_query($query, "SELECT * from customer where Nama_lengkap='$username' and password='$password'")
    or die(mysqli_error($query));

$cek = mysqli_num_rows($data);
if ($cek > 0) {
    $_SESSION['username'] = $username;

    // Jangan simpan password ke dalam session demi keamanan
    // $_SESSION['password'] = $password;

    $lower_username = strtolower($_SESSION['username']);

    if ($_SESSION['username'] === 'Destiana') {
        header("Location: home_owner.php");
    } elseif (strpos($lower_username, 'pegawai') !== false) {
        header("Location: home_pegawai.php");
    } else {
        header("Location: home2.php");
    }
    exit();
} else {
    header("Location: login.php?pesan=gagal");
    exit();
}
