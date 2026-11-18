-- ---------------------------------------------------------------------
-- He thong dat lich kham — schema chuan
-- Goc: lleduy/database/DATLICHKHAM_FINAL.sql
-- Thay doi so voi ban goc:
--   1. LICH_LAM_VIEC.trangthai: ('con_trong','ngung_nhan') -> ('con_trong','da_dat')
--   2. NGUOI_DUNG co them cot anhdaidien
--   3. Them trigger dong bo LICH_LAM_VIEC.trangthai tu LICH_KHAM
--      (ban goc chi nhac ten trigger trong comment, khong tao)
--
-- Chay: mysql -u root < database/schema.sql
-- ---------------------------------------------------------------------

DROP DATABASE IF EXISTS dat_lich_kham;
CREATE DATABASE IF NOT EXISTS dat_lich_kham
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE dat_lich_kham;
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- 1. Vai tro: benh_nhan, bac_si, quan_tri
-- ---------------------------------------------------------------------
CREATE TABLE VAI_TRO (
    mavaitro TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenvaitro VARCHAR(30) NOT NULL UNIQUE,
    motavaitro VARCHAR(50) NULL
) ENGINE=InnoDB;

INSERT INTO VAI_TRO (mavaitro, tenvaitro, motavaitro) VALUES
    (1, 'benh_nhan', 'Bệnh nhân'),
    (2, 'bac_si', 'Bác sĩ'),
    (3, 'quan_tri', 'Quản trị viên');

-- ---------------------------------------------------------------------
-- 2. Nguoi dung (matkhau luu chuoi bam, khong luu mat khau thuan)
--    cccd, bhyt, email: luu NULL thay vi chuoi rong '' (cot UNIQUE)
--    anhdaidien: duong dan tuong doi trong assets/uploads/avatar/
-- ---------------------------------------------------------------------
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
    cccd VARCHAR(20) NULL UNIQUE,
    dantoc VARCHAR(15) NULL,
    bhyt VARCHAR(20) NULL UNIQUE,
    anhdaidien VARCHAR(255) NULL,
    CONSTRAINT fk_nguoi_dung_vai_tro
        FOREIGN KEY (mavaitro) REFERENCES VAI_TRO (mavaitro)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. Bac si
