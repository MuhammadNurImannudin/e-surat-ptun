-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 14, 2025 at 03:40 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `e_surat_ptun`
--

-- --------------------------------------------------------

--
-- Table structure for table `surat_keluar`
--

CREATE TABLE `surat_keluar` (
  `id` int(11) NOT NULL,
  `nomor_surat` varchar(50) NOT NULL,
  `nomor_agenda` varchar(30) NOT NULL,
  `tanggal_surat` date NOT NULL,
  `tanggal_keluar` date NOT NULL,
  `penerima` varchar(100) NOT NULL,
  `alamat_penerima` text DEFAULT NULL,
  `perihal` text NOT NULL,
  `jenis_surat` varchar(50) NOT NULL,
  `sifat_surat` enum('Biasa','Penting','Segera','Sangat Segera') DEFAULT 'Biasa',
  `lampiran` varchar(100) DEFAULT NULL,
  `tembusan` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `file_surat` varchar(255) DEFAULT NULL,
  `status` enum('Draft','Dikirim','Sampai') DEFAULT 'Draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `surat_keluar`
--

INSERT INTO `surat_keluar` (`id`, `nomor_surat`, `nomor_agenda`, `tanggal_surat`, `tanggal_keluar`, `penerima`, `alamat_penerima`, `perihal`, `jenis_surat`, `sifat_surat`, `lampiran`, `tembusan`, `keterangan`, `file_surat`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '001/SK/I/2024', 'AK001/2024', '2024-01-20', '2024-01-20', 'Mahkamah Agung RI', 'Jakarta Pusat', 'Laporan Kinerja Bulanan Januari 2024', 'Surat Laporan', 'Penting', NULL, NULL, NULL, NULL, 'Draft', 1, '2025-09-14 01:27:05', '2025-09-14 01:27:05'),
(2, '002/SK/I/2024', 'AK002/2024', '2024-01-22', '2024-01-22', 'Pengadilan Tinggi Kalimantan Selatan', 'Banjarmasin', 'Undangan Rapat Koordinasi', 'Surat Undangan', 'Segera', NULL, NULL, NULL, NULL, 'Draft', 1, '2025-09-14 01:27:05', '2025-09-14 01:27:05');

-- --------------------------------------------------------

--
-- Table structure for table `surat_masuk`
--

CREATE TABLE `surat_masuk` (
  `id` int(11) NOT NULL,
  `nomor_surat` varchar(50) NOT NULL,
  `nomor_agenda` varchar(30) NOT NULL,
  `tanggal_surat` date NOT NULL,
  `tanggal_diterima` date NOT NULL,
  `pengirim` varchar(100) NOT NULL,
  `alamat_pengirim` text DEFAULT NULL,
  `perihal` text NOT NULL,
  `jenis_surat` varchar(50) NOT NULL,
  `sifat_surat` enum('Biasa','Penting','Segera','Sangat Segera') DEFAULT 'Biasa',
  `lampiran` varchar(100) DEFAULT NULL,
  `disposisi` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `file_surat` varchar(255) DEFAULT NULL,
  `status` enum('Masuk','Diproses','Selesai') DEFAULT 'Masuk',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `surat_masuk`
--

INSERT INTO `surat_masuk` (`id`, `nomor_surat`, `nomor_agenda`, `tanggal_surat`, `tanggal_diterima`, `pengirim`, `alamat_pengirim`, `perihal`, `jenis_surat`, `sifat_surat`, `lampiran`, `disposisi`, `keterangan`, `file_surat`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '001/SM/I/2024', 'AG001/2024', '2024-01-15', '2024-01-16', 'Pengadilan Negeri Banjarmasin', 'Jl. Lambung Mangkurat No. 1, Banjarmasin', 'Permohonan Koordinasi Pelaksanaan Sidang', 'Surat Dinas', 'Penting', NULL, NULL, NULL, NULL, 'Masuk', 1, '2025-09-14 01:27:05', '2025-09-14 01:27:05'),
(2, '002/SM/I/2024', 'AG002/2024', '2024-01-18', '2024-01-19', 'Kementerian Hukum dan HAM', 'Jakarta Selatan', 'Laporan Pelaksanaan Tugas Triwulan I', 'Surat Laporan', 'Biasa', NULL, NULL, NULL, NULL, 'Masuk', 1, '2025-09-14 01:27:05', '2025-09-14 01:27:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `jabatan` varchar(50) NOT NULL,
  `level` enum('admin','operator','pimpinan') DEFAULT 'operator',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `jabatan`, `level`, `created_at`, `updated_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'Administrator', 'Administrator Sistem', 'admin', '2025-09-14 01:27:05', '2025-09-14 01:27:05'),
(2, 'operator1', '2407bd807d6ca01d1bcd766c730cec9a', 'Budi Santoso', 'Operator Surat', 'operator', '2025-09-14 01:27:05', '2025-09-14 01:27:05'),
(3, 'pimpinan1', '7d3207a13dc221ac13c2f3dac3011f50', 'Dr. Ahmad Fauzi, S.H., M.H.', 'Ketua Pengadilan', 'pimpinan', '2025-09-14 01:27:05', '2025-09-14 01:27:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_surat` (`nomor_surat`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `surat_masuk`
--
ALTER TABLE `surat_masuk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_surat` (`nomor_surat`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `surat_masuk`
--
ALTER TABLE `surat_masuk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  ADD CONSTRAINT `surat_keluar_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `surat_masuk`
--
ALTER TABLE `surat_masuk`
  ADD CONSTRAINT `surat_masuk_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
