<?php
session_start();
require 'koneksi.php';
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Pastikan hanya admin yang bisa ekspor semua data
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil data pendaftaran ekskul
$query = mysqli_query($koneksi, "
    SELECT id, nama_siswa, kelas, nama_ekskul, tanggal_daftar
    FROM ekskul
    ORDER BY id DESC
");

if (!$query) {
    exit("Gagal mengambil data.");
}

$html = '
<h2 style="text-align:center;">LAPORAN PENDAFTARAN EKSKUL</h2>
<p style="text-align:center;">EkskulKu</p>
<table border="1" cellpadding="6" cellspacing="0"
       width="100%" style="border-collapse:collapse;">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th>Ekstrakurikuler</th>
            <th>Tanggal Daftar</th>
        </tr>
    </thead>
    <tbody>';

$no = 1;

while ($row = mysqli_fetch_assoc($query)) {
    $html .= '<tr>
        <td>' . $no++ . '</td>
        <td>' . htmlspecialchars($row['nama_siswa']) . '</td>
        <td>' . htmlspecialchars($row['kelas']) . '</td>
        <td>' . htmlspecialchars($row['nama_ekskul']) . '</td>
        <td>' . htmlspecialchars($row['tanggal_daftar']) . '</td>
    </tr>';
}

$html .= '
    </tbody>
</table>';

$options = new Options();
$options->set('isRemoteEnabled', false);

$pdf = new Dompdf($options);
$pdf->loadHtml($html, 'UTF-8');
$pdf->setPaper('A4', 'landscape');
$pdf->render();
$pdf->stream('laporan_pendaftaran_ekskul.pdf', [
    'Attachment' => true
]);
exit;
?>