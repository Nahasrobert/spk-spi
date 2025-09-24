<?php
error_reporting(0);
// session_start(); // Panggil paling awal sebelum output apa pun
require_once 'db.php';
session_start();
require_once 'db.php'; // pastikan path sesuai lokasi db.php

// Cek apakah user sudah login (session 'role' dan 'username' harus ada)
if (!isset($_SESSION['role']) || !isset($_SESSION['username'])) {
    // Jika belum login, redirect ke halaman login
    header('Location: login.php'); // sesuaikan path ke halaman login kamu
    exit();
}
$error = '';
$success = '';

// Ambil user_id dari session (harus login)
$user_id = $_SESSION['user_id'] ?? 1; // Default user jika belum login

// Ambil kriteria & subkriteria
$kriteriaRes = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id_kriteria");
$subkriteriaArr = [];
$subRes = mysqli_query($conn, "SELECT * FROM subkriteria ORDER BY id_kriteria, id_subkriteria");
while ($sub = mysqli_fetch_assoc($subRes)) {
    $subkriteriaArr[$sub['id_kriteria']][] = $sub;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap   = trim($_POST['nama_lengkap'] ?? '');
    $nik            = trim($_POST['nik'] ?? '');
    $alamat         = trim($_POST['alamat'] ?? '');
    $no_hp          = trim($_POST['no_hp'] ?? '');
    $jenis_bantuan  = trim($_POST['jenis_bantuan'] ?? '');

    // Subkriteria yang dipilih, array dengan key = id_kriteria, value = id_subkriteria
    $selectedSubkriteria = $_POST['subkriteria'] ?? [];

    // Validasi wajib isi
    if (!$nama_lengkap || !$nik || !$alamat || !$no_hp || !$jenis_bantuan) {
        $error = "Semua data wajib diisi.";
    } elseif (count($selectedSubkriteria) !== mysqli_num_rows($kriteriaRes)) {
        // Pastikan user memilih subkriteria untuk semua kriteria
        $error = "Silakan pilih opsi untuk semua kriteria.";
    } else {
        // Reset pointer result (karena sudah dipakai di count di atas)
        mysqli_data_seek($kriteriaRes, 0);

        // Cek NIK unik
        $nikSafe = mysqli_real_escape_string($conn, $nik);
        $cekNik = mysqli_query($conn, "SELECT COUNT(*) FROM pengajuan_bantuan WHERE nik = '$nikSafe'");
        $countNik = mysqli_fetch_row($cekNik)[0];

        if ($countNik > 0) {
            $error = "NIK sudah terdaftar.";
        } else {
            $nama_safe  = mysqli_real_escape_string($conn, $nama_lengkap);
            $alamat_safe = mysqli_real_escape_string($conn, $alamat);
            $hp_safe     = mysqli_real_escape_string($conn, $no_hp);
            $jenis_safe  = mysqli_real_escape_string($conn, $jenis_bantuan);

            $insert = mysqli_query($conn, "INSERT INTO pengajuan_bantuan (user_id, nama_lengkap, nik, alamat, no_hp, jenis_bantuan)
                        VALUES ($user_id, '$nama_safe', '$nikSafe', '$alamat_safe', '$hp_safe', '$jenis_safe')");

            if ($insert) {
                $id_pengajuan = mysqli_insert_id($conn);

                // Loop simpan nilai_pengajuan berdasarkan pilihan subkriteria dan ambil nilai dari DB
                foreach ($selectedSubkriteria as $id_kriteria => $id_subkriteria) {
                    $id_subkriteria = intval($id_subkriteria);

                    // Ambil nilai dari DB subkriteria
                    $query = mysqli_query($conn, "SELECT nilai FROM subkriteria WHERE id_subkriteria = $id_subkriteria");
                    $row = mysqli_fetch_assoc($query);
                    $nilai = $row ? mysqli_real_escape_string($conn, $row['nilai']) : '';

                    mysqli_query($conn, "INSERT INTO nilai_pengajuan (id_pengajuan, id_subkriteria, nilai)
                                         VALUES ($id_pengajuan, $id_subkriteria, '$nilai')");
                }

                header('Location: pengajuan.php?status=created');
                exit; // Jangan lupa exit setelah header
            } else {
                $error = "Gagal menyimpan data pengajuan.";
            }
        }
    }
}
include('header.php');

?>



<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title"> Form Pengajuan Bantuan </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index-pengajuan.php">Pengajuan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah</li>
                </ol>
            </nav>
        </div>

        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Tambah Pengajuan</h4>
                        <p class="card-description">Isi formulir pengajuan bantuan sesuai data dan syarat</p>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php elseif ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>

                        <form method="post" action="" class="forms-sample">
                            <!-- Section 1 -->
                            <h5 class="mb-3">Data Pemohon</h5>
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" required
                                    value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label>NIK</label>
                                <input type="number" name="nik" class="form-control" required
                                    value="<?= htmlspecialchars($_POST['nik'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label>Alamat</label>
                                <textarea name="alamat" class="form-control"
                                    required><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>No. HP</label>
                                <input type="number" name="no_hp" class="form-control" required
                                    value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label>Jenis Bantuan</label>
                                <input type="text" name="jenis_bantuan" class="form-control" required
                                    value="<?= htmlspecialchars($_POST['jenis_bantuan'] ?? '') ?>">
                            </div>

                            <!-- Section 2 -->
                            <h5 class="mt-4 mb-3">Persyaratan (Kriteria dan Subkriteria)</h5>
                            <?php foreach ($kriteriaRes as $kriteria): ?>
                                <fieldset class="form-group mb-3">
                                    <h6><strong><?= htmlspecialchars($kriteria['nama_kriteria']) ?></strong></h6>
                                    <?php
                                    $idK = $kriteria['id_kriteria'];
                                    if (isset($subkriteriaArr[$idK])):
                                        // Ambil pilihan yang dipilih sebelumnya, kalau ada
                                        $selectedValue = $_POST['subkriteria'][$idK] ?? '';
                                    ?>
                                        <select name="subkriteria[<?= $idK ?>]" class="form-control" required>
                                            <option value="" disabled <?= $selectedValue === '' ? 'selected' : '' ?>>-- Pilih
                                                <?= htmlspecialchars($kriteria['nama_kriteria']) ?> --</option>
                                            <?php foreach ($subkriteriaArr[$idK] as $sub): ?>
                                                <option value="<?= $sub['id_subkriteria'] ?>"
                                                    <?= ($selectedValue == $sub['id_subkriteria']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($sub['nama_subkriteria']) ?>
                                                    <!-- (Nilai:
                                                    <?= htmlspecialchars($sub['nilai']) ?>) -->
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <em>Tidak ada subkriteria.</em>
                                    <?php endif; ?>
                                </fieldset>
                            <?php endforeach; ?>

                            <button type="submit" class="btn btn-gradient-primary me-2">Simpan</button>
                            <a href="index-pengajuan.php" class="btn btn-light">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>