-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Waktu pembuatan: 24 Sep 2025 pada 03.51
-- Versi server: 9.1.0
-- Versi PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_spk`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `hasil_psi`
--

DROP TABLE IF EXISTS `hasil_psi`;
CREATE TABLE IF NOT EXISTS `hasil_psi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `nilai_psi` float NOT NULL,
  `ranking` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_pengajuan` (`id_pengajuan`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `hasil_psi`
--

INSERT INTO `hasil_psi` (`id`, `id_pengajuan`, `nilai_psi`, `ranking`) VALUES
(1, 1, 0.420584, 6),
(2, 2, 0.975699, 1),
(3, 6, 0.927097, 2),
(4, 4, 0.826874, 3),
(5, 3, 0.464051, 4),
(6, 5, 0.435356, 5);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kriteria`
--

DROP TABLE IF EXISTS `kriteria`;
CREATE TABLE IF NOT EXISTS `kriteria` (
  `id_kriteria` int NOT NULL AUTO_INCREMENT,
  `kode_kriteria` varchar(10) NOT NULL,
  `nama_kriteria` varchar(100) NOT NULL,
  `atribut` enum('Benefit','Cost') NOT NULL,
  PRIMARY KEY (`id_kriteria`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `kriteria`
--

INSERT INTO `kriteria` (`id_kriteria`, `kode_kriteria`, `nama_kriteria`, `atribut`) VALUES
(1, 'C1', 'Pekerjaan', 'Benefit'),
(2, 'C2', 'Penghasilan per bulan', 'Cost'),
(3, 'C3', 'Usia', 'Benefit'),
(4, 'C4', 'Pendidikan', 'Benefit'),
(5, 'C5', 'Luas lantai bangunan', 'Cost'),
(6, 'C6', 'Jenis lantai', 'Benefit'),
(7, 'C7', 'Jenis dinding', 'Benefit'),
(8, 'C8', 'Kendaraan', 'Benefit'),
(9, 'C9', 'Sumber penerangan', 'Benefit'),
(10, 'C10', 'Status rumah', 'Benefit');

-- --------------------------------------------------------

--
-- Struktur dari tabel `nilai_pengajuan`
--

DROP TABLE IF EXISTS `nilai_pengajuan`;
CREATE TABLE IF NOT EXISTS `nilai_pengajuan` (
  `id_nilai` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `id_subkriteria` int NOT NULL,
  `nilai` float NOT NULL,
  PRIMARY KEY (`id_nilai`),
  KEY `id_pengajuan` (`id_pengajuan`),
  KEY `id_subkriteria` (`id_subkriteria`)
) ENGINE=MyISAM AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `nilai_pengajuan`
--

INSERT INTO `nilai_pengajuan` (`id_nilai`, `id_pengajuan`, `id_subkriteria`, `nilai`) VALUES
(80, 1, 32, 1),
(79, 1, 30, 2),
(78, 1, 25, 1),
(77, 1, 20, 1),
(76, 1, 18, 2),
(75, 1, 23, 2),
(74, 1, 14, 2),
(73, 1, 9, 1),
(72, 1, 7, 2),
(71, 1, 1, 1),
(11, 2, 4, 4),
(12, 2, 8, 1),
(13, 2, 12, 4),
(14, 2, 16, 4),
(15, 2, 24, 1),
(16, 2, 19, 3),
(17, 2, 22, 3),
(18, 2, 28, 4),
(19, 2, 31, 3),
(20, 2, 34, 3),
(21, 3, 2, 2),
(22, 3, 6, 3),
(23, 3, 11, 3),
(24, 3, 13, 1),
(25, 3, 23, 2),
(26, 3, 17, 1),
(27, 3, 21, 2),
(28, 3, 26, 2),
(29, 3, 29, 1),
(30, 3, 33, 2),
(31, 4, 3, 3),
(32, 4, 7, 2),
(33, 4, 10, 2),
(34, 4, 15, 3),
(35, 4, 24, 1),
(36, 4, 19, 3),
(37, 4, 22, 3),
(38, 4, 27, 3),
(39, 4, 31, 3),
(40, 4, 35, 4),
(41, 5, 1, 1),
(42, 5, 5, 4),
(43, 5, 12, 4),
(44, 5, 14, 2),
(45, 5, 23, 2),
(46, 5, 18, 2),
(47, 5, 20, 1),
(48, 5, 25, 1),
(49, 5, 29, 1),
(50, 5, 32, 1),
(51, 6, 4, 4),
(52, 6, 8, 1),
(53, 6, 9, 1),
(54, 6, 16, 4),
(55, 6, 24, 1),
(56, 6, 19, 3),
(57, 6, 22, 3),
(58, 6, 28, 4),
(59, 6, 31, 3),
(60, 6, 35, 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengajuan_bantuan`
--

DROP TABLE IF EXISTS `pengajuan_bantuan`;
CREATE TABLE IF NOT EXISTS `pengajuan_bantuan` (
  `id_pengajuan` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `nik` varchar(50) NOT NULL,
  `alamat` text NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `jenis_bantuan` varchar(100) NOT NULL,
  `tanggal_pengajuan` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pengajuan`),
  UNIQUE KEY `nik` (`nik`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `pengajuan_bantuan`
--

INSERT INTO `pengajuan_bantuan` (`id_pengajuan`, `user_id`, `nama_lengkap`, `nik`, `alamat`, `no_hp`, `jenis_bantuan`, `tanggal_pengajuan`) VALUES
(1, 2, 'Budi Santoso', '3501011111110001', 'Jl. Merdeka No. 10', '081234567890', 'Bantuan Sembako', '2025-09-24 01:10:41'),
(2, 3, 'Siti Aminah', '3501011111110002', 'Jl. Melati No. 21', '081234567891', 'Bantuan PKH', '2025-09-24 01:10:41'),
(3, 4, 'Andi Wijaya', '3501011111110003', 'Jl. Anggrek No. 5', '081234567892', 'Bantuan Pendidikan', '2025-09-24 01:10:41'),
(4, 5, 'Rina Kartika', '3501011111110004', 'Jl. Mawar No. 12', '081234567893', 'Bantuan UMKM', '2025-09-24 01:10:41'),
(5, 6, 'Joko Prasetyo', '3501011111110005', 'Jl. Kenanga No. 7', '081234567894', 'Bantuan Kesehatan', '2025-09-24 01:10:41'),
(6, 7, 'Dewi Lestari', '3501011111110006', 'Jl. Dahlia No. 18', '081234567895', 'Bantuan Sosial', '2025-09-24 01:10:41');

-- --------------------------------------------------------

--
-- Struktur dari tabel `subkriteria`
--

DROP TABLE IF EXISTS `subkriteria`;
CREATE TABLE IF NOT EXISTS `subkriteria` (
  `id_subkriteria` int NOT NULL AUTO_INCREMENT,
  `id_kriteria` int NOT NULL,
  `nama_subkriteria` varchar(255) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `nilai` int NOT NULL,
  PRIMARY KEY (`id_subkriteria`),
  KEY `id_kriteria` (`id_kriteria`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `subkriteria`
--

INSERT INTO `subkriteria` (`id_subkriteria`, `id_kriteria`, `nama_subkriteria`, `kategori`, `nilai`) VALUES
(1, 1, 'Pegawai swasta / negeri', 'Kurang', 1),
(2, 1, 'Pengusaha / pedagang', 'Cukup', 2),
(3, 1, 'Petani / buruh / karyawan', 'Memenuhi', 3),
(4, 1, 'Tidak bekerja / pengangguran', 'Sangat memenuhi', 4),
(5, 2, '> 3.000.000', 'Kurang', 4),
(6, 2, '1.500.000 s/d 3.000.000', 'Cukup', 3),
(7, 2, '500.000 s/d 1.500.000', 'Memenuhi', 2),
(8, 2, '< 500.000', 'Sangat memenuhi', 1),
(9, 3, '< 25 tahun', 'Kurang', 1),
(10, 3, '26 s/d 35 tahun', 'Cukup', 2),
(11, 3, '36 s/d 45 tahun', 'Memenuhi', 3),
(12, 3, '> 46 tahun', 'Sangat memenuhi', 4),
(13, 4, 'Perguruan tinggi', 'Kurang', 1),
(14, 4, 'SMA', 'Cukup', 2),
(15, 4, 'SMP', 'Memenuhi', 3),
(16, 4, 'SD', 'Sangat memenuhi', 4),
(17, 6, 'Keramik', 'Kurang', 1),
(18, 6, 'Plester / semen', 'Cukup', 2),
(19, 6, 'Tanah', 'Memenuhi', 3),
(20, 7, 'Tembok', 'Kurang', 1),
(21, 7, 'Kayu', 'Cukup', 2),
(22, 7, 'Bambu', 'Memenuhi', 3),
(23, 5, 'Di atas 8 m2', 'Kurang', 2),
(24, 5, 'Di bawah 8 m2', 'Sangat memenuhi', 1),
(25, 8, 'Mobil', 'Kurang', 1),
(26, 8, 'Motor', 'Cukup', 2),
(27, 8, 'Sepeda', 'Memenuhi', 3),
(28, 8, 'Tidak punya', 'Sangat memenuhi', 4),
(29, 9, 'PLN subsidi', 'Kurang', 1),
(30, 9, 'PLN non subsidi', 'Cukup', 2),
(31, 9, 'Tidak punya', 'Memenuhi', 3),
(32, 10, 'Milik sendiri', 'Kurang', 1),
(33, 10, 'Milik orang tua', 'Cukup', 2),
(34, 10, 'Kontrak / sewa', 'Memenuhi', 3),
(35, 10, 'Bebas sewa', 'Sangat memenuhi', 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'Super Admin', 'superadmin@gmail.com', '103d5896b784ecd6ff6117949dc189d5', 'admin'),
(2, 'User1', 'user1@gmail.com', 'c6c721befe4a046edb938e23ddf038ed', 'user'),
(3, 'User2', 'user2@gmail.com', 'c6c721befe4a046edb938e23ddf038ed', 'user'),
(4, 'User3', 'user3@gmail.com', 'c6c721befe4a046edb938e23ddf038ed', 'user'),
(5, 'User4', 'user4@gmail.com', 'c6c721befe4a046edb938e23ddf038ed', 'user'),
(6, 'User5', 'user5@gmail.com', 'c6c721befe4a046edb938e23ddf038ed', 'user'),
(7, 'User6', 'user6@gmail.com', 'c6c721befe4a046edb938e23ddf038ed', 'user');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
