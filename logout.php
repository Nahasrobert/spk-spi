<?php
session_start();

// Hapus semua data session
session_unset();
session_destroy();

// Redirect ke halaman login atau halaman utama
header("Location: login.php"); // ganti dengan halaman login kamu
exit();