-- ---------------------------------------------------------------------
CREATE TABLE BAC_SI (
    manguoidung INT UNSIGNED PRIMARY KEY,
    trinhdo VARCHAR(100) NULL,
    gioithieu TEXT NULL,
    CONSTRAINT fk_bac_si_nguoi_dung
        FOREIGN KEY (manguoidung) REFERENCES NGUOI_DUNG (manguoidung)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. Khoa, chuyen khoa
-- ---------------------------------------------------------------------
CREATE TABLE KHOA (
    makhoa INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenkhoa VARCHAR(100) NOT NULL UNIQUE,
    motakhoa TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE CHUYEN_KHOA (
    machuyenkhoa INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    makhoa INT UNSIGNED NOT NULL,
    tenchuyenkhoa VARCHAR(100) NOT NULL,
    motachuyenkhoa TEXT NULL,
    CONSTRAINT uq_chuyen_khoa_ten UNIQUE (makhoa, tenchuyenkhoa),
    CONSTRAINT fk_chuyen_khoa_khoa
        FOREIGN KEY (makhoa) REFERENCES KHOA (makhoa)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Mot bac si thuoc nhieu chuyen khoa, mot chuyen khoa co nhieu bac si
CREATE TABLE BAC_SI_CHUYEN_KHOA (
    manguoidung INT UNSIGNED NOT NULL,
    machuyenkhoa INT UNSIGNED NOT NULL,
    PRIMARY KEY (manguoidung, machuyenkhoa),
    CONSTRAINT fk_bsck_bac_si
        FOREIGN KEY (manguoidung) REFERENCES BAC_SI (manguoidung)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_bsck_chuyen_khoa
        FOREIGN KEY (machuyenkhoa) REFERENCES CHUYEN_KHOA (machuyenkhoa)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. Lich lam viec
--    trangthai: 'con_trong' = chua co lich kham hieu luc,
--               'da_dat'    = da co lich kham chua huy.
--    Cot nay do trigger trg_llv_dong_bo ghi; code ung dung khong UPDATE tay.
-- ---------------------------------------------------------------------
CREATE TABLE LICH_LAM_VIEC (
    malichlamviec INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    manguoidung INT UNSIGNED NOT NULL,
    thoigianbatdau DATETIME NOT NULL,
    thoigianketthuc DATETIME NOT NULL,
    trangthai ENUM('con_trong', 'da_dat') NOT NULL DEFAULT 'con_trong',

    CONSTRAINT chk_lich_lam_viec_thoi_gian
        CHECK (thoigianketthuc > thoigianbatdau),
    CONSTRAINT uq_bac_si_gio_bat_dau
        UNIQUE (manguoidung, thoigianbatdau),
    CONSTRAINT fk_lich_lam_viec_bac_si
        FOREIGN KEY (manguoidung) REFERENCES BAC_SI (manguoidung)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    INDEX idx_llv_bat_dau (thoigianbatdau)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. Lich kham
--    malichdangdung (cot sinh): = malichlamviec khi lich con hieu luc
--    (cho_xac_nhan, da_xac_nhan, da_kham); NULL khi da_huy.
--    UNIQUE(malichdangdung) => moi khung gio co toi da mot lich chua huy.
--
--    Quy tac trang thai (trigger kiem_tra_chuyen_trang_thai bao ve):
--      cho_xac_nhan -> da_xac_nhan | da_huy
--      da_xac_nhan  -> da_kham     | da_huy
--      da_kham, da_huy: ket thuc
--    manguoidung va malichlamviec khong duoc sua; muon doi khung gio thi
--    huy lich cu va tao lich moi.
-- ---------------------------------------------------------------------
CREATE TABLE LICH_KHAM (
    malichkham INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    manguoidung INT UNSIGNED NOT NULL,          -- benh nhan dat lich
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
        FOREIGN KEY (manguoidung) REFERENCES NGUOI_DUNG (manguoidung)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT fk_lich_kham_lich_lam_viec
        FOREIGN KEY (malichlamviec) REFERENCES LICH_LAM_VIEC (malichlamviec)
        ON UPDATE RESTRICT ON DELETE RESTRICT,

    CONSTRAINT fk_lich_kham_nguoi_huy
        FOREIGN KEY (nguoihuy) REFERENCES NGUOI_DUNG (manguoidung)
        ON UPDATE RESTRICT ON DELETE RESTRICT,

    -- da_huy: bat buoc biet ai huy; chua huy: khong duoc co thong tin huy
    CONSTRAINT chk_lich_kham_huy CHECK (
        (trangthai = 'da_huy' AND nguoihuy IS NOT NULL)
        OR
        (trangthai <> 'da_huy' AND lydohuy IS NULL AND nguoihuy IS NULL)
    ),

    INDEX idx_lich_kham_benh_nhan (manguoidung),
    INDEX idx_lich_kham_trang_thai (trangthai)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. Ho so benh nhan (benh nhan lay qua JOIN LICH_KHAM)
--    ngaylap: thoi diem nhap ho so; gio kham that nam o LICH_LAM_VIEC.
-- ---------------------------------------------------------------------
CREATE TABLE HO_SO_BENH_NHAN (
    mahoso INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    malichkham INT UNSIGNED NOT NULL UNIQUE,
    ngaylap DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    chuandoan TEXT NULL,
    loidan TEXT NULL,
    CONSTRAINT fk_ho_so_lich_kham
        FOREIGN KEY (malichkham) REFERENCES LICH_KHAM (malichkham)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. Trigger
-- ---------------------------------------------------------------------

DELIMITER $$

-- 8.1 Dong bo LICH_LAM_VIEC.trangthai tu LICH_KHAM.
--     Day la duong ghi DUY NHAT cua cot trangthai: dat lich, huy lich,
--     xoa lich (ke ca thao tac tay trong phpMyAdmin) deu di qua day.
--
--     Ba trigger duoi day chi doc OLD/NEW, khong SELECT lai LICH_KHAM.
--     Ly do: UNIQUE(malichdangdung) bao dam moi khung gio co toi da mot
--     lich chua huy, nen chi can biet dong vua doi co con hieu luc hay khong
--     la du ket luan ve ca khung gio.
DROP TRIGGER IF EXISTS trg_llv_dong_bo_ins $$
CREATE TRIGGER trg_llv_dong_bo_ins
AFTER INSERT ON LICH_KHAM
FOR EACH ROW
BEGIN
    UPDATE LICH_LAM_VIEC
    SET trangthai = IF(NEW.trangthai <> 'da_huy', 'da_dat', 'con_trong')
    WHERE malichlamviec = NEW.malichlamviec;
END $$

DROP TRIGGER IF EXISTS trg_llv_dong_bo_upd $$
CREATE TRIGGER trg_llv_dong_bo_upd
AFTER UPDATE ON LICH_KHAM
FOR EACH ROW
BEGIN
    -- Chi phan ung khi trang thai thuc su doi. Khung gio khong doi duoc
    -- (trg_khoa_truong_lich_kham da chan).
    IF NEW.trangthai <> OLD.trangthai THEN
        UPDATE LICH_LAM_VIEC
        SET trangthai = IF(NEW.trangthai <> 'da_huy', 'da_dat', 'con_trong')
        WHERE malichlamviec = NEW.malichlamviec;
    END IF;
END $$

DROP TRIGGER IF EXISTS trg_llv_dong_bo_del $$
CREATE TRIGGER trg_llv_dong_bo_del
AFTER DELETE ON LICH_KHAM
FOR EACH ROW
BEGIN
    -- Xoa mot lich da huy khong doi gi. Xoa lich con hieu luc thi khung gio
    -- tro lai con trong. Khung gio co the da bi xoa truoc do, khi do cau
    -- UPDATE khong anh huong dong nao.
    IF OLD.trangthai <> 'da_huy' THEN
        UPDATE LICH_LAM_VIEC
        SET trangthai = 'con_trong'
        WHERE malichlamviec = OLD.malichlamviec;
    END IF;
END $$

-- 8.2 Chan chuyen trang thai sai quy tac va chan sua slot cua lich kham.
DROP TRIGGER IF EXISTS trg_khoa_truong_lich_kham $$
CREATE TRIGGER trg_khoa_truong_lich_kham
BEFORE UPDATE ON LICH_KHAM
FOR EACH ROW
BEGIN
    IF NEW.manguoidung <> OLD.manguoidung
       OR NEW.malichlamviec <> OLD.malichlamviec THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Khong duoc doi benh nhan hoac khung gio cua lich kham.';
    END IF;

    IF NOT (
        (OLD.trangthai = 'cho_xac_nhan' AND NEW.trangthai IN ('da_xac_nhan', 'da_huy'))
        OR (OLD.trangthai = 'da_xac_nhan' AND NEW.trangthai IN ('da_kham', 'da_huy'))
        OR (OLD.trangthai = NEW.trangthai)
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Chuyen trang thai lich kham khong hop le.';
    END IF;
END $$

DELIMITER ;
