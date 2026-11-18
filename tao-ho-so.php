<?php
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Helpers/csrf.php';
require_once __DIR__ . '/app/Helpers/HoSoValidator.php';
require_once __DIR__ . '/app/Models/NguoiDung.php';
require_once __DIR__ . '/app/Controllers/HoSoController.php';

$nguoiDung = new NguoiDung($conn);
$hoSoController = new HoSoController($nguoiDung);
$viewData = $hoSoController->hienThi();

require __DIR__ . '/app/Views/profile/edit.php';
