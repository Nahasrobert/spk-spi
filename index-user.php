<?php
error_reporting(0);
require_once 'db.php'; // sesuaikan path ke file koneksi database
$query = "SELECT id, username, email, role FROM users";
$result = mysqli_query($conn, $query);
?>

<?php include('header.php'); ?>

<!-- Tambahkan CSS DataTables di header (kalau header.php belum ada) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title"> User Management </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Tables</a></li>
                    <li class="breadcrumb-item active" aria-current="page">User Management</li>
                </ol>
            </nav>
        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card-body">
                    <!-- Tombol Tambah User di kanan atas dan kecil -->
                    <div class="d-flex justify-content-end mb-3">
                        <a href="create-user.php" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Tambah
                        </a>
                    </div>

                    <!-- Tabel User -->
                    <table id="userTable" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['role']) ?></td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Aksi User">
                                            <a href="edit-user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary"
                                                title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="delete-user.php?id=<?= $row['id'] ?>"
                                                class="btn btn-sm btn-danger btn-delete-user" data-id="<?= $row['id'] ?>"
                                                title="Hapus">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
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
                    "targets": 4
                }],
                "pageLength": 10,
                "lengthMenu": [5, 10, 25, 50]
            });

            // Tangani klik tombol hapus menggunakan SweetAlert2
            $('.btn-delete-user').on('click', function(e) {
                e.preventDefault(); // cegah link langsung jalan

                const href = $(this).attr('href'); // URL delete-user.php?id=...

                Swal.fire({
                    title: 'Yakin akan dihapus?',
                    text: "Data user akan dihapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika user klik Ya, redirect ke URL hapus
                        window.location.href = href;
                    }
                });
            });
        });
    </script>