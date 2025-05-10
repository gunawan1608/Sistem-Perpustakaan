<?php
require_once '../config/database.php';

// Fungsi untuk membersihkan input
function sanitize($data)
{
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Fungsi untuk login
function login($username, $password)
{
    global $conn;
    $username = sanitize($username);

    $query = "SELECT * FROM admin WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_nama'] = $user['nama'];
            return true;
        }
    }
    return false;
}

// Fungsi untuk cek login
function cek_login()
{
    if (!isset($_SESSION['admin_id'])) {
        header("Location: ../login.php");
        exit();
    }
}

// Fungsi untuk tambah buku
function tambah_buku($judul, $penulis, $penerbit, $tahun_terbit, $isbn, $kategori, $jumlah)
{
    global $conn;

    $judul = sanitize($judul);
    $penulis = sanitize($penulis);
    $penerbit = sanitize($penerbit);
    $tahun_terbit = (int) $tahun_terbit;
    $isbn = sanitize($isbn);
    $kategori = sanitize($kategori);
    $jumlah = (int) $jumlah;

    $query = "INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, isbn, kategori, jumlah_tersedia, total_stok) 
            VALUES ('$judul', '$penulis', '$penerbit', $tahun_terbit, '$isbn', '$kategori', $jumlah, $jumlah)";

    return mysqli_query($conn, $query);
}

// Fungsi untuk mendapatkan semua buku
function get_all_books()
{
    global $conn;
    $query = "SELECT * FROM buku ORDER BY judul ASC";
    $result = mysqli_query($conn, $query);
    $books = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $books[] = $row;
        }
    }

    return $books;
}

