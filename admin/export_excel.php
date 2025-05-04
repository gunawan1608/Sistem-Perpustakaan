<?php
$conn = new mysqli("localhost", "root", "", "perpustakaan"); // Ganti sesuai database Anda

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=laporan_pengembalian.xls");

$query = "SELECT * FROM pengembalian";
$result = $conn->query($query);

echo "<table border='1'>
<tr>
    <th>ID</th>
    <th>ID Peminjaman</th>
    <th>Tanggal Dikembalikan</th>
    <th>Keterlambatan</th>
    <th>Denda</th>
    <th>Keterangan</th>
    <th>Tanggal Dibuat</th>
</tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>" . $row['id'] . "</td>
        <td>" . $row['id_peminjaman'] . "</td>
        <td>" . $row['tanggal_dikembalikan'] . "</td>
        <td>" . $row['keterlambatan'] . "</td>
        <td>Rp " . number_format($row['denda'], 2, ',', '.') . "</td>
        <td>" . $row['keterangan'] . "</td>
        <td>" . $row['created_at'] . "</td>
    </tr>";
}

echo "</table>";
?>
