<?php
// subkriteria.php

require_once 'db.php';

// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fungsi cek nama subkriteria unik per kriteria (kecuali untuk edit)
function isSubkriteriaUnique($conn, $id_kriteria, $nama_sub, $excludeId = null)
{
    $id_kriteria = intval($id_kriteria);
    $nama_sub = mysqli_real_escape_string($conn, $nama_sub);
    if ($excludeId) {
        $excludeId = intval($excludeId);
        $query = "SELECT COUNT(*) FROM subkriteria 
                  WHERE id_kriteria=$id_kriteria 
                  AND nama_subkriteria='$nama_sub' 
                  AND id_subkriteria != $excludeId";
    } else {
        $query = "SELECT COUNT(*) FROM subkriteria 
                  WHERE id_kriteria=$id_kriteria 
                  AND nama_subkriteria='$nama_sub'";
    }
    $res = mysqli_query($conn, $query);
    $count = mysqli_fetch_row($res)[0];
    return $count == 0;
}

$error = '';
$success = '';

// Proses simpan/edit subkriteria
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_subkriteria = $_POST['id_subkriteria'] ?? '';
    $id_kriteria = $_POST['id_kriteria'];
    $nama_subkriteria = trim($_POST['nama_subkriteria']);
    $nilai = $_POST['nilai'];

    // Validasi
    if ($nama_subkriteria === '') {
        $error = "Nama subkriteria tidak boleh kosong.";
    } else if (!isSubkriteriaUnique($conn, $id_kriteria, $nama_subkriteria, $id_subkriteria ?: null)) {
        $error = "Nama subkriteria sudah ada untuk kriteria ini.";
    } else {
        $id_kriteria_esc = intval($id_kriteria);
        $nama_subkriteria_esc = mysqli_real_escape_string($conn, $nama_subkriteria);
        $nilai_esc = mysqli_real_escape_string($conn, $nilai);

        if ($id_subkriteria == '') {
            // Insert baru
            $sql = "INSERT INTO subkriteria (id_kriteria, nama_subkriteria, nilai) 
                    VALUES ($id_kriteria_esc, '$nama_subkriteria_esc', '$nilai_esc')";
            if (mysqli_query($conn, $sql)) {
                header("Location: subkriteria.php?status=created");
                exit;
            } else {
                $error = "Gagal tambah: " . mysqli_error($conn);
            }
        } else {
            // Update
            $id_subkriteria = intval($id_subkriteria);
            $sql = "UPDATE subkriteria 
                    SET nama_subkriteria='$nama_subkriteria_esc', nilai='$nilai_esc' 
                    WHERE id_subkriteria=$id_subkriteria";
            if (mysqli_query($conn, $sql)) {
                header("Location: subkriteria.php?status=updated");
                exit;
            } else {
                $error = "Gagal update: " . mysqli_error($conn);
            }
        }
    }
}

// Proses hapus
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM subkriteria WHERE id_subkriteria=$id";
    if (mysqli_query($conn, $sql)) {
        header("Location: subkriteria.php?status=deleted");
        exit;
    } else {
        $error = "Gagal hapus: " . mysqli_error($conn);
    }
}

// Ambil pesan sukses
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'tambah') $success = "Subkriteria berhasil ditambah.";
    if ($_GET['success'] === 'update') $success = "Subkriteria berhasil diperbarui.";
    if ($_GET['success'] === 'hapus') $success = "Subkriteria berhasil dihapus.";
}

// Ambil data kriteria
$kriteria_res = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id_kriteria");

// Ambil semua subkriteria
$subkriteria_all = [];
$total_subkriteria = 0;
$sub_res = mysqli_query($conn, "SELECT * FROM subkriteria");
while ($row = mysqli_fetch_assoc($sub_res)) {
    $subkriteria_all[$row['id_kriteria']][] = $row;
    $total_subkriteria++;
}

include('header.php');
?>

<!-- CSS eksternal -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />

<style>
.card-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.card {
    flex: 1 1 calc(50% - 20px);
    border: 1px solid #ccc;
    padding: 15px;
    margin-bottom: 25px;
    border-radius: 8px;
}

.card-header {
    font-weight: bold;
    font-size: 1.2rem;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-body {
    overflow-x: auto;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1050;
    overflow-y: auto;
}

.modal-dialog {
    max-width: 500px;
    margin: 5% auto;
}

.modal-content {
    background-color: #fff;
    border-radius: .3rem;
    padding: 1.5rem;
    position: relative;
}

.modal-close {
    position: absolute;
    right: 1rem;
    top: 1rem;
    font-size: 1.5rem;
    font-weight: 700;
    color: #000;
    opacity: 0.5;
    cursor: pointer;
    border: none;
    background: transparent;
}

.modal-close:hover {
    opacity: 1;
}

input[id^="modal-tambah-"]:checked+.modal,
#modal-edit:checked+.modal {
    display: block;
}
</style>

