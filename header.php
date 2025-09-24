<?php
session_start();
require_once 'db.php'; // pastikan path sesuai lokasi db.php

// Cek apakah user sudah login (session 'role' dan 'username' harus ada)
if (!isset($_SESSION['role']) || !isset($_SESSION['username'])) {
    // Jika belum login, redirect ke halaman login
    header('Location: login.php'); // sesuaikan path ke halaman login kamu
    exit();
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];

// Routing halaman berdasarkan query param 'page'
$page = $_GET['page'] ?? 'dashboard';

function loadPage($page, $role)
{
    $adminPages = ['dashboard', 'master-kriteria', 'master-subkriteria', 'calon-penerima', 'penilaian', 'perhitungan', 'hasil', 'manajemen-user'];
    $userPages = ['dashboard', 'pengajuan', 'profil', 'penilaian', 'hasil'];

    if ($role === 'admin' && in_array($page, $adminPages)) {
        $path = "pages/{$page}.php";
        if (file_exists($path)) {
            include $path;
        } else {
            echo "<h2>Page {$page} not found.</h2>";
        }
    } elseif ($role === 'user' && in_array($page, $userPages)) {
        $path = "pages/{$page}.php";
        if (file_exists($path)) {
            include $path;
        } else {
            echo "<h2>Page {$page} not found.</h2>";
        }
    } else {
        echo "<h2>Access Denied or Page Not Found</h2>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Sistem Pengambil Keputusan</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css" />
    <link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css" />
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css" />
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <!-- SweetAlert2 CSS & JS -->


</head>

<body>
    <div class="container-scroller">

        <!-- Navbar -->
        <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
                <a class="navbar-brand brand-logo" href="index.php"><img src="assets/images/logo.png" alt="logo" /></a>
                <a class="navbar-brand brand-logo-mini" href="index.php"><img src="assets/images/logo-mini.svg"
                        alt="logo" /></a>
            </div>
            <div class="navbar-menu-wrapper d-flex align-items-stretch">
                <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                    <span class="mdi mdi-menu"></span>
                </button>
                <div class="search-field d-none d-md-block">
                    <form class="d-flex align-items-center h-100" action="#">
                        <div class="input-group">
                            <div class="input-group-prepend bg-transparent">
                                <i class="input-group-text border-0 mdi mdi-magnify"></i>
                            </div>
                            <input type="text" class="form-control bg-transparent border-0"
                                placeholder="Search projects" />
                        </div>
                    </form>
                </div>
                <ul class="navbar-nav navbar-nav-right">
                    <li class="nav-item nav-profile dropdown">
                        <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <div class="nav-profile-img">
                                <img src="assets/images/faces/face1.jpg" alt="image" />
                                <span class="availability-status online"></span>
                            </div>
                            <div class="nav-profile-text">
                                <p class="mb-1 text-black"><?= htmlspecialchars($username) ?></p>
                            </div>
                        </a>
                        <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
                            <a class="dropdown-item" href="update_profil.php">
                                <i class="mdi mdi-cached me-2 text-success"></i> Update Profile
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php">
                                <i class="mdi mdi-logout me-2 text-primary"></i> Signout
                            </a>
                        </div>
                    </li>
                </ul>
                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                    data-toggle="offcanvas">
                    <span class="mdi mdi-menu"></span>
                </button>
            </div>
        </nav>

        <!-- Sidebar -->
        <div class="container-fluid page-body-wrapper">
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    <li class="nav-item nav-profile">
                        <a href="#" class="nav-link">
                            <div class="nav-profile-image">
                                <img src="assets/images/faces/face1.jpg" alt="profile" />
                                <span class="login-status online"></span>
                            </div>
                            <div class="nav-profile-text d-flex flex-column">
                                <span class="font-weight-bold mb-2"><?= htmlspecialchars($username) ?></span>
                                <span
                                    class="text-secondary text-small"><?= $role === 'admin' ? 'Administrator' : 'User' ?></span>
                            </div>
                            <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
                        </a>
                    </li>

                    <?php if ($role === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <span class="menu-title">Dashboard</span>
                                <i class="mdi mdi-home menu-icon"></i>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="collapse" href="#masterData" aria-expanded="false"
                                aria-controls="masterData">
                                <span class="menu-title">Master Data</span>
                                <i class="menu-arrow"></i>
                                <i class="mdi mdi-crosshairs-gps menu-icon"></i>
                            </a>
                            <div class="collapse" id="masterData">
                                <ul class="nav flex-column sub-menu">
                                    <li class="nav-item">
                                        <a class="nav-link" href="kriteria.php">Kriteria</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="subkriteria.php">Sub-Kriteria</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="pengajuan.php">
                                <span class="menu-title">Pengajuan Bantuan</span>
                                <i class="mdi mdi-account menu-icon"></i>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="penilaian.php">
                                <span class="menu-title">Penilaian</span>
                                <i class="mdi mdi-clipboard-check menu-icon"></i>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="hasil_perhitungan.php">
                                <span class="menu-title">Hasil Perhitungan</span>
                                <i class="mdi mdi-calculator menu-icon"></i>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="hasil_rangking.php">
                                <span class="menu-title">Hasil/Rangking</span>
                                <i class="mdi mdi-chart-bar menu-icon"></i>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="index-user.php">
                                <span class="menu-title">Manajemen User</span>
                                <i class="mdi mdi-account-multiple menu-icon"></i>
                            </a>
                        </li>

                    <?php else: /* user biasa */ ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <span class="menu-title">Dashboard</span>
                                <i class="mdi mdi-home menu-icon"></i>
                            </a>
                        </li>



                        <li class="nav-item">
                            <a class="nav-link" href="pengajuan.php">
                                <span class="menu-title">Pengajuan Bantuan</span>
                                <i class="mdi mdi-account menu-icon"></i>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="hasil_rangking.php">
                                <span class="menu-title">Hasil/Rangking</span>
                                <i class="mdi mdi-chart-bar menu-icon"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>