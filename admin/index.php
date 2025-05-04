<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek login
cek_login();

// Mendapatkan daftar peminjaman aktif
$active_loans = get_active_loans();

// Proses kembalikan buku
$message = '';
if (isset($_POST['kembalikan'])) {
    $id_peminjaman = $_POST['id_peminjaman'];
    
    if (kembalikan_buku($id_peminjaman)) {
        $message = "<div class='alert alert-success'>Buku berhasil dikembalikan!</div>";
        // Refresh data
        $active_loans = get_active_loans();
    } else {
        $message = "<div class='alert alert-danger'>Gagal mengembalikan buku!</div>";
    }
}

// Update status terlambat
$today = date('Y-m-d');
$query_update = "UPDATE peminjaman SET status = 'terlambat' WHERE tanggal_kembali < '$today' AND status = 'dipinjam'";
mysqli_query($conn, $query_update);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Perpustakaan</title>
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
                <h2>Selamat Datang, <?php echo $_SESSION['admin_nama']; ?></h2>
            </div>
            <p>Selamat datang di panel admin Sistem Perpustakaan. Dari sini Anda dapat mengelola buku dan melihat laporan peminjaman.</p>
        </div>
        
        <?php echo $message; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>Peminjaman Aktif</h2>
            </div>
            <?php if (count($active_loans) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Judul Buku</th>
                            <th>Peminjam</th>
                            <th>NIS</th>
                            <th>Tanggal Pinjam</th>
                            <th>Batas Kembali</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($active_loans as $loan): ?>
                            <tr>
                                <td><?php echo $loan['judul']; ?></td>
                                <td><?php echo $loan['nama']; ?></td>
                                <td><?php echo $loan['nis']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($loan['tanggal_pinjam'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($loan['tanggal_kembali'])); ?></td>
                                <td>
                                    <?php 
                                    if ($loan['status'] == 'terlambat') {
                                        echo '<span style="color: red;">Terlambat</span>';
                                    } else {
                                        echo 'Dipinjam';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <form method="POST" action="">
                                        <input type="hidden" name="id_peminjaman" value="<?php echo $loan['id']; ?>">
                                        <button type="submit" name="kembalikan" class="btn btn-success" onclick="return confirm('Pastikan buku dalam kondisi baik. Lanjutkan?');">Kembalikan</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Tidak ada peminjaman aktif saat ini.</p>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>Statistik Perpustakaan</h2>
            </div>
            <?php
            // Hitung jumlah buku
            $query_buku = "SELECT COUNT(*) as total_buku, SUM(total_stok) as total_stok, SUM(jumlah_tersedia) as total_tersedia FROM buku";
            $result_buku = mysqli_query($conn, $query_buku);
            $stat_buku = mysqli_fetch_assoc($result_buku);
            
            // Hitung jumlah anggota
            $query_anggota = "SELECT COUNT(*) as total_anggota FROM anggota";
            $result_anggota = mysqli_query($conn, $query_anggota);
            $stat_anggota = mysqli_fetch_assoc($result_anggota);
            
            // Hitung jumlah peminjaman
            $query_pinjam = "SELECT COUNT(*) as total_pinjam FROM peminjaman";
            $result_pinjam = mysqli_query($conn, $query_pinjam);
            $stat_pinjam = mysqli_fetch_assoc($result_pinjam);
            ?>
            
            <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                <div style="text-align: center; padding: 15px; background-color: #e3f2fd; border-radius: 5px; width: 22%;">
                    <h3>Judul Buku</h3>
                    <p style="font-size: 24px; font-weight: bold;"><?php echo $stat_buku['total_buku']; ?></p>
                </div>
                <div style="text-align: center; padding: 15px; background-color: #e8f5e9; border-radius: 5px; width: 22%;">
                    <h3>Total Stok</h3>
                    <p style="font-size: 24px; font-weight: bold;"><?php echo $stat_buku['total_stok']; ?></p>
                </div>
                <div style="text-align: center; padding: 15px; background-color: #fff3e0; border-radius: 5px; width: 22%;">
                    <h3>Anggota</h3>
                    <p style="font-size: 24px; font-weight: bold;"><?php echo $stat_anggota['total_anggota']; ?></p>
                </div>
                <div style="text-align: center; padding: 15px; background-color: #ffebee; border-radius: 5px; width: 22%;">
                    <h3>Peminjaman</h3>
                    <p style="font-size: 24px; font-weight: bold;"><?php echo $stat_pinjam['total_pinjam']; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Sistem Perpustakaan Sederhana</p>
        </div>
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>