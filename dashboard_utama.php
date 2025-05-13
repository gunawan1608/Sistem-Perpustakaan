<?php
session_start();
require_once 'config/database.php';

// Menangani pencarian dan filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Query dasar untuk buku
$query = "SELECT * FROM buku WHERE 1=1";

// Tambahkan filter pencarian jika ada
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $query .= " AND (judul LIKE '%$search%' OR penulis LIKE '%$search%' OR penerbit LIKE '%$search%')";
}

// Tambahkan filter kategori jika ada
if (!empty($category) && $category !== 'all') {
    $category = mysqli_real_escape_string($conn, $category);
    $query .= " AND kategori = '$category'";
}

// Perintah pengurutan
$query .= " ORDER BY judul ASC";

$result = mysqli_query($conn, $query);
$books = [];

while ($row = mysqli_fetch_assoc($result)) {
    $books[] = $row;
}

// Mendapatkan semua kategori unik untuk dropdown filter
$query_categories = "SELECT DISTINCT kategori FROM buku ORDER BY kategori ASC";
$result_categories = mysqli_query($conn, $query_categories);
$categories = [];

while ($row = mysqli_fetch_assoc($result_categories)) {
    $categories[] = $row['kategori'];
}

// Proses peminjaman buku jika form disubmit
$message = '';
if (isset($_POST['pinjam_buku'])) {
    $id_buku = $_POST['id_buku'];
    $no_identitas = $_POST['no_identitas'];
    $tanggal_kembali = $_POST['tanggal_kembali'];
    
    // Cek apakah anggota dengan NIS tersebut terdaftar
    $query_anggota = "SELECT * FROM anggota WHERE no_identitas = '$no_identitas'";
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
                header("Location: dashboard_utama.php?status=success&msg=Buku berhasil dipinjam!" . (!empty($search) ? "&search=$search" : "") . (!empty($category) ? "&category=$category" : ""));
                exit();
            } else {
                $message = "<div class='alert alert-danger'>Gagal meminjam buku: " . mysqli_error($conn) . "</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Maaf, buku tidak tersedia untuk dipinjam.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Nomor Identitas tidak ditemukan. Silakan daftar terlebih dahulu.</div>";
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
    <title>Sistem Perpustakaan Ohara</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .user-info {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .welcome-text {
            font-size: 16px;
            margin: 0;
        }
        .logout-btn {
            background-color: #dc3545;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Sistem Perpustakaan Sederhana</h1>
            <nav>
                <ul>
                    <li><a href="#">Beranda</a></li>
                    <li><a href="anggota/register.php">Daftar Anggota</a></li>
                    <?php if(!isset($_SESSION['admin_id'])): ?>
                        <li><a href="login.php">Dashboard Admin</a></li>
                    <?php else: ?>
                        <li><a href="admin/dashboard.php">Dashboard Admin</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <?php if(isset($_SESSION['admin_id'])): ?>
            <div class="user-info">
                <p class="welcome-text">
                    <i class="fas fa-user-circle"></i> Selamat datang, <strong><?php echo $_SESSION['admin_nama']; ?></strong>
                </p>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        <?php endif; ?>
        
        <?php echo $message; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>Daftar Buku</h2>
            </div>
            
            <!-- Search and Filter Section -->
            <div class="search-filter-container">
                <div class="search-box">
                    <form method="GET" action="" id="search-form">
                        <input type="text" name="search" placeholder="Cari judul, penulis, atau penerbit..." value="<?php echo htmlspecialchars($search); ?>">
                        <?php if(!empty($category) && $category !== 'all'): ?>
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                        <?php endif; ?>
                    </form>
                </div>
                <div class="filter-dropdown">
                    <select name="category" id="category-filter" onchange="applyFilter()">
                        <option value="all">Semua Kategori</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo ($category == $cat ? 'selected' : ''); ?>><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="view-options">
                    <button class="view-btn active" id="grid-view-btn" title="Grid View">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="view-btn" id="list-view-btn" title="List View">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
            
            <?php if (count($books) > 0): ?>
                <div class="book-list" id="book-container">
                    <?php foreach ($books as $book): ?>
                        <div class="book-card">
                            <div class="category-badge"><?php echo $book['kategori']; ?></div>
                            <div class="book-image-container">
                                <img src="assets/images/<?php echo $book['gambar'] ?? 'default_book.jpg'; ?>" alt="<?php echo $book['judul']; ?>" class="book-image">
                            </div>
                            <div class="book-details">
                                <h3><?php echo $book['judul']; ?></h3>
                                <p class="book-info"><strong>Penulis:</strong> <?php echo $book['penulis']; ?></p>
                                <p class="book-info"><strong>Penerbit:</strong> <?php echo $book['penerbit']; ?></p>
                                <p class="book-info"><strong>Tahun:</strong> <?php echo $book['tahun_terbit']; ?></p>
                                <p class="book-info"><strong>ISBN:</strong> <?php echo $book['isbn']; ?></p>
                                <p class="book-info">
                                    <strong>Status:</strong> 
                                    <?php if ($book['jumlah_tersedia'] > 0): ?>
                                        <span class="status-available">Tersedia (<?php echo $book['jumlah_tersedia']; ?>)</span>
                                    <?php else: ?>
                                        <span class="status-borrowed">Tidak Tersedia</span>
                                    <?php endif; ?>
                                </p>
                                <div class="book-actions">
                                    <?php if ($book['jumlah_tersedia'] > 0): ?>
                                        <button class="btn" onclick="openPinjamForm('<?php echo $book['id']; ?>')">Pinjam</button>
                                    <?php else: ?>
                                        <button class="btn" disabled style="opacity: 0.5;">Tidak Tersedia</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-book-open"></i>
                    <h3>Tidak Ada Buku Ditemukan</h3>
                    <p>Coba ubah kata kunci pencarian atau filter kategori Anda.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div id="pinjam-form-container" class="card" style="display: none;">
            <div class="card-header">
                <h2>Form Peminjaman Buku</h2>
            </div>
            <form id="pinjam-form" method="POST" action="">
                <input type="hidden" id="id_buku" name="id_buku">
                <div>
                    <label for="no_identitas">Nomor Identitas Anggota:</label>
                    <input type="text" id="nis" name="no_identitas" required>
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
            <p>&copy; <?php echo date('Y'); ?> Sistem Perpustakaan Ohara</p>
        </div>
    </footer>

    <script>
    // Script untuk pencarian dan filter
    function applyFilter() {
        const categoryValue = document.getElementById('category-filter').value;
        const searchValue = document.querySelector('input[name="search"]').value;
        
        let url = 'dashboard_utama.php?';
        const params = [];
        
        if (searchValue) {
            params.push(`search=${encodeURIComponent(searchValue)}`);
        }
        
        if (categoryValue && categoryValue !== 'all') {
            params.push(`category=${encodeURIComponent(categoryValue)}`);
        }
        
        window.location.href = url + params.join('&');
    }
    
    // Mengirimkan form pencarian saat pengguna selesai mengetik
    const searchInput = document.querySelector('input[name="search"]');
    let typingTimer;
    const doneTypingInterval = 500; // Waktu dalam ms
    
    searchInput.addEventListener('keyup', function() {
        clearTimeout(typingTimer);
        if (searchInput.value) {
            typingTimer = setTimeout(function() {
                document.getElementById('search-form').submit();
            }, doneTypingInterval);
        }
    });
    
    // Toggle view antara grid dan list
    document.getElementById('grid-view-btn').addEventListener('click', function() {
        document.getElementById('book-container').className = 'book-list';
        this.classList.add('active');
        document.getElementById('list-view-btn').classList.remove('active');
    });
    
    document.getElementById('list-view-btn').addEventListener('click', function() {
        document.getElementById('book-container').className = 'book-list list-view';
        this.classList.add('active');
        document.getElementById('grid-view-btn').classList.remove('active');
    });
    
    // Fungsi untuk membuka form peminjaman
    function openPinjamForm(bookId) {
        document.getElementById('id_buku').value = bookId;
        document.getElementById('pinjam-form-container').style.display = 'block';
        
        // Set tanggal kembali default (7 hari dari sekarang)
        const today = new Date();
        const returnDate = new Date(today);
        returnDate.setDate(today.getDate() + 7);
        
        const returnDateFormatted = returnDate.toISOString().split('T')[0];
        document.getElementById('tanggal_kembali').value = returnDateFormatted;
        
        // Scroll ke form
        document.getElementById('pinjam-form-container').scrollIntoView({ behavior: 'smooth' });
    }
    </script>
</body>
</html>