<?php
require_once 'db.php';
session_start();
if ($_SESSION['role'] !== 'admin') {
    echo "Access Denied!";
    exit();
}

$id = $_GET['id'] ?? '';
if ($id) {
    // optional: jangan hapus diri sendiri
    if ($id == $_SESSION['user_id']) {
        // jika kamu simpan `user_id` di session
        header('Location: index-user.php?status=deleted');
        exit();
    }

    $sql = "DELETE FROM users WHERE id = '$id'";
    mysqli_query($conn, $sql);
}

header('Location: index-user.php?status=deleted');
exit();
