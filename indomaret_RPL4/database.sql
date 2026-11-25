-- ==============================================================
-- Database SQL Dump: indomaret_rpl4
-- Sistem Point of Sales (POS) Kasir Indomaret RPL 4
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `indomaret_rpl4` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `indomaret_rpl4`;

-- --------------------------------------------------------
-- 1. Struktur tabel `tabel_kasir`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `tabel_detail_transaksi`;
DROP TABLE IF EXISTS `tabel_transaksi`;
DROP TABLE IF EXISTS `tabel_kasir`;
DROP TABLE IF EXISTS `tabel_produk`;
DROP TABLE IF EXISTS `tabel_donasi`;

CREATE TABLE `tabel_kasir` (
  `id_kasir` int(11) NOT NULL,
  `nama_kasir` varchar(50) NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 2. Struktur tabel `tabel_produk`
-- --------------------------------------------------------
CREATE TABLE `tabel_produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(50) NOT NULL,
  `harga` int(10) NOT NULL,
  `diskon` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 3. Struktur tabel `tabel_donasi`
-- --------------------------------------------------------
CREATE TABLE `tabel_donasi` (
  `id_donasi` int(11) NOT NULL,
  `nama_donasi` varchar(50) NOT NULL,
  `harga_donasi` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 4. Struktur tabel `tabel_transaksi`
-- --------------------------------------------------------
CREATE TABLE `tabel_transaksi` (
  `kode_transaksi` varchar(50) NOT NULL,
  `tanggal_transaksi` datetime NOT NULL,
  `no_order` int(11) NOT NULL,
  `total` float NOT NULL,
  `QRIS` float NOT NULL,
  `tunai` int(11) NOT NULL,
  `kembalian` int(11) NOT NULL,
  `id_donasi` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 5. Struktur tabel `tabel_detail_transaksi`
-- --------------------------------------------------------
CREATE TABLE `tabel_detail_transaksi` (
  `kode_transaksi` varchar(50) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `id_kasir` int(11) NOT NULL,
  `harga` int(11) NOT NULL,
  `kuantitas` int(11) NOT NULL,
  `diskon` int(11) NOT NULL,
  `harga_jual` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Indeks & Kunci Utama (Primary & Foreign Keys)
-- --------------------------------------------------------

ALTER TABLE `tabel_kasir`
  ADD PRIMARY KEY (`id_kasir`);

ALTER TABLE `tabel_produk`
  ADD PRIMARY KEY (`id_produk`);

ALTER TABLE `tabel_donasi`
  ADD PRIMARY KEY (`id_donasi`);

ALTER TABLE `tabel_transaksi`
  ADD PRIMARY KEY (`kode_transaksi`),
  ADD KEY `fk_transaksi_donasi` (`id_donasi`);

ALTER TABLE `tabel_detail_transaksi`
  ADD PRIMARY KEY (`kode_transaksi`, `id_produk`),
  ADD KEY `fk_detail_produk` (`id_produk`),
  ADD KEY `fk_detail_kasir` (`id_kasir`);

-- --------------------------------------------------------
-- Auto Increment untuk Tabel
-- --------------------------------------------------------

ALTER TABLE `tabel_kasir`
  MODIFY `id_kasir` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `tabel_produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `tabel_donasi`
  MODIFY `id_donasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

-- --------------------------------------------------------
-- Relasi Antar Tabel (Foreign Key Constraints)
-- --------------------------------------------------------

ALTER TABLE `tabel_transaksi`
  ADD CONSTRAINT `fk_transaksi_donasi` FOREIGN KEY (`id_donasi`) REFERENCES `tabel_donasi` (`id_donasi`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `tabel_detail_transaksi`
  ADD CONSTRAINT `fk_detail_transaksi` FOREIGN KEY (`kode_transaksi`) REFERENCES `tabel_transaksi` (`kode_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_produk` FOREIGN KEY (`id_produk`) REFERENCES `tabel_produk` (`id_produk`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_kasir` FOREIGN KEY (`id_kasir`) REFERENCES `tabel_kasir` (`id_kasir`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- --------------------------------------------------------
-- Data Sampel (Dummy Data) untuk Pengujian
-- --------------------------------------------------------

-- Data Kasir
INSERT INTO `tabel_kasir` (`id_kasir`, `nama_kasir`, `no_telp`, `alamat`) VALUES
(1, 'Siti Aminah', '081234567890', 'Jl. Merdeka No. 12, Jakarta'),
(2, 'Budi Santoso', '085712345678', 'Jl. Sudirman No. 45, Bandung'),
(3, 'Rina Wulandari', '087890123456', 'Jl. Pahlawan No. 88, Surabaya');

-- Data Produk Indomaret (dengan Promo Diskon)
INSERT INTO `tabel_produk` (`id_produk`, `nama_produk`, `harga`, `diskon`) VALUES
(1, 'Indomie Goreng Spesial 85g', 3500, 500),
(2, 'Pocari Sweat Botol 500ml', 8000, 1000),
(3, 'Ultra Milk UHT Cokelat 250ml', 6500, 500),
(4, 'Chitato Sapi Panggang 68g', 11500, 1500),
(5, 'Aqua Air Mineral 600ml', 3500, 0),
(6, 'Teh Botol Sosro Kotak 250ml', 4000, 500),
(7, 'Silverqueen Cashew 58g', 16500, 2500),
(8, 'Minyak Goreng Bimoli Pouch 2L', 38000, 3000);

-- Data Program Donasi Indomaret
INSERT INTO `tabel_donasi` (`id_donasi`, `nama_donasi`, `harga_donasi`) VALUES
(1, 'Peduli Anak Yatim & Dhuafa', 500),
(2, 'PMI Peduli Bencana Alam', 1000),
(3, 'Beasiswa Pendidikan Generasi Bangsa', 2000);

-- Data Transaksi Contoh
INSERT INTO `tabel_transaksi` (`kode_transaksi`, `tanggal_transaksi`, `no_order`, `total`, `QRIS`, `tunai`, `kembalian`, `id_donasi`) VALUES
('TRX202611200001', '2026-11-20 08:30:00', 1001, 24000, 0, 50000, 26000, 1),
('TRX202611200002', '2026-11-20 09:15:00', 1002, 36000, 36000, 0, 0, 2);

-- Data Detail Transaksi Contoh
-- Transaksi 1: 2 Indomie (@3000 = 6000) + 1 Silverqueen (@14000 = 14000) + 1 Aqua (@3500 = 3500) + Donasi (500) = Total 24000
-- Transaksi 2: 1 Minyak Goreng (@35000 = 35000) + Donasi (1000) = Total 36000
INSERT INTO `tabel_detail_transaksi` (`kode_transaksi`, `id_produk`, `id_kasir`, `harga`, `kuantitas`, `diskon`, `harga_jual`) VALUES
('TRX202611200001', 1, 1, 3500, 2, 500, 6000),
('TRX202611200001', 7, 1, 16500, 1, 2500, 14000),
('TRX202611200001', 5, 1, 3500, 1, 0, 3500),
('TRX202611200002', 8, 2, 38000, 1, 3000, 35000);
