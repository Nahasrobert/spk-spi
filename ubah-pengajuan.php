<?php
session_start();
error_reporting(0);
require_once 'db.php';

// Cek login (sesuaikan session user)
if (!isset($_SESSION['role']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

$id_pengajuan = $_GET['id'] ?? null;
if (!$id_pengajuan) {
    header('Location: index-pengajuan.php');
    exit();
}
$id_pengajuan = intval($id_pengajuan);

// Ambil data pengajuan yang ingin diedit
$query = mysqli_query($conn, "SELECT * FROM pengajuan_bantuan WHERE id_pengajuan = $id_pengajuan");
$pengajuan = mysqli_fetch_assoc($query);
if (!$pengajuan) {
    header('Location: index-pengajuan.php');
    exit();
}

// Ambil kriteria & subkriteria
$kriteriaRes = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id_kriteria");
$subkriteriaArr = [];
$subRes = mysqli_query($conn, "SELECT * FROM subkriteria ORDER BY id_kriteria, id_subkriteria");
while ($sub = mysqli_fetch_assoc($subRes)) {
    $subkriteriaArr[$sub['id_kriteria']][] = $sub;
}

// Ambil nilai subkriteria yang sudah dipilih untuk pengajuan ini
$selectedSubkriteria = [];
$nilaiQuery = mysqli_query($conn, "SELECT id_subkriteria FROM nilai_pengajuan WHERE id_pengajuan = $id_pengajuan");
while ($row = mysqli_fetch_assoc($nilaiQuery)) {
    // Cari id_kriteria dari subkriteria
    $id_sub = $row['id_subkriteria'];
    $res = mysqli_query($conn, "SELECT id_kriteria FROM subkriteria WHERE id_subkriteria = $id_sub");
    $k = mysqli_fetch_assoc($res);
    if ($k) {
        $selectedSubkriteria[$k['id_kriteria']] = $id_sub;
    }
}

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap   = trim($_POST['nama_lengkap'] ?? '');
    $nik            = trim($_POST['nik'] ?? '');
    $alamat         = trim($_POST['alamat'] ?? '');
    $no_hp          = trim($_POST['no_hp'] ?? '');
    $jenis_bantuan  = trim($_POST['jenis_bantuan'] ?? '');
    $subkriteriaPost = $_POST['subkriteria'] ?? [];

    // Validasi wajib isi
    if (!$nama_lengkap || !$nik || !$alamat || !$no_hp || !$jenis_bantuan) {
        $error = "Semua data wajib diisi.";
    } elseif (count($subkriteriaPost) !== mysqli_num_rows($kriteriaRes)) {
        $error = "Silakan pilih opsi untuk semua kriteria.";
    } else {
        // Cek NIK unik, kecuali untuk data ini sendiri
        $nikSafe = mysqli_real_escape_string($conn, $nik);
        $cekNik = mysqli_query($conn, "SELECT COUNT(*) FROM pengajuan_bantuan WHERE nik = '$nikSafe' AND id_pengajuan != $id_pengajuan");
        $countNik = mysqli_fetch_row($cekNik)[0];

        if ($countNik > 0) {
            $error = "NIK sudah terdaftar pada pengajuan lain.";
        } else {
            $nama_safe  = mysqli_real_escape_string($conn, $nama_lengkap);
            $alamat_safe = mysqli_real_escape_string($conn, $alamat);
            $hp_safe     = mysqli_real_escape_string($conn, $no_hp);
            $jenis_safe  = mysqli_real_escape_string($conn, $jenis_bantuan);

            // Update data pengajuan
            $update = mysqli_query($conn, "UPDATE pengajuan_bantuan SET 
                nama_lengkap='$nama_safe',
                nik='$nikSafe',
                alamat='$alamat_safe',
                no_hp='$hp_safe',
                jenis_bantuan='$jenis_safe'
                WHERE id_pengajuan = $id_pengajuan");

            if ($update) {
                // Hapus nilai_pengajuan lama dulu
                mysqli_query($conn, "DELETE FROM nilai_pengajuan WHERE id_pengajuan = $id_pengajuan");

                // Insert nilai_pengajuan baru
                foreach ($subkriteriaPost as $id_kriteria => $id_subkriteria) {
                    $id_subkriteria = intval($id_subkriteria);
                    $query = mysqli_query($conn, "SELECT nilai FROM subkriteria WHERE id_subkriteria = $id_subkriteria");
                    $row = mysqli_fetch_assoc($query);
                    $nilai = $row ? mysqli_real_escape_string($conn, $row['nilai']) : '';

                    mysqli_query($conn, "INSERT INTO nilai_pengajuan (id_pengajuan, id_subkriteria, nilai)
                                         VALUES ($id_pengajuan, $id_subkriteria, '$nilai')");
                }
                header('Location: pengajuan.php?status=updated');

                // Refresh data pengajuan dan selectedSubkriteria agar form menampilkan data terbaru
                $pengajuan = [
                    'nama_lengkap' => $nama_lengkap,
                    'nik' => $nik,
                    'alamat' => $alamat,
                    'no_hp' => $no_hp,
                    'jenis_bantuan' => $jenis_bantuan
                ];
                $selectedSubkriteria = $subkriteriaPost;
            } else {
                $error = "Gagal memperbarui data pengajuan.";
            }
        }
    }
}

include('header.php');
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">Ubah Pengajuan Bantuan</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index-pengajuan.php">Pengajuan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Ubah</li>
                </ol>
            </nav>
        </div>

        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">

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
                                    value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? $pengajuan['nama_lengkap']) ?>">
                            </div>

                            <div class="form-group">
                                <label>NIK</label>
                                <input type="text" name="nik" class="form-control" required
                                    value="<?= htmlspecialchars($_POST['nik'] ?? $pengajuan['nik']) ?>">
                            </div>

                            <div class="form-group">
                                <label>Alamat</label>
                                <textarea name="alamat" class="form-control"
                                    required><?= htmlspecialchars($_POST['alamat'] ?? $pengajuan['alamat']) ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>No. HP</label>
                                <input type="text" name="no_hp" class="form-control" required
                                    value="<?= htmlspecialchars($_POST['no_hp'] ?? $pengajuan['no_hp']) ?>">
                            </div>

                            <div class="form-group">
                                <label>Jenis Bantuan</label>
                                <input type="text" name="jenis_bantuan" class="form-control" required
                                    value="<?= htmlspecialchars($_POST['jenis_bantuan'] ?? $pengajuan['jenis_bantuan']) ?>">
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
                                        $selectedValue = $_POST['subkriteria'][$idK] ?? ($selectedSubkriteria[$idK] ?? '');
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

                            <button type="submit" class="btn btn-gradient-primary me-2">Simpan Perubahan</button>
                            <a href="index-pengajuan.php" class="btn btn-light">Batal</a>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>