// Fungsi untuk mendapatkan detail buku berdasarkan ID
function get_book_by_id($id)
{
    global $conn;
    $id = (int) $id;

    $query = "SELECT * FROM buku WHERE id = $id";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

// Fungsi untuk update buku
function update_book($id, $judul, $penulis, $penerbit, $tahun_terbit, $isbn, $kategori, $gambar, $jumlah)
{
    global $conn;
    $id = (int)$id;
    
    // Get current book data to calculate available books
    $current_book = get_book_by_id($id);
    if (!$current_book) {
        return false;
    }
    
    // Calculate new available books
    $borrowed_books = $current_book['total_stok'] - $current_book['jumlah_tersedia'];
    $new_available = $jumlah - $borrowed_books;
    if ($new_available < 0) {
        $new_available = 0;
    }
    
    $query = "UPDATE buku SET 
              judul = '$judul', 
              penulis = '$penulis', 
              penerbit = '$penerbit', 
              tahun_terbit = $tahun_terbit, 
              isbn = '$isbn', 
              kategori = '$kategori', 
              gambar = '$gambar',
              total_stok = $jumlah,
              jumlah_tersedia = $new_available
              WHERE id = $id";
              
    return mysqli_query($conn, $query);
}

// Fungsi untuk menghapus buku
function delete_book($id)
{
    global $conn;
    $id = (int)$id;
    
    // Check if the book is borrowed
    $query_check = "SELECT COUNT(*) as total FROM peminjaman WHERE id_buku = $id AND status = 'dipinjam'";
    $result_check = mysqli_query($conn, $query_check);
    $data_check = mysqli_fetch_assoc($result_check);
    
    if ($data_check['total'] > 0) {
        return false; // Cannot delete, book is being borrowed
    }
    
    // Get image filename
    $book = get_book_by_id($id);
    if ($book && $book['gambar'] != 'default_book.jpg') {
        $image_path = "../assets/images/" . $book['gambar'];
        if (file_exists($image_path)) {
            unlink($image_path); // Delete the image file
        }
    }
    
    // Delete the book
    $query = "DELETE FROM buku WHERE id = $id";
    return mysqli_query($conn, $query);
}

// Fungsi untuk mendapatkan jumlah total anggota
function get_total_members()
{
    global $conn;
    
    $query = "SELECT COUNT(*) as total FROM anggota";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    
    return $data['total'];
}

// Fungsi untuk meminjam buku
function pinjam_buku($id_buku, $id_anggota, $tanggal_kembali)
{
    global $conn;

    $id_buku = (int) $id_buku;
    $id_anggota = (int) $id_anggota;
    $tanggal_pinjam = date('Y-m-d');

    // Cek ketersediaan buku
    $buku = get_book_by_id($id_buku);   
    if ($buku['jumlah_tersedia'] <= 0) {
        return false;
    }

    // Update jumlah buku tersedia
    $query_update = "UPDATE buku SET jumlah_tersedia = jumlah_tersedia - 1 WHERE id = $id_buku";
    mysqli_query($conn, $query_update);

    // Catat peminjaman
    $query = "INSERT INTO peminjaman (id_buku, id_anggota, tanggal_pinjam, tanggal_kembali) 
            VALUES ($id_buku, $id_anggota, '$tanggal_pinjam', '$tanggal_kembali')";

    return mysqli_query($conn, $query);
}

// Fungsi untuk mengembalikan buku
function kembalikan_buku($id_peminjaman)
{
    global $conn;

    $id_peminjaman = (int) $id_peminjaman;
    $tanggal_kembali = date('Y-m-d');

    // Ambil data peminjaman
    $query_pinjam = "SELECT * FROM peminjaman WHERE id = $id_peminjaman";
    $result_pinjam = mysqli_query($conn, $query_pinjam);
    $peminjaman = mysqli_fetch_assoc($result_pinjam);

    if (!$peminjaman) {
        return false;
    }

    // Hitung keterlambatan
    $tanggal_seharusnya = new DateTime($peminjaman['tanggal_kembali']);
    $tanggal_aktual = new DateTime($tanggal_kembali);
    $selisih = $tanggal_seharusnya->diff($tanggal_aktual);

    $keterlambatan = 0;
    $denda = 0;

    if ($tanggal_aktual > $tanggal_seharusnya) {
        $keterlambatan = $selisih->days;
        $denda = $keterlambatan * 1000; // Denda Rp 1.000 per hari
    }

    // Update status peminjaman
    $query_update = "UPDATE peminjaman SET status = 'dikembalikan' WHERE id = $id_peminjaman";
    mysqli_query($conn, $query_update);

    // Update jumlah buku tersedia
    $query_buku = "UPDATE buku SET jumlah_tersedia = jumlah_tersedia + 1 WHERE id = {$peminjaman['id_buku']}";
    mysqli_query($conn, $query_buku);

    // Catat pengembalian
    $query_kembali = "INSERT INTO pengembalian (id_peminjaman, tanggal_dikembalikan, keterlambatan, denda) 
                    VALUES ($id_peminjaman, '$tanggal_kembali', $keterlambatan, $denda)";

    return mysqli_query($conn, $query_kembali);
}

// Fungsi untuk mendapatkan daftar peminjaman aktif
function get_active_loans()
{
    global $conn;

    $query = "SELECT p.*, b.judul, a.nama, a.no_identitas
            FROM peminjaman p 
            JOIN buku b ON p.id_buku = b.id 
            JOIN anggota a ON p.id_anggota = a.id 
            WHERE p.status = 'dipinjam' 
            ORDER BY p.tanggal_kembali ASC";

    $result = mysqli_query($conn, $query);
    $loans = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $loans[] = $row;
    }

    return $loans;
}

// Fungsi untuk mendapatkan laporan peminjaman
function get_loan_report()
{
    global $conn;

    $query = "SELECT p.*, b.judul, a.nama, a.no_identitas, 
                IFNULL(pk.tanggal_dikembalikan, 'Belum Kembali') as tanggal_dikembalikan,
                IFNULL(pk.denda, 0) as denda
                FROM peminjaman p 
                JOIN buku b ON p.id_buku = b.id 
                JOIN anggota a ON p.id_anggota = a.id 
                LEFT JOIN pengembalian pk ON p.id = pk.id_peminjaman
                ORDER BY p.tanggal_pinjam DESC";

    $result = mysqli_query($conn, $query);
    $report = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $report[] = $row;
    }

    return $report;
}
?>