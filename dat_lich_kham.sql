-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 02, 2026 at 01:01 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dat_lich_kham`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_kiem_tra_chong_lan` (IN `p_manguoidung` INT UNSIGNED, IN `p_tgbatdau` DATETIME, IN `p_tgketthuc` DATETIME, IN `p_loai_tru` INT UNSIGNED)   BEGIN
    DECLARE v_bac_si INT UNSIGNED DEFAULT NULL;
    DECLARE v_so_lan_trung INT DEFAULT 0;

    -- Kiem tra bac si ton tai
    BEGIN
        DECLARE CONTINUE HANDLER FOR NOT FOUND
            SET v_bac_si = NULL;

        SELECT manguoidung
        INTO v_bac_si
        FROM BAC_SI
        WHERE manguoidung = p_manguoidung
        FOR UPDATE;
    END;

    IF v_bac_si IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Bác sĩ không tồn tại';
    END IF;

    -- Kiem tra lich lam viec bi chong lan
    SELECT COUNT(*)
    INTO v_so_lan_trung
    FROM LICH_LAM_VIEC
    WHERE manguoidung = p_manguoidung
      AND thoigianbatdau < p_tgketthuc
      AND thoigianketthuc > p_tgbatdau
      AND (
          p_loai_tru IS NULL
          OR malichlamviec <> p_loai_tru
      );

    IF v_so_lan_trung > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
                'Khung giờ làm việc bị chồng lấn với lịch đã có';
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `bac_si`
--

