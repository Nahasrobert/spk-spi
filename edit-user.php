<?php
require_once 'db.php';


$error = '';
$id = $_GET['id'] ?? '';

if (!$id) {
    echo "ID user tidak valid.";
    exit();
}

// Ambil data user
$resp = mysqli_query($conn, "SELECT * FROM users WHERE id = '$id'");
if (!$resp || mysqli_num_rows($resp) !== 1) {
    echo "User tidak ditemukan";
    exit();
}

$user = mysqli_fetch_assoc($resp);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; // kosong jika tidak diubah
    $role     = mysqli_real_escape_string($conn, $_POST['role']);

    // Validasi role
    $allowed_roles = ['admin', 'user', 'moderator'];
    if (!in_array($role, $allowed_roles)) {
        $error = "Role tidak valid.";
    } else {
        // Cek email (kecuali user yang sedang diedit)
        $cek = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' AND id != '$id'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Email sudah digunakan oleh user lain!";
        } else {
            if ($password) {
                $password_md5 = md5(mysqli_real_escape_string($conn, $password));
                $sql = "UPDATE users SET username = '$username', email = '$email', password = '$password_md5', role = '$role' WHERE id = '$id'";
            } else {
                $sql = "UPDATE users SET username = '$username', email = '$email', role = '$role' WHERE id = '$id'";
            }

            if (mysqli_query($conn, $sql)) {
                header('Location: index-user.php?status=updated');
                exit();
            } else {
                $error = "Gagal update: " . mysqli_error($conn);
            }
        }
    }
}
?>

<?php include('header.php'); ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title"> Form Edit User </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Forms</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit User</li>
                </ol>
            </nav>
        </div>

        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Edit User</h4>
                        <p class="card-description"> Form untuk mengubah data user </p>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form action="" method="post" class="forms-sample">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" name="username" id="username" class="form-control" required
                                    value="<?= htmlspecialchars($user['username']) ?>">
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" class="form-control" required
                                    value="<?= htmlspecialchars($user['email']) ?>">
                            </div>

                            <div class="form-group">
                                <label for="password">Password <small>(kosongkan jika tidak ingin ganti)</small></label>
                                <input type="password" name="password" id="password" class="form-control"
                                    placeholder="Password">
                            </div>

                            <div class="form-group">
                                <label for="role">Role</label>
                                <select name="role" id="role" class="form-control" required>
                                    <option value="">-- Pilih Role --</option>
                                    <option value="admin" <?= ($user['role'] === 'admin') ? 'selected' : '' ?>>Admin
                                    </option>
                                    <option value="user" <?= ($user['role'] === 'user') ? 'selected' : '' ?>>User
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-gradient-primary me-2">Update</button>
                            <a href="index-user.php" class="btn btn-light">Batal</a>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>