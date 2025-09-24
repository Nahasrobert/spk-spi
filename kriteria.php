<?php
require_once 'db.php';

// Fungsi cek kode_kriteria unik
function isKodeUnique($conn, $kode, $excludeId = null)
{
    $kode = mysqli_real_escape_string($conn, $kode);
    if ($excludeId) {
        $excludeId = intval($excludeId);
        $query = "SELECT COUNT(*) FROM kriteria WHERE kode_kriteria='$kode' AND id_kriteria != $excludeId";
    } else {
        $query = "SELECT COUNT(*) FROM kriteria WHERE kode_kriteria='$kode'";
    }
    $res = mysqli_query($conn, $query);
    $count = mysqli_fetch_row($res)[0];
    return $count == 0;
}

$error = '';
$success = '';

// Proses simpan/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kriteria = $_POST['id_kriteria'] ?? '';
    $kode = trim($_POST['kode_kriteria']);
    $nama = trim($_POST['nama_kriteria']);
    $atribut = $_POST['atribut'];

    // Cek duplikat kode_kriteria
    if (!isKodeUnique($conn, $kode, $id_kriteria ?: null)) {
        $error = "Kode kriteria  sudah digunakan, silakan gunakan kode lain.";
    } else {
        $kode_esc = mysqli_real_escape_string($conn, $kode);
        $nama_esc = mysqli_real_escape_string($conn, $nama);
        $atribut_esc = mysqli_real_escape_string($conn, $atribut);

        if ($id_kriteria == '') {
            mysqli_query($conn, "INSERT INTO kriteria (kode_kriteria, nama_kriteria, atribut) VALUES ('$kode_esc', '$nama_esc', '$atribut_esc')");
            header("Location: kriteria.php?status=created");
            exit();
        } else {
            $id_kriteria = intval($id_kriteria);
            mysqli_query($conn, "UPDATE kriteria SET kode_kriteria='$kode_esc', nama_kriteria='$nama_esc', atribut='$atribut_esc' WHERE id_kriteria=$id_kriteria");
            header("Location: kriteria.php?status=updated");
            exit();
        }
    }
}

// Proses hapus
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM kriteria WHERE id_kriteria=$id");
    header('Location: kriteria.php?status=deleted');
    exit();
}

// Ambil semua data
$result = mysqli_query($conn, "SELECT * FROM kriteria");

?>
<?php include('header.php'); ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />

<style>
    /* Modal CSS menggunakan checkbox toggle */
    #modal-tambah:checked+.modal,
    #modal-edit:checked+.modal {
        display: block;
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
</style>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">Manajemen Kriteria</h3>
        </div>
        <!-- Tombol Tambah: label untuk checkbox modal-tambah -->
        <div class="d-flex justify-content-end mb-2">
            <label for="modal-tambah" class="btn btn-primary btn-sm mb-1" style="cursor:pointer;">
                <i class="fa fa-plus"></i> Tambah Kriteria
            </label>
        </div>

        <!-- Tabel Data Kriteria -->
        <table id="kriteriaTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Atribut</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $row['id_kriteria'] ?></td>
                        <td><?= htmlspecialchars($row['kode_kriteria']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kriteria']) ?></td>
                        <td><?= htmlspecialchars($row['atribut']) ?></td>
                        <td>
                            <!-- Tombol Edit modal, kita buat label dengan for modal-edit dan value id -->
                            <label for="modal-edit" class="btn btn-warning btn-sm open-edit" style="cursor:pointer;"
                                data-id="<?= $row['id_kriteria'] ?>"
                                data-kode="<?= htmlspecialchars($row['kode_kriteria'], ENT_QUOTES) ?>"
                                data-nama="<?= htmlspecialchars($row['nama_kriteria'], ENT_QUOTES) ?>"
                                data-atribut="<?= $row['atribut'] ?>">
                                <i class="fa fa-edit"></i>
                            </label>
                            <button class="btn btn-danger btn-sm btn-delete" data-id="<?= $row['id_kriteria'] ?>">
                                <i class="fa fa-trash"></i>
                            </button>

                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Modal Tambah (Checkbox hidden toggle) -->
        <input type="checkbox" id="modal-tambah" hidden>
        <div class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <label for="modal-tambah" class="modal-close" aria-label="Close">&times;</label>
                    <h5 class="modal-title mb-3">Tambah Kriteria</h5>
                    <form method="post" action="">
                        <input type="hidden" name="id_kriteria" value="">
                        <div class="mb-3">
                            <label for="kode_kriteria" class="form-label">Kode Kriteria</label>
                            <input type="text" id="kode_kriteria" name="kode_kriteria" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama_kriteria" class="form-label">Nama Kriteria</label>
                            <input type="text" id="nama_kriteria" name="nama_kriteria" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="atribut" class="form-label">Atribut</label>
                            <select id="atribut" name="atribut" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <option value="Benefit">Benefit</option>
                                <option value="Cost">Cost</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <label for="modal-tambah" class="btn btn-secondary ms-2" style="cursor:pointer;">Batal</label>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Edit -->
        <input type="checkbox" id="modal-edit" hidden>
        <div class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <label for="modal-edit" class="modal-close" aria-label="Close">&times;</label>
                    <h5 class="modal-title mb-3">Edit Kriteria</h5>
                    <form method="post" action="">
                        <input type="hidden" id="edit_id_kriteria" name="id_kriteria" value="">
                        <div class="mb-3">
                            <label for="edit_kode_kriteria" class="form-label">Kode Kriteria</label>
                            <input type="text" id="edit_kode_kriteria" name="kode_kriteria" class="form-control"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_nama_kriteria" class="form-label">Nama Kriteria</label>
                            <input type="text" id="edit_nama_kriteria" name="nama_kriteria" class="form-control"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_atribut" class="form-label">Atribut</label>
                            <select id="edit_atribut" name="atribut" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <option value="Benefit">Benefit</option>
                                <option value="Cost">Cost</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <label for="modal-edit" class="btn btn-secondary ms-2" style="cursor:pointer;">Batal</label>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <?php include('footer.php'); ?>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {


            // Pasang event handler klik tombol edit modal
            $('.open-edit').on('click', function() {
                // Ambil data dari atribut data-*
                const id = $(this).data('id');
                const kode = $(this).data('kode');
                const nama = $(this).data('nama');
                const atribut = $(this).data('atribut');

                // Set nilai form edit modal
                $('#edit_id_kriteria').val(id);
                $('#edit_kode_kriteria').val(kode);
                $('#edit_nama_kriteria').val(nama);
                $('#edit_atribut').val(atribut);
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // DataTable init sudah ada

            // Event handler untuk tombol hapus
            $('.btn-delete').on('click', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Yakin hapus data?',
                    text: "Data yang dihapus tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika user yakin hapus, redirect ke URL hapus
                        window.location.href = 'kriteria.php?delete=' + id;
                    }
                });
            });

            // Event handler edit modal sudah ada (tidak perlu diubah)
        });
    </script>
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTable
            $('#kriteriaTable').DataTable({
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    paginate: {
                        previous: "Sebelumnya",
                        next: "Berikutnya"
                    }
                }
            });
        });
    </script>