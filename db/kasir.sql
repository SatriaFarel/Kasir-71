-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 05, 2025 at 07:28 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kasir`
--

-- --------------------------------------------------------

--
-- Table structure for table `t_admin`
--

CREATE TABLE `t_admin` (
  `f_id` int NOT NULL,
  `f_email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `f_username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `f_password` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `f_phone` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `f_otp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `f_otp_expired` datetime DEFAULT NULL,
  `f_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `f_token_expired` datetime DEFAULT NULL,
  `f_gambar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_admin`
--

INSERT INTO `t_admin` (`f_id`, `f_email`, `f_username`, `f_password`, `f_phone`, `f_otp`, `f_otp_expired`, `f_token`, `f_token_expired`, `f_gambar`) VALUES
(6, 'farelasik123@gmail.com', 'satjgkjgggk', '321', '088299309375', NULL, NULL, NULL, NULL, '../../asset/pfp/18ce9eb312.jpg'),
(7, 'satriafarel40@gmail.com', 'farel', '321', '088299309375', NULL, NULL, NULL, NULL, '../../asset/pfp/d4190e96d4.jpg'),
(8, 'admin@gmail.com', 'admin', '123', '089739809434', NULL, NULL, NULL, NULL, '../../asset/pfp/cfc8c2237a.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `t_detail_transaksi`
--

CREATE TABLE `t_detail_transaksi` (
  `no` int NOT NULL,
  `f_id` int NOT NULL,
  `f_id_transaksi` int NOT NULL,
  `f_id_produk` int NOT NULL,
  `f_quantity` int NOT NULL,
  `f_subtotal` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_detail_transaksi`
--

INSERT INTO `t_detail_transaksi` (`no`, `f_id`, `f_id_transaksi`, `f_id_produk`, `f_quantity`, `f_subtotal`) VALUES
(15, 1, 1, 1, 1, 12000),
(16, 2, 2, 2, 1, 12000),
(17, 3, 3, 4, 1, 5000),
(18, 4, 4, 5, 1, 5000),
(19, 5, 5, 1, 1, 12000),
(20, 5, 5, 2, 1, 12000),
(21, 6, 6, 3, 1, 12000),
(22, 7, 7, 2, 1, 12000),
(23, 8, 8, 4, 1, 5000),
(24, 8, 8, 5, 1, 5000),
(25, 9, 9, 3, 1, 12000),
(26, 10, 10, 4, 1, 5000),
(27, 11, 11, 3, 1, 12000),
(28, 12, 12, 3, 1, 12000),
(29, 13, 13, 5, 1, 5000);

-- --------------------------------------------------------

--
-- Table structure for table `t_kategori`
--

