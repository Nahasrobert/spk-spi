<?php
require_once 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role     = mysqli_real_escape_string($conn, $_POST['role']);

    // Validasi role
    $allowed_roles = ['admin', 'user', 'moderator'];
    if (!in_array($role, $allowed_roles)) {
        $error = "Role tidak valid.";
    } else {
        // Cek apakah email sudah ada
        $cek = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Email sudah digunakan!";
        } else {
            $password_md5 = md5($password); // Gunakan hash yang lebih kuat di produksi

            $sql = "INSERT INTO users (username, email, password, role) 
                    VALUES ('$username', '$email', '$password_md5', '$role')";
            if (mysqli_query($conn, $sql)) {
                header('Location: index-user.php?status=created');
                exit();
            } else {
                $error = "Gagal tambah user: " . mysqli_error($conn);
            }
        }
    }
}
?>

<?php include('header.php'); ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title"> Form Tambah User </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Forms</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah User</li>
                </ol>
            </nav>
        </div>

        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Tambah User</h4>
                        <p class="card-description"> Form untuk menambahkan user baru </p>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form action="" method="post" class="forms-sample">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" name="username" required class="form-control" id="username"
                                    placeholder="Username"
                                    value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" required class="form-control" id="email"
                                    placeholder="Email"
                                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" name="password" required class="form-control" id="password"
                                    placeholder="Password">
                            </div>

                            <div class="form-group">
                                <label for="role">Role</label>
                                <select name="role" id="role" class="form-control" required>
                                    <option value="">-- Pilih Role --</option>
                                    <option value="admin"
                                        <?= (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : '' ?>>
                                        Admin</option>
                                    <option value="user"
                                        <?= (isset($_POST['role']) && $_POST['role'] === 'user') ? 'selected' : '' ?>>
                                        User</option>

                                </select>
                            </div>

                            <button type="submit" class="btn btn-gradient-primary me-2">Simpan</button>
                            <a href="index-user.php" class="btn btn-light">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>