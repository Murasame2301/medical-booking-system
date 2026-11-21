USE dat_lich_kham;
SET NAMES utf8mb4;

DELETE FROM HO_SO_BENH_NHAN;
DELETE FROM LICH_KHAM;
DELETE FROM LICH_LAM_VIEC;
DELETE FROM BAC_SI_CHUYEN_KHOA;
DELETE FROM BAC_SI;
DELETE FROM NGUOI_DUNG;

ALTER TABLE NGUOI_DUNG AUTO_INCREMENT = 1;
ALTER TABLE LICH_LAM_VIEC AUTO_INCREMENT = 1;
ALTER TABLE LICH_KHAM AUTO_INCREMENT = 1;
ALTER TABLE HO_SO_BENH_NHAN AUTO_INCREMENT = 1;

INSERT IGNORE INTO KHOA (makhoa, tenkhoa, motakhoa) VALUES
    (1, 'Nội khoa', 'Chẩn đoán và điều trị bệnh lý nội khoa'),
    (2, 'Ngoại khoa', 'Phẫu thuật và chấn thương chỉnh hình'),
    (3, 'Nhi khoa', 'Khám và điều trị cho trẻ em'),
    (4, 'Sản phụ khoa', 'Chăm sóc thai sản và sức khoẻ phụ nữ'),
    (5, 'Tai Mũi Họng', 'Khám và điều trị bệnh tai mũi họng'),
    (6, 'Da liễu', 'Khám và điều trị bệnh về da');

INSERT IGNORE INTO CHUYEN_KHOA (machuyenkhoa, makhoa, tenchuyenkhoa, motachuyenkhoa) VALUES
    (1, 1, 'Tim mạch', 'Tăng huyết áp, bệnh mạch vành, rối loạn nhịp'),
    (2, 1, 'Tiêu hoá', 'Dạ dày, gan mật, đại tràng'),
    (3, 2, 'Chấn thương chỉnh hình', 'Gãy xương, thoái hoá khớp'),
    (4, 3, 'Nhi tổng quát', 'Khám sức khoẻ và bệnh thường gặp ở trẻ'),
    (5, 4, 'Sản khoa', 'Theo dõi thai kỳ và khám phụ khoa'),
    (6, 5, 'Tai Mũi Họng', 'Viêm xoang, viêm họng, ù tai'),
    (7, 6, 'Da liễu', 'Viêm da, nấm da, dị ứng da');

INSERT INTO NGUOI_DUNG
    (manguoidung, mavaitro, hoten, sdt, email, matkhau, ngaysinh, gioitinh,
     diachi, trangthai, cccd, dantoc, bhyt)
