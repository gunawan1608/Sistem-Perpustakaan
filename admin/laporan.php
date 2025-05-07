<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek login - menggunakan sesi login_utama alih-alih sesi admin
if (!isset($_SESSION['admin_id'])) {
    // Redirect ke halaman login utama jika belum login
    header("Location: ../index.php");
    exit();
}

// Default tanggal untuk filter
$tgl_mulai = isset($_POST['tgl_mulai']) ? $_POST['tgl_mulai'] : date('Y-m-d', strtotime('-30 days'));
$tgl_selesai = isset($_POST['tgl_selesai']) ? $_POST['tgl_selesai'] : date('Y-m-d');

// Query untuk mendapatkan laporan peminjaman (selalu menggunakan query lengkap)
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

// Eksekusi query
$result = mysqli_query($conn, $query);
$report = [];
while ($row = mysqli_fetch_assoc($result)) {
    $report[] = $row;
}

// Mendapatkan informasi anggota yang sedang login
$admin_id = $_SESSION['admin_id'];
$query_admin = "SELECT * FROM admin WHERE id = '$admin_id'";
$result_admin = mysqli_query($conn, $query_admin);
$admin = mysqli_fetch_assoc($result_admin);
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
        <h1>Dashboard Perpustakaan</h1>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="tambah_buku.php">Tambah Buku</a></li>
                <li><a href="laporan.php">Laporan</a></li>
                <li><a href="../dashboard_utama.php">Halaman Utama</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Selamat Datang, <?php echo $admin['nama']; ?></h2>
        </div>
        <p>Dari halaman ini Anda dapat melihat dan mengekspor laporan peminjaman buku perpustakaan.</p>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>Laporan Peminjaman</h2>
        </div>
        <form method="POST" action="" style="margin-bottom: 20px;">
            <div style="display: flex; gap: 10px; align-items: flex-end;">
                <div>
                    <label for="tgl_mulai">Tanggal Mulai:</label>
                    <input type="date" id="tgl_mulai" name="tgl_mulai"
                           value="<?php echo $tgl_mulai; ?>" required>
                </div>
                <div>
                    <label for="tgl_selesai">Tanggal Selesai:</label>
                    <input type="date" id="tgl_selesai" name="tgl_selesai"
                           value="<?php echo $tgl_selesai; ?>" required>
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
                                    <?php
                                    $today = date('Y-m-d');
                                    $batas_kembali = isset($row['batas_kembali']) ? $row['batas_kembali'] : null;
                                    
                                    if ($batas_kembali && $today > $batas_kembali) {
                                        echo '<span style="color: red;">Terlambat</span>';
                                    } else {
                                        echo 'Dipinjam';
                                    }
                                    ?>
                                <?php else: ?>
                                    <span style="color: green;">Dikembalikan</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['tanggal_dikembalikan'] != 'Belum Kembali' ? date('d/m/Y', strtotime($row['tanggal_dikembalikan'])) : '-'; ?></td>
                            <td>Rp <?php echo number_format($row['denda'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="8" style="text-align: right;"><strong>Total Denda:</strong></td>
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
    
    <div class="card">
        <div class="card-header">
            <h2>Statistik Peminjaman</h2>
        </div>
        <?php
        // Hitung statistik peminjaman
        $query_stats = "SELECT 
            COUNT(*) as total_peminjaman,
            SUM(CASE WHEN status = 'dipinjam' THEN 1 ELSE 0 END) as sedang_dipinjam,
            SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as terlambat,
            SUM(CASE WHEN status = 'dikembalikan' THEN 1 ELSE 0 END) as dikembalikan
            FROM peminjaman";
        $result_stats = mysqli_query($conn, $query_stats);
        $stats = mysqli_fetch_assoc($result_stats);
        
        // Hitung total denda
        $query_denda = "SELECT SUM(denda) as total_denda FROM pengembalian";
        $result_denda = mysqli_query($conn, $query_denda);
        $denda = mysqli_fetch_assoc($result_denda);
        ?>
        
        <div style="display: flex; justify-content: space-between; margin-top: 20px;">
            <div style="text-align: center; padding: 15px; background-color: #e3f2fd; border-radius: 5px; width: 22%;">
                <h3>Total Peminjaman</h3>
                <p style="font-size: 24px; font-weight: bold;"><?php echo $stats['total_peminjaman']; ?></p>
            </div>
            <div style="text-align: center; padding: 15px; background-color: #e8f5e9; border-radius: 5px; width: 22%;">
                <h3>Sedang Dipinjam</h3>
                <p style="font-size: 24px; font-weight: bold;"><?php echo $stats['sedang_dipinjam']; ?></p>
            </div>
            <div style="text-align: center; padding: 15px; background-color: #fff3e0; border-radius: 5px; width: 22%;">
                <h3>Terlambat</h3>
                <p style="font-size: 24px; font-weight: bold;"><?php echo $stats['terlambat']; ?></p>
            </div>
            <div style="text-align: center; padding: 15px; background-color: #ffebee; border-radius: 5px; width: 22%;">
                <h3>Total Denda</h3>
                <p style="font-size: 24px; font-weight: bold;">Rp <?php echo number_format($denda['total_denda'], 0, ',', '.'); ?></p>
            </div>
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
        <p>&copy; <?php echo date('Y'); ?> Sistem Perpustakaan Sederhana</p>
    </div>
</footer>
</body>
</html>