<?php
require __DIR__ . '/../vendor/autoload.php';// pastikan ini sesuai dengan struktur folder project kamu

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$conn = new mysqli("localhost", "root", "", "perpustakaan");

// Query data pengembalian
$query = "SELECT * FROM pengembalian";
$result = $conn->query($query);

// Buat spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header kolom
$headers = ['ID', 'ID Peminjaman', 'Tanggal Dikembalikan', 'Keterlambatan', 'Denda', 'Keterangan', 'Tanggal Dibuat'];
$sheet->fromArray($headers, NULL, 'A1');

// Data
$rowNumber = 2;
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue("A$rowNumber", $row['id']);
    $sheet->setCellValue("B$rowNumber", $row['id_peminjaman']);
    $sheet->setCellValue("C$rowNumber", $row['tanggal_dikembalikan']);
    $sheet->setCellValue("D$rowNumber", $row['keterlambatan']);
    $sheet->setCellValue("E$rowNumber", $row['denda']);
    $sheet->setCellValue("F$rowNumber", $row['keterangan']);
    $sheet->setCellValue("G$rowNumber", $row['created_at']);
    $rowNumber++;
}

// Set header download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="laporan_pengembalian.xlsx"');
header('Cache-Control: max-age=0');

// Tulis ke output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
