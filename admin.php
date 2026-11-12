<?php
session_start();
require_once 'koneksi.php';

// Pastikan hanya admin yang bisa membuka halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Mengamankan teks yang ditampilkan di HTML
function aman($teks) {
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

// Ambil total pendaftar
$queryTotal = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM ekskul");
$totalPendaftar = mysqli_fetch_assoc($queryTotal)['total'] ?? 0;

// Hitung jumlah jenis ekskul
$queryJenis = mysqli_query(
    $koneksi,
    "SELECT COUNT(DISTINCT nama_ekskul) AS total FROM ekskul"
);
$totalJenis = mysqli_fetch_assoc($queryJenis)['total'] ?? 0;

// Ambil data pendaftaran
$queryData = mysqli_query(
    $koneksi,
    "SELECT * FROM ekskul ORDER BY id DESC"
);

if (!$queryData) {
    die('Gagal mengambil data pendaftaran: ' . aman(mysqli_error($koneksi)));
}

// Nama admin dari session
$namaAdmin = $_SESSION['username'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | EkskulKu</title>
    <link rel="icon" href="images.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<div class="dashboard">

    <aside class="sidebar">
        <a href="admin.php" class="brand">
            <span class="brand-icon">
                <i class="fa-solid fa-graduation-cap"></i>
            </span>
            <span>EkskulKu</span>
        </a>

        <p class="sidebar-label">MENU UTAMA</p>

        <a href="admin.php" class="menu-link active">
            <i class="fa-solid fa-gauge-high"></i>
            <span>Dashboard</span>
        </a>

        <a href="#data-pendaftar" class="menu-link">
            <i class="fa-solid fa-users"></i>
            <span>Data Pendaftar</span>
        </a>

        <div class="sidebar-bottom">
            <div class="profile-mini">
                <div class="avatar">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div class="profile-info">
                    <strong><?= aman($namaAdmin) ?></strong>
                    <span>Administrator</span>
                </div>
            </div>

            <a href="logout.php" class="menu-link logout-link"
               onclick="return confirm('Yakin ingin keluar dari akun?')">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <main class="main-content">

        <header class="topbar">
            <div>
                <p class="eyebrow">PANEL ADMINISTRATOR</p>
                <h1>Dashboard</h1>
                <p class="subtitle">Kelola pendaftaran ekstrakurikuler siswa.</p>
            </div>

            <div class="admin-badge">
                <i class="fa-solid fa-shield-halved"></i>
                Admin
            </div>
        </header>

        <section class="welcome-banner">
            <div>
                <p class="welcome-small">SELAMAT DATANG KEMBALI</p>
                <h2>Halo, <?= aman($namaAdmin) ?>! 👋</h2>
                <p>Pantau dan kelola data pendaftaran ekskul di sini.</p>
            </div>
            <div class="banner-icon">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </section>

        <section class="stats-grid">

            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <p>Total Pendaftar</p>
                    <h3><?= (int) $totalPendaftar ?></h3>
                    <span>Siswa terdaftar</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div>
                    <p>Jenis Ekskul</p>
                    <h3><?= (int) $totalJenis ?></h3>
                    <span>Jenis ekskul terdaftar</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <div>
                    <p>Status Sistem</p>
                    <h3 class="status-active">Aktif</h3>
                    <span>Dashboard siap digunakan</span>
                </div>
            </div>

        </section>

        <section class="table-card" id="data-pendaftar">

            <div class="table-heading">
                <div>
                    <h2>Data Pendaftar Ekskul</h2>
                    <p>Daftar siswa yang sudah mendaftar ekstrakurikuler.</p>
                </div>

                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" id="cariData"
                           placeholder="Cari nama atau ekskul...">
                </div>
                <a href="export_pdf.php" class="search-box">Expor PDF</a>

            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Ekstrakurikuler</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="isiTabel">
                        <?php if (mysqli_num_rows($queryData) > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($queryData)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <div class="student-name">
                                            <span class="student-avatar">
                                                <i class="fa-solid fa-user"></i>
                                            </span>
                                            <strong><?= aman($row['nama_siswa']) ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="class-badge">
                                            <?= aman($row['kelas']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="ekskul-badge">
                                            <?= aman($row['nama_ekskul']) ?>
                                        </span>
                                    </td>
                                    <td><?= aman($row['tanggal_daftar']) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a class="action-btn edit-btn"
                                               href="edit.php?id=<?= (int) $row['id'] ?>"
                                               title="Edit data"
                                               aria-label="Edit data">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>

                                            <a class="action-btn delete-btn"
                                               href="hapus.php?id=<?= (int) $row['id'] ?>"
                                               title="Hapus data"
                                               aria-label="Hapus data"
                                               onclick="return confirm('Yakin ingin menghapus pendaftaran ini?')">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="empty-state">
                                    <i class="fa-solid fa-folder-open"></i>
                                    <p>Belum ada data pendaftaran.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <span>Total data: <strong><?= (int) $totalPendaftar ?></strong> pendaftar</span>
                <span>EkskulKu Admin Panel</span>
            </div>

        </section>

        <footer class="footer">
            &copy; <?= date('Y') ?> EkskulKu. Sistem Pendaftaran Ekstrakurikuler.
        </footer>

    </main>
</div>

<script>
const kolomCari = document.getElementById('cariData');
const barisTabel = document.querySelectorAll('#isiTabel tr');

kolomCari.addEventListener('input', function () {
    const kata = this.value.toLowerCase();

    barisTabel.forEach(function (baris) {
        const isi = baris.textContent.toLowerCase();
        baris.style.display = isi.includes(kata) ? '' : 'none';
    });
});
</script>

</body>
</html>