<?php
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Helpers/csrf.php';
require_once __DIR__ . '/app/Helpers/HoSoValidator.php';
require_once __DIR__ . '/app/Models/NguoiDung.php';
require_once __DIR__ . '/app/Controllers/TaiKhoanController.php';

$nguoiDung = new NguoiDung($conn);
$taiKhoanController = new TaiKhoanController($nguoiDung);
$viewData = $taiKhoanController->hienThi();

require __DIR__ . '/app/Views/account/index.php';
