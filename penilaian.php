<?php
error_reporting(0);
require_once 'db.php';
session_start();

$role = $_SESSION['role'] ?? '';
$user_id = intval($_SESSION['user_id'] ?? 0);

// Query ambil hanya data yang dibutuhkan
if ($role === 'admin') {
    $query = "
        SELECT 
            p.nama_lengkap,
            k.id_kriteria,
            k.kode_kriteria,
            k.nama_kriteria,
            np.nilai
        FROM nilai_pengajuan np
        JOIN pengajuan_bantuan p ON np.id_pengajuan = p.id_pengajuan
        JOIN subkriteria s ON np.id_subkriteria = s.id_subkriteria
        JOIN kriteria k ON s.id_kriteria = k.id_kriteria
        ORDER BY p.nama_lengkap, k.id_kriteria
    ";
} else {
    $query = "
        SELECT 
            p.nama_lengkap,
            k.id_kriteria,
            k.kode_kriteria,
            k.nama_kriteria,
            np.nilai
        FROM nilai_pengajuan np
        JOIN pengajuan_bantuan p ON np.id_pengajuan = p.id_pengajuan
        JOIN subkriteria s ON np.id_subkriteria = s.id_subkriteria
        JOIN kriteria k ON s.id_kriteria = k.id_kriteria
        WHERE p.user_id = $user_id
        ORDER BY p.nama_lengkap, k.id_kriteria
    ";
}

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

$users = [];
$kriteriaList = []; // kode_kriteria => nama_kriteria
$dataMatrix = [];   // [user][kode_kriteria] = nilai

// Proses hasil query
while ($row = mysqli_fetch_assoc($result)) {
    $nama  = $row['nama_lengkap'];
    $kode  = $row['kode_kriteria'];   // Misal: C1, C2, C3...
    $label = $row['nama_kriteria'];   // Nama lengkap kriteria
    $nilai = floatval($row['nilai']);

    if (!in_array($nama, $users)) {
        $users[] = $nama;
    }
    if (!isset($kriteriaList[$kode])) {
        $kriteriaList[$kode] = $label;
    }

    // Simpan nilai (ambil nilai max kalau ada subkriteria)
    if (!isset($dataMatrix[$nama][$kode]) || $nilai > $dataMatrix[$nama][$kode]) {
        $dataMatrix[$nama][$kode] = $nilai;
    }
}

// Urutkan kriteria berdasarkan angka setelah huruf "C"
uksort($kriteriaList, function ($a, $b) {
    $numA = intval(substr($a, 1));
    $numB = intval(substr($b, 1));
    return $numA - $numB;
});
?>

<?php include('header.php'); ?>
<style>
    .dataTables_wrapper {
        width: 100%;
        overflow-x: auto;
    }

    th,
    td {
        white-space: nowrap;
        text-align: center;
    }

    td:first-child,
    th:first-child {
        text-align: left;
    }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<div class="main-panel">
    <div class="content-wrapper">
        <h3>Data Penilaian Berdasarkan Kriteria</h3>

        <table class="table table-bordered table-responsive">
            <thead class="thead-light" style="text-wrap: bold;">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <?php foreach ($kriteriaList as $kode => $label): ?>
                        <!-- Header pakai kode kriteria -->
                        <th><?= htmlspecialchars($kode) ?></th>

                        <!-- Kalau mau header pakai nama kriteria -->
                        <!-- <th><?= htmlspecialchars($label) ?></th> -->
                    <?php endforeach; ?>

                    <!-- Tambahan kolom total nilai (opsional, uncomment kalau mau dipakai) -->
                    <!-- <th>Total</th> -->
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($user) ?></td>
                        <?php
                        $total = 0;
                        foreach ($kriteriaList as $kode => $label):
                            $nilai = isset($dataMatrix[$user][$kode]) ? $dataMatrix[$user][$kode] : 0;
                            $total += $nilai;
                        ?>
                            <td><?= $nilai > 0 ? htmlspecialchars($nilai) : '-' ?></td>
                        <?php endforeach; ?>

                        <!-- Kolom total (opsional) -->
                        <!-- <td><strong><?= $total ?></strong></td> -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

    <?php include('footer.php'); ?>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#userTable').DataTable({
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                colReorder: false, // pastikan tidak bisa drag kolom
                scrollX: true
            });
        });
    </script>