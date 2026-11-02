USE dat_lich_kham;

-- Xóa hồ sơ bệnh nhân của tài khoản này (quản trị viên không phải bệnh nhân)
DELETE FROM BENH_NHAN
WHERE manguoidung = (SELECT manguoidung FROM NGUOI_DUNG WHERE sdt = '0123456789');

-- Đổi vai trò sang quan_tri (mavaitro = 3 trong bảng VAI_TRO)
UPDATE NGUOI_DUNG SET mavaitro = 3 WHERE sdt = '0123456789';