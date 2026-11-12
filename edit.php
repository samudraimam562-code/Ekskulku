<?php
session_start();
include 'koneksi.php';

// Cek hak akses
$role = $_SESSION['role'] ?? '';
$username = $_SESSION['username'] ?? '';

if (!in_array($role, ['admin', 'murid'], true)) {
    header("Location: login.php");
    exit;
}

// Membuat CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Mengambil ID dari URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit('ID tidak valid.');
}

// Mengambil data berdasarkan role
if ($role === 'admin') {
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT * FROM ekskul WHERE id = ?"
    );

    if (!$stmt) {
        exit('Query gagal disiapkan.');
    }

    mysqli_stmt_bind_param($stmt, "i", $id);

} else {
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT * FROM ekskul
         WHERE id = ? AND username_pendaftar = ?"
    );

    if (!$stmt) {
        exit('Query gagal disiapkan.');
    }

    mysqli_stmt_bind_param($stmt, "is", $id, $username);
}

// Jalankan query
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Jika data tidak ditemukan
if (!$row) {
    exit('Data tidak ditemukan atau akses ditolak.');
}

// Pilihan ekskul
$pilihan = [
    'Pramuka',
    'Paskibra',
    'Basket',
    'PMR',
    'Rohis'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Pendaftaran Ekskul</title>

    <link rel="icon" href="images.png">
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="edit-page">
    <div class="edit-card">

        <div class="edit-header">
            <div class="edit-header-icon">✎</div>

            <div>
                <h2>Edit Pendaftaran</h2>
                <p>Perbarui data dan pilihan ekstrakurikulermu.</p>
            </div>
        </div>

        <form action="proses_update.php"
              method="POST"
              class="edit-form">

            <!-- ID data -->
            <input type="hidden"
                   name="id"
                   value="<?= (int) $row['id'] ?>">

            <!-- Token keamanan -->
            <input type="hidden"
                   name="token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-group">
                <label for="nama_siswa">Nama Lengkap</label>

                <input
                    type="text"
                    id="nama_siswa"
                    name="nama_siswa"
                    value="<?= htmlspecialchars($row['nama_siswa']) ?>"
                    maxlength="100"
                    required
                >
            </div>

            <div class="form-group">
                <label for="kelas">Kelas</label>

                <input
                    type="text"
                    id="kelas"
                    name="kelas"
                    value="<?= htmlspecialchars($row['kelas']) ?>"
                    maxlength="50"
                    required
                >
            </div>

            <div class="form-group">
                <label for="nama_ekskul">
                    Pilih Ekstrakurikuler
                </label>

                <select
                    id="nama_ekskul"
                    name="nama_ekskul"
                    required
                >
                    <option value="">-- Pilih Ekskul --</option>

                    <?php foreach ($pilihan as $item): ?>
                        <option
                            value="<?= htmlspecialchars($item) ?>"
                            <?= $row['nama_ekskul'] === $item ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($item) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <div class="edit-actions">

                <button
                    type="submit"
                    name="update"
                    value="1"
                    class="edit-submit"
                >
                    Simpan Perubahan
                </button>

                <a
                    href="<?= $role === 'admin' ? 'admin.php' : 'murid.php' ?>"
                    class="edit-cancel"
                >
                    Batal
                </a>

            </div>

        </form>

        <div class="edit-note">
            Pastikan nama, kelas, dan pilihan ekskul sudah benar.
        </div>

    </div>
</div>

</body>
</html>