// Fungsi untuk konfirmasi sebelum menghapus data
function confirmDelete(message) {
    return confirm(message || 'Apakah Anda yakin ingin menghapus data ini?');
}

// Fungsi untuk validasi form peminjaman buku
function validatePinjamForm() {
    let nis = document.getElementById('nis').value;
    let id_buku = document.getElementById('id_buku').value;
    let tanggal_kembali = document.getElementById('tanggal_kembali').value;
    
    if (nis === '' || id_buku === '' || tanggal_kembali === '') {
        alert('Semua field harus diisi!');
        return false;
    }
    
    // Cek tanggal kembali tidak boleh kurang dari hari ini
    let today = new Date();
    let returnDate = new Date(tanggal_kembali);
    
    if (returnDate <= today) {
        alert('Tanggal kembali harus lebih dari hari ini!');
        return false;
    }
    
    return true;
}

// Fungsi untuk validasi form tambah buku
function validateBookForm() {
    let judul = document.getElementById('judul').value;
    let penulis = document.getElementById('penulis').value;
    let penerbit = document.getElementById('penerbit').value;
    let tahun_terbit = document.getElementById('tahun_terbit').value;
    let isbn = document.getElementById('isbn').value;
    let kategori = document.getElementById('kategori').value;
    let jumlah = document.getElementById('jumlah').value;
    
    if (judul === '' || penulis === '' || penerbit === '' || 
        tahun_terbit === '' || isbn === '' || kategori === '' || jumlah === '') {
        alert('Semua field harus diisi!');
        return false;
    }
    
    if (isNaN(tahun_terbit) || parseInt(tahun_terbit) < 1900 || parseInt(tahun_terbit) > new Date().getFullYear()) {
        alert('Tahun terbit tidak valid!');
        return false;
    }
    
    if (isNaN(jumlah) || parseInt(jumlah) <= 0) {
        alert('Jumlah buku harus lebih dari 0!');
        return false;
    }
    
    return true;
}

// Fungsi untuk validasi form registrasi anggota
function validateRegisterForm() {
    let nama = document.getElementById('nama').value;
    let nis = document.getElementById('nis').value;
    let kelas = document.getElementById('kelas').value;
    let email = document.getElementById('email').value;
    
    if (nama === '' || nis === '' || kelas === '' || email === '') {
        alert('Semua field harus diisi!');
        return false;
    }
    
    // Validasi email sederhana
    let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Format email tidak valid!');
        return false;
    }
    
    return true;
}

// Event listener saat dokumen dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Tambahkan event listener untuk form jika ada
    const pinjamForm = document.getElementById('pinjam-form');
    if (pinjamForm) {
        pinjamForm.addEventListener('submit', function(e) {
            if (!validatePinjamForm()) {
                e.preventDefault();
            }
        });
    }
    
    const bookForm = document.getElementById('book-form');
    if (bookForm) {
        bookForm.addEventListener('submit', function(e) {
            if (!validateBookForm()) {
                e.preventDefault();
            }
        });
    }
    
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (!validateRegisterForm()) {
                e.preventDefault();
            }
        });
    }
});