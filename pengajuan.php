<?php
error_reporting(0);
require_once 'db.php';
session_start();

$role = $_SESSION['role'] ?? '';
$user_id = intval($_SESSION['user_id'] ?? 0);

if ($role === 'admin') {
    // Admin lihat semua data
    $query = "
       SELECT 
            p.id_pengajuan, 
            p.nama_lengkap, 
            p.nik, 
            p.alamat, 
            p.no_hp, 
            p.jenis_bantuan, 
            p.tanggal_pengajuan, 
            u.username
        FROM pengajuan_bantuan p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.id_pengajuan DESC
    ";
} else {
    // User biasa hanya lihat data miliknya
    $query = "
       SELECT 
            p.id_pengajuan, 
            p.nama_lengkap, 
            p.nik, 
            p.alamat, 
            p.no_hp, 
            p.jenis_bantuan, 
            p.tanggal_pengajuan, 
            u.username
        FROM pengajuan_bantuan p
        JOIN users u ON p.user_id = u.id
        WHERE p.user_id = $user_id
        ORDER BY p.id_pengajuan DESC
    ";
}

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Gagal mengambil data pengajuan: " . mysqli_error($conn));
}

?>

<?php include('header.php'); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<div class="main-panel">
    <div class="content-wrapper">
        <h3>Daftar Pengajuan Bantuan</h3>
        <a href="create-pengajuan.php" class="btn btn-primary mb-3">Tambah Pengajuan Baru</a>

        <table id="userTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Nama Lengkap</th>
                    <th>NIK</th>
                    <th>Alamat</th>
                    <th>No HP</th>
                    <th>Jenis Bantuan</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Aksi</th> <!-- Tambah kolom Aksi -->
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($row['nik']) ?></td>
                        <td><?= htmlspecialchars($row['alamat']) ?></td>
                        <td><?= htmlspecialchars($row['no_hp']) ?></td>
                        <td><?= htmlspecialchars($row['jenis_bantuan']) ?></td>
                        <td><?= htmlspecialchars($row['tanggal_pengajuan']) ?></td>
                        <td>
                            <a href="ubah-pengajuan.php?id=<?= $row['id_pengajuan'] ?>"
                                class="btn btn-warning btn-sm">Ubah</a>
                            <a href="hapus-pengajuan.php?id=<?= $row['id_pengajuan'] ?>"
                                class="btn btn-danger btn-sm btn-delete-pengajuan">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>

        </table>
    </div>
    <?php include('footer.php'); ?>

    <!-- Tambahkan jQuery dan DataTables JS (jika belum ada di footer.php) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Tambahkan SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#userTable').DataTable({
                "columnDefs": [{
                    "orderable": false,
                    "targets": 4 // kamu bisa sesuaikan index kolom ini jika perlu
                }],
                "pageLength": 10,
                "lengthMenu": [5, 10, 25, 50]
            });

            // Hanya aktifkan jika ada tombol hapus
            $('.btn-delete-pengajuan').on('click', function(e) {
                e.preventDefault();

                const href = $(this).attr('href');

                Swal.fire({
                    title: 'Yakin akan dihapus?',
                    text: "Data pengajuan dan nilai terkait akan dihapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            });

        });
    </script>