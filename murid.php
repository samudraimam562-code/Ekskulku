
<?php
session_start();
include 'koneksi.php';

if (($_SESSION['role'] ?? '') !== 'murid') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'] ?? '';
$pesan = '';
$status_pesan = '';

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'berhasil') {
        $pesan = 'Pendaftaran berhasil!';
        $status_pesan = 'success';
    } elseif ($_GET['status'] === 'gagal') {
        $pesan = 'Pendaftaran gagal. Periksa kembali data kamu.';
        $status_pesan = 'error';
    } elseif ($_GET['status'] === 'kosong') {
        $pesan = 'Mohon isi semua data dengan benar.';
        $status_pesan = 'error';
    }
}

/* RIWAYAT PENDAFTARAN */
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM ekskul
     WHERE username_pendaftar = ?
     ORDER BY id DESC"
);

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);

$data = mysqli_stmt_get_result($stmt);
$total = mysqli_num_rows($data);

/* HITUNG STATUS PENDAFTARAN */
$jumlah_menunggu = 0;
$jumlah_diterima = 0;
$jumlah_ditolak = 0;

$hitung = mysqli_prepare(
    $koneksi,
    "SELECT
        SUM(CASE WHEN status = 'Menunggu' THEN 1 ELSE 0 END) AS menunggu,
        SUM(CASE WHEN status = 'Diterima' THEN 1 ELSE 0 END) AS diterima,
        SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) AS ditolak
     FROM ekskul
     WHERE username_pendaftar = ?"
);

mysqli_stmt_bind_param($hitung, "s", $username);
mysqli_stmt_execute($hitung);

$hasil_hitung = mysqli_fetch_assoc(
    mysqli_stmt_get_result($hitung)
);

if ($hasil_hitung) {
    $jumlah_menunggu = (int)($hasil_hitung['menunggu'] ?? 0);
    $jumlah_diterima = (int)($hasil_hitung['diterima'] ?? 0);
    $jumlah_ditolak = (int)($hasil_hitung['ditolak'] ?? 0);
}

/* INFORMASI EKSKUL */
$daftar_ekskul = [];

$query_info = mysqli_query(
    $koneksi,
    "SELECT id, nama_ekskul, deskripsi, jadwal, lokasi, pembina
     FROM informasi_ekskul
     ORDER BY nama_ekskul ASC"
);

if ($query_info) {
    while ($info = mysqli_fetch_assoc($query_info)) {
        $daftar_ekskul[] = $info;
    }
}

