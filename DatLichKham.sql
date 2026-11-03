DROP DATABASE IF EXISTS dat_lich_kham;

CREATE DATABASE IF NOT EXISTS dat_lich_kham
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE dat_lich_kham;
SET NAMES utf8mb4;

-- Vai tro nguoi dung: benh_nhan, bac_si, quan_tri
CREATE TABLE VAI_TRO (
    mavaitro TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenvaitro VARCHAR(30) NOT NULL UNIQUE,
    motavaitro VARCHAR(50) NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO VAI_TRO (mavaitro, tenvaitro, motavaitro) VALUES
    (1, 'benh_nhan', 'Bệnh nhân'),
    (2, 'bac_si', 'Bác sĩ'),
    (3, 'quan_tri', 'Quản trị viên');


-- Nguoi dung: matkhau can luu chuoi bam (hash), khong luu mat khau thuan.
CREATE TABLE NGUOI_DUNG (
    manguoidung INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mavaitro TINYINT UNSIGNED NOT NULL,
    hoten VARCHAR(100) NOT NULL,
    sdt VARCHAR(15) NOT NULL UNIQUE,
    email VARCHAR(100) NULL UNIQUE,
    matkhau VARCHAR(255) NOT NULL,
    ngaysinh DATE NULL,
    gioitinh ENUM('nam', 'nu') NULL DEFAULT NULL,
    diachi VARCHAR(255) NULL,
    ngaytao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    trangthai ENUM('hoat_dong', 'bi_khoa') NOT NULL DEFAULT 'hoat_dong',

    CONSTRAINT fk_nguoi_dung_vai_tro
        FOREIGN KEY (mavaitro)
        REFERENCES VAI_TRO (mavaitro)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- benh nhan
CREATE TABLE BENH_NHAN (
    manguoidung INT UNSIGNED PRIMARY KEY,

    CONSTRAINT fk_benh_nhan_nguoi_dung
        FOREIGN KEY (manguoidung)
        REFERENCES NGUOI_DUNG (manguoidung)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ho so benh nhan
CREATE TABLE HO_SO_BENH (
    mahoso INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    manguoidung INT UNSIGNED NOT NULL,
    tinhtrang TEXT NULL,

    CONSTRAINT fk_ho_so_benh_nhan
        FOREIGN KEY (manguoidung)
        REFERENCES BENH_NHAN (manguoidung)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;
-- bac si
CREATE TABLE BAC_SI (
    manguoidung INT UNSIGNED PRIMARY KEY,
    trinhdo VARCHAR(100) NULL,
    gioithieu TEXT NULL,

    CONSTRAINT fk_bac_si_nguoi_dung
        FOREIGN KEY (manguoidung)
        REFERENCES NGUOI_DUNG (manguoidung)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- khoa
CREATE TABLE KHOA (
    makhoa INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenkhoa VARCHAR(100) NOT NULL UNIQUE,
    motakhoa TEXT NULL
) ENGINE=InnoDB;


-- chuyen khoa
CREATE TABLE CHUYEN_KHOA (
    machuyenkhoa INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    makhoa INT UNSIGNED NOT NULL,
    tenchuyenkhoa VARCHAR(100) NOT NULL,
    motachuyenkhoa TEXT NULL,

    CONSTRAINT uq_chuyen_khoa_ten UNIQUE (makhoa, tenchuyenkhoa),

    CONSTRAINT fk_chuyen_khoa_khoa
        FOREIGN KEY (makhoa)
        REFERENCES KHOA (makhoa)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- bac si chuyen khoa, mot bac si co the thuoc nhieu chuyen khoa, mot chuyen khoa co nhieu bac si
CREATE TABLE BAC_SI_CHUYEN_KHOA (
    manguoidung INT UNSIGNED NOT NULL,
    machuyenkhoa INT UNSIGNED NOT NULL,

    PRIMARY KEY (manguoidung, machuyenkhoa),

    CONSTRAINT fk_bsck_bac_si
        FOREIGN KEY (manguoidung)
        REFERENCES BAC_SI (manguoidung)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_bsck_chuyen_khoa
        FOREIGN KEY (machuyenkhoa)
        REFERENCES CHUYEN_KHOA (machuyenkhoa)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- 8 lich lam viec, khong co hai lich lam viec cua cung mot bac si bi trung gio
CREATE TABLE LICH_LAM_VIEC (
    malichlamviec INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    manguoidung INT UNSIGNED NOT NULL,
    thoigianbatdau DATETIME NOT NULL,
    thoigianketthuc DATETIME NOT NULL,
    trangthai ENUM('con_trong', 'ngung_nhan') NOT NULL DEFAULT 'con_trong',

    CONSTRAINT chk_lich_lam_viec_thoi_gian
        CHECK (thoigianketthuc > thoigianbatdau),

    CONSTRAINT uq_bac_si_gio_bat_dau
        UNIQUE (manguoidung, thoigianbatdau),

    CONSTRAINT fk_lich_lam_viec_bac_si
        FOREIGN KEY (manguoidung)
        REFERENCES BAC_SI (manguoidung)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- 9 lich kham, mot lich lam viec co the co nhieu lich kham, mot lich kham thuoc mot lich lam viec
-- malichdangdung la cot sinh:
--   - slot duoc giu khi cho_xac_nhan, da_xac_nhan hoac da_kham;
--   - slot duoc giai phong khi da_huy (gia tri cot sinh la NULL).
-- UNIQUE(malichdangdung) giup moi slot co toi da mot lich chua bi huy.

CREATE TABLE LICH_KHAM (
    malichkham INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    manguoidung INT UNSIGNED NOT NULL,
    malichlamviec INT UNSIGNED NOT NULL,
    lydo VARCHAR(255) NULL,
    trangthai ENUM('cho_xac_nhan', 'da_xac_nhan', 'da_kham', 'da_huy')
        NOT NULL DEFAULT 'cho_xac_nhan',
    ngaytao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ngaycapnhat DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    lydohuy VARCHAR(255) NULL,
    nguoihuy INT UNSIGNED NULL,
    malichdangdung INT UNSIGNED
        GENERATED ALWAYS AS (
            CASE
                WHEN trangthai IN ('cho_xac_nhan', 'da_xac_nhan', 'da_kham')
                THEN malichlamviec
                ELSE NULL
            END
        ) STORED,

    CONSTRAINT uq_lich_dang_dung UNIQUE (malichdangdung),

    CONSTRAINT fk_lich_kham_benh_nhan
        FOREIGN KEY (manguoidung)
        REFERENCES BENH_NHAN (manguoidung)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_lich_kham_lich_lam_viec
        FOREIGN KEY (malichlamviec)
        REFERENCES LICH_LAM_VIEC (malichlamviec)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT fk_lich_kham_nguoi_huy
        FOREIGN KEY (nguoihuy)
        REFERENCES NGUOI_DUNG (manguoidung)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT chk_lich_kham_ly_do_huy
        CHECK (trangthai <> 'da_huy' OR lydohuy IS NOT NULL OR nguoihuy IS NOT NULL)
) ENGINE=InnoDB;


-- 10 TRIGGER CHONG CHONG LAN KHUNG GIO LAM VIEC
-- Hai khung gio cua CUNG MOT bac si bi coi la chong lan khi:
--     cu.thoigianbatdau < moi.thoigianketthuc
--     AND cu.thoigianketthuc > moi.thoigianbatdau
-- Hai khung noi tiep nhau (08:00-08:30 va 08:30-09:00) KHONG bi chong lan.
--
-- Logic nam trong thu tuc sp_kiem_tra_chong_lan, duoc goi tu trigger
-- BEFORE INSERT va BEFORE UPDATE tren LICH_LAM_VIEC. Khi trung, SIGNAL
-- SQLSTATE '45000' (MySQL errno 1644) va cau lenh bi huy.

DROP TRIGGER IF EXISTS trg_lich_lam_viec_bi;
DROP TRIGGER IF EXISTS trg_lich_lam_viec_bu;
DROP PROCEDURE IF EXISTS sp_kiem_tra_chong_lan;

DELIMITER $$

CREATE PROCEDURE sp_kiem_tra_chong_lan (
    IN p_manguoidung INT UNSIGNED,
    IN p_tgbatdau DATETIME,
    IN p_tgketthuc DATETIME,
    IN p_loai_tru INT UNSIGNED
)
BEGIN
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


CREATE TRIGGER trg_lich_lam_viec_bi
BEFORE INSERT ON LICH_LAM_VIEC
FOR EACH ROW
BEGIN
    CALL sp_kiem_tra_chong_lan(
        NEW.manguoidung,
        NEW.thoigianbatdau,
        NEW.thoigianketthuc,
        NULL
    );
END$$


CREATE TRIGGER trg_lich_lam_viec_bu
BEFORE UPDATE ON LICH_LAM_VIEC
FOR EACH ROW
BEGIN
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
END$$

DELIMITER ;


-- LUU Y NGHIEP VU:
-- 1. Khi tao BENH_NHAN/BAC_SI, hay tao NGUOI_DUNG truoc, sau do dung
--    cung manguoidung de tao ho so tuong ung.
-- 2. Ung dung PHP can dam bao tai khoan co vai tro 'benh_nhan' moi tao
--    duoc BENH_NHAN va vai tro 'bac_si' moi tao duoc BAC_SI.
-- 3. Khi dat lich, PHP can kiem tra slot con trong, nam trong tuong lai
--    va khong bi chong lan; giao dich (transaction) giup tranh dat trung.
-- 4. Chi cho phep huy lich theo quy tac nghiep vu; khi huy can cap nhat
--    trangthai='da_huy', ghi lydohuy/nguoihuy neu can.