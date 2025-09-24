<?php
session_start();
require_once 'db.php';

// Cek login
if (!isset($_SESSION['role']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$id_pengajuan = $_GET['id'] ?? null;
if (!$id_pengajuan) {
    header('Location: pengajuan.php');
    exit();
}
$id_pengajuan = intval($id_pengajuan);

// Hapus data nilai_pengajuan yang berelasi dulu
mysqli_query($conn, "DELETE FROM nilai_pengajuan WHERE id_pengajuan = $id_pengajuan");

// Hapus data pengajuan
mysqli_query($conn, "DELETE FROM pengajuan_bantuan WHERE id_pengajuan = $id_pengajuan");

// Redirect ke halaman daftar dengan status sukses
header('Location: pengajuan.php?status=deleted');
exit();