/* BADGE STATUS */
function badge_status($status) {
    $status = $status ?: 'Menunggu';
    $class = 'status-menunggu';

    if ($status === 'Diterima') {
        $class = 'status-diterima';
    } elseif ($status === 'Ditolak') {
        $class = 'status-ditolak';
    }

    return '<span class="status-badge ' . $class . '">' .
        htmlspecialchars($status) .
        '</span>';
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Murid | EkskulKu</title>

    <link rel="icon" href="images.png">
    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .status-badge {
            display: inline-block;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-menunggu {
            color: #92400e;
            background: #fef3c7;
        }

        .status-diterima {
            color: #166534;
            background: #dcfce7;
        }

        .status-ditolak {
            color: #991b1b;
            background: #fee2e2;
        }

        .detail-ekskul-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .detail-ekskul-card {
            background: #fff;
            border: 1px solid var(--border, #e5eaf2);
            border-radius: 14px;
            padding: 18px;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .detail-ekskul-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(23, 37, 84, .08);
        }

        .detail-ekskul-card h4 {
            margin: 0 0 9px;
            color: var(--navy, #172554);
            font-size: 16px;
        }

        .detail-ekskul-card p {
            color: var(--muted, #64748b);
            font-size: 13px;
            line-height: 1.6;
            margin: 0 0 13px;
        }

        .detail-meta {
            display: grid;
            gap: 7px;
            color: var(--muted, #64748b);
            font-size: 12px;
            margin-bottom: 14px;
        }

        .detail-meta i {
            width: 17px;
            color: var(--blue, #2563eb);
        }

        .detail-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--blue, #2563eb);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }

        .detail-link:hover {
            text-decoration: underline;
        }

        .empty-info {
            padding: 20px;
            text-align: center;
            color: var(--muted, #64748b);
            background: #f8fafc;
            border-radius: 12px;
        }

        .alasan-ditolak {
            display: block;
            max-width: 220px;
            margin-top: 5px;
            color: #991b1b;
            font-size: 11px;
        }

        @media (max-width: 600px) {
            .detail-ekskul-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <a href="murid.php" class="brand">
            <span class="brand-icon">
                <i class="fa-solid fa-graduation-cap"></i>
            </span>
            <span>EkskulKu</span>
        </a>

        <p class="sidebar-label">MENU MURID</p>

        <a href="#dashboard" class="menu-link active">
            <i class="fa-solid fa-gauge-high"></i>
            <span>Dashboard</span>
        </a>

        <a href="#pendaftaran" class="menu-link">
            <i class="fa-solid fa-pen-to-square"></i>
            <span>Daftar Ekskul</span>
        </a>

        <a href="#informasi" class="menu-link">
            <i class="fa-solid fa-circle-info"></i>
            <span>Informasi Ekskul</span>
        </a>

        <a href="#riwayat" class="menu-link">
            <i class="fa-solid fa-layer-group"></i>
            <span>Riwayat Pendaftaran</span>
        </a>

        <div class="sidebar-bottom">

            <div class="profile-mini">

                <div class="avatar">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div class="profile-info">
                    <strong><?= htmlspecialchars($username) ?></strong>
                    <span>Murid</span>
                </div>

            </div>

            <a href="logout.php" class="menu-link logout-link"
               onclick="return confirm('Yakin ingin keluar dari akun ini?')">

                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>

            </a>

        </div>

    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content">

        <!-- HEADER -->
        <header class="topbar" id="dashboard">

            <div>
                <p class="eyebrow">PANEL MURID</p>
                <h1>Dashboard</h1>

                <p class="subtitle">
                    Kelola pendaftaran dan kembangkan bakatmu.
                </p>
            </div>

            <div class="admin-badge">👤 Akun Murid</div>

        </header>

        <!-- BANNER -->
        <section class="welcome-banner">

            <div>
                <p class="welcome-small">
                    SELAMAT DATANG DI EKSKULKU
                </p>

                <h2>
                    Temukan Minatmu,<br>
                    Raih Prestasimu!
                </h2>

                <p>
                    Pilih kegiatan favoritmu dan kembangkan bakat
                    bersama teman-temanmu.
                </p>
            </div>

        </section>

        <!-- STATISTIK -->
        <section class="stats-grid">

            <div class="stat-card">

                <div class="stat-icon blue">
                    <i class="fa-solid fa-users"></i>
                </div>

                <div>
                    <p>Total Pendaftaran</p>
                    <h3><?= $total ?></h3>
                    <span>Data milikmu</span>
                </div>

            </div>

            <div class="stat-card">

                <div class="stat-icon purple">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>

                <div>
                    <p>Menunggu</p>
                    <h3><?= $jumlah_menunggu ?></h3>
                    <span>Belum diproses admin</span>
                </div>

            </div>

            <div class="stat-card">

                <div class="stat-icon green">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>

                <div>
                    <p>Diterima</p>
                    <h3><?= $jumlah_diterima ?></h3>
                    <span>Pendaftaran disetujui</span>
                </div>

            </div>

        </section>

        <!-- FORM PENDAFTARAN DAN INFORMASI -->
        <div class="content-grid">

            <!-- FORM PENDAFTARAN -->
            <section class="card" id="pendaftaran">

                <div class="section-heading">

                    <div>
                        <p class="eyebrow">MULAI SEKARANG</p>

                        <h3>Formulir Pendaftaran</h3>

                        <p class="muted">
                            Isi data berikut untuk mendaftar ekskul.
                        </p>
                    </div>

                    <div class="heading-icon">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>

                </div>

                <?php if ($pesan !== ''): ?>

                    <div class="alert alert-<?= htmlspecialchars($status_pesan) ?>"
                         role="status">

                        <?= htmlspecialchars($pesan) ?>

                    </div>

                <?php endif; ?>

                <form action="create.php"
                      method="POST"
                      class="registration-form">

                    <div class="form-group">

                        <label for="nama_siswa">Nama Lengkap</label>

                        <input
                            type="text"
                            id="nama_siswa"
                            name="nama_siswa"
                            placeholder="Masukkan nama lengkap"
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
                            placeholder="Contoh: XI PPLG 1"
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

                            <option value="">
                                Pilih kegiatan favoritmu
                            </option>

                            <option value="Pramuka">Pramuka</option>
                            <option value="Paskibra">Paskibra</option>
                            <option value="Basket">Basket</option>
                            <option value="PMR">PMR</option>
                            <option value="Rohis">Rohis</option>

                        </select>

                    </div>

                    <button type="submit"
                            name="daftar"
                            class="btn btn-primary">

                        Daftar Sekarang →

                    </button>

                    <p class="form-note">
                        Pastikan nama dan kelas sudah benar.
                    </p>

                </form>

            </section>

            <!-- INFORMASI SINGKAT -->
            <section class="card info-card">

                <p class="eyebrow">KENALI DIRIMU</p>

                <h3>Pilih Sesuai Minatmu</h3>

                <p class="muted">
                    Kembangkan kemampuanmu melalui kegiatan di luar kelas.
                </p>

                <div class="activity-list">

                    <div class="activity-item">
                        <div class="activity-icon orange">⚑</div>
                        <div>
                            <strong>Pramuka</strong>
                            <p>Melatih disiplin dan kemandirian.</p>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon blue">★</div>
                        <div>
                            <strong>Paskibra</strong>
                            <p>Membangun nasionalisme dan kepemimpinan.</p>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon green">●</div>
                        <div>
                            <strong>Basket</strong>
                            <p>Melatih kebugaran dan kerja sama tim.</p>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon red">+</div>
                        <div>
                            <strong>PMR</strong>
                            <p>Belajar kepedulian dan pertolongan pertama.</p>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon purple">✦</div>
                        <div>
                            <strong>Rohis</strong>
                            <p>Mengembangkan pengetahuan keagamaan.</p>
                        </div>
                    </div>

                </div>

            </section>

        </div>

        <!-- DETAIL INFORMASI EKSKUL -->
        <section class="card" id="informasi" style="margin-top: 24px;">

            <div class="section-heading">

                <div>
                    <p class="eyebrow">JELAJAHI KEGIATAN</p>

                    <h3>Detail Informasi Ekstrakurikuler</h3>

                    <p class="muted">
                        Lihat deskripsi, jadwal, lokasi, dan pembina
                        setiap kegiatan.
                    </p>
                </div>

                <div class="heading-icon">
                    <i class="fa-solid fa-circle-info"></i>
                </div>

            </div>

            <?php if (count($daftar_ekskul) > 0): ?>

                <div class="detail-ekskul-grid">

                    <?php foreach ($daftar_ekskul as $info): ?>

                        <article class="detail-ekskul-card">

                            <h4>
                                <?= htmlspecialchars($info['nama_ekskul']) ?>
                            </h4>

                            <p>
                                <?= htmlspecialchars(
                                    $info['deskripsi'] ?:
                                    'Informasi kegiatan belum ditambahkan.'
                                ) ?>
                            </p>

                            <div class="detail-meta">

                                <span>
                                    <i class="fa-regular fa-clock"></i>

                                    <?= htmlspecialchars(
                                        $info['jadwal'] ?:
                                        'Jadwal belum tersedia'
                                    ) ?>
                                </span>

                                <span>
                                    <i class="fa-solid fa-location-dot"></i>

                                    <?= htmlspecialchars(
                                        $info['lokasi'] ?:
                                        'Lokasi belum tersedia'
                                    ) ?>
                                </span>

                                <span>
                                    <i class="fa-solid fa-user-tie"></i>

                                    <?= htmlspecialchars(
                                        $info['pembina'] ?:
                                        'Pembina belum tersedia'
                                    ) ?>
                                </span>

                            </div>

                            <a class="detail-link"
                               href="detail_ekskul.php?id=<?= (int)$info['id'] ?>">

                                Lihat detail
                                <i class="fa-solid fa-arrow-right"></i>

                            </a>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="empty-info">
                    Informasi ekstrakurikuler belum tersedia.
                    Silakan hubungi admin sekolah.
                </div>

            <?php endif; ?>

        </section>

        <!-- RIWAYAT PENDAFTARAN -->
        <section class="table-card" id="riwayat">

            <div class="table-heading">

                <div>
                    <h2>Riwayat Pendaftaran</h2>

                    <p>
                        Periksa status setiap pendaftaran ekstrakurikulermu.
                    </p>
                </div>

                <span class="count-badge">
                    <?= $total ?> data
                </span>

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
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($total > 0): ?>

                        <?php $no = 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($data)): ?>

                            <tr>

                                <td><?= $no++ ?></td>

                                <td>
                                    <?= htmlspecialchars($row['nama_siswa']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['kelas']) ?>
                                </td>

                                <td>
                                    <span class="ekskul-badge">
                                        <?= htmlspecialchars($row['nama_ekskul']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['tanggal_daftar']) ?>
                                </td>

                                <td>

                                    <?= badge_status($row['status'] ?? 'Menunggu') ?>

                                    <?php if (
                                        ($row['status'] ?? '') === 'Ditolak' &&
                                        !empty($row['alasan_penolakan'])
                                    ): ?>

                                        <small class="alasan-ditolak">
                                            Alasan:
                                            <?= htmlspecialchars($row['alasan_penolakan']) ?>
                                        </small>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <?php if (
                                        ($row['status'] ?? 'Menunggu') === 'Menunggu'
                                    ): ?>

                                        <a
                                            href="edit.php?id=<?= (int)$row['id'] ?>"
                                            class="action-btn edit-btn"
                                            title="Edit pendaftaran"
                                        >✎</a>

                                    <?php else: ?>

                                        <span title="Pendaftaran sudah diproses admin">
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="empty-state">
                                Belum ada data pendaftaran.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <div class="table-footer">

                <span>
                    Total pendaftaran:
                    <strong><?= $total ?></strong>
                </span>

                <span>
                    EkskulKu · Dashboard Murid
                </span>

            </div>

        </section>

        <!-- FOOTER -->
        <footer class="footer">
            © <?= date('Y') ?> EkskulKu · Sistem Informasi Ekstrakurikuler
        </footer>

    </main>

</div>

</body>
</html>
