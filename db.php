<?php
$host = "localhost";
$user = "root";
$pass = ""; // ubah sesuai password database MySQL-mu
$dbname = "db_spk"; // ganti sesuai nama database kamu

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
