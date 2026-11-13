<?php
session_start();

require_once __DIR__ . '/include/mysqlConnect.php';
require_once __DIR__ . '/app/Models/NguoiDung.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';

$nguoiDung = new NguoiDung($conn);
$authController = new AuthController($nguoiDung);
$viewData = $authController->dangNhap();

require __DIR__ . '/app/Views/auth/login.php';