CREATE TABLE `t_kategori` (
  `f_id` int NOT NULL,
  `f_kategori` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_kategori`
--

INSERT INTO `t_kategori` (`f_id`, `f_kategori`) VALUES
(1, 'Makanan'),
(2, 'Minuman'),
(3, 'coba');

-- --------------------------------------------------------

--
-- Table structure for table `t_member`
--

CREATE TABLE `t_member` (
  `f_id` int NOT NULL,
  `f_nama_member` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `f_no_telp` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `f_status` enum('Aktif','Tidak Aktif') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `f_last_activity` date NOT NULL,
  `f_point` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_member`
--

INSERT INTO `t_member` (`f_id`, `f_nama_member`, `f_no_telp`, `f_status`, `f_last_activity`, `f_point`) VALUES
(1, 'Nur Said', '08892475683', 'Aktif', '2025-04-23', 24),
(2, 'Satria farel cipta pertama', '088299309375', 'Aktif', '2025-05-02', 94),
(3, 'Pakdhe ', '082647653554', 'Aktif', '2025-04-23', 5),
(4, 'memberrrr', '8362836184', 'Aktif', '2025-04-23', 0),
(5, 'cobaagfyf', '088268666', 'Aktif', '2025-04-23', 0);

-- --------------------------------------------------------

--
-- Table structure for table `t_produk`
--

CREATE TABLE `t_produk` (
  `f_id` int NOT NULL,
  `f_kodep` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `f_nama_produk` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `f_tanggal_expired` date NOT NULL,
  `f_stok` int NOT NULL,
  `f_modal` int NOT NULL,
  `f_harga_jual` int NOT NULL,
  `f_keuntungan` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `f_id_kategori` int NOT NULL,
  `f_gambar` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `f_qr` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `f_deskripsi` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_produk`
--

INSERT INTO `t_produk` (`f_id`, `f_kodep`, `f_nama_produk`, `f_tanggal_expired`, `f_stok`, `f_modal`, `f_harga_jual`, `f_keuntungan`, `f_id_kategori`, `f_gambar`, `f_qr`, `f_deskripsi`) VALUES
(1, 'PBB001', 'Potabee Beef BBQ', '2025-04-23', 0, 10000, 12000, '0', 1, '../../asset/product/6a910c520e.jpg', 'barcode_PBB001.png', 'Potabee Beef dengan rasa sapi panggang enak'),
(2, 'PAB002', 'Potabee Ayam Bakar', '2025-04-23', 27, 10000, 12000, '56000', 1, '../../asset/product/74be86eb34.webp', 'barcode_PAB002.png', 'Potabee dengan rasa ayam bakar sangat mengugah selera'),
(3, 'PRL003', 'Potabee Rumput Laut', '2025-05-21', 26, 10000, 12000, '2000', 1, '../../asset/product/7723f5fc31.png', 'barcode_PRL003.png', 'Potabee dengan rasa rumput laut sangat gurih '),
(4, 'MC001', 'Milku Coklat', '2025-05-21', 12, 4000, 5000, '1000', 2, '../../asset/product/2844fbacb0.jpg', 'barcode_MC001.png', 'milku dengan rasa coklat enak'),
(5, 'MO002', 'Milku Original', '2025-05-21', 12, 4000, 5000, '1000', 2, '../../asset/product/5e84af96ea.webp', 'barcode_MO002.png', 'milku dengan rasa origina enak dan menyegarkan'),
(6, 'MS003', 'Milku Strowberry', '2025-02-10', 15, 4000, 5000, '1000', 2, '../../asset/product/ac04faeb62.webp', 'barcode_MS003.png', 'milku denga rasa strowberry rasa asam-asam manis dan enak'),
(9, 'ATR', 'Astor', '2025-06-19', 12, 13000, 12000, '-1000', 3, '../../asset/product/90a7fc90e9.jpg', 'barcode_ATR.png', 'joig'),
(10, 'C004', 'Coba', '2025-05-31', 12, 9000, 10000, '1000', 1, '../../asset/product/28b17fb676.png', 'barcode_C004.png', 'Coba Aja bro\r\n'),
(11, 'PB001', 'produk baru', '2025-07-31', 12, 10000, 10000, '0', 1, '../../asset/product/2e24106a9c.png', 'barcode_PB001.png', 'kahdlkhfe');

-- --------------------------------------------------------

--
-- Table structure for table `t_transaksi`
--

CREATE TABLE `t_transaksi` (
  `f_id_transaksi` int NOT NULL,
  `f_id_detail` int DEFAULT NULL,
  `f_tanggal_pembelian` date NOT NULL,
  `f_total_harga` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `f_id_admin` int NOT NULL,
  `f_id_member` int NOT NULL,
  `f_total_keuntungan` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_transaksi`
--

INSERT INTO `t_transaksi` (`f_id_transaksi`, `f_id_detail`, `f_tanggal_pembelian`, `f_total_harga`, `f_id_admin`, `f_id_member`, `f_total_keuntungan`) VALUES
(1, 1, '2025-04-22', '12000', 7, 2, '2000'),
(2, 2, '2025-03-10', '12000', 7, 2, '2000'),
(3, 3, '2025-04-22', '5000', 7, 2, '1000'),
(4, 4, '2025-03-25', '5000', 7, 3, '1000'),
(5, 5, '2025-04-23', '24000', 7, 1, '4000'),
(6, 6, '2025-03-25', '12000', 7, 2, '2000'),
(7, 7, '2025-04-23', '12000', 7, 2, '2000'),
(8, 8, '2025-04-23', '10000', 8, 2, '2000'),
(9, 9, '2025-04-23', '20000', 8, 2, '2000'),
(10, 10, '2025-04-24', '5000', 8, 2, '1000'),
(11, 11, '2025-04-24', '12000', 7, 2, '2000'),
(12, 12, '2025-04-30', '12000', 7, 2, '2000'),
(13, 13, '2025-05-02', '5000', 7, 2, '1000');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `t_admin`
--
ALTER TABLE `t_admin`
  ADD PRIMARY KEY (`f_id`);

--
-- Indexes for table `t_detail_transaksi`
--
ALTER TABLE `t_detail_transaksi`
  ADD PRIMARY KEY (`no`),
  ADD KEY `f_id_produk` (`f_id_produk`);

--
-- Indexes for table `t_kategori`
--
ALTER TABLE `t_kategori`
  ADD PRIMARY KEY (`f_id`);

--
-- Indexes for table `t_member`
--
ALTER TABLE `t_member`
  ADD PRIMARY KEY (`f_id`);

--
-- Indexes for table `t_produk`
--
ALTER TABLE `t_produk`
  ADD PRIMARY KEY (`f_id`),
  ADD KEY `index` (`f_id_kategori`);

--
-- Indexes for table `t_transaksi`
--
ALTER TABLE `t_transaksi`
  ADD PRIMARY KEY (`f_id_transaksi`),
  ADD KEY `index` (`f_id_admin`,`f_id_member`),
  ADD KEY `f_id_member` (`f_id_member`),
  ADD KEY `f_id_detail` (`f_id_detail`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `t_admin`
--
ALTER TABLE `t_admin`
  MODIFY `f_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `t_detail_transaksi`
--
ALTER TABLE `t_detail_transaksi`
  MODIFY `no` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `t_kategori`
--
ALTER TABLE `t_kategori`
  MODIFY `f_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `t_member`
--
ALTER TABLE `t_member`
  MODIFY `f_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `t_produk`
--
ALTER TABLE `t_produk`
  MODIFY `f_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `t_transaksi`
--
ALTER TABLE `t_transaksi`
  MODIFY `f_id_transaksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1002;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `t_detail_transaksi`
--
ALTER TABLE `t_detail_transaksi`
  ADD CONSTRAINT `t_detail_transaksi_ibfk_1` FOREIGN KEY (`f_id_produk`) REFERENCES `t_produk` (`f_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `t_produk`
--
ALTER TABLE `t_produk`
  ADD CONSTRAINT `t_produk_ibfk_1` FOREIGN KEY (`f_id_kategori`) REFERENCES `t_kategori` (`f_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `t_transaksi`
--
ALTER TABLE `t_transaksi`
  ADD CONSTRAINT `t_transaksi_ibfk_3` FOREIGN KEY (`f_id_member`) REFERENCES `t_member` (`f_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `t_transaksi_ibfk_4` FOREIGN KEY (`f_id_admin`) REFERENCES `t_admin` (`f_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
