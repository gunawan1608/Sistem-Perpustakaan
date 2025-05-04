<?php
session_start();
require_once 'config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/index.php");
    exit();
}

$message = '';

// Proses login
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM admin WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Login berhasil
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_nama'] = $user['nama'];
            
            header("Location: admin/index.php");
            exit();
        } else {
            $message = "<div class='alert alert-danger'>Username atau password salah!</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Username atau password salah!</div>";
    }
}

// Cek apakah tabel admin kosong
$query_check = "SELECT * FROM admin";
$result_check = mysqli_query($conn, $query_check);

// Jika belum ada admin, buat admin default
if (mysqli_num_rows($result_check) == 0) {
    $default_username = "admin";
    $default_password = password_hash("admin123", PASSWORD_DEFAULT);
    $default_nama = "Administrator";
    
    $query_insert = "INSERT INTO admin (username, password, nama) VALUES ('$default_username', '$default_password', '$default_nama')";
    mysqli_query($conn, $query_insert);
    
    $message = "<div class='alert alert-info'>Admin default telah dibuat. Username: admin, Password: admin123</div>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Sistem Perpustakaan</title>
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
                <h2>Login Admin</h2>
            </div>
            <form method="POST" action="">
                <div>
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div>
                    <button type="submit" name="login" class="btn btn-success">Login</button>
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