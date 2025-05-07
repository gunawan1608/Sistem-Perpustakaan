<?php
require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "perpustakaan");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data
$query = "SELECT * FROM pengembalian";
$result = $conn->query($query);

// Siapkan HTML
$html = '<h2 style="text-align:center;">Laporan Pengembalian Buku</h2>';
$html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
$html .= '
    <tr>
        <th>ID</th>
        <th>ID Peminjaman</th>
        <th>Tgl Dikembalikan</th>
        <th>Keterlambatan</th>
        <th>Denda</th>
        <th>Keterangan</th>
        <th>Tgl Dibuat</th>
    </tr>
';

while ($row = $result->fetch_assoc()) {
    $html .= '<tr>';
    $html .= '<td>' . $row['id'] . '</td>';
    $html .= '<td>' . $row['id_peminjaman'] . '</td>';
    $html .= '<td>' . $row['tanggal_dikembalikan'] . '</td>';
    $html .= '<td>' . $row['keterlambatan'] . '</td>';
    $html .= '<td>' . $row['denda'] . '</td>';
    $html .= '<td>' . $row['keterangan'] . '</td>';
    $html .= '<td>' . $row['created_at'] . '</td>';
    $html .= '</tr>';
}
$html .= '</table>';

// Buat dan render PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Output ke browser (download)
$dompdf->stream('laporan_pengembalian.pdf', ['Attachment' => true]);
exit;
?>