<div class="main-panel">
    <div class="content-wrapper">
        <h3 class="page-title">Manajemen Subkriteria</h3>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="card-container">
            <?php
            mysqli_data_seek($kriteria_res, 0);
            while ($k = mysqli_fetch_assoc($kriteria_res)): ?>
            <div class="card">
                <div class="card-header">
                    <span><?= htmlspecialchars($k['nama_kriteria']) ?> (Kode:
                        <?= htmlspecialchars($k['kode_kriteria']) ?>)</span>
                    <label for="modal-tambah-<?= $k['id_kriteria'] ?>" class="btn btn-primary btn-sm"
                        style="cursor:pointer;">
                        Tambah
                    </label>
                </div>
                <div class="card-body">
                    <table id="table-sub-<?= $k['id_kriteria'] ?>" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Subkriteria</th>
                                <th>Nilai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $idk = $k['id_kriteria'];
                                if (isset($subkriteria_all[$idk])):
                                    foreach ($subkriteria_all[$idk] as $sub): ?>
                            <tr>
                                <td><?= $sub['id_subkriteria'] ?></td>
                                <td><?= htmlspecialchars($sub['nama_subkriteria']) ?></td>
                                <td><?= htmlspecialchars($sub['nilai']) ?></td>
                                <td>
                                    <?php if ($total_subkriteria > 0): ?>
                                    <button type="button" class="btn btn-warning btn-sm open-edit"
                                        data-id="<?= $sub['id_subkriteria'] ?>" data-idkriteria="<?= $idk ?>"
                                        data-nama="<?= htmlspecialchars($sub['nama_subkriteria'], ENT_QUOTES) ?>"
                                        data-nilai="<?= htmlspecialchars($sub['nilai'], ENT_QUOTES) ?>">
                                        Ubah
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm btn-delete"
                                        data-id="<?= $sub['id_subkriteria'] ?>">
                                        Hapus
                                    </button>
                                    <?php else: ?>
                                    <button type="button" class="btn btn-warning btn-sm" disabled>Ubah</button>
                                    <button type="button" class="btn btn-danger btn-sm" disabled>Hapus</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach;
                                else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data subkriteria.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Tambah untuk kriteria ini -->
            <input type="checkbox" id="modal-tambah-<?= $k['id_kriteria'] ?>" hidden>
            <div class="modal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <label for="modal-tambah-<?= $k['id_kriteria'] ?>" class="modal-close">&times;</label>
                        <h5>Tambah Subkriteria untuk <?= htmlspecialchars($k['nama_kriteria']) ?></h5>
                        <form method="post" action="">
                            <input type="hidden" name="id_subkriteria" value="">
                            <input type="hidden" name="id_kriteria" value="<?= $k['id_kriteria'] ?>">
                            <div class="mb-3">
                                <label>Nama Subkriteria</label>
                                <input type="text" name="nama_subkriteria" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Nilai</label>
                                <input type="text" name="nilai" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <label for="modal-tambah-<?= $k['id_kriteria'] ?>" class="btn btn-secondary"
                                style="cursor:pointer;">Batal</label>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- Modal Edit -->
        <input type="checkbox" id="modal-edit" hidden>
        <div class="modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <label for="modal-edit" class="modal-close">&times;</label>
                    <h5>Edit Subkriteria</h5>
                    <form method="post" action="">
                        <input type="hidden" id="edit_id_subkriteria" name="id_subkriteria" value="">
                        <input type="hidden" id="edit_id_kriteria" name="id_kriteria" value="">
                        <div class="mb-3">
                            <label>Nama Subkriteria</label>
                            <input type="text" id="edit_nama_subkriteria" name="nama_subkriteria" class="form-control"
                                required>
                        </div>
                        <div class="mb-3">
                            <label>Nilai</label>
                            <input type="text" id="edit_nilai" name="nilai" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <label for="modal-edit" class="btn btn-secondary" style="cursor:pointer;">Batal</label>
                    </form>
                </div>
            </div>
        </div>


        <?php include('footer.php'); ?>

        <!-- JS eksternal -->
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
        $(document).ready(function() {
            // Inisialisasi DataTable untuk setiap tabel sub-kriteria
            <?php
                mysqli_data_seek($kriteria_res, 0);
                while ($k = mysqli_fetch_assoc($kriteria_res)): ?>
            $('#table-sub-<?= $k['id_kriteria'] ?>').DataTable();
            <?php endwhile; ?>
        });
        </script>
        <script>
        $(document).ready(function() {
            // Delegated handler Edit
            $(document).on('click', '.open-edit', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var idk = $(this).data('idkriteria');
                var nama = $(this).data('nama');
                var nilai = $(this).data('nilai');

                $('#edit_id_subkriteria').val(id);
                $('#edit_id_kriteria').val(idk);
                $('#edit_nama_subkriteria').val(nama);
                $('#edit_nilai').val(nilai);

                // buka modal
                $('#modal-edit').prop('checked', true);
            });
        });
        </script>
        <script>
        $(document).ready(function() {
            // Delegated handler Delete
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Yakin?',
                    text: "Data akan dihapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'subkriteria.php?delete=' + id;
                    }
                });
            });
        });
        </script>