VALUES
    (1, 3, 'Nguyễn Thị Quản Trị', '0900000001', 'quantri@phongkham.vn',
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1985-03-12', 'nu', '12 Nguyễn Trãi, Quận 1, TP. Hồ Chí Minh',
     'hoat_dong', NULL, 'Kinh', NULL),

    (2, 2, 'Nguyễn Văn An', '0900000002', 'bs.an@phongkham.vn',
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1978-05-20', 'nam', '45 Lê Lợi, Quận Hải Châu, Đà Nẵng',
     'hoat_dong', NULL, 'Kinh', NULL),
    (3, 2, 'Trần Thị Bình', '0900000003', 'bs.binh@phongkham.vn',
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1982-09-02', 'nu', '18 Trần Phú, TP. Huế, Thừa Thiên Huế',
     'hoat_dong', NULL, 'Kinh', NULL),
    (4, 2, 'Lê Minh Cường', '0900000004', 'bs.cuong@phongkham.vn',
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1975-11-30', 'nam', '90 Nguyễn Huệ, Quận Ninh Kiều, Cần Thơ',
     'hoat_dong', NULL, 'Kinh', NULL),
    (5, 2, 'Phạm Thu Dung', '0900000005', 'bs.dung@phongkham.vn',
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1988-01-14', 'nu', '7 Hai Bà Trưng, Quận 3, TP. Hồ Chí Minh',
     'hoat_dong', NULL, 'Kinh', NULL),
    (6, 2, 'Hoàng Thị Em', '0900000006', 'bs.em@phongkham.vn',
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1980-07-08', 'nu', '22 Lý Thường Kiệt, TP. Nha Trang, Khánh Hòa',
     'hoat_dong', NULL, 'Kinh', NULL),
    (7, 2, 'Vũ Quốc Giang', '0900000007', 'bs.giang@phongkham.vn',
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1972-04-25', 'nam', '31 Điện Biên Phủ, Quận Bình Thạnh, TP. Hồ Chí Minh',
     'hoat_dong', NULL, 'Kinh', NULL),
    (8, 2, 'Đỗ Thị Hạnh', '0900000008', 'bs.hanh@phongkham.vn',
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1986-12-05', 'nu', '64 Trần Hưng Đạo, Quận 5, TP. Hồ Chí Minh',
     'hoat_dong', NULL, 'Kinh', NULL),

    (101, 1, 'Trần Gia Cường', '0910000001', 'benhnhan1@example.com',
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1990-02-17', 'nam', '215 Lê Lợi, Quận Ninh Kiều, Cần Thơ',
     'hoat_dong', '040007137352', 'Kinh', 'CT4010001234'),
    (102, 1, 'Hoàng Mai Chi', '0910000002', NULL,
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1994-06-01', 'nu', '281 Cách Mạng Tháng 8, Quận Hải Châu, Đà Nẵng',
     'hoat_dong', '040172728351', 'Kinh', 'DN4020005678'),
    (103, 1, 'Hồ Bảo Lan', '0910000003', NULL,
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1997-09-15', 'nu', '185 Cách Mạng Tháng 8, TP. Nha Trang, Khánh Hòa',
     'hoat_dong', '048030031106', 'Kinh', 'KH4030009012'),
    (104, 1, 'Võ Thanh Tài', '0910000004', NULL,
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1963-04-01', 'nam', '59 Điện Biên Phủ, TP. Huế, Thừa Thiên Huế',
     'hoat_dong', '040179082759', 'Nùng', 'TTH4040003456'),
    (105, 1, 'Hồ Thanh Nhung', '0910000005', NULL,
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1976-03-18', 'nu', '231 Nguyễn Huệ, TP. Nha Trang, Khánh Hòa',
     'hoat_dong', '075048360831', 'Kinh', 'KH4050007890'),
    (106, 1, 'Dương Hoàng Tuấn', '0910000006', NULL,
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1974-01-19', 'nam', '162 Trần Hưng Đạo, Quận Ninh Kiều, Cần Thơ',
     'hoat_dong', '079175158724', 'Kinh', 'CT4060002345'),
    (107, 1, 'Bùi Khánh Vy', '0910000007', 'benhnhan7@example.com',
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '2001-08-22', 'nu', '73 Nguyễn Văn Linh, Quận Hải Châu, Đà Nẵng',
     'hoat_dong', '040199002233', 'Kinh', 'DN4070006789'),
    (108, 1, 'Ngô Đức Huy', '0910000008', NULL,
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1988-10-09', 'nam', '11 Phan Chu Trinh, Quận Hoàn Kiếm, Hà Nội',
     'hoat_dong', '001088014455', 'Kinh', 'HN4080001122'),
    (109, 1, 'Đặng Thị Kim Ngân', '0910000009', NULL,
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1992-05-04', 'nu', '5 Trường Chinh, Quận Thanh Xuân, Hà Nội',
     'hoat_dong', '001092023344', 'Kinh', 'HN4090003344'),
    (110, 1, 'Lý Văn Phúc', '0910000010', NULL,
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1969-07-27', 'nam', '102 Quang Trung, Quận Gò Vấp, TP. Hồ Chí Minh',
     'hoat_dong', '079169031122', 'Kinh', 'HCM4100005566'),
    (111, 1, 'Trịnh Thị Xuân', '0910000011', NULL,
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1999-11-11', 'nu', '88 Lê Duẩn, Quận 1, TP. Hồ Chí Minh',
     'hoat_dong', '079199045566', 'Kinh', 'HCM4110007788'),
    (112, 1, 'Phan Anh Dũng', '0910000012', NULL,
     '$2y$10$JlEH6DI/8G58M9889ed1SO9WFpF.wJF69OswzzhSgDK93.V2xcQKO',
     '1983-03-03', 'nam', '39 Nguyễn Tất Thành, Quận Liên Chiểu, Đà Nẵng',
     'hoat_dong', '048083067788', 'Kinh', 'DN4120009900');

