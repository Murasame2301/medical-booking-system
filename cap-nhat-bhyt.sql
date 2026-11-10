-- Cập nhật database hiện tại để lưu số BHYT trong hồ sơ bệnh nhân.
-- Chạy câu lệnh này một lần sau khi đã USE dat_lich_kham;

USE dat_lich_kham;

ALTER TABLE HO_SO_BENH
    ADD COLUMN sobhyt VARCHAR(20) NULL AFTER manguoidung;
