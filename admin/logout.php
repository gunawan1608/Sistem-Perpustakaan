<?php
session_start();

// Hapus semua data sesi
$_SESSION = [];
session_unset();
session_destroy();

// Redirect ke halaman index (login)
header("Location: ../index.php");
exit;
?>
