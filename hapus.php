
<?php
session_start();
include 'koneksi.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit('ID data tidak valid.');
}

$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM ekskul WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    $terhapus = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($terhapus > 0) {
        header("Location: admin.php?status=deleted");
        exit;
    }

    exit('Data tidak ditemukan atau sudah dihapus.');
}

mysqli_stmt_close($stmt);
exit('Gagal menghapus data.');
?>
