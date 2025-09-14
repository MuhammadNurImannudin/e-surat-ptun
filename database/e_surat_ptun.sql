-- Database: e_surat_ptun
-- Hapus database jika sudah ada, lalu buat database baru
-- CREATE DATABASE IF NOT EXISTS e_surat_ptun;
-- USE e_surat_ptun;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
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
-- Password default untuk semua user: admin123, operator123, pimpinan123
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `jabatan`, `level`, `created_at`, `updated_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'Administrator Sistem', 'Administrator', 'admin', '2025-09-14 01:27:05', '2025-09-14 01:27:05'),
(2, 'operator1', '2407bd807d6ca01d1bcd766c730cec9a', 'Budi Santoso, S.H.', 'Staff Administrasi', 'operator', '2025-09-14 01:27:05', '2025-09-14 01:27:05'),
(3, 'pimpinan1', '7d3207a13dc221ac13c2f3dac3011f50', 'Dr. Ahmad Fauzi, S.H., M.H.', 'Ketua Pengadilan', 'pimpinan', '2025-09-14 01:27:05', '2025-09-14 01:27:05');

-- --------------------------------------------------------

--
-- Table structure for table `surat_masuk`
--

DROP TABLE IF EXISTS `surat_masuk`;
CREATE TABLE `surat_masuk` (
  `id` int(11) NOT NULL,
  `nomor_surat` varchar(100) NOT NULL,
  `nomor_agenda` varchar(50) NOT NULL,
  `tanggal_surat` date NOT NULL,
  `tanggal_diterima` date NOT NULL,
  `pengirim` varchar(200) NOT NULL,
  `alamat_pengirim` text DEFAULT NULL,
  `perihal` text NOT NULL,
  `jenis_surat` varchar(100) NOT NULL,
  `sifat_surat` enum('Biasa','Penting','Segera','Sangat Segera') DEFAULT 'Biasa',
  `lampiran` varchar(200) DEFAULT NULL,
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
(1, '001/PN-BJM/I/2024', 'AG001/2024', '2024-01-15', '2024-01-16', 'Pengadilan Negeri Banjarmasin', 'Jl. Lambung Mangkurat No. 1, Banjarmasin', 'Permohonan Koordinasi Pelaksanaan Sidang Sengketa TUN', 'Surat Dinas', 'Penting', '1 berkas', 'Untuk ditindaklanjuti sesuai prosedur', NULL, NULL, 'Diproses', 1, '2024-01-16 02:30:00', '2024-01-20 03:15:00'),
(2, '002/KEMENKUMHAM/I/2024', 'AG002/2024', '2024-01-18', '2024-01-19', 'Kementerian Hukum dan HAM RI', 'Jl. H.R. Rasuna Said Kav. 6-7, Jakarta Selatan', 'Laporan Pelaksanaan Tugas dan Fungsi Triwulan I 2024', 'Surat Laporan', 'Biasa', '2 lampiran', 'Untuk diverifikasi dan diserahkan ke pimpinan', NULL, NULL, 'Selesai', 1, '2024-01-19 01:45:00', '2024-01-25 04:20:00'),
(3, '003/MA-RI/I/2024', 'AG003/2024', '2024-01-20', '2024-01-22', 'Mahkamah Agung Republik Indonesia', 'Jl. Medan Merdeka Utara No. 9-13, Jakarta Pusat', 'Edaran tentang Implementasi Sistem Informasi Administrasi Perkara', 'Surat Edaran', 'Segera', '3 lampiran', 'Untuk disebarluaskan kepada seluruh pegawai', 'Segera untuk implementasi', NULL, 'Masuk', 1, '2024-01-22 06:10:00', '2024-01-22 06:10:00'),
(4, '004/PTUN-PLK/I/2024', 'AG004/2024', '2024-01-25', '2024-01-26', 'PTUN Palangkaraya', 'Jl. Tjilik Riwut Km. 1,5 Palangkaraya', 'Undangan Rapat Koordinasi Pengadilan TUN se-Kalimantan', 'Surat Undangan', 'Penting', '1 berkas', 'Untuk dihadiri oleh pimpinan atau perwakilan', NULL, NULL, 'Diproses', 1, '2024-01-26 04:20:00', '2024-01-28 02:15:00'),
(5, '005/BPN-KALSEL/II/2024', 'AG005/2024', '2024-02-01', '2024-02-02', 'BPN Provinsi Kalimantan Selatan', 'Jl. A. Yani Km. 6 No. 1, Banjarmasin', 'Konsultasi Penyelesaian Sengketa Pertanahan', 'Surat Konsultasi', 'Biasa', '5 berkas', 'Untuk dikaji dan diberikan pendapat hukum', NULL, NULL, 'Masuk', 2, '2024-02-02 03:30:00', '2024-02-02 03:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `surat_keluar`
--

DROP TABLE IF EXISTS `surat_keluar`;
CREATE TABLE `surat_keluar` (
  `id` int(11) NOT NULL,
  `nomor_surat` varchar(100) NOT NULL,
  `nomor_agenda` varchar(50) NOT NULL,
  `tanggal_surat` date NOT NULL,
  `tanggal_keluar` date NOT NULL,
  `penerima` varchar(200) NOT NULL,
  `alamat_penerima` text DEFAULT NULL,
  `perihal` text NOT NULL,
  `jenis_surat` varchar(100) NOT NULL,
  `sifat_surat` enum('Biasa','Penting','Segera','Sangat Segera') DEFAULT 'Biasa',
  `lampiran` varchar(200) DEFAULT NULL,
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
(1, '001/SK-PTUN-BJM/I/2024', 'AK001/2024', '2024-01-20', '2024-01-20', 'Mahkamah Agung RI', 'Jl. Medan Merdeka Utara No. 9-13, Jakarta Pusat', 'Laporan Kinerja Bulanan Januari 2024', 'Surat Laporan', 'Penting', '2 lampiran', 'Ketua PTUN se-Indonesia', 'Laporan lengkap sesuai format', NULL, 'Dikirim', 1, '2024-01-20 07:00:00', '2024-01-20 08:30:00'),
(2, '002/SK-PTUN-BJM/I/2024', 'AK002/2024', '2024-01-22', '2024-01-22', 'Pengadilan Tinggi Kalimantan Selatan', 'Jl. Lambung Mangkurat No. 34, Banjarmasin', 'Undangan Rapat Koordinasi Penanganan Perkara', 'Surat Undangan', 'Segera', '1 agenda', 'PTUN se-Kalimantan Selatan', NULL, NULL, 'Sampai', 1, '2024-01-22 08:15:00', '2024-01-23 04:20:00'),
(3, '003/SK-PTUN-BJM/I/2024', 'AK003/2024', '2024-01-25', '2024-01-25', 'BPN Provinsi Kalimantan Selatan', 'Jl. A. Yani Km. 6 No. 1, Banjarmasin', 'Jawaban Konsultasi Penyelesaian Sengketa Pertanahan', 'Surat Jawaban', 'Penting', '3 lampiran', 'Kepala BPN Kabupaten/Kota se-Kalsel', 'Sesuai peraturan yang berlaku', NULL, 'Dikirim', 2, '2024-01-25 05:45:00', '2024-01-26 02:10:00'),
(4, '004/SK-PTUN-BJM/II/2024', 'AK004/2024', '2024-02-01', '2024-02-01', 'Kementerian Hukum dan HAM RI', 'Jl. H.R. Rasuna Said Kav. 6-7, Jakarta Selatan', 'Usulan Perbaikan Sarana dan Prasarana Pengadilan', 'Surat Usulan', 'Biasa', '5 lampiran', 'Direktur Jenderal Badan Peradilan Umum', 'Sesuai kebutuhan operasional', NULL, 'Draft', 3, '2024-02-01 06:30:00', '2024-02-01 06:30:00'),
(5, '005/SK-PTUN-BJM/II/2024', 'AK005/2024', '2024-02-05', '2024-02-05', 'Walikota Banjarmasin', 'Jl. Trunojoyo No. 1, Banjarmasin', 'Pemberitahuan Putusan Pengadilan TUN', 'Surat Pemberitahuan', 'Segera', '1 salinan putusan', 'Kepala Bagian Hukum Pemkot', 'Untuk segera ditindaklanjuti', NULL, 'Dikirim', 2, '2024-02-05 04:00:00', '2024-02-05 07:15:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `surat_masuk`
--
ALTER TABLE `surat_masuk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_surat` (`nomor_surat`),
  ADD UNIQUE KEY `nomor_agenda` (`nomor_agenda`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_tanggal_diterima` (`tanggal_diterima`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_surat` (`nomor_surat`),
  ADD UNIQUE KEY `nomor_agenda` (`nomor_agenda`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_tanggal_keluar` (`tanggal_keluar`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `surat_masuk`
--
ALTER TABLE `surat_masuk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  MODIFY `id` int(11) NOT NULL AUTO_AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `surat_masuk`
--
ALTER TABLE `surat_masuk`
  ADD CONSTRAINT `surat_masuk_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  ADD CONSTRAINT `surat_keluar_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */; NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `surat_keluar`
--

INSERT INTO `surat_keluar` (`id`, `nomor_surat`, `nomor_agenda`, `tanggal_surat`, `tanggal_keluar`, `penerima`, `alamat_penerima`, `perihal`, `jenis_surat`, `sifat_surat`, `lampiran`, `tembusan`, `keterangan`, `file_surat`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '001/SK-PTUN-BJM/I/2024', 'AK001/2024', '2024-01-20', '2024-01-20', 'Mahkamah Agung RI', 'Jl. Medan Merdeka Utara No. 9-13, Jakarta Pusat', 'Laporan Kinerja Bulanan Januari 2024', 'Surat Laporan', 'Penting', '2 lampiran', 'Ketua PTUN se-Indonesia', 'Laporan lengkap sesuai format', NULL, 'Dikirim', 1, '2024-01-20 07:00:00', '2024-01-20 08:30:00'),
(2, '002/SK-PTUN-BJM/I/2024', 'AK002/2024', '2024-01-22', '2024-01-22', 'Pengadilan Tinggi Kalimantan Selatan', 'Jl. Lambung Mangkurat No. 34, Banjarmasin', 'Undangan Rapat Koordinasi Penanganan Perkara', 'Surat Undangan', 'Segera', '1 agenda', 'PTUN se-Kalimantan Selatan', NULL, NULL, 'Sampai', 1, '2024-01-22 08:15:00', '2024-01-23 04:20:00'),
(3, '003/SK-PTUN-BJM/I/2024', 'AK003/2024', '2024-01-25', '2024-01-25', 'BPN Provinsi Kalimantan Selatan', 'Jl. A. Yani Km. 6 No. 1, Banjarmasin', 'Jawaban Konsultasi Penyelesaian Sengketa Pertanahan', 'Surat Jawaban', 'Penting', '3 lampiran', 'Kepala BPN Kabupaten/Kota se-Kalsel', 'Sesuai peraturan yang berlaku', NULL, 'Dikirim', 2, '2024-01-25 05:45:00', '2024-01-26 02:10:00'),
(4, '004/SK-PTUN-BJM/II/2024', 'AK004/2024', '2024-02-01', '2024-02-01', 'Kementerian Hukum dan HAM RI', 'Jl. H.R. Rasuna Said Kav. 6-7, Jakarta Selatan', 'Usulan Perbaikan Sarana dan Prasarana Pengadilan', 'Surat Usulan', 'Biasa', '5 lampiran', 'Direktur Jenderal Badan Peradilan Umum', 'Sesuai kebutuhan operasional', NULL, 'Draft', 3, '2024-02-01 06:30:00', '2024-02-01 06:30:00'),
(5, '005/SK-PTUN-BJM/II/2024', 'AK005/2024', '2024-02-05', '2024-02-05', 'Walikota Banjarmasin', 'Jl. Trunojoyo No. 1, Banjarmasin', 'Pemberitahuan Putusan Pengadilan TUN', 'Surat Pemberitahuan', 'Segera', '1 salinan putusan', 'Kepala Bagian Hukum Pemkot', 'Untuk segera ditindaklanjuti', NULL, 'Dikirim', 2, '2024-02-05 04:00:00', '2024-02-05 07:15:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `surat_masuk`
--
ALTER TABLE `surat_masuk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_surat` (`nomor_surat`),
  ADD UNIQUE KEY `nomor_agenda` (`nomor_agenda`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_tanggal_diterima` (`tanggal_diterima`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_surat` (`nomor_surat`),
  ADD UNIQUE KEY `nomor_agenda` (`nomor_agenda`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_tanggal_keluar` (`tanggal_keluar`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `surat_masuk`
--
ALTER TABLE `surat_masuk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `surat_masuk`
--
ALTER TABLE `surat_masuk`
  ADD CONSTRAINT `surat_masuk_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  ADD CONSTRAINT `surat_keluar_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;