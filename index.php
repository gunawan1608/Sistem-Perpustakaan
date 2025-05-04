<?php
session_start();
require_once 'config/database.php';

// Menampilkan daftar buku
$query = "SELECT * FROM buku ORDER BY judul ASC";
$result = mysqli_query($conn, $query);
$books = [];

while ($row = mysqli_fetch_assoc($result)) {
    $books[] = $row;
}

// Proses peminjaman buku jika form disubmit
$message = '';
if (isset($_POST['pinjam_buku'])) {
    $id_buku = $_POST['id_buku'];
    $nis = $_POST['nis'];
    $tanggal_kembali = $_POST['tanggal_kembali'];
    
    // Cek apakah anggota dengan NIS tersebut terdaftar
    $query_anggota = "SELECT * FROM anggota WHERE nis = '$nis'";
    $result_anggota = mysqli_query($conn, $query_anggota);
    
    if (mysqli_num_rows($result_anggota) > 0) {
        $anggota = mysqli_fetch_assoc($result_anggota);
        $id_anggota = $anggota['id'];
        
        // Cek ketersediaan buku
        $query_buku = "SELECT * FROM buku WHERE id = $id_buku";
        $result_buku = mysqli_query($conn, $query_buku);
        $buku = mysqli_fetch_assoc($result_buku);
        
        if ($buku['jumlah_tersedia'] > 0) {
            // Proses peminjaman
            $tanggal_pinjam = date('Y-m-d');
            
            // Update jumlah buku tersedia
            $query_update = "UPDATE buku SET jumlah_tersedia = jumlah_tersedia - 1 WHERE id = $id_buku";
            mysqli_query($conn, $query_update);
            
            // Catat peminjaman
            $query_pinjam = "INSERT INTO peminjaman (id_buku, id_anggota, tanggal_pinjam, tanggal_kembali) 
                              VALUES ($id_buku, $id_anggota, '$tanggal_pinjam', '$tanggal_kembali')";
            
            if (mysqli_query($conn, $query_pinjam)) {
                $message = "<div class='alert alert-success'>Buku berhasil dipinjam!</div>";
                // Refresh halaman untuk mendapatkan data terbaru
                header("Location: index.php?status=success&msg=Buku berhasil dipinjam!");
                exit();
            } else {
                $message = "<div class='alert alert-danger'>Gagal meminjam buku: " . mysqli_error($conn) . "</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Maaf, buku tidak tersedia untuk dipinjam.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>NIS tidak ditemukan. Silakan daftar terlebih dahulu.</div>";
    }
}

// Menampilkan pesan status jika ada
if (isset($_GET['status']) && isset($_GET['msg'])) {
    if ($_GET['status'] == 'success') {
        $message = "<div class='alert alert-success'>" . $_GET['msg'] . "</div>";
    } else if ($_GET['status'] == 'error') {
        $message = "<div class='alert alert-danger'>" . $_GET['msg'] . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Perpustakaan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Sistem Perpustakaan</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="register.php">Daftar Anggota</a></li>
                    <li><a href="login.php">Login Admin</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <?php echo $message; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>Daftar Buku</h2>
            </div>
            <?php if (count($books) > 0): ?>
                <div class="book-list">
                    <?php foreach ($books as $book): ?>
                        <div class="book-card">
                            <h3><?php echo $book['judul']; ?></h3>
                            <p><strong>Penulis:</strong> <?php echo $book['penulis']; ?></p>
                            <p><strong>Penerbit:</strong> <?php echo $book['penerbit']; ?></p>
                            <p><strong>Tahun:</strong> <?php echo $book['tahun_terbit']; ?></p>
                            <p><strong>Kategori:</strong> <?php echo $book['kategori']; ?></p>
                            <p>
                                <strong>Status:</strong> 
                                <?php if ($book['jumlah_tersedia'] > 0): ?>
                                    <span class="status-available">Tersedia (<?php echo $book['jumlah_tersedia']; ?>)</span>
                                <?php else: ?>
                                    <span class="status-borrowed">Tidak Tersedia</span>
                                <?php endif; ?>
                            </p>
                            <?php if ($book['jumlah_tersedia'] > 0): ?>
                                <button class="btn" onclick="document.getElementById('pinjam-form-container').style.display='block'; document.getElementById('id_buku').value='<?php echo $book['id']; ?>';">Pinjam</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Belum ada buku yang tersedia.</p>
            <?php endif; ?>
        </div>
        
        <div id="pinjam-form-container" class="card" style="display: none;">
            <div class="card-header">
                <h2>Form Peminjaman Buku</h2>
            </div>
            <form id="pinjam-form" method="POST" action="">
                <input type="hidden" id="id_buku" name="id_buku">
                <div>
                    <label for="nis">NIS Anggota:</label>
                    <input type="text" id="nis" name="nis" required>
                </div>
                <div>
                    <label for="tanggal_kembali">Tanggal Kembali:</label>
                    <input type="date" id="tanggal_kembali" name="tanggal_kembali" required>
                </div>
                <div>
                    <button type="submit" name="pinjam_buku" class="btn btn-success">Pinjam Buku</button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('pinjam-form-container').style.display='none';">Batal</button>
                </div>
            </form>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Sistem Perpustakaan Sederhana</p>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>