<?php
require_once 'db.php';
error_reporting(0);
session_start();

// --- Ambil data dari tabel hasil_psi JOIN dengan pengajuan_bantuan
$query = "
    SELECT 
        h.id_pengajuan,
        p.nama_lengkap,
        h.nilai_psi,
        h.ranking
    FROM hasil_psi h
    JOIN pengajuan_bantuan p ON h.id_pengajuan = p.id_pengajuan
    ORDER BY h.ranking ASC
";
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

include('header.php');
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title"> Hasil Ranking Akhir (Metode PSI)</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Tables</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Hasil Ranking Akhir (Metode PSI)</li>
                </ol>
            </nav>
        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card-body">
                    <!-- Tombol Tambah User di kanan atas dan kecil -->


                    <!-- Tabel User -->
                    <table id="rankingTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ranking</th>
                                <th>Alternatif</th>
                                <th>Nilai PSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr <?= $row['ranking'] == 1 ? 'style="background-color:#d4edda;"' : '' ?>>
                                    <td><?= $row['ranking'] ?></td>
                                    <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                    <td><?= number_format($row['nilai_psi'], 4) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>


    <!-- Tambahkan DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#rankingTable').DataTable({
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                ordering: true,
                searching: true,
                info: true
            });
        });
    </script>