<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek login
cek_login();

$message = '';

// Proses tambah buku
if (isset($_POST['tambah_buku'])) {
    $judul = sanitize($_POST['judul']);
    $penulis = sanitize($_POST['penulis']);
    $penerbit = sanitize($_POST['penerbit']);
    $tahun_terbit = (int)$_POST['tahun_terbit'];
    $isbn = sanitize($_POST['isbn']);
    $kategori = sanitize($_POST['kategori']);
    $jumlah = (int)$_POST['jumlah'];
    
    // Validasi input
    if (empty($judul) || empty($penulis) || empty($penerbit) || $tahun_terbit <= 0 || empty($isbn) || empty($kategori) || $jumlah <= 0) {
        $message = "<div class='alert alert-danger'>Semua field harus diisi dengan benar!</div>";
    } else {
        // Tambahkan buku ke database
        if (tambah_buku($judul, $penulis, $penerbit, $tahun_terbit, $isbn, $kategori, $jumlah)) {
            $message = "<div class='alert alert-success'>Buku berhasil ditambahkan!</div>";
            // Reset form
            $_POST = array();
        } else {
            $message = "<div class='alert alert-danger'>Gagal menambahkan buku: " . mysqli_error($conn) . "</div>";
        }
    }
}

// Proses hapus buku
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Cek apakah buku sedang dipinjam
    $query_check = "SELECT COUNT(*) as total FROM peminjaman WHERE id_buku = $id AND status = 'dipinjam'";
    $result_check = mysqli_query($conn, $query_check);
    $data_check = mysqli_fetch_assoc($result_check);
    
    if ($data_check['total'] > 0) {
        $message = "<div class='alert alert-danger'>Buku tidak dapat dihapus karena sedang dipinjam!</div>";
    } else {
        // Hapus buku
        $query_delete = "DELETE FROM buku WHERE id = $id";
        if (mysqli_query($conn, $query_delete)) {
            $message = "<div class='alert alert-success'>Buku berhasil dihapus!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Gagal menghapus buku: " . mysqli_error($conn) . "</div>";
        }
    }
}

// Mendapatkan daftar buku
$books = get_all_books();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku - Sistem Perpustakaan</title>
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
        <?php echo $message; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>Tambah Buku Baru</h2>
            </div>
            <form id="book-form" method="POST" action="">
                <div>
                    <label for="judul">Judul Buku:</label>
                    <input type="text" id="judul" name="judul" value="<?php echo isset($_POST['judul']) ? $_POST['judul'] : ''; ?>" required>
                </div>
                <div>
                    <label for="penulis">Penulis:</label>
                    <input type="text" id="penulis" name="penulis" value="<?php echo isset($_POST['penulis']) ? $_POST['penulis'] : ''; ?>" required>
                </div>
                <div>
                    <label for="penerbit">Penerbit:</label>
                    <input type="text" id="penerbit" name="penerbit" value="<?php echo isset($_POST['penerbit']) ? $_POST['penerbit'] : ''; ?>" required>
                </div>
                <div>
                    <label for="tahun_terbit">Tahun Terbit:</label>
                    <input type="number" id="tahun_terbit" name="tahun_terbit" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo isset($_POST['tahun_terbit']) ? $_POST['tahun_terbit'] : date('Y'); ?>" required>
                </div>
                <div>
                    <label for="isbn">ISBN:</label>
                    <input type="text" id="isbn" name="isbn" value="<?php echo isset($_POST['isbn']) ? $_POST['isbn'] : ''; ?>" required>
                </div>
                <div>
                    <label for="kategori">Kategori:</label>
                    <select id="kategori" name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Fiksi" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Fiksi') ? 'selected' : ''; ?>>Fiksi</option>
                        <option value="Non-Fiksi" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Non-Fiksi') ? 'selected' : ''; ?>>Non-Fiksi</option>
                        <option value="Sains" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Sains') ? 'selected' : ''; ?>>Sains</option>
                        <option value="Teknologi" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Teknologi') ? 'selected' : ''; ?>>Teknologi</option>
                        <option value="Sejarah" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Sejarah') ? 'selected' : ''; ?>>Sejarah</option>
                        <option value="Pendidikan" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Pendidikan') ? 'selected' : ''; ?>>Pendidikan</option>
                    </select>
                </div>
                <div>
                    <label for="jumlah">Jumlah Buku:</label>
                    <input type="number" id="jumlah" name="jumlah" min="1" value="<?php echo isset($_POST['jumlah']) ? $_POST['jumlah'] : '1'; ?>" required>
                </div>
                <div>
                    <button type="submit" name="tambah_buku" class="btn btn-success">Tambah Buku</button>
                </div>
            </form>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>Daftar Buku</h2>
            </div>
            <?php if (count($books) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Kategori</th>
                            <th>Tahun</th>
                            <th>ISBN</th>
                            <th>Stok</th>
                            <th>Tersedia</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td><?php echo $book['judul']; ?></td>
                                <td><?php echo $book['penulis']; ?></td>
                                <td><?php echo $book['kategori']; ?></td>
                                <td><?php echo $book['tahun_terbit']; ?></td>
                                <td><?php echo $book['isbn']; ?></td>
                                <td><?php echo $book['total_stok']; ?></td>
                                <td><?php echo $book['jumlah_tersedia']; ?></td>
                                <td>
                                    <a href="tambah_buku.php?action=delete&id=<?php echo $book['id']; ?>" class="btn btn-danger" onclick="return confirmDelete('Yakin ingin menghapus buku ini?');">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Belum ada buku yang tersedia.</p>
            <?php endif; ?>
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