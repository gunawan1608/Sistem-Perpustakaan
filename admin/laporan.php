<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek login
cek_login();

// Mendapatkan laporan peminjaman
$report = get_loan_report();

// Filter berdasarkan tanggal jika form disubmit
if (isset($_POST['filter'])) {
    $tgl_mulai = $_POST['tgl_mulai'];
    $tgl_selesai = $_POST['tgl_selesai'];
    $query = "SELECT 
            p.tanggal_pinjam,
            p.tanggal_kembali AS batas_kembali,
            b.judul,
            a.nama,
            a.nis,
            IFNULL(pk.tanggal_dikembalikan, 'Belum Kembali') AS tanggal_dikembalikan,
            IFNULL(pk.denda, 0) AS denda
          FROM peminjaman p
          JOIN buku b ON p.id_buku = b.id
          JOIN anggota a ON p.id_anggota = a.id
          LEFT JOIN pengembalian pk ON p.id = pk.id_peminjaman
          WHERE p.tanggal_pinjam BETWEEN '$tgl_mulai' AND '$tgl_selesai'
          ORDER BY p.tanggal_pinjam DESC";


    $result = mysqli_query($conn, $query);
    $report = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $report[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Peminjaman - Sistem Perpustakaan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <h1>Dashboard Admin</h1>
        <nav>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="tambah_buku.php">Tambah Buku</a></li>
                <li><a href="laporan.php">Laporan</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Laporan Peminjaman</h2>
        </div>
        <form method="POST" action="" style="margin-bottom: 20px;">
            <div style="display: flex; gap: 10px; align-items: flex-end;">
                <div>
                    <label for="tgl_mulai">Tanggal Mulai:</label>
                    <input type="date" id="tgl_mulai" name="tgl_mulai"
                           value="<?php echo isset($_POST['tgl_mulai']) ? $_POST['tgl_mulai'] : date('Y-m-d', strtotime('-30 days')); ?>" required>
                </div>
                <div>
                    <label for="tgl_selesai">Tanggal Selesai:</label>
                    <input type="date" id="tgl_selesai" name="tgl_selesai"
                           value="<?php echo isset($_POST['tgl_selesai']) ? $_POST['tgl_selesai'] : date('Y-m-d'); ?>" required>
                </div>
                <div>
                    <button type="submit" name="filter" class="btn btn-primary">Filter</button>
                    <button type="button" onclick="exportToPDF()" class="btn btn-success">Export PDF</button>
                    <button type="button" onclick="exportToExcel()" class="btn btn-info">Export Excel</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal Pinjam</th>
                    <th>Batas Kembali</th>
                    <th>Judul Buku</th>
                    <th>Nama Peminjam</th>
                    <th>NIS</th>
                    <th>Status</th>
                    <th>Tanggal Kembali</th>
                    <th>Denda</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($report)): ?>
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data peminjaman untuk periode ini.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($report as $row): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                            <td>
                                <?php
                                echo isset($row['batas_kembali']) && $row['batas_kembali'] !== null
                                    ? date('d/m/Y', strtotime($row['batas_kembali']))
                                    : '-';
                                ?>
                            </td>
                            <td><?php echo $row['judul']; ?></td>
                            <td><?php echo $row['nama']; ?></td>
                            <td><?php echo $row['nis']; ?></td>
                            <td>
                                <?php if ($row['tanggal_dikembalikan'] == 'Belum Kembali'): ?>
                                    <span class="badge bg-warning">Belum Kembali</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Sudah Kembali</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['tanggal_dikembalikan'] != 'Belum Kembali' ? date('d/m/Y', strtotime($row['tanggal_dikembalikan'])) : '-'; ?></td>
                            <td>Rp <?php echo number_format($row['denda'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="8" class="text-end"><strong>Total Denda:</strong></td>
                        <td>
                            <strong>
                                Rp <?php
                                $total_denda = array_sum(array_column($report, 'denda'));
                                echo number_format($total_denda, 0, ',', '.');
                                ?>
                            </strong>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function exportToPDF() {
        window.location.href = 'export_pdf.php?tgl_mulai=' + document.getElementById('tgl_mulai').value + '&tgl_selesai=' + document.getElementById('tgl_selesai').value;
    }

    function exportToExcel() {
        window.location.href = 'export_excel.php?tgl_mulai=' + document.getElementById('tgl_mulai').value + '&tgl_selesai=' + document.getElementById('tgl_selesai').value;
    }
</script>

<footer>
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Sistem Perpustakaan. All rights reserved.</p>
    </div>
</footer>
</body>
</html>