UPDATE NGUOI_DUNG SET trangthai = 'bi_khoa' WHERE manguoidung = 110;

INSERT INTO BAC_SI (manguoidung, trinhdo, gioithieu) VALUES
    (2, 'Bác sĩ chuyên khoa II - Tim mạch',
     'Hơn 20 năm khám và điều trị tăng huyết áp, bệnh mạch vành.'),
    (3, 'Thạc sĩ - Tiêu hoá',
     'Chuyên nội soi dạ dày, điều trị viêm gan và bệnh đại tràng.'),
    (4, 'Bác sĩ chuyên khoa I - Chấn thương chỉnh hình',
     'Phẫu thuật gãy xương, thay khớp háng và khớp gối.'),
    (5, 'Bác sĩ chuyên khoa I - Nhi khoa',
     'Khám sức khoẻ định kỳ và điều trị bệnh hô hấp ở trẻ.'),
    (6, 'Bác sĩ chuyên khoa II - Sản phụ khoa',
     'Theo dõi thai kỳ, tầm soát tiền sản giật và khám phụ khoa.'),
    (7, 'Thạc sĩ - Tai Mũi Họng',
     'Điều trị viêm xoang mạn, viêm họng hạt và ù tai.'),
    (8, 'Bác sĩ chuyên khoa I - Da liễu',
     'Điều trị viêm da cơ địa, nấm da và dị ứng da.');

INSERT INTO BAC_SI_CHUYEN_KHOA (manguoidung, machuyenkhoa) VALUES
    (2, 1), (3, 2), (4, 3), (5, 4), (6, 5), (7, 6), (8, 7);

INSERT INTO LICH_LAM_VIEC (manguoidung, thoigianbatdau, thoigianketthuc)
SELECT bs.manguoidung,
       TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL d.ngay DAY), g.gio),
       TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL d.ngay DAY),
                 ADDTIME(CAST(g.gio AS TIME), '00:30:00'))
FROM BAC_SI bs
CROSS JOIN (
    SELECT -2 AS ngay UNION ALL
    SELECT -1 UNION ALL
    SELECT 1 UNION ALL
    SELECT 2 UNION ALL
    SELECT 3
) d
CROSS JOIN (
    SELECT '08:00:00' AS gio UNION ALL
    SELECT '08:30:00' UNION ALL
    SELECT '09:00:00' UNION ALL
    SELECT '14:00:00' UNION ALL
    SELECT '14:30:00'
) g;

SET @v = (SELECT malichlamviec FROM LICH_LAM_VIEC WHERE manguoidung = 2 AND thoigianbatdau = TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -2 DAY), '08:00:00'));
INSERT INTO LICH_KHAM (manguoidung, malichlamviec, lydo, trangthai) VALUES (101, @v, 'Đau ngực khi gắng sức', 'da_kham');
SET @v = (SELECT malichlamviec FROM LICH_LAM_VIEC WHERE manguoidung = 2 AND thoigianbatdau = TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY), '08:00:00'));
INSERT INTO LICH_KHAM (manguoidung, malichlamviec, lydo, trangthai) VALUES (102, @v, 'Tái khám huyết áp', 'da_kham');

