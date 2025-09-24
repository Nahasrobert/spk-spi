<?php
require_once 'db.php';
error_reporting(0);

session_start();

// --- Hitung data dinamis dari database
$q1 = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM pengajuan_bantuan");
$totalPengajuan = mysqli_fetch_assoc($q1)['jml'];

$q2 = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM kriteria");
$totalKriteria = mysqli_fetch_assoc($q2)['jml'];

$q3 = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM subkriteria");
$totalSubkriteria = mysqli_fetch_assoc($q3)['jml'];

$q4 = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM hasil_psi");
$totalRanking = mysqli_fetch_assoc($q4)['jml'];

include('header.php');
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-home"></i>
                </span> Dashboard
            </h3>
            <nav aria-label="breadcrumb">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">
                        <span></span>Overview
                        <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle"></i>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="row">
            <!-- Total Pengajuan -->
            <div class="col-md-3 stretch-card grid-margin">
                <div class="card bg-gradient-primary card-img-holder text-white">
                    <div class="card-body">
                        <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">Total Pengajuan
                            <i class="mdi mdi-file-document-box mdi-24px float-end"></i>
                        </h4>
                        <h2 class="mb-5"><?= $totalPengajuan ?></h2>
                        <h6 class="card-text">Jumlah semua pengajuan</h6>
                    </div>
                </div>
            </div>

            <!-- Jumlah Kriteria -->
            <div class="col-md-3 stretch-card grid-margin">
                <div class="card bg-gradient-info card-img-holder text-white">
                    <div class="card-body">
                        <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">Jumlah Kriteria
                            <i class="mdi mdi-format-list-bulleted mdi-24px float-end"></i>
                        </h4>
                        <h2 class="mb-5"><?= $totalKriteria ?></h2>
                        <h6 class="card-text">Kriteria yang digunakan</h6>
                    </div>
                </div>
            </div>

            <!-- Jumlah Subkriteria -->
            <div class="col-md-3 stretch-card grid-margin">
                <div class="card bg-gradient-success card-img-holder text-white">
                    <div class="card-body">
                        <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">Jumlah Subkriteria
                            <i class="mdi mdi-format-list-checks mdi-24px float-end"></i>
                        </h4>
                        <h2 class="mb-5"><?= $totalSubkriteria ?></h2>
                        <h6 class="card-text">Subkriteria detail penilaian</h6>
                    </div>
                </div>
            </div>

            <!-- Jumlah Ranking -->
            <div class="col-md-3 stretch-card grid-margin">
                <div class="card bg-gradient-danger card-img-holder text-white">
                    <div class="card-body">
                        <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">Alternatif Ter-ranking
                            <i class="mdi mdi-trophy mdi-24px float-end"></i>
                        </h4>
                        <h2 class="mb-5"><?= $totalRanking ?></h2>
                        <h6 class="card-text">Sudah dihitung dengan PSI</h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tambahan chart / tabel bisa kamu masukkan di sini -->
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Informasi Sistem</h4>
                        <p>Dashboard ini menampilkan ringkasan data pengajuan, kriteria, subkriteria, dan hasil ranking
                            metode PSI.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php include('footer.php'); ?>
</div>