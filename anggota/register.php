<?php
session_start();
require_once '../config/database.php';

$message = '';

// Proses pendaftaran anggota
if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $nis = mysqli_real_escape_string($conn, $_POST['nis']);
    $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Cek apakah NIS sudah terdaftar
    $query_check = "SELECT * FROM anggota WHERE nis = '$nis'";
    $result_check = mysqli_query($conn, $query_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        $message = "<div class='alert alert-danger'>NIS sudah terdaftar!</div>";
    } else {
        // Simpan data anggota
        $query = "INSERT INTO anggota (nama, nis, kelas, email) VALUES ('$nama', '$nis', '$kelas', '$email')";
        
        if (mysqli_query($conn, $query)) {
            $message = "<div class='alert alert-success'>Pendaftaran berhasil! Anda sekarang dapat meminjam buku menggunakan NIS Anda.</div>";
            // Reset form setelah berhasil
            $_POST = array();
        } else {
            $message = "<div class='alert alert-danger'>Pendaftaran gagal: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Anggota - Sistem Perpustakaan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Sistem Perpustakaan</h1>
            <nav>
                <ul>
                    <li><a href="../dashboard_utama.php">Beranda</a></li>
                    <li><a href="register.php">Daftar Anggota</a></li>
                    <li><a href="../admin/dashboard.php">Dashboard Admin</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <?php echo $message; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>Daftar Sebagai Anggota Perpustakaan</h2>
            </div>
            <form id="register-form" method="POST" action="">
                <div>
                    <label for="nama">Nama Lengkap:</label>
                    <input type="text" id="nama" name="nama" value="<?php echo isset($_POST['nama']) ? $_POST['nama'] : ''; ?>" required>
                </div>
                <div>
                    <label for="nis">NIS (Nomor Induk Siswa):</label>
                    <input type="text" id="nis" name="nis" value="<?php echo isset($_POST['nis']) ? $_POST['nis'] : ''; ?>" required>
                </div>
                <div>
                    <label for="kelas">Kelas:</label>
                    <input type="text" id="kelas" name="kelas" value="<?php echo isset($_POST['kelas']) ? $_POST['kelas'] : ''; ?>" required>
                </div>
                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>
                </div>
                <div>
                    <button type="submit" name="register" class="btn btn-success">Daftar</button>
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