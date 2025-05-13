<?php
session_start();

// Jika sudah login, langsung redirect ke halaman utama
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard_utama.php");
    exit;
}

// Koneksi database
$conn = mysqli_connect("localhost", "root", "", "perpustakaan");

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$error = "";
$success = "";

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    
    // Validasi
    if (empty($username) || empty($password) || empty($confirm_password) || empty($nama)) {
        $error = "Semua field harus diisi!";
    } elseif ($password != $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } else {
        // Cek apakah username sudah ada
        $check_query = "SELECT * FROM admin WHERE username = '$username'";
        $result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($result) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert ke database
            $query = "INSERT INTO admin (username, password, nama) VALUES ('$username', '$hashed_password', '$nama')";
            
            if (mysqli_query($conn, $query)) {
                $success = "Registrasi berhasil! Silahkan login.";
                // Redirect ke halaman login setelah 2 detik
                header("Refresh: 2; URL=login.php?status=success&msg=Registrasi berhasil! Silahkan login.");
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Admin - Sistem Perpustakaan Ohara</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container" style="max-width: 600px; margin-top: 50px;">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-user-plus"></i> Registrasi Admin</h2>
            </div>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div>
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                
                <div>
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div>
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn">Register</button>
                </div>
                
                <div class="text-center mt-3">
                    Sudah punya akun? <a href="login.php">Login</a>
                </div>
                
                <div class="text-center mt-2">
                    <a href="index.php">Kembali ke Beranda</a>
                </div>
            </form>
        </div>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Sistem Perpustakaan Sederhana</p>
        </footer>
    </div>
</body>
</html>