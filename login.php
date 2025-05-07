<?php
session_start();

// Jika sudah login, langsung redirect ke dashboard
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

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    // Validasi
    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } else {
        // Query untuk mencari admin dengan username tersebut
        $query = "SELECT * FROM admin WHERE username = '$username'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $admin = mysqli_fetch_assoc($result);
            
            // Verifikasi password
            if (password_verify($password, $admin['password'])) {
                // Set session
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_nama'] = $admin['nama'];
                
                // Redirect ke halaman utama (bukan dashboard admin)
                header("Location: dashboard_utama.php?status=success&msg=Login berhasil! Selamat datang, " . $admin['nama']);
                exit;
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
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
    <title>Login Admin - Sistem Perpustakaan</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container" style="max-width: 500px; margin-top: 80px;">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-lock"></i> Login Admin</h2>
            </div>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php
            // Menampilkan pesan status jika ada
            if (isset($_GET['status']) && isset($_GET['msg'])) {
                if ($_GET['status'] == 'success') {
                    echo "<div class='alert alert-success'>" . $_GET['msg'] . "</div>";
                } else if ($_GET['status'] == 'error') {
                    echo "<div class='alert alert-danger'>" . $_GET['msg'] . "</div>";
                }
            }
            ?>
            
            <form method="POST" action="">
                <div>
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn">Login</button>
                </div>
                
                <div class="text-center mt-3">
                    Belum punya akun? <a href="register.php">Register</a>
                </div>
                
                <div class="text-center mt-2">
                    <a href="index.php">Kembali ke Beranda</a>
                </div>
            </form>
        </div>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Sistem Perpustakaan</p>
        </footer>
    </div>
</body>
</html>