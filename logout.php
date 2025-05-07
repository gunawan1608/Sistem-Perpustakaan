<?php
session_start();

// Hancurkan semua data sesi
$_SESSION = array();

// Hapus cookie sesi jika ada
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Hancurkan sesi
session_destroy();

// Redirect ke halaman login dengan pesan
header("Location: login.php?status=success&msg=Berhasil logout");
exit;
?>