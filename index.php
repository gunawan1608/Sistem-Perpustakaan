<?php
session_start();
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
        .hero-section {
            text-align: center;
            padding: 80px 20px;
            background: linear-gradient(135deg, rgba(84, 105, 212, 0.1) 0%, rgba(84, 105, 212, 0.05) 100%);
            border-radius: var(--border-radius);
            margin-bottom: 40px;
        }
        
        .hero-section h1 {
            font-size: 36px;
            color: var(--secondary);
            margin-bottom: 20px;
        }
        
        .hero-section p {
            font-size: 18px;
            color: var(--gray);
            max-width: 800px;
            margin: 0 auto 30px;
            line-height: 1.8;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }
        
        .feature-card {
            text-align: center;
            padding: 30px;
        }
        
        .feature-icon {
            font-size: 36px;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
        }
        
        .btn-large {
            padding: 14px 28px;
            font-size: 16px;
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: var(--primary);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }
        
        /* Tambahan CSS untuk bagian developer */
        .about-developer {
            background-color: #f8f9fa;
            padding: 50px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 40px;
        }
        
        .developer-profile {
            display: flex;
            align-items: center;
            gap: 30px;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .developer-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: 4px solid white;
        }
        
        .developer-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .developer-info {
            flex: 1;
        }
        
        .developer-info h2 {
            color: var(--secondary);
            margin-bottom: 10px;
        }
        
        .developer-info h4 {
            color: var(--primary);
            margin-bottom: 15px;
            font-weight: 500;
        }
        
        .developer-info p {
            color: var(--gray);
            line-height: 1.8;
            margin-bottom: 20px;
        }
        
        .tech-stack {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        
        .tech-badge {
            background-color: rgba(84, 105, 212, 0.1);
            color: var(--primary);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-links a {
            color: var(--primary);
            font-size: 22px;
            transition: transform 0.3s ease;
        }
        
        .social-links a:hover {
            transform: translateY(-3px);
        }

        /* Statistik Sistem */
        .stats-section {
            margin-bottom: 60px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .stat-card {
            text-align: center;
            padding: 20px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: var(--gray);
            font-size: 16px;
        }
        
        /* FAQ Section */
        .faq-section {
            margin-bottom: 60px;
        }
        
        .faq-item {
            margin-bottom: 20px;
            background-color: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .faq-question {
            padding: 20px;
            cursor: pointer;
            font-weight: 600;
            color: var(--secondary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .faq-question:after {
            content: '\f078';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            color: var(--primary);
            transition: transform 0.3s ease;
        }
        
        .faq-item.active .faq-question:after {
            transform: rotate(180deg);
        }
        
        .faq-answer {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .faq-item.active .faq-answer {
            padding: 0 20px 20px;
            max-height: 500px;
        }
        
        /* Update footer */
        footer {
            background-color: var(--secondary);
            padding: 40px 0;
            color: white;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .footer-info {
            flex: 1;
            min-width: 250px;
        }
        
        .footer-info h3 {
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .footer-info p {
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .footer-contact a {
            color: white;
            display: block;
            margin-bottom: 10px;
            text-decoration: none;
        }
        
        .footer-contact a i {
            margin-right: 10px;
            color: var(--primary);
        }
        
        .copyright {
            text-align: center;
            padding-top: 30px;
            margin-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        @media (max-width: 768px) {
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-large {
                width: 80%;
            }
            
            .developer-profile {
                flex-direction: column;
                text-align: center;
            }
            
            .tech-stack, .social-links {
                justify-content: center;
            }
            
            .footer-content {
                flex-direction: column;
                gap: 30px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><i class="fas fa-book-reader"></i> Sistem Perpustakaan Sederhana</h1>
            <nav>
                <ul>
                    <?php if(isset($_SESSION['admin_id'])): ?>
                    <li><a href="dashboard_utama.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <?php else: ?>
                    <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="register.php"><i class="fas fa-user-plus"></i> Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <div class="hero-section">
            <h1>Selamat Datang di Sistem Perpustakaan Sederhana</h1>
            <p>Sistem manajemen perpustakaan modern yang mempermudah pengelolaan buku, anggota, dan peminjaman. Akses informasi koleksi buku dengan cepat, kelola peminjaman dengan efisien, dan dapatkan laporan lengkap.</p>
            
            <div class="cta-buttons">
                <a href="login.php" class="btn btn-large btn-primary"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="register.php" class="btn btn-large btn-outline"><i class="fas fa-user-plus"></i> Register</a>
            </div>
        </div>
        
        <div class="features">
            <div class="feature-card card">
                <div class="feature-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h3>Katalog Digital</h3>
                <p>Akses katalog buku digital dengan fitur pencarian yang canggih untuk menemukan buku yang Anda butuhkan dengan cepat.</p>
            </div>
            
            <div class="feature-card card">
                <div class="feature-icon">
                    <i class="fas fa-sync"></i>
                </div>
                <h3>Peminjaman & Pengembalian</h3>
                <p>Proses peminjaman dan pengembalian buku yang mudah dan efisien dengan sistem pelacakan yang akurat.</p>
            </div>
            
            <div class="feature-card card">
                <div class="feature-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3>Laporan & Statistik</h3>
                <p>Dapatkan laporan dan statistik lengkap tentang aktivitas perpustakaan untuk analisis dan pengambilan keputusan.</p>
            </div>
        </div>
        
        <div class="stats-section">
            <h2 class="section-title text-center">Statistik Sistem (Gimmick Semata)</h2>
            <br>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Judul Buku</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">200+</div>
                    <div class="stat-label">Anggota Aktif</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">1000+</div>
                    <div class="stat-label">Transaksi per Bulan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Tingkat Kepuasan</div>
                </div>
            </div>
        </div>
        
        <!-- Bagian Tentang Pengembang -->
        <div class="about-developer">
            <h2 class="section-title text-center" style="margin-bottom: 40px;">Tentang Pengembang</h2>
            <div class="developer-profile">
                <div class="developer-image">
                    <!-- Ganti dengan foto profil Anda atau gunakan placeholder -->
                    <img src="assets/images/image1.png" alt="Gunawan Madia Pratama">
                </div>
                <div class="developer-info">
                    <h2>Gunawan Madia Pratama</h2>
                    <h4>Web Developer & Game Developer</h4>
                    <p>Saya adalah siswa SMKN 1 Jakarta jurusan Rekayasa Perangkat Lunak yang berfokus pada pengembangan web dan game. Sistem Perpustakaan ini merupakan proyek yang saya kembangkan untuk membantu pengelolaan perpustakaan secara digital dengan antarmuka yang intuitif dan fitur-fitur modern.</p>
                    <p>Saya memiliki passion dalam menciptakan solusi teknologi yang user-friendly dan dapat meningkatkan efisiensi operasional. Melalui proyek ini, saya berharap dapat berkontribusi dalam digitalisasi layanan perpustakaan di Indonesia.</p>
                    
                    <div class="tech-stack">
                        <span class="tech-badge"><i class="fab fa-php"></i> PHP</span>
                        <span class="tech-badge"><i class="fab fa-js"></i> JavaScript</span>
                        <span class="tech-badge"><i class="fab fa-html5"></i> HTML5</span>
                        <span class="tech-badge"><i class="fab fa-css3-alt"></i> CSS3</span>
                        <span class="tech-badge"><i class="fas fa-database"></i> MySQL</span>
                        <span class="tech-badge"> <img src="assets/images/images2.png" alt="Godot" style="width: 16px; height: 16px; vertical-align: middle; margin-bottom: 0.25rem; margin-left: -0.1rem;"> Godot</span>
                    </div>
                    
                    <div class="social-links">
                        <a href="#" target="_blank"><i class="fab fa-github"></i></a>
                        <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="#" target="_blank"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="faq-section">
            <h2 class="section-title text-center">Pertanyaan Umum</h2>
            <br>
            <div class="faq-item">
                <div class="faq-question">Bagaimana cara mendaftar sebagai anggota perpustakaan?</div>
                <div class="faq-answer">
                    <p>Untuk mendaftar sebagai anggota perpustakaan, Anda dapat mengklik tombol "Register" pada halaman utama. Isi formulir pendaftaran dengan data yang valid, lalu tunggu konfirmasi dari administrator perpustakaan. Setelah disetujui, Anda dapat login dan mulai menggunakan layanan perpustakaan.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">Berapa lama masa peminjaman buku?</div>
                <div class="faq-answer">
                    <p>Masa peminjaman buku standar adalah 14 hari. Namun, Anda dapat memperpanjang masa peminjaman maksimal 2 kali dengan masing-masing perpanjangan selama 7 hari, selama tidak ada anggota lain yang menginginkan buku tersebut.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">Bagaimana jika saya terlambat mengembalikan buku?</div>
                <div class="faq-answer">
                    <p>Keterlambatan pengembalian buku akan dikenakan denda sesuai dengan kebijakan perpustakaan. Denda dihitung per hari keterlambatan. Selama memiliki tanggungan denda, Anda tidak dapat meminjam buku lain sampai denda tersebut dilunasi.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">Apakah saya bisa melihat riwayat peminjaman buku?</div>
                <div class="faq-answer">
                    <p>Ya, Anda dapat melihat riwayat peminjaman buku melalui akun Anda. Login ke sistem, lalu akses menu "Riwayat Peminjaman" di dashboard anggota. Di sana Anda dapat melihat semua transaksi peminjaman dan pengembalian yang pernah Anda lakukan.</p>
                </div>
            </div>
        </div>
        
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
    </div>
    
    <footer>
        <div class="footer-content">
            <div class="footer-info">
                <h3>Sistem Perpustakaan Ohara</h3>
                <p>Sistem manajemen perpustakaan modern yang dikembangkan oleh Gunawan Madia Pratama, siswa SMKN 1 Jakarta jurusan Rekayasa Perangkat Lunak.</p>
                <p>Dibangun dengan teknologi web terkini untuk memberikan pengalaman pengguna yang optimal dan manajemen data yang efisien.</p>
            </div>
            
            <div class="footer-info footer-contact">
                <h3>Kontak Pengembang</h3>
                <a href="mailto:tamagunawan08@gmail.com"><i class="fas fa-envelope"></i> tamagunawan08@gmail.com</a>
                <a href="tel:+6285886934134"><i class="fas fa-phone"></i> +62 858-8693-4134</a>
                <a href="https://github.com/gunawan1608" target="_blank"><i class="fab fa-github"></i> gunawan1608</a>
            </div>
        </div>
        
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> Sistem Perpustakaan | Dikembangkan oleh Gunawan Madia Pratama </p>
        </div>
    </footer>
    
    <script>
        // Script untuk FAQ accordion
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                
                question.addEventListener('click', () => {
                    // Toggle active class on current item
                    item.classList.toggle('active');
                    
                    // Close other items
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item && otherItem.classList.contains('active')) {
                            otherItem.classList.remove('active');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>