<?php

// Validate dung chung cho form ho so (tao-ho-so.php) va trang quan ly tai khoan.
// Moi method tra ve chuoi loi; chuoi rong nghia la hop le.

class HoSoValidator
{
    /**
     * Validate bo truong ho so bat buoc. Giu nguyen message cua HoSoController cu.
     *
     * @return string
     */
    public static function validateHoSo($ngaysinh, $gioitinh, $email, $diachi, $bhyt)
    {
        if ($ngaysinh === '' || $gioitinh === '' || $diachi === '' || $bhyt === '') {
            return 'Vui lòng bổ sung những thông tin bắt buộc.';
        }

        $date = DateTime::createFromFormat('!Y-m-d', $ngaysinh);
        $isValidDate = $date && $date->format('Y-m-d') === $ngaysinh && $ngaysinh <= date('Y-m-d');

        if (!$isValidDate) {
            return 'Ngày sinh không hợp lệ.';
        }

        if ($gioitinh !== 'nam' && $gioitinh !== 'nu') {
            return 'Vui lòng chọn giới tính hợp lệ.';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Địa chỉ email không hợp lệ.';
        }

        // Dem theo ky tu (khong phai byte) cho khop VARCHAR(n) va maxlength cua form:
        // chu co dau chiem 2-3 byte nen strlen() bao "qua dai" sai.
        if (self::mbLength($email) > 100) {
            return 'Email không được dài quá 100 ký tự.';
        }

        if (self::mbLength($diachi) > 255) {
            return 'Địa chỉ không được dài quá 255 ký tự.';
        }

        if (self::mbLength($bhyt) > 20) {
            return 'Số BHYT không được dài quá 20 ký tự.';
        }

        return '';
    }

    /**
     * @return string
     */
    public static function validateHoTen($hoten)
    {
        if ($hoten === '') {
            return 'Vui lòng nhập họ và tên.';
        }

        if (self::mbLength($hoten) > 100) {
            return 'Họ và tên không được dài quá 100 ký tự.';
        }

        return '';
    }

    /**
     * CCCD va dan toc la tuy chon: chi kiem tra khi co nhap.
     *
     * @return string
     */
    public static function validateTruongMoRong($cccd, $dantoc)
    {
        if ($cccd !== '' && !preg_match('/^\d{12}$/', $cccd)) {
            return 'Số CCCD phải gồm đúng 12 chữ số.';
        }

        if (self::mbLength($dantoc) > 15) {
            return 'Dân tộc không được dài quá 15 ký tự.';
        }
        return '';
    }

    /**
     * @return string
     */
    public static function validateMatKhauMoi($matKhauMoi)
    {
        $length = strlen($matKhauMoi);

        if ($length < 8) {
            return 'Mật khẩu mới phải có ít nhất 8 ký tự.';
        }

        if ($length > 72) {
            return 'Mật khẩu mới không được dài quá 72 ký tự.';
        }

        $soLoai = 0;
        if (preg_match('/[a-z]/', $matKhauMoi)) {
            $soLoai++;
        }
        if (preg_match('/[A-Z]/', $matKhauMoi)) {
            $soLoai++;
        }
        if (preg_match('/\d/', $matKhauMoi)) {
            $soLoai++;
        }
        if (preg_match('/[^a-zA-Z0-9]/', $matKhauMoi)) {
            $soLoai++;
        }

        if ($soLoai < 2) {
            return 'Mật khẩu mới cần có ít nhất 2 loại ký tự (chữ thường, chữ hoa, số, ký tự đặc biệt).';
        }

        return '';
    }

    private static function mbLength($value)
    {
        return function_exists('mb_strlen')
            ? mb_strlen((string) $value, 'UTF-8')
            : strlen((string) $value);
    }
}
