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

$message = '';
$edit_mode = false;
$edit_data = null;

// Proses edit buku
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_mode = true;
    $book_id = (int)$_GET['id'];
    
    // Ambil data buku yang akan diedit
    $query_edit = "SELECT * FROM buku WHERE id = $book_id";
    $result_edit = mysqli_query($conn, $query_edit);
    
    if ($result_edit && mysqli_num_rows($result_edit) > 0) {
        $edit_data = mysqli_fetch_assoc($result_edit);
    } else {
        $message = "<div class='alert alert-danger'>Buku tidak ditemukan!</div>";
        $edit_mode = false;
    }
}

// Proses update buku
if (isset($_POST['update_buku']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
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
        // Proses upload gambar jika ada
        $gambar_lama = $_POST['gambar_lama'];
        $gambar = $gambar_lama; // Default jika tidak ada upload baru
        
        if ($_FILES['gambar']['size'] > 0) {
            $target_dir = "../assets/images/";
            
            // Cek apakah direktori sudah ada, jika belum buat baru
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION));
            $new_filename = "book_" . time() . "_" . uniqid() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            // Cek jenis file (hanya gambar yang diizinkan)
            $allowed_types = array("jpg", "jpeg", "png", "gif");
            if (!in_array($file_extension, $allowed_types)) {
                $message = "<div class='alert alert-danger'>Hanya file JPG, JPEG, PNG & GIF yang diizinkan!</div>";
            }
            // Cek ukuran file (maksimal 2MB)
            elseif ($_FILES["gambar"]["size"] > 2000000) {
                $message = "<div class='alert alert-danger'>Ukuran file terlalu besar (maksimal 2MB)!</div>";
            }
            else {
                // Upload file
                if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                    $gambar = $new_filename;
                    
                    // Hapus gambar lama jika bukan gambar default
                    if ($gambar_lama != 'default_book.jpg' && file_exists($target_dir . $gambar_lama)) {
                        unlink($target_dir . $gambar_lama);
                    }
                } else {
                    $message = "<div class='alert alert-danger'>Gagal mengupload gambar!</div>";
                }
            }
        }
        
        if (empty($message)) {
            // Update buku ke database
            $query_update = "UPDATE buku SET 
                judul = '$judul', 
                penulis = '$penulis', 
                penerbit = '$penerbit', 
                tahun_terbit = $tahun_terbit, 
                isbn = '$isbn', 
                kategori = '$kategori', 
                gambar = '$gambar',
                total_stok = $jumlah,
                jumlah_tersedia = $jumlah - (total_stok - jumlah_tersedia)
                WHERE id = $id";
                
            if (mysqli_query($conn, $query_update)) {
                $message = "<div class='alert alert-success'>Buku berhasil diperbarui!</div>";
                $edit_mode = false;
            } else {
                $message = "<div class='alert alert-danger'>Gagal memperbarui buku: " . mysqli_error($conn) . "</div>";
            }
        }
    }
}

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
        // Proses upload gambar
        $gambar = "default_book.jpg"; // Default jika tidak ada upload
        
        if ($_FILES['gambar']['size'] > 0) {
            $target_dir = "../assets/images/";
            
            // Cek apakah direktori sudah ada, jika belum buat baru
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION));
            $new_filename = "book_" . time() . "_" . uniqid() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            // Cek jenis file (hanya gambar yang diizinkan)
            $allowed_types = array("jpg", "jpeg", "png", "gif");
            if (!in_array($file_extension, $allowed_types)) {
                $message = "<div class='alert alert-danger'>Hanya file JPG, JPEG, PNG & GIF yang diizinkan!</div>";
            }
            // Cek ukuran file (maksimal 2MB)
            elseif ($_FILES["gambar"]["size"] > 2000000) {
                $message = "<div class='alert alert-danger'>Ukuran file terlalu besar (maksimal 2MB)!</div>";
            }
            else {
                // Upload file
                if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                    $gambar = $new_filename;
                } else {
                    $message = "<div class='alert alert-danger'>Gagal mengupload gambar!</div>";
                }
            }
        }
        
        if (empty($message)) {
            // Tambahkan buku ke database dengan gambar
            $query_insert = "INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, isbn, kategori, gambar, jumlah_tersedia, total_stok) 
                            VALUES ('$judul', '$penulis', '$penerbit', $tahun_terbit, '$isbn', '$kategori', '$gambar', $jumlah, $jumlah)";
                            
            if (mysqli_query($conn, $query_insert)) {
                $message = "<div class='alert alert-success'>Buku berhasil ditambahkan!</div>";
                // Reset form
                $_POST = array();
            } else {
                $message = "<div class='alert alert-danger'>Gagal menambahkan buku: " . mysqli_error($conn) . "</div>";
            }
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
        // Ambil nama file gambar sebelum hapus
        $query_image = "SELECT gambar FROM buku WHERE id = $id";
        $result_image = mysqli_query($conn, $query_image);
        if ($result_image && mysqli_num_rows($result_image) > 0) {
            $data_image = mysqli_fetch_assoc($result_image);
            $image_file = $data_image['gambar'];
            
            // Hapus file gambar jika bukan default
            if ($image_file != 'default_book.jpg' && file_exists("../assets/images/" . $image_file)) {
                unlink("../assets/images/" . $image_file);
            }
        }
        
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
    <title><?php echo $edit_mode ? 'Edit Buku' : 'Tambah Buku'; ?> - Sistem Perpustakaan Ohara</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .book-image {
            width: 100px;
            height: auto;
            object-fit: cover;
            border-radius: 5px;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
    </style>
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
            <p>Dari halaman ini Anda dapat menambahkan buku baru dan mengelola daftar buku yang ada.</p>
        </div>
        
        <?php echo $message; ?>
        
        <div class="card">
            <div class="card-header">
                <h2><?php echo $edit_mode ? 'Edit Buku' : 'Tambah Buku Baru'; ?></h2>
            </div>
            <form id="book-form" method="POST" action="" enctype="multipart/form-data">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                    <input type="hidden" name="gambar_lama" value="<?php echo $edit_data['gambar']; ?>">
                <?php endif; ?>
                
                <div>
                    <label for="judul">Judul Buku:</label>
                    <input type="text" id="judul" name="judul" value="<?php echo $edit_mode ? $edit_data['judul'] : (isset($_POST['judul']) ? $_POST['judul'] : ''); ?>" required>
                </div>
                <div>
                    <label for="penulis">Penulis:</label>
                    <input type="text" id="penulis" name="penulis" value="<?php echo $edit_mode ? $edit_data['penulis'] : (isset($_POST['penulis']) ? $_POST['penulis'] : ''); ?>" required>
                </div>
                <div>
                    <label for="penerbit">Penerbit:</label>
                    <input type="text" id="penerbit" name="penerbit" value="<?php echo $edit_mode ? $edit_data['penerbit'] : (isset($_POST['penerbit']) ? $_POST['penerbit'] : ''); ?>" required>
                </div>
                <div>
                    <label for="tahun_terbit">Tahun Terbit:</label>
                    <input type="number" id="tahun_terbit" name="tahun_terbit" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo $edit_mode ? $edit_data['tahun_terbit'] : (isset($_POST['tahun_terbit']) ? $_POST['tahun_terbit'] : date('Y')); ?>" required>
                </div>
                <div>
                    <label for="isbn">ISBN:</label>
                    <input type="text" id="isbn" name="isbn" value="<?php echo $edit_mode ? $edit_data['isbn'] : (isset($_POST['isbn']) ? $_POST['isbn'] : ''); ?>" required>
                </div>
                <div>
                    <label for="kategori">Kategori:</label>
                    <select id="kategori" name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Fiksi" <?php echo ($edit_mode && $edit_data['kategori'] == 'Fiksi') || (isset($_POST['kategori']) && $_POST['kategori'] == 'Fiksi') ? 'selected' : ''; ?>>Fiksi</option>
                        <option value="Non-Fiksi" <?php echo ($edit_mode && $edit_data['kategori'] == 'Non-Fiksi') || (isset($_POST['kategori']) && $_POST['kategori'] == 'Non-Fiksi') ? 'selected' : ''; ?>>Non-Fiksi</option>
                        <option value="Sains" <?php echo ($edit_mode && $edit_data['kategori'] == 'Sains') || (isset($_POST['kategori']) && $_POST['kategori'] == 'Sains') ? 'selected' : ''; ?>>Sains</option>
                        <option value="Teknologi" <?php echo ($edit_mode && $edit_data['kategori'] == 'Teknologi') || (isset($_POST['kategori']) && $_POST['kategori'] == 'Teknologi') ? 'selected' : ''; ?>>Teknologi</option>
                        <option value="Sejarah" <?php echo ($edit_mode && $edit_data['kategori'] == 'Sejarah') || (isset($_POST['kategori']) && $_POST['kategori'] == 'Sejarah') ? 'selected' : ''; ?>>Sejarah</option>
                        <option value="Pendidikan" <?php echo ($edit_mode && $edit_data['kategori'] == 'Pendidikan') || (isset($_POST['kategori']) && $_POST['kategori'] == 'Pendidikan') ? 'selected' : ''; ?>>Pendidikan</option>
                        <option value="Psikologi" <?php if(isset($edit_data) && $edit_data['kategori'] == 'Psikologi') echo 'selected'; ?>>Psikologi</option>
                        <option value="Pengembangan Diri" <?php if(isset($edit_data) && $edit_data['kategori'] == 'Pengembangan Diri') echo 'selected'; ?>>Pengembangan Diri</option>
                    </select>
                </div>
                <div>
                    <label for="jumlah">Jumlah Buku:</label>
                    <input type="number" id="jumlah" name="jumlah" min="1" value="<?php echo $edit_mode ? $edit_data['total_stok'] : (isset($_POST['jumlah']) ? $_POST['jumlah'] : '1'); ?>" required>
                </div>
                <div>
                    <label for="gambar">Gambar Buku:</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*" onchange="previewImage(this);">
                    <p class="help-text">Format: JPG, JPEG, PNG, GIF. Maks. 2MB</p>
                    
                    <?php if ($edit_mode && $edit_data['gambar']): ?>
                        <p>Gambar Saat Ini:</p>
                        <img src="../assets/images/<?php echo $edit_data['gambar']; ?>" alt="<?php echo $edit_data['judul']; ?>" class="book-image">
                    <?php endif; ?>
                    
                    <img id="preview" class="preview-image" src="#" alt="Preview">
                </div>
                <div>
                    <?php if ($edit_mode): ?>
                        <button type="submit" name="update_buku" class="btn btn-primary">Update Buku</button>
                        <a href="tambah_buku.php" class="btn btn-secondary">Batal</a>
                    <?php else: ?>
                        <button type="submit" name="tambah_buku" class="btn btn-success">Tambah Buku</button>
                    <?php endif; ?>
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
                            <th>Gambar</th>
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
                                <td>
                                    <img src="../assets/images/<?php echo $book['gambar'] ?? 'default_book.jpg'; ?>" alt="<?php echo $book['judul']; ?>" class="book-image">
                                </td>
                                <td><?php echo $book['judul']; ?></td>
                                <td><?php echo $book['penulis']; ?></td>
                                <td><?php echo $book['kategori']; ?></td>
                                <td><?php echo $book['tahun_terbit']; ?></td>
                                <td><?php echo $book['isbn']; ?></td>
                                <td><?php echo $book['total_stok']; ?></td>
                                <td><?php echo $book['jumlah_tersedia']; ?></td>
                                <td>
                                    <a href="tambah_buku.php?action=edit&id=<?php echo $book['id']; ?>" class="btn btn-primary">Edit</a>
                                    <a href="tambah_buku.php?action=delete&id=<?php echo $book['id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus buku ini?');">Hapus</a>
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
            <p>&copy; <?php echo date('Y'); ?> Sistem Perpustakaan Ohara</p>
        </div>
    </footer>

    <script>
    function previewImage(input) {
        var preview = document.getElementById('preview');
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.style.display = 'none';
        }
    }
    </script>
    <script src="../assets/js/script.js"></script>
</body>
</html>