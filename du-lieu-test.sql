-- =====================================================================
-- DU LIEU MAU DE TEST - do an Dat lich kham
-- Chay SAU KHI da import DATLICHKHAM_FINAL.sql (CSDL dat_lich_kham).
-- Chi chay MOT LAN. Chay lai se bao trung du lieu; muon chay lai tu dau
-- thi import lai DATLICHKHAM_FINAL.sql truoc (file do co DROP DATABASE).
-- =====================================================================

USE dat_lich_kham;
SET NAMES utf8mb4;

-- 1. Khoa
INSERT INTO KHOA (tenkhoa) VALUES
('Khoa Nội'),
('Khoa Nhi'),
('Khoa Khám chuyên khoa');

-- 2. Chuyen khoa (makhoa: 1 = Noi, 2 = Nhi, 3 = Kham chuyen khoa)
INSERT INTO CHUYEN_KHOA (makhoa, tenchuyenkhoa) VALUES
(1, 'Tim mạch'),       -- machuyenkhoa = 1
(1, 'Tiêu hóa'),       -- machuyenkhoa = 2
(2, 'Nhi khoa'),       -- machuyenkhoa = 3
(3, 'Da liễu'),        -- machuyenkhoa = 4
(3, 'Tai Mũi Họng');   -- machuyenkhoa = 5

-- 3. Bac si mau: mavaitro = 2 (bac_si)
-- Mat khau gia nen cac tai khoan nay CHUA dang nhap duoc (chi de hien ra tren web)
INSERT INTO NGUOI_DUNG (manguoidung, mavaitro, hoten, sdt, matkhau) VALUES
(1, 2, 'BS. Nguyễn Văn An',  '0900000001', 'chua_dat_mat_khau'),
(2, 2, 'BS. Trần Thị Bình',  '0900000002', 'chua_dat_mat_khau'),
(3, 2, 'BS. Lê Minh Châu',   '0900000003', 'chua_dat_mat_khau'),
(4, 2, 'BS. Phạm Quốc Dũng', '0900000004', 'chua_dat_mat_khau');

INSERT INTO BAC_SI (manguoidung, trinhdo) VALUES
(1, 'Thạc sĩ'),
(2, 'Bác sĩ CKI'),
(3, 'Tiến sĩ'),
(4, 'Bác sĩ CKII');

-- 4. Bac si - chuyen khoa (BS An co 2 chuyen khoa, BS Dung co 2 chuyen khoa)
INSERT INTO BAC_SI_CHUYEN_KHOA (manguoidung, machuyenkhoa) VALUES
(1, 1), (1, 2),
(2, 3),
(3, 1),
(4, 4), (4, 5);

-- 5. Khung gio lam viec: moi bac si 3 khung (2 khung ngay mai, 1 khung ngay kia)
-- LUU Y: khong viet "INSERT INTO LICH_LAM_VIEC ... SELECT ... FROM BAC_SI",
-- vi trigger cua LICH_LAM_VIEC co khoa bang BAC_SI (loi #1442).
-- Cac so 1-4 trong bang d la manguoidung cua 4 bac si o tren.
INSERT INTO LICH_LAM_VIEC (manguoidung, thoigianbatdau, thoigianketthuc)
SELECT d.id,
       CONCAT(CURDATE() + INTERVAL g.ngay DAY, ' ', g.batdau),
       CONCAT(CURDATE() + INTERVAL g.ngay DAY, ' ', g.ketthuc)
FROM (
    SELECT 1 AS id
    UNION ALL SELECT 2
    UNION ALL SELECT 3
    UNION ALL SELECT 4
) d
CROSS JOIN (
    SELECT 1 AS ngay, '08:00:00' AS batdau, '08:30:00' AS ketthuc
    UNION ALL SELECT 1, '08:30:00', '09:00:00'
    UNION ALL SELECT 2, '14:00:00', '14:30:00'
) g;

-- =====================================================================
-- CAU LENH KIEM TRA (chay rieng tung dong khi can)
-- =====================================================================
-- SELECT COUNT(*) FROM LICH_LAM_VIEC;   -- ket qua dung: 12
-- SELECT * FROM NGUOI_DUNG;             -- 4 bac si (benh nhan dang ky tu trang web)
-- SELECT * FROM BAC_SI_CHUYEN_KHOA;     -- 6 dong
-- SELECT * FROM LICH_KHAM;              -- rong cho den khi dat lich tren web