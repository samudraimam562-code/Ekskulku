<?php
session_start();
require_once 'koneksi.php';

if (($_SESSION['role'] ?? '') !== 'murid') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['daftar'])) {
    header("Location: murid.php");
    exit;
}

$username = $_SESSION['username'] ?? '';
$nama = trim($_POST['nama_siswa'] ?? '');
$kelas = trim($_POST['kelas'] ?? '');
$ekskul = trim($_POST['nama_ekskul'] ?? '');
$tanggal = date('Y-m-d');

$pilihan = [
    'Pramuka',
    'Paskibra',
    'Basket',
    'PMR',
    'Rohis'
];

if (
    $username === '' ||
    $nama === '' ||
    $kelas === '' ||
    strlen($nama) > 100 ||
    strlen($kelas) > 50 ||
    !in_array($ekskul, $pilihan, true)
) {
    header("Location: murid.php?status=kosong#pendaftaran");
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    "INSERT INTO ekskul
    (nama_siswa, kelas, nama_ekskul, tanggal_daftar, username_pendaftar)
    VALUES (?, ?, ?, ?, ?)"
);

if (!$stmt) {
    header("Location: murid.php?status=gagal#pendaftaran");
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "sssss",
    $nama,
    $kelas,
    $ekskul,
    $tanggal,
    $username
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: murid.php?status=berhasil#riwayat");
    exit;
}

mysqli_stmt_close($stmt);
header("Location: murid.php?status=gagal#pendaftaran");
exit;
?>

