<?php
// Số điện thoại hợp lệ: đúng 10 chữ số, bắt đầu bằng 0
function sdt_hop_le($sdt) {
    return preg_match('/^0[0-9]{9}$/', $sdt) === 1;
}

// Chuỗi rỗng đổi thành NULL (để cột trong DB lưu "không có" thay vì chuỗi rỗng)
function null_neu_rong($giatri) {
    return $giatri === '' ? null : $giatri;
}