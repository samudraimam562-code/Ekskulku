<?php
session_start();
include 'koneksi.php';

$role = $_SESSION['role'] ?? '';
$username = $_SESSION['username'] ?? '';

if (!in_array($role, ['admin', 'murid'], true)) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['update'])) {
    header("Location: " . ($role === 'admin' ? 'admin.php' : 'murid.php'));
    exit;
}

$token = $_POST['token'] ?? '';

if (
    !is_string($token) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    exit('Token tidak valid. Silakan kembali dan muat ulang halaman edit.');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nama = trim($_POST['nama_siswa'] ?? '');
$kelas = trim($_POST['kelas'] ?? '');
$ekskul = $_POST['nama_ekskul'] ?? '';

$pilihan = ['Pramuka', 'Paskibra', 'Basket', 'PMR', 'Rohis'];

if (
    !$id || $id < 1 || $nama === '' || $kelas === '' ||
    strlen($nama) > 100 || strlen($kelas) > 50 ||
    !in_array($ekskul, $pilihan, true)
) {
    exit('Data tidak valid.');
}

if ($role === 'admin') {
    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE ekskul
         SET nama_siswa = ?, kelas = ?, nama_ekskul = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt, "sssi", $nama, $kelas, $ekskul, $id
    );

    $tujuan = 'admin.php';
} else {
    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE ekskul
         SET nama_siswa = ?, kelas = ?, nama_ekskul = ?
         WHERE id = ? AND username_pendaftar = ?"
    );

    mysqli_stmt_bind_param(
        $stmt, "sssis",
        $nama, $kelas, $ekskul, $id, $username
    );

    $tujuan = 'murid.php';
}

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: $tujuan?pesan=berhasil");
    exit;
}

mysqli_stmt_close($stmt);
exit('Gagal memperbarui data.');
?>