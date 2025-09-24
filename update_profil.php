<?php
error_reporting(0);
require_once 'db.php';
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data user sekarang
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = mysqli_real_escape_string($conn, $_POST['username']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $old_pass   = $_POST['old_password'];
    $new_pass   = $_POST['new_password'];
    $confirm    = $_POST['confirm_password'];

    // --- Update Profil (username & email)
    $sql = "UPDATE users SET username = '$username', email = '$email' WHERE id = $user_id";
    $ok = mysqli_query($conn, $sql);

    // --- Update Password (jika diisi)
    if (!empty($old_pass) && !empty($new_pass) && !empty($confirm)) {
        $old_pass_md5 = md5($old_pass);
        $new_pass_md5 = md5($new_pass);
        $confirm_md5  = md5($confirm);

        // cek password lama
        if ($user['password'] !== $old_pass_md5) {
            $error = "Password lama salah!";
        } elseif ($new_pass_md5 !== $confirm_md5) {
            $error = "Password baru tidak sama!";
        } else {
            $sql_pass = "UPDATE users SET password = '$new_pass_md5' WHERE id = $user_id";
            if (mysqli_query($conn, $sql_pass)) {
                $_SESSION['message'] = "Profil & password berhasil diperbarui!";
                header("Location: update_profil.php");
                exit();
            } else {
                $error = "Gagal update password: " . mysqli_error($conn);
            }
        }
    } else {
        if ($ok) {
            $_SESSION['message'] = "Profil berhasil diperbarui!";
            header("Location: update_profil.php");
            exit();
        } else {
            $error = "Gagal update profil: " . mysqli_error($conn);
        }
    }
}
?>

<?php include('header.php'); ?>
<div class="main-panel">
    <div class="content-wrapper">
        <h3>Update Profil</h3>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'];
                                                unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <!-- Update Profil -->
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>"
                    class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control"
                    required>
            </div>

            <hr>
            <h4>Ganti Password (Opsional)</h4>
            <div class="form-group">
                <label>Password Lama</label>
                <input type="password" name="old_password" class="form-control">
            </div>
            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" name="new_password" class="form-control">
            </div>
            <div class="form-group">
                <label>Konfirmasi Password Baru</label>
                <input type="password" name="confirm_password" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
    <?php include('footer.php'); ?>