<?php
//Hàm dùng chung cho ba vai trò: định dạng, kiểm tra dữ liệu, phản hồi AJAX.

if (!function_exists('h')) {
    //Hiện dữ liệu, ngăn XSS.
    function h($s): string
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('hop_le_ngay')) {
    //Kiểm tra chuỗi đúng định dạng Y-m-d và là ngày có thật.
    function hop_le_ngay(string $ngay): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $ngay);

        return $d && $d->format('Y-m-d') === $ngay;
    }
}

if (!function_exists('hop_le_gio')) {
    function hop_le_gio(string $gio): bool
    {
        return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $gio);
    }
}

if (!function_exists('hop_le_sdt')) {
    //Kiểm tra SDT
    function hop_le_sdt(string $sdt): bool
    {
        return (bool) preg_match('/^0\d{9}$/', $sdt);
    }
}

if (!function_exists('gio_sang_phut')) {
    function gio_sang_phut(string $gio): int
    {
        [$h, $m] = array_map('intval', explode(':', $gio));

        return $h * 60 + $m;
    }
}

if (!function_exists('phut_sang_gio')) {
    function phut_sang_gio(int $tongPhut): string
    {
        return sprintf('%02d:%02d', intdiv($tongPhut, 60), $tongPhut % 60);
    }
}

if (!function_exists('thoi_diem_chan_dat_lich')) {
    //Mốc sớm nhất còn được đặt lịch: bây giờ + PHUT_CHAN_DAT_TRUOC (tính theo giờ PHP).
    function thoi_diem_chan_dat_lich(): string
    {
        return date('Y-m-d H:i:s', time() + PHUT_CHAN_DAT_TRUOC * 60);
    }
}

if (!function_exists('dinh_dang_ngay')) {
    function dinh_dang_ngay(string $ngay): string
    {
        $d = DateTime::createFromFormat('Y-m-d', substr($ngay, 0, 10));

        return $d ? $d->format('d/m/Y') : $ngay;
    }
}

if (!function_exists('dinh_dang_khung_gio')) {
    function dinh_dang_khung_gio(string $batDau, string $ketThuc): string
    {
        return substr($batDau, 0, 5) . ' - ' . substr($ketThuc, 0, 5);
    }
}

if (!function_exists('ghep_ngay_gio')) {
    //Ghép ngày giờ theo chuẩn DATETIME của MySQL.
    function ghep_ngay_gio(string $ngay, string $gio, string $giay = ':00'): string
    {
        return $ngay . ' ' . $gio . $giay;
    }
}

if (!function_exists('nhan_trang_thai')) {
    //Nhãn hiển thị và lớp CSS cho một trạng thái.
    function nhan_trang_thai(string $tt): array
    {
        $map = [
            LK_CHO_XAC_NHAN => ['Chờ xác nhận', 'n-vang'],
            LK_DA_XAC_NHAN  => ['Đã xác nhận', 'n-xanh'],
            LK_DA_KHAM      => ['Đã khám', 'n-luc'],
            LK_DA_HUY       => ['Đã huỷ', 'n-do'],
            LLV_CON_TRONG   => ['Còn trống', 'n-luc'],
            LLV_DA_DAT      => ['Đã đặt', 'n-xam'],
            TK_HOAT_DONG    => ['Hoạt động', 'n-luc'],
            TK_BI_KHOA      => ['Bị khoá', 'n-do'],
        ];

        return $map[$tt] ?? [$tt, 'n-xam'];
    }
}

if (!function_exists('ket_qua')) {
    //Cấu trúc trả về của controller: ok / msg / data.
    function ket_qua(bool $ok, string $msg = '', $data = null): array
    {
        return ['ok' => $ok, 'msg' => $msg, 'data' => $data];
    }
}

if (!function_exists('tra_json')) {
    function tra_json($ketQuaGoiHam = [], int $status = 200): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($ketQuaGoiHam, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }
}

if (!function_exists('doc_du_lieu_gui')) {
    //Dữ liệu client gửi lên: JSON body (fetch) hoặc form POST.
    function doc_du_lieu_gui(): array
    {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw !== false && $raw !== '' ? $raw : '', true);

        return is_array($json) ? $json : $_POST;
    }
}

if (!function_exists('bat_buoc_ajax')) {
    //Hàm xử lý AJAX quên trả JSON thì báo lỗi,
    //không thì in cả trang HTML.
    function bat_buoc_ajax(string $thongDiep = 'Yêu cầu AJAX không được xử lý.'): void
    {
        tra_json(ket_qua(false, $thongDiep), 400);
    }
}

if (!function_exists('chung_chuoi')) {
    //Chuỗi đã trim hoặc '' nếu giá trị không phải chuỗi.
    function chung_chuoi($value): string
    {
        return is_string($value) ? trim($value) : '';
    }
}

if (!function_exists('chung_so_duong')) {
    //Số nguyên dương; null nếu không hợp lệ.
    function chung_so_duong($value): ?int
    {
        if (is_int($value) && $value > 0) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value) && (int) $value > 0) {
            return (int) $value;
        }

        return null;
    }
}

if (!function_exists('chung_so_nguyen')) {
    function chung_so_nguyen($value): int
    {
        $so = chung_so_duong($value);

        return $so === null ? 0 : $so;
    }
}

if (!function_exists('chung_do_dai')) {
    //Độ dài theo ký tự (không phải byte), khớp VARCHAR(n) và maxlength.
    function chung_do_dai($value): int
    {
        return function_exists('mb_strlen')
            ? mb_strlen((string) $value, 'UTF-8')
            : strlen((string) $value);
    }
}

if (!function_exists('chung_dat_flash')) {
    //Ghi lời nhắc để hiện ở lần tải trang kế tiếp.
    function chung_dat_flash($key, $message, $type, $extra = []): void
    {
        $_SESSION[$key] = array_merge(
            [
                'message' => $message,
                'type' => $type,
            ],
            $extra
        );
    }
}

if (!function_exists('chung_doc_flash')) {
    //Đọc lời nhắc đã ghi rồi xoá để không hiện lại lần sau.
    function chung_doc_flash($key): array
    {
        $flash = $_SESSION[$key] ?? [];
        unset($_SESSION[$key]);

        return is_array($flash) ? $flash : [];
    }
}

if (!function_exists('render_fragment')) {
    //Render một view/partial thành chuỗi.
    function render_fragment($__file, array $__vars = []): string
    {
        extract($__vars, EXTR_SKIP);

        ob_start();
        require $__file;

        return (string) ob_get_clean();
    }
}
