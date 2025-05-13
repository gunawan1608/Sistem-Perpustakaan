<?php
session_start();
require_once '../config/database.php';

$message = '';
$edit_mode = false;
$anggota_data = null;

// Fungsi untuk mendapatkan semua data anggota
function getAllMembers($conn)
{
    $query = "SELECT * FROM anggota ORDER BY nama ASC";
    $result = mysqli_query($conn, $query);
    return $result;
}

// Proses penghapusan anggota
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $query = "DELETE FROM anggota WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        $message = "<div class='alert alert-success'>Data anggota berhasil dihapus!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Gagal menghapus data: " . mysqli_error($conn) . "</div>";
    }
}

// Mengambil data untuk edit
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = mysqli_real_escape_string($conn, $_GET['edit']);
    $query = "SELECT * FROM anggota WHERE id = '$id'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $anggota_data = mysqli_fetch_assoc($result);
    } else {
        $message = "<div class='alert alert-danger'>Data anggota tidak ditemukan!</div>";
    }
}

// Proses update data anggota
if (isset($_POST['update'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $no_identitas = mysqli_real_escape_string($conn, $_POST['no_identitas']);
    $jenis_identitas = mysqli_real_escape_string($conn, $_POST['jenis_identitas']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $telepon = mysqli_real_escape_string($conn, $_POST['telepon']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    $query = "UPDATE anggota SET 
              nama = '$nama', 
              no_identitas = '$no_identitas', 
              jenis_identitas = '$jenis_identitas',
              kategori = '$kategori',
              kelas = '$kelas',
              email = '$email',
              telepon = '$telepon',
              alamat = '$alamat',
              tanggal_update = NOW()
              WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        $message = "<div class='alert alert-success'>Data anggota berhasil diperbarui!</div>";
        $edit_mode = false;
    } else {
        $message = "<div class='alert alert-danger'>Gagal memperbarui data: " . mysqli_error($conn) . "</div>";
    }
}

// Proses pendaftaran anggota baru
if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $no_identitas = mysqli_real_escape_string($conn, $_POST['no_identitas']);
    $jenis_identitas = mysqli_real_escape_string($conn, $_POST['jenis_identitas']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $kelas = mysqli_real_escape_string($conn, isset($_POST['kelas']) ? $_POST['kelas'] : '');
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $telepon = mysqli_real_escape_string($conn, $_POST['telepon']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    // Cek apakah nomor identitas sudah terdaftar
    $query_check = "SELECT * FROM anggota WHERE no_identitas = '$no_identitas' AND jenis_identitas = '$jenis_identitas'";
    $result_check = mysqli_query($conn, $query_check);

    if (mysqli_num_rows($result_check) > 0) {
        $message = "<div class='alert alert-danger'>Nomor identitas sudah terdaftar!</div>";
    } else {
        // Simpan data anggota
        $query = "INSERT INTO anggota (nama, no_identitas, jenis_identitas, kategori, kelas, email, telepon, alamat, tanggal_daftar, tanggal_update) 
                  VALUES ('$nama', '$no_identitas', '$jenis_identitas', '$kategori', '$kelas', '$email', '$telepon', '$alamat', NOW(), NOW())";

        if (mysqli_query($conn, $query)) {
            $message = "<div class='alert alert-success'>Pendaftaran berhasil! Anda sekarang terdaftar sebagai anggota perpustakaan.</div>";
            // Reset form setelah berhasil
            $_POST = array();
        } else {
            $message = "<div class='alert alert-danger'>Pendaftaran gagal: " . mysqli_error($conn) . "</div>";
        }
    }
}

// Mendapatkan semua data anggota untuk tabel
$all_members = getAllMembers($conn);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keanggotaan - Sistem Perpustakaan Ohara</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS tambahan */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .card-header {
            background:rgb(255, 255, 255);
            color: white;
            padding: 15px 20px;
        }

        form {
            padding: 20px;
        }

        form div {
            margin-bottom: 15px;
        }

        form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        form input,
        form select,
        form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-success {
            background: #20bf6b;
            color: white;
        }

        .btn-danger {
            background: #eb3b5a;
            color: white;
        }

        .btn-warning {
            background: #f7b731;
            color: white;
        }

        .btn-info {
            background: #3867d6;
            color: white;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background: #f5f6fa;
        }

        .action-buttons a {
            margin-right: 5px;
            display: inline-block;
        }

        /* Mengubah form row menjadi vertikal */
        .form-row {
            display: block;
            margin-bottom: 15px;
        }

        .form-col {
            width: 100%;
            margin-bottom: 15px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .pagination a {
            padding: 8px 16px;
            text-decoration: none;
            color: black;
            background-color: #f1f1f1;
            margin: 0 4px;
            border-radius: 4px;
        }

        .pagination a.active {
            background-color: #4b6584;
            color: white;
        }

        .search-box {
            margin-bottom: 20px;
            position: relative;
        }

        .search-box input {
            width: 300px;
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <h1>Sistem Perpustakaan Sederhana</h1>
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
                <h2><?php echo $edit_mode ? 'Edit Data Anggota' : 'Daftar Sebagai Anggota Perpustakaan'; ?></h2>
            </div>
            <form id="register-form" method="POST" action="">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="id" value="<?php echo $anggota_data['id']; ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-col">
                        <label for="nama">Nama Lengkap:</label>
                        <input type="text" id="nama" name="nama"
                            value="<?php echo $edit_mode ? $anggota_data['nama'] : (isset($_POST['nama']) ? $_POST['nama'] : ''); ?>"
                            required>
                    </div>

                    <div class="form-col">
                        <label for="jenis_identitas">Jenis Identitas:</label>
                        <select id="jenis_identitas" name="jenis_identitas" required>
                            <option value="">Pilih Jenis Identitas</option>
                            <option value="NIS" <?php echo ($edit_mode && $anggota_data['jenis_identitas'] == 'NIS') || (isset($_POST['jenis_identitas']) && $_POST['jenis_identitas'] == 'NIS') ? 'selected' : ''; ?>>NIS</option>
                            <option value="NISN" <?php echo ($edit_mode && $anggota_data['jenis_identitas'] == 'NISN') || (isset($_POST['jenis_identitas']) && $_POST['jenis_identitas'] == 'NISN') ? 'selected' : ''; ?>>NISN</option>
                            <option value="NIK" <?php echo ($edit_mode && $anggota_data['jenis_identitas'] == 'NIK') || (isset($_POST['jenis_identitas']) && $_POST['jenis_identitas'] == 'NIK') ? 'selected' : ''; ?>>NIK</option>
                            <option value="NIM" <?php echo ($edit_mode && $anggota_data['jenis_identitas'] == 'NIM') || (isset($_POST['jenis_identitas']) && $_POST['jenis_identitas'] == 'NIM') ? 'selected' : ''; ?>>NIM</option>
                        </select>
                    </div>

                    <div class="form-col">
                        <label for="no_identitas">Nomor Identitas:</label>
                        <input type="text" id="no_identitas" name="no_identitas"
                            value="<?php echo $edit_mode ? $anggota_data['no_identitas'] : (isset($_POST['no_identitas']) ? $_POST['no_identitas'] : ''); ?>"
                            required>
                    </div>

                    <div class="form-col">
                        <label for="kategori">Kategori Anggota:</label>
                        <select id="kategori" name="kategori" onchange="toggleKelasField()" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Pelajar" <?php echo ($edit_mode && $anggota_data['kategori'] == 'Pelajar') || (isset($_POST['kategori']) && $_POST['kategori'] == 'Pelajar') ? 'selected' : ''; ?>>
                                Pelajar</option>
                            <option value="Mahasiswa" <?php echo ($edit_mode && $anggota_data['kategori'] == 'Mahasiswa') || (isset($_POST['kategori']) && $_POST['kategori'] == 'Mahasiswa') ? 'selected' : ''; ?>>
                                Mahasiswa</option>
                            <option value="Guru/Dosen" <?php echo ($edit_mode && $anggota_data['kategori'] == 'Guru/Dosen') || (isset($_POST['kategori']) && $_POST['kategori'] == 'Guru/Dosen') ? 'selected' : ''; ?>>Guru/Dosen</option>
                            <option value="Umum" <?php echo ($edit_mode && $anggota_data['kategori'] == 'Umum') || (isset($_POST['kategori']) && $_POST['kategori'] == 'Umum') ? 'selected' : ''; ?>>Umum
                            </option>
                        </select>
                    </div>

                    <div class="form-col" id="kelas-container">
                        <label for="kelas">Kelas/Jurusan:</label>
                        <input type="text" id="kelas" name="kelas"
                            value="<?php echo $edit_mode ? $anggota_data['kelas'] : (isset($_POST['kelas']) ? $_POST['kelas'] : ''); ?>">
                    </div>

                    <div class="form-col">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email"
                            value="<?php echo $edit_mode ? $anggota_data['email'] : (isset($_POST['email']) ? $_POST['email'] : ''); ?>"
                            required>
                    </div>

                    <div class="form-col">
                        <label for="telepon">Nomor Telepon:</label>
                        <input type="tel" id="telepon" name="telepon"
                            value="<?php echo $edit_mode ? $anggota_data['telepon'] : (isset($_POST['telepon']) ? $_POST['telepon'] : ''); ?>"
                            required>
                    </div>

                    <div class="form-col">
                        <label for="alamat">Alamat:</label>
                        <textarea id="alamat" name="alamat" rows="3"
                            required><?php echo $edit_mode ? $anggota_data['alamat'] : (isset($_POST['alamat']) ? $_POST['alamat'] : ''); ?></textarea>
                    </div>
                </div>

                <div>
                    <?php if ($edit_mode): ?>
                        <button type="submit" name="update" class="btn btn-warning">Update Data</button>
                        <a href="register.php" class="btn btn-info">Batal</a>
                    <?php else: ?>
                        <button type="submit" name="register" class="btn btn-success">Daftar</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tabel data anggota -->
        <div class="card">
            <div class="card-header">
                <h2>Data Anggota Perpustakaan</h2>
            </div>
            <div style="padding: 20px;">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" placeholder="Cari anggota..." onkeyup="searchMembers()">
                </div>

                <div style="overflow-x: auto;">
                    <table id="membersTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Jenis ID</th>
                                <th>Nomor ID</th>
                                <th>Kategori</th>
                                <th>Kelas/Jurusan</th>
                                <th>Email</th>
                                <th>Telepon</th>
                                <th>Tgl Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (mysqli_num_rows($all_members) > 0):
                                while ($row = mysqli_fetch_assoc($all_members)):
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($row['jenis_identitas']); ?></td>
                                        <td><?php echo htmlspecialchars($row['no_identitas']); ?></td>
                                        <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                                        <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['telepon']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_daftar'])); ?></td>
                                        <td class="action-buttons">
                                            <a href="register.php?edit=<?php echo $row['id']; ?>"
                                                class="btn btn-warning">Edit</a>
                                            <a href="register.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus data anggota ini?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="10" style="text-align: center;">Tidak ada data anggota</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination - hanya ditampilkan jika data melebihi batas -->
                <?php if (mysqli_num_rows($all_members) > 10): ?>
                    <div class="pagination">
                        <a href="#">&laquo;</a>
                        <a href="#" class="active">1</a>
                        <a href="#">2</a>
                        <a href="#">3</a>
                        <a href="#">&raquo;</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Sistem Perpustakaan Ohara</p>
        </div>
    </footer>

    <script>
        // Fungsi untuk menampilkan/menyembunyikan field kelas berdasarkan kategori anggota
        function toggleKelasField() {
            const kategori = document.getElementById('kategori').value;
            const kelasContainer = document.getElementById('kelas-container');

            if (kategori === 'Pelajar' || kategori === 'Mahasiswa' || kategori === 'Guru/Dosen') {
                kelasContainer.style.display = 'block';
                document.getElementById('kelas').required = true;
            } else {
                kelasContainer.style.display = 'none';
                document.getElementById('kelas').required = false;
                document.getElementById('kelas').value = '';
            }
        }

        // Panggil fungsi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function () {
            toggleKelasField();
        });

        // Fungsi pencarian pada tabel
        function searchMembers() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('membersTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) { // Mulai dari 1 untuk melewati header
                let visible = false;
                const td = tr[i].getElementsByTagName('td');

                for (let j = 0; j < td.length; j++) {
                    const cell = td[j];
                    if (cell) {
                        const txtValue = cell.textContent || cell.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            visible = true;
                            break;
                        }
                    }
                }

                tr[i].style.display = visible ? '' : 'none';
            }
        }
    </script>
</body>

</html>