CREATE TABLE `bac_si` (
  `manguoidung` int(10) UNSIGNED NOT NULL,
  `trinhdo` varchar(100) DEFAULT NULL,
  `gioithieu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bac_si`
--

INSERT INTO `bac_si` (`manguoidung`, `trinhdo`, `gioithieu`) VALUES
(1, 'Thạc sĩ', NULL),
(2, 'Bác sĩ CKI', NULL),
(3, 'Tiến sĩ', NULL),
(4, 'Bác sĩ CKII', NULL),
(8, 'Lậu', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bac_si_chuyen_khoa`
--

CREATE TABLE `bac_si_chuyen_khoa` (
  `manguoidung` int(10) UNSIGNED NOT NULL,
  `machuyenkhoa` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bac_si_chuyen_khoa`
--

INSERT INTO `bac_si_chuyen_khoa` (`manguoidung`, `machuyenkhoa`) VALUES
(1, 1),
(1, 2),
(2, 3),
(3, 1),
(4, 4),
(4, 5),
(8, 2),
(8, 3);

-- --------------------------------------------------------

--
-- Table structure for table `benh_nhan`
--

CREATE TABLE `benh_nhan` (
  `manguoidung` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `benh_nhan`
--

INSERT INTO `benh_nhan` (`manguoidung`) VALUES
(7);

-- --------------------------------------------------------

--
-- Table structure for table `chuyen_khoa`
--

CREATE TABLE `chuyen_khoa` (
  `machuyenkhoa` int(10) UNSIGNED NOT NULL,
  `makhoa` int(10) UNSIGNED NOT NULL,
  `tenchuyenkhoa` varchar(100) NOT NULL,
  `motachuyenkhoa` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chuyen_khoa`
--

INSERT INTO `chuyen_khoa` (`machuyenkhoa`, `makhoa`, `tenchuyenkhoa`, `motachuyenkhoa`) VALUES
(1, 1, 'Tim mạch', NULL),
(2, 1, 'Tiêu hóa', NULL),
(3, 2, 'Nhi khoa', NULL),
(4, 3, 'Da liễu', NULL),
(5, 3, 'Tai Mũi Họng', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ho_so_benh`
--

CREATE TABLE `ho_so_benh` (
  `mahoso` int(10) UNSIGNED NOT NULL,
  `manguoidung` int(10) UNSIGNED NOT NULL,
  `tinhtrang` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `khoa`
--

CREATE TABLE `khoa` (
  `makhoa` int(10) UNSIGNED NOT NULL,
  `tenkhoa` varchar(100) NOT NULL,
  `motakhoa` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `khoa`
--

INSERT INTO `khoa` (`makhoa`, `tenkhoa`, `motakhoa`) VALUES
(1, 'Khoa Nội', NULL),
(2, 'Khoa Nhi', NULL),
(3, 'Khoa Khám chuyên khoa', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lich_kham`
--

CREATE TABLE `lich_kham` (
  `malichkham` int(10) UNSIGNED NOT NULL,
  `manguoidung` int(10) UNSIGNED NOT NULL,
  `malichlamviec` int(10) UNSIGNED NOT NULL,
  `lydo` varchar(255) DEFAULT NULL,
  `trangthai` enum('cho_xac_nhan','da_xac_nhan','da_kham','da_huy') NOT NULL DEFAULT 'cho_xac_nhan',
  `ngaytao` datetime NOT NULL DEFAULT current_timestamp(),
  `ngaycapnhat` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `lydohuy` varchar(255) DEFAULT NULL,
  `nguoihuy` int(10) UNSIGNED DEFAULT NULL,
  `malichdangdung` int(10) UNSIGNED GENERATED ALWAYS AS (case when `trangthai` in ('cho_xac_nhan','da_xac_nhan','da_kham') then `malichlamviec` else NULL end) STORED
) ;

-- --------------------------------------------------------

--
-- Table structure for table `lich_lam_viec`
--

CREATE TABLE `lich_lam_viec` (
  `malichlamviec` int(10) UNSIGNED NOT NULL,
  `manguoidung` int(10) UNSIGNED NOT NULL,
  `thoigianbatdau` datetime NOT NULL,
  `thoigianketthuc` datetime NOT NULL,
  `trangthai` enum('con_trong','ngung_nhan') NOT NULL DEFAULT 'con_trong'
) ;

--
-- Dumping data for table `lich_lam_viec`
--

INSERT INTO `lich_lam_viec` (`malichlamviec`, `manguoidung`, `thoigianbatdau`, `thoigianketthuc`, `trangthai`) VALUES
(1, 1, '2026-10-29 08:00:00', '2026-10-29 08:30:00', 'con_trong'),
(2, 1, '2026-10-29 08:30:00', '2026-10-29 09:00:00', 'con_trong'),
(3, 1, '2026-10-30 14:00:00', '2026-10-30 14:30:00', 'con_trong'),
(4, 2, '2026-10-29 08:00:00', '2026-10-29 08:30:00', 'con_trong'),
(5, 2, '2026-10-29 08:30:00', '2026-10-29 09:00:00', 'con_trong'),
(6, 2, '2026-10-30 14:00:00', '2026-10-30 14:30:00', 'con_trong'),
(7, 3, '2026-10-29 08:00:00', '2026-10-29 08:30:00', 'con_trong'),
(8, 3, '2026-10-29 08:30:00', '2026-10-29 09:00:00', 'con_trong'),
(9, 3, '2026-10-30 14:00:00', '2026-10-30 14:30:00', 'con_trong'),
(10, 4, '2026-10-29 08:00:00', '2026-10-29 08:30:00', 'con_trong'),
(11, 4, '2026-10-29 08:30:00', '2026-10-29 09:00:00', 'con_trong'),
(12, 4, '2026-10-30 14:00:00', '2026-10-30 14:30:00', 'con_trong');

--
-- Triggers `lich_lam_viec`
--
DELIMITER $$
CREATE TRIGGER `trg_lich_lam_viec_bi` BEFORE INSERT ON `lich_lam_viec` FOR EACH ROW BEGIN
    CALL sp_kiem_tra_chong_lan(
        NEW.manguoidung,
        NEW.thoigianbatdau,
        NEW.thoigianketthuc,
        NULL
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_lich_lam_viec_bu` BEFORE UPDATE ON `lich_lam_viec` FOR EACH ROW BEGIN
    IF NOT (
        NEW.manguoidung <=> OLD.manguoidung
        AND NEW.thoigianbatdau <=> OLD.thoigianbatdau
        AND NEW.thoigianketthuc <=> OLD.thoigianketthuc
    ) THEN
        CALL sp_kiem_tra_chong_lan(
            NEW.manguoidung,
            NEW.thoigianbatdau,
            NEW.thoigianketthuc,
            OLD.malichlamviec
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `manguoidung` int(10) UNSIGNED NOT NULL,
  `mavaitro` tinyint(3) UNSIGNED NOT NULL,
  `hoten` varchar(100) NOT NULL,
  `sdt` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `matkhau` varchar(255) NOT NULL,
  `ngaysinh` date DEFAULT NULL,
  `gioitinh` enum('nam','nu') DEFAULT NULL,
  `diachi` varchar(255) DEFAULT NULL,
  `ngaytao` datetime NOT NULL DEFAULT current_timestamp(),
  `trangthai` enum('hoat_dong','bi_khoa') NOT NULL DEFAULT 'hoat_dong',
  `cccd` varchar(20) DEFAULT NULL,
  `dantoc` varchar(15) DEFAULT NULL,
  `bhyt` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`manguoidung`, `mavaitro`, `hoten`, `sdt`, `email`, `matkhau`, `ngaysinh`, `gioitinh`, `diachi`, `ngaytao`, `trangthai`, `cccd`, `dantoc`, `bhyt`) VALUES
(1, 2, 'BS. Nguyễn Văn An', '0900000001', NULL, 'chua_dat_mat_khau', NULL, NULL, NULL, '2026-10-28 19:35:42', 'hoat_dong', NULL, NULL, NULL),
(2, 2, 'BS. Trần Thị Bình', '0900000002', NULL, 'chua_dat_mat_khau', NULL, NULL, NULL, '2026-10-28 19:35:42', 'hoat_dong', NULL, NULL, NULL),
(3, 2, 'BS. Lê Minh Châu', '0900000003', NULL, 'chua_dat_mat_khau', NULL, NULL, NULL, '2026-10-28 19:35:42', 'hoat_dong', NULL, NULL, NULL),
(4, 2, 'BS. Phạm Quốc Dũng', '0900000004', NULL, 'chua_dat_mat_khau', NULL, NULL, NULL, '2026-10-28 19:35:42', 'hoat_dong', NULL, NULL, NULL),
(6, 3, 'Quản Trị Viên', '0123456789', 'admin@gmail.com', '$2y$10$W0eRIfqL3tWXsttYCSZOeOlJN.F0HriOWX6CemkjaslKHpjO66wku', '2026-10-08', NULL, NULL, '2026-10-31 12:42:56', 'hoat_dong', NULL, NULL, NULL),
(7, 1, 'benhnhan1', '0123451111', 'benhnhan1@gmail.com', '$2y$10$UUkhPuFZBdnt62AgiwPUcOFOWe7v9pQNjVXEMtjsJsuj96SuFKPnS', '2026-10-16', NULL, NULL, '2026-10-31 12:54:55', 'hoat_dong', NULL, NULL, NULL),
(8, 2, 'Hải Thượng Lãng Ông', '0922222222', NULL, '$2y$10$SXSaOJH6Ajuiot0ZgSiYyOKQ4OaXVl8Eb5GTSE0ntpiFotqMnW4Xm', NULL, NULL, NULL, '2026-10-31 13:02:44', 'hoat_dong', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vai_tro`
--

CREATE TABLE `vai_tro` (
  `mavaitro` tinyint(3) UNSIGNED NOT NULL,
  `tenvaitro` varchar(30) NOT NULL,
  `motavaitro` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vai_tro`
--

INSERT INTO `vai_tro` (`mavaitro`, `tenvaitro`, `motavaitro`) VALUES
(1, 'benh_nhan', 'Bệnh nhân'),
(2, 'bac_si', 'Bác sĩ'),
(3, 'quan_tri', 'Quản trị viên');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bac_si`
--
ALTER TABLE `bac_si`
  ADD PRIMARY KEY (`manguoidung`);

--
-- Indexes for table `bac_si_chuyen_khoa`
--
ALTER TABLE `bac_si_chuyen_khoa`
  ADD PRIMARY KEY (`manguoidung`,`machuyenkhoa`),
  ADD KEY `fk_bsck_chuyen_khoa` (`machuyenkhoa`);

--
-- Indexes for table `benh_nhan`
--
ALTER TABLE `benh_nhan`
  ADD PRIMARY KEY (`manguoidung`);

--
-- Indexes for table `chuyen_khoa`
--
ALTER TABLE `chuyen_khoa`
  ADD PRIMARY KEY (`machuyenkhoa`),
  ADD UNIQUE KEY `uq_chuyen_khoa_ten` (`makhoa`,`tenchuyenkhoa`);

--
-- Indexes for table `ho_so_benh`
--
ALTER TABLE `ho_so_benh`
  ADD PRIMARY KEY (`mahoso`),
  ADD KEY `fk_ho_so_benh_nhan` (`manguoidung`);

--
-- Indexes for table `khoa`
--
ALTER TABLE `khoa`
  ADD PRIMARY KEY (`makhoa`),
  ADD UNIQUE KEY `tenkhoa` (`tenkhoa`);

--
-- Indexes for table `lich_kham`
--
ALTER TABLE `lich_kham`
  ADD PRIMARY KEY (`malichkham`),
  ADD UNIQUE KEY `uq_lich_dang_dung` (`malichdangdung`),
  ADD KEY `fk_lich_kham_benh_nhan` (`manguoidung`),
  ADD KEY `fk_lich_kham_lich_lam_viec` (`malichlamviec`),
  ADD KEY `fk_lich_kham_nguoi_huy` (`nguoihuy`);

--
-- Indexes for table `lich_lam_viec`
--
ALTER TABLE `lich_lam_viec`
  ADD PRIMARY KEY (`malichlamviec`),
  ADD UNIQUE KEY `uq_bac_si_gio_bat_dau` (`manguoidung`,`thoigianbatdau`);

--
-- Indexes for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`manguoidung`),
  ADD UNIQUE KEY `sdt` (`sdt`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_nguoi_dung_vai_tro` (`mavaitro`);

--
-- Indexes for table `vai_tro`
--
ALTER TABLE `vai_tro`
  ADD PRIMARY KEY (`mavaitro`),
  ADD UNIQUE KEY `tenvaitro` (`tenvaitro`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chuyen_khoa`
--
ALTER TABLE `chuyen_khoa`
  MODIFY `machuyenkhoa` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ho_so_benh`
--
ALTER TABLE `ho_so_benh`
  MODIFY `mahoso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `khoa`
--
ALTER TABLE `khoa`
  MODIFY `makhoa` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lich_kham`
--
ALTER TABLE `lich_kham`
  MODIFY `malichkham` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lich_lam_viec`
--
ALTER TABLE `lich_lam_viec`
  MODIFY `malichlamviec` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `manguoidung` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `vai_tro`
--
ALTER TABLE `vai_tro`
  MODIFY `mavaitro` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bac_si`
--
ALTER TABLE `bac_si`
  ADD CONSTRAINT `fk_bac_si_nguoi_dung` FOREIGN KEY (`manguoidung`) REFERENCES `nguoi_dung` (`manguoidung`) ON UPDATE CASCADE;

--
-- Constraints for table `bac_si_chuyen_khoa`
--
ALTER TABLE `bac_si_chuyen_khoa`
  ADD CONSTRAINT `fk_bsck_bac_si` FOREIGN KEY (`manguoidung`) REFERENCES `bac_si` (`manguoidung`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bsck_chuyen_khoa` FOREIGN KEY (`machuyenkhoa`) REFERENCES `chuyen_khoa` (`machuyenkhoa`) ON UPDATE CASCADE;

--
-- Constraints for table `benh_nhan`
--
ALTER TABLE `benh_nhan`
  ADD CONSTRAINT `fk_benh_nhan_nguoi_dung` FOREIGN KEY (`manguoidung`) REFERENCES `nguoi_dung` (`manguoidung`) ON UPDATE CASCADE;

--
-- Constraints for table `chuyen_khoa`
--
ALTER TABLE `chuyen_khoa`
  ADD CONSTRAINT `fk_chuyen_khoa_khoa` FOREIGN KEY (`makhoa`) REFERENCES `khoa` (`makhoa`) ON UPDATE CASCADE;

--
-- Constraints for table `ho_so_benh`
--
ALTER TABLE `ho_so_benh`
  ADD CONSTRAINT `fk_ho_so_benh_nhan` FOREIGN KEY (`manguoidung`) REFERENCES `benh_nhan` (`manguoidung`) ON UPDATE CASCADE;

--
-- Constraints for table `lich_kham`
--
ALTER TABLE `lich_kham`
  ADD CONSTRAINT `fk_lich_kham_benh_nhan` FOREIGN KEY (`manguoidung`) REFERENCES `benh_nhan` (`manguoidung`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lich_kham_lich_lam_viec` FOREIGN KEY (`malichlamviec`) REFERENCES `lich_lam_viec` (`malichlamviec`),
  ADD CONSTRAINT `fk_lich_kham_nguoi_huy` FOREIGN KEY (`nguoihuy`) REFERENCES `nguoi_dung` (`manguoidung`);

--
-- Constraints for table `lich_lam_viec`
--
ALTER TABLE `lich_lam_viec`
  ADD CONSTRAINT `fk_lich_lam_viec_bac_si` FOREIGN KEY (`manguoidung`) REFERENCES `bac_si` (`manguoidung`) ON UPDATE CASCADE;

--
-- Constraints for table `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD CONSTRAINT `fk_nguoi_dung_vai_tro` FOREIGN KEY (`mavaitro`) REFERENCES `vai_tro` (`mavaitro`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
