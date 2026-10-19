-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2026 at 08:52 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_parkir`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_area_parkir`
--

CREATE TABLE `tb_area_parkir` (
  `id_area` int(11) NOT NULL,
  `nama_area` varchar(50) NOT NULL,
  `kapasitas` int(5) NOT NULL,
  `terisi` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_area_parkir`
--

INSERT INTO `tb_area_parkir` (`id_area`, `nama_area`, `kapasitas`, `terisi`) VALUES
(1, 'Area A - Motor', 50, 0),
(2, 'Area B - Mobil', 30, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tb_kendaraan`
--

CREATE TABLE `tb_kendaraan` (
  `id_kendaraan` int(11) NOT NULL,
  `plat_nomor` varchar(15) NOT NULL,
  `jenis_kendaraan` varchar(20) NOT NULL,
  `warna` varchar(20) DEFAULT NULL,
  `pemilik` varchar(100) DEFAULT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_kendaraan`
--

INSERT INTO `tb_kendaraan` (`id_kendaraan`, `plat_nomor`, `jenis_kendaraan`, `warna`, `pemilik`, `id_user`) VALUES
(1, 'M 1234 AB', 'motor', 'Hitam', 'Andi', 2),
(2, 'M 5678 CD', 'mobil', 'Putih', 'Rina', 2),
(3, 'AE 2565 DF', 'mobil', 'Merah', 'Cici', 1),
(4, 'AE 2365 GR', 'motor', 'Putih', 'Bagas', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tb_log_aktivitas`
--

CREATE TABLE `tb_log_aktivitas` (
  `id_log` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `aktivitas` varchar(100) NOT NULL,
  `waktu_aktivitas` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_log_aktivitas`
--

INSERT INTO `tb_log_aktivitas` (`id_log`, `id_user`, `aktivitas`, `waktu_aktivitas`) VALUES
(1, 1, 'Login ke aplikasi', '2026-10-19 09:21:58'),
(2, 1, 'Logout dari aplikasi', '2026-10-19 09:22:17'),
(3, 3, 'Login ke aplikasi', '2026-10-19 09:22:42'),
(4, 3, 'Logout dari aplikasi', '2026-10-19 09:23:12'),
(5, 2, 'Login ke aplikasi', '2026-10-19 09:27:24'),
(6, 2, 'Logout dari aplikasi', '2026-10-19 09:32:08'),
(7, 1, 'Login ke aplikasi', '2026-10-19 09:32:21'),
(8, 1, 'Logout dari aplikasi', '2026-10-19 09:32:46'),
(9, 2, 'Login ke aplikasi', '2026-10-19 09:49:15'),
(10, 2, 'Memproses kendaraan keluar: M 1234 AB biaya 2000', '2026-10-19 09:49:49'),
(11, 2, 'Logout dari aplikasi', '2026-10-19 10:39:35'),
(12, 1, 'Login ke aplikasi', '2026-10-19 10:47:19'),
(13, 1, 'Logout dari aplikasi', '2026-10-19 10:48:09'),
(14, 2, 'Login ke aplikasi', '2026-10-19 11:00:31'),
(15, 2, 'Logout dari aplikasi', '2026-10-19 11:01:32'),
(16, 1, 'Login ke aplikasi', '2026-10-19 11:16:55'),
(17, 1, 'Logout dari aplikasi', '2026-10-19 11:22:52'),
(18, 3, 'Login ke aplikasi', '2026-10-19 11:28:29'),
(19, 3, 'Logout dari aplikasi', '2026-10-19 11:28:40'),
(20, 1, 'Login ke aplikasi', '2026-10-19 11:28:51'),
(21, 1, 'Logout dari aplikasi', '2026-10-19 11:29:33'),
(22, 1, 'Login ke aplikasi', '2026-10-19 11:30:15'),
(23, 1, 'Mencatat kendaraan masuk: M 5678 CD', '2026-10-19 11:36:50'),
(24, 1, 'Menyimpan kendaraan AE 2565 DF', '2026-10-19 11:41:48'),
(25, 1, 'Logout dari aplikasi', '2026-10-19 11:42:18'),
(26, 2, 'Login ke aplikasi', '2026-10-19 12:49:38'),
(27, 2, 'Logout dari aplikasi', '2026-10-19 12:54:21'),
(28, 1, 'Login ke aplikasi', '2026-10-19 12:54:36'),
(29, 1, 'Menambah user auroraa', '2026-10-19 12:55:36'),
(30, 1, 'Mengubah user petugas2', '2026-10-19 12:56:03'),
(31, 1, 'Menyimpan kendaraan AE 2365 GR', '2026-10-19 12:56:56'),
(32, 1, 'Logout dari aplikasi', '2026-10-19 12:57:10'),
(33, 3, 'Login ke aplikasi', '2026-10-19 12:57:23'),
(34, 3, 'Mencatat kendaraan masuk: AE 2365 GR', '2026-10-19 12:57:34'),
(35, 3, 'Logout dari aplikasi', '2026-10-19 12:57:43'),
(36, 1, 'Login ke aplikasi', '2026-10-19 13:07:27'),
(37, 1, 'Logout dari aplikasi', '2026-10-19 13:08:14'),
(38, 1, 'Login ke aplikasi', '2026-10-19 13:47:28'),
(39, 1, 'Logout dari aplikasi', '2026-10-19 13:47:45'),
(40, 3, 'Login ke aplikasi', '2026-10-19 13:47:52'),
(41, 3, 'Memproses kendaraan keluar: M 5678 CD biaya 5000', '2026-10-19 13:47:58'),
(42, 3, 'Memproses kendaraan keluar: AE 2365 GR biaya 2000', '2026-10-19 13:48:19');

-- --------------------------------------------------------

--
-- Table structure for table `tb_tarif`
--

CREATE TABLE `tb_tarif` (
  `id_tarif` int(11) NOT NULL,
  `jenis_kendaraan` enum('motor','mobil','lainnya') NOT NULL,
  `tarif_per_jam` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_tarif`
--

INSERT INTO `tb_tarif` (`id_tarif`, `jenis_kendaraan`, `tarif_per_jam`) VALUES
(1, 'motor', 2000),
(2, 'mobil', 5000),
(3, 'lainnya', 3000);

-- --------------------------------------------------------

--
-- Table structure for table `tb_transaksi`
--

CREATE TABLE `tb_transaksi` (
  `id_parkir` int(11) NOT NULL,
  `id_kendaraan` int(11) NOT NULL,
  `waktu_masuk` datetime NOT NULL,
  `waktu_keluar` datetime DEFAULT NULL,
  `id_tarif` int(11) NOT NULL,
  `durasi_jam` int(5) DEFAULT NULL,
  `biaya_total` decimal(10,0) DEFAULT NULL,
  `status` enum('masuk','keluar') NOT NULL DEFAULT 'masuk',
  `id_user` int(11) NOT NULL,
  `id_area` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_transaksi`
--

INSERT INTO `tb_transaksi` (`id_parkir`, `id_kendaraan`, `waktu_masuk`, `waktu_keluar`, `id_tarif`, `durasi_jam`, `biaya_total`, `status`, `id_user`, `id_area`) VALUES
(1, 1, '2026-10-19 08:49:17', '2026-10-19 09:49:49', 1, 1, 2000, 'keluar', 2, 1),
(2, 2, '2026-10-19 11:36:50', '2026-10-19 13:47:58', 2, 1, 5000, 'keluar', 1, 2),
(3, 4, '2026-10-19 12:57:34', '2026-10-19 13:48:19', 1, 1, 2000, 'keluar', 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tb_user`
--

CREATE TABLE `tb_user` (
  `id_user` int(11) NOT NULL,
  `nama_lengkap` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('admin','petugas','owner') NOT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_user`
--

INSERT INTO `tb_user` (`id_user`, `nama_lengkap`, `username`, `password`, `role`, `status_aktif`) VALUES
(1, 'Budi Santoso', 'admin1', '$2y$10$W8rnx9vuVEjmcSFrE3IM4ejmJv8OONX0UaDxSmoZD4Xp0BxxyCEj6', 'admin', 1),
(2, 'Siti Aminah', 'petugas1', '$2y$10$dOPpR/WsTZzNpMdbMcnVU.rwOaYd/xl9tOAjIfmwGZmaFLlKU.tni', 'petugas', 1),
(3, 'Pak Hendra', 'petugas2', '$2y$10$P9Du7/K4L5tUpqVyJWsmjeOJYLfP7LN3ymGDX8Qjj7Pl0tXf4fiI.', 'petugas', 1),
(4, 'Aurora Ditria', 'auroraa', '$2y$10$WRj4r01RtFqmnf7moJ0YfuZ3LTXPWLc/yPk5jQ6MLCQ81rjoA1qL2', 'owner', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_area_parkir`
--
ALTER TABLE `tb_area_parkir`
  ADD PRIMARY KEY (`id_area`);

--
-- Indexes for table `tb_kendaraan`
--
ALTER TABLE `tb_kendaraan`
  ADD PRIMARY KEY (`id_kendaraan`),
  ADD KEY `tb_kendaraan_id_user_foreign` (`id_user`);

--
-- Indexes for table `tb_log_aktivitas`
--
ALTER TABLE `tb_log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `tb_log_aktivitas_id_user_foreign` (`id_user`);

--
-- Indexes for table `tb_tarif`
--
ALTER TABLE `tb_tarif`
  ADD PRIMARY KEY (`id_tarif`);

--
-- Indexes for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  ADD PRIMARY KEY (`id_parkir`),
  ADD KEY `tb_transaksi_id_user_foreign` (`id_user`),
  ADD KEY `tb_transaksi_id_tarif_foreign` (`id_tarif`),
  ADD KEY `tb_transaksi_id_kendaraan_foreign` (`id_kendaraan`),
  ADD KEY `tb_transaksi_id_area_foreign` (`id_area`);

--
-- Indexes for table `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `tb_user_username_unique` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_area_parkir`
--
ALTER TABLE `tb_area_parkir`
  MODIFY `id_area` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tb_kendaraan`
--
ALTER TABLE `tb_kendaraan`
  MODIFY `id_kendaraan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tb_log_aktivitas`
--
ALTER TABLE `tb_log_aktivitas`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `tb_tarif`
--
ALTER TABLE `tb_tarif`
  MODIFY `id_tarif` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  MODIFY `id_parkir` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_kendaraan`
--
ALTER TABLE `tb_kendaraan`
  ADD CONSTRAINT `tb_kendaraan_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`);

--
-- Constraints for table `tb_log_aktivitas`
--
ALTER TABLE `tb_log_aktivitas`
  ADD CONSTRAINT `tb_log_aktivitas_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`);

--
-- Constraints for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  ADD CONSTRAINT `tb_transaksi_id_area_foreign` FOREIGN KEY (`id_area`) REFERENCES `tb_area_parkir` (`id_area`),
  ADD CONSTRAINT `tb_transaksi_id_kendaraan_foreign` FOREIGN KEY (`id_kendaraan`) REFERENCES `tb_kendaraan` (`id_kendaraan`),
  ADD CONSTRAINT `tb_transaksi_id_tarif_foreign` FOREIGN KEY (`id_tarif`) REFERENCES `tb_tarif` (`id_tarif`),
  ADD CONSTRAINT `tb_transaksi_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