SET @v = (SELECT malichlamviec FROM LICH_LAM_VIEC WHERE manguoidung = 5 AND thoigianbatdau = TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '08:30:00'));
INSERT INTO LICH_KHAM (manguoidung, malichlamviec, lydo, trangthai) VALUES (104, @v, 'Sốt và đau họng ba ngày nay', 'cho_xac_nhan');
SET @v = (SELECT malichlamviec FROM LICH_LAM_VIEC WHERE manguoidung = 7 AND thoigianbatdau = TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '08:00:00'));
INSERT INTO LICH_KHAM (manguoidung, malichlamviec, lydo, trangthai) VALUES (108, @v, 'Ù tai kéo dài một tuần', 'cho_xac_nhan');
SET @v = (SELECT malichlamviec FROM LICH_LAM_VIEC WHERE manguoidung = 8 AND thoigianbatdau = TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00'));
INSERT INTO LICH_KHAM (manguoidung, malichlamviec, lydo, trangthai) VALUES (109, @v, 'Nổi mẩn ngứa ở tay', 'cho_xac_nhan');
SET @v = (SELECT malichlamviec FROM LICH_LAM_VIEC WHERE manguoidung = 7 AND thoigianbatdau = TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '14:30:00'));
INSERT INTO LICH_KHAM (manguoidung, malichlamviec, lydo, trangthai) VALUES (112, @v, 'Đau vai phải sau khi mang nặng', 'cho_xac_nhan');

SET @v = (SELECT malichlamviec FROM LICH_LAM_VIEC WHERE manguoidung = 3 AND thoigianbatdau = TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00'));
INSERT INTO LICH_KHAM (manguoidung, malichlamviec, lydo, trangthai) VALUES (103, @v, 'Đau dạ dày sau khi ăn', 'da_xac_nhan');
SET @v = (SELECT malichlamviec FROM LICH_LAM_VIEC WHERE manguoidung = 6 AND thoigianbatdau = TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00'));
INSERT INTO LICH_KHAM (manguoidung, malichlamviec, lydo, trangthai) VALUES (107, @v, 'Khám thai định kỳ tuần 28', 'da_xac_nhan');
SET @v = (SELECT malichlamviec FROM LICH_LAM_VIEC WHERE manguoidung = 5 AND thoigianbatdau = TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '08:30:00'));
INSERT INTO LICH_KHAM (manguoidung, malichlamviec, lydo, trangthai) VALUES (111, @v, 'Khám sức khoẻ cho bé 3 tuổi', 'da_xac_nhan');

SET @v = (SELECT malichlamviec FROM LICH_LAM_VIEC WHERE manguoidung = 6 AND thoigianbatdau = TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00'));
INSERT INTO LICH_KHAM (manguoidung, malichlamviec, lydo, trangthai, lydohuy, nguoihuy) VALUES (105, @v, 'Đau lưng', 'da_huy', 'Bận việc đột xuất', 105);
INSERT INTO LICH_KHAM (manguoidung, malichlamviec, lydo, trangthai) VALUES (106, @v, 'Tư vấn sức khoẻ sinh sản', 'cho_xac_nhan');
SET @v = (SELECT malichlamviec FROM LICH_LAM_VIEC WHERE manguoidung = 4 AND thoigianbatdau = TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00:00'));
INSERT INTO LICH_KHAM (manguoidung, malichlamviec, lydo, trangthai, lydohuy, nguoihuy) VALUES (110, @v, 'Đau khớp gối', 'da_huy', 'Đổi sang bệnh viện khác', 110);

SET @v = (SELECT malichkham FROM LICH_KHAM WHERE manguoidung = 101 AND trangthai = 'da_kham' LIMIT 1);
INSERT INTO HO_SO_BENH_NHAN (malichkham, chuandoan, loidan) VALUES (@v, 'Tăng huyết áp độ 1, theo dõi bệnh mạch vành', 'Uống thuốc đều mỗi sáng, hạn chế muối, tái khám sau 2 tuần.');
SET @v = (SELECT malichkham FROM LICH_KHAM WHERE manguoidung = 102 AND trangthai = 'da_kham' LIMIT 1);
INSERT INTO HO_SO_BENH_NHAN (malichkham, chuandoan, loidan) VALUES (@v, 'Huyết áp ổn định sau điều chỉnh thuốc', 'Tiếp tục liều hiện tại, đo huyết áp tại nhà mỗi ngày.');

SELECT
    (SELECT COUNT(*) FROM LICH_LAM_VIEC WHERE trangthai = 'da_dat') AS khung_gio_da_dat,
    (SELECT COUNT(DISTINCT malichlamviec) FROM LICH_KHAM
        WHERE trangthai <> 'da_huy')                                AS khung_gio_co_lich,
    (SELECT COUNT(*) FROM LICH_KHAM)                                AS tong_lich_